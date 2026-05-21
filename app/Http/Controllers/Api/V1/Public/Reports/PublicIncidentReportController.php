<?php

namespace App\Http\Controllers\Api\V1\Public\Reports;

use App\Domain\Payments\Actions\InitiateDamageDeclarationFineoPaymentAction;
use App\Domain\Payments\Actions\InitiateIncidentReportFineoPaymentAction;
use App\Domain\Reports\Enums\IncidentReportStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Public\Reports\StoreIncidentReportDamageRequest;
use App\Http\Requests\Api\V1\Public\Reports\StoreIncidentReportRequest;
use App\Http\Resources\Api\V1\Public\Payments\IncidentReportPaymentSessionResource;
use App\Http\Resources\Api\V1\Public\Reports\IncidentReportDamageResource;
use App\Http\Resources\Api\V1\Public\Reports\IncidentReportResource;
use App\Models\IncidentReport;
use App\Support\Api\ApiResponse;
use App\Support\Audit\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PublicIncidentReportController extends Controller
{
    public function index(Request $request)
    {
        $reports = IncidentReport::query()
            ->with(['application', 'organization', 'meter.organization', 'country', 'city', 'commune', 'payments.pricingRule'])
            ->where('public_user_id', $request->user('public_api')->id)
            ->latest('id')
            ->get();

        return ApiResponse::success([
            'reports' => IncidentReportResource::collection($reports),
        ]);
    }

    public function store(StoreIncidentReportRequest $request, InitiateIncidentReportFineoPaymentAction $action, ActivityLogger $activityLogger)
    {
        $attributes = $request->validated();
        unset($attributes['signal_attachment']);

        $paymentSession = $action->handle(
            $request->user('public_api'),
            $attributes,
            $request->file('signal_attachment')
        );

        $activityLogger->log(
            'public.report.payment_session_created',
            'Initialisation du paiement FineoPay pour un signalement public.',
            $paymentSession,
            [
                'sync_ref' => $paymentSession->sync_ref,
                'status' => $paymentSession->status,
                'amount' => $paymentSession->amount,
                'currency' => $paymentSession->currency,
                'provider' => $paymentSession->provider,
            ],
            $request
        );

        return ApiResponse::success([
            'payment_session' => new IncidentReportPaymentSessionResource($paymentSession),
            'checkout_link' => $paymentSession->checkout_link,
        ], 'Lien de paiement genere avec succes. Le signalement sera enregistre apres paiement.', 201);
    }

    public function show(Request $request, IncidentReport $report)
    {
        abort_unless((int) $report->public_user_id === (int) $request->user('public_api')->id, 404);
        $report->load(['application', 'organization', 'meter.organization', 'country', 'city', 'commune', 'payments.pricingRule']);

        return ApiResponse::success([
            'report' => new IncidentReportResource($report),
        ]);
    }

    public function damages(Request $request)
    {
        $damages = IncidentReport::query()
            ->with(['application', 'organization', 'reparationCase'])
            ->where('public_user_id', $request->user('public_api')->id)
            ->whereNotNull('damage_declared_at')
            ->when($request->filled('resolution_status'), fn ($query) => $query->where('damage_resolution_status', (string) $request->string('resolution_status')))
            ->latest('damage_declared_at')
            ->latest('id')
            ->get();

        return ApiResponse::success([
            'damages' => IncidentReportDamageResource::collection($damages),
        ]);
    }

    public function confirmResolution(Request $request, IncidentReport $report, ActivityLogger $activityLogger)
    {
        abort_unless((int) $report->public_user_id === (int) $request->user('public_api')->id, 404);
        abort_unless($report->status === IncidentReportStatus::Resolved->value, 422, 'Ce signalement n est pas encore marque comme resolu.');
        abort_unless($report->resolution_confirmation_status !== 'confirmed', 422, 'La resolution de ce signalement a deja ete confirmee.');

        $report->update([
            'resolution_confirmation_status' => 'confirmed',
            'resolution_confirmed_at' => now(),
        ]);

        $report->load(['application', 'organization', 'meter.organization', 'country', 'city', 'commune', 'payments.pricingRule']);

        $activityLogger->log(
            'public.report.resolution_confirmed',
            'Confirmation de resolution d un signalement par l usager.',
            $report,
            [
                'reference' => $report->reference,
                'status' => $report->status,
                'resolution_confirmation_status' => $report->resolution_confirmation_status,
            ],
            $request
        );

        return ApiResponse::success([
            'report' => new IncidentReportResource($report),
        ], 'La resolution du signalement a ete confirmee.');
    }

    public function storeDamage(StoreIncidentReportDamageRequest $request, IncidentReport $report, InitiateDamageDeclarationFineoPaymentAction $action, ActivityLogger $activityLogger)
    {
        return $this->initiateDamagePayment($request, $report, $action, $activityLogger);
    }

    public function storeDamageFromBody(StoreIncidentReportDamageRequest $request, InitiateDamageDeclarationFineoPaymentAction $action, ActivityLogger $activityLogger)
    {
        $reportId = $request->validated('report_id');

        if (! $reportId) {
            throw ValidationException::withMessages([
                'report_id' => ['Le champ report_id est requis.'],
            ]);
        }

        $report = IncidentReport::query()->findOrFail($reportId);

        return $this->initiateDamagePayment($request, $report, $action, $activityLogger);
    }

    private function initiateDamagePayment(StoreIncidentReportDamageRequest $request, IncidentReport $report, InitiateDamageDeclarationFineoPaymentAction $action, ActivityLogger $activityLogger)
    {
        $attributes = $request->validated();
        $damageAttachmentFile = $request->file('damage_attachment');

        if (! $damageAttachmentFile) {
            throw ValidationException::withMessages([
                'damage_attachment' => ['Le justificatif du dommage est requis.'],
            ]);
        }

        $paymentSession = $action->handle(
            $request->user('public_api'),
            $report,
            $attributes,
            $damageAttachmentFile
        );

        $activityLogger->log(
            'public.damage.payment_session_created',
            'Initialisation du paiement FineoPay pour une declaration de dommage.',
            $paymentSession,
            [
                'sync_ref' => $paymentSession->sync_ref,
                'incident_report_id' => $paymentSession->incident_report_id,
                'status' => $paymentSession->status,
                'amount' => $paymentSession->amount,
                'currency' => $paymentSession->currency,
                'provider' => $paymentSession->provider,
            ],
            $request
        );

        return ApiResponse::success([
            'payment_session' => new IncidentReportPaymentSessionResource($paymentSession),
            'checkout_link' => $paymentSession->checkout_link,
        ], 'Lien de paiement genere avec succes. Le dommage sera enregistre apres paiement.', 201);
    }
}
