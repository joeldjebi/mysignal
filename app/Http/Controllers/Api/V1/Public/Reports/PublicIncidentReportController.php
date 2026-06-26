<?php

namespace App\Http\Controllers\Api\V1\Public\Reports;

use App\Domain\Payments\Actions\InitiateDamageDeclarationFineoPaymentAction;
use App\Domain\Payments\Actions\InitiateIncidentReportFineoPaymentAction;
use App\Domain\Payments\Enums\PaymentStatus;
use App\Domain\Reports\Actions\CreateIncidentReportAction;
use App\Domain\Reports\Enums\IncidentReportStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Public\Reports\StoreIncidentReportDamageRequest;
use App\Http\Requests\Api\V1\Public\Reports\StoreIncidentReportRequest;
use App\Http\Requests\Api\V1\Public\Reports\UpdateIncidentReportDamageRequest;
use App\Http\Resources\Api\V1\Public\Payments\IncidentReportPaymentSessionResource;
use App\Http\Resources\Api\V1\Public\Reports\IncidentReportDamageResource;
use App\Http\Resources\Api\V1\Public\Reports\IncidentReportResource;
use App\Models\IncidentReport;
use App\Models\PurchaseReceipt;
use App\Services\Notifications\IncidentReportNotificationService;
use App\Services\WasabiService;
use App\Support\Api\ApiResponse;
use App\Support\Audit\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

class PublicIncidentReportController extends Controller
{
    public function __construct(private readonly WasabiService $wasabiService) {}

    public function index(Request $request)
    {
        $reports = IncidentReport::query()
            ->with(['application', 'organization', 'meter.organization', 'country', 'city', 'commune', 'purchaseReceipt', 'payments.pricingRule'])
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

    public function storeTest(
        StoreIncidentReportRequest $request,
        CreateIncidentReportAction $action,
        ActivityLogger $activityLogger,
        IncidentReportNotificationService $notificationService
    ) {
        abort_unless((bool) config('services.public_reports.test_endpoint_enabled'), 403, 'L API de test des signalements est desactivee.');

        $attributes = $request->validated();
        unset($attributes['signal_attachment']);

        $report = $action->handle(
            $request->user('public_api'),
            $attributes,
            $request->file('signal_attachment')
        );

        $report->update([
            'payment_status' => PaymentStatus::Paid->value,
            'paid_at' => now(),
        ]);

        $report->load(['application', 'organization', 'meter.organization', 'country', 'city', 'commune', 'purchaseReceipt', 'payments.pricingRule']);

        $activityLogger->log(
            'public.report.created_test_without_payment',
            'Creation d un signalement public via l API de test sans paiement FineoPay.',
            $report,
            [
                'reference' => $report->reference,
                'status' => $report->status,
                'payment_status' => $report->payment_status,
                'application_id' => $report->application_id,
                'organization_id' => $report->organization_id,
                'signal_code' => $report->signal_code,
                'signal_label' => $report->signal_label,
            ],
            $request,
            $report->publicUser,
            'public',
        );

        $notificationService->notifyInstitutionReportCreated($report);
        $notificationService->notifyCommunityReportCreated($report);

        return ApiResponse::success([
            'report' => new IncidentReportResource($report),
            'payment_bypassed' => true,
        ], 'Signalement de test cree avec succes sans paiement.', 201);
    }

    public function show(Request $request, IncidentReport $report)
    {
        abort_unless((int) $report->public_user_id === (int) $request->user('public_api')->id, 404);
        $report->load(['application', 'organization', 'meter.organization', 'country', 'city', 'commune', 'purchaseReceipt', 'payments.pricingRule']);

        return ApiResponse::success([
            'report' => new IncidentReportResource($report),
        ]);
    }

    public function damages(Request $request)
    {
        $damages = IncidentReport::query()
            ->with(['application', 'organization', 'purchaseReceipt', 'reparationCase'])
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
        abort_unless($report->status !== IncidentReportStatus::Rejected->value, 422, 'Ce signalement ne peut pas etre confirme car il a ete rejete.');
        abort_unless($report->resolution_confirmation_status !== 'confirmed', 422, 'La resolution de ce signalement a deja ete confirmee.');

        $isValidatedByAi = $report->status === IncidentReportStatus::Resolved->value && $report->resolved_at !== null;

        $report->update([
            'status' => IncidentReportStatus::Resolved->value,
            'resolution_confirmation_status' => 'confirmed',
            'resolution_confirmed_at' => now(),
            'resolution_confirmed_without_ai_validation' => ! $isValidatedByAi,
        ]);

        $report->load(['application', 'organization', 'meter.organization', 'country', 'city', 'commune', 'purchaseReceipt', 'payments.pricingRule']);

        $activityLogger->log(
            'public.report.resolution_confirmed',
            'Confirmation de resolution d un signalement par l usager.',
            $report,
            [
                'reference' => $report->reference,
                'status' => $report->status,
                'resolution_confirmation_status' => $report->resolution_confirmation_status,
                'resolution_confirmed_without_ai_validation' => $report->resolution_confirmed_without_ai_validation,
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

    public function updateDamage(UpdateIncidentReportDamageRequest $request, IncidentReport $report, ActivityLogger $activityLogger)
    {
        abort_unless((int) $report->public_user_id === (int) $request->user('public_api')->id, 404);

        if ($report->damage_declared_at === null) {
            throw ValidationException::withMessages([
                'report_id' => ['Aucun dommage n est encore enregistre pour ce signalement.'],
            ]);
        }

        $attributes = $request->validated();
        $purchaseReceipt = $this->resolveDamagePurchaseReceipt($request, $attributes);
        $payload = [];

        foreach (['damage_summary', 'damage_amount_estimated', 'damage_notes'] as $field) {
            if (array_key_exists($field, $attributes)) {
                $payload[$field] = $attributes[$field];
            }
        }

        if ($purchaseReceipt instanceof PurchaseReceipt) {
            $payload['purchase_receipt_id'] = $purchaseReceipt->id;
        } elseif (array_key_exists('purchase_receipt_id', $attributes)) {
            $payload['purchase_receipt_id'] = null;
        }

        if ($payload !== []) {
            $report->update($payload);
        }

        $report->load(['application', 'organization', 'meter.organization', 'country', 'city', 'commune', 'purchaseReceipt', 'payments.pricingRule']);

        $activityLogger->log(
            'public.report.damage_updated',
            'Mise a jour d un dommage par l usager.',
            $report,
            [
                'reference' => $report->reference,
                'damage_amount_estimated' => $report->damage_amount_estimated,
                'purchase_receipt_id' => $report->purchase_receipt_id,
            ],
            $request
        );

        return ApiResponse::success([
            'report' => new IncidentReportResource($report),
            'damage' => new IncidentReportDamageResource($report),
        ], 'Dommage mis a jour avec succes.');
    }

    private function initiateDamagePayment(StoreIncidentReportDamageRequest $request, IncidentReport $report, InitiateDamageDeclarationFineoPaymentAction $action, ActivityLogger $activityLogger)
    {
        $attributes = $request->validated();
        $damageAttachmentFile = $request->file('damage_attachment');
        $purchaseReceipt = $this->resolveDamagePurchaseReceipt($request, $attributes);

        if (! $damageAttachmentFile) {
            throw ValidationException::withMessages([
                'damage_attachment' => ['Le justificatif du dommage est requis.'],
            ]);
        }

        if ($purchaseReceipt instanceof PurchaseReceipt) {
            $attributes['purchase_receipt_id'] = $purchaseReceipt->id;
        }

        unset($attributes['receipt_material_name'], $attributes['receipt_purchase_date'], $attributes['receipt_amount'], $attributes['receipt_attachment']);

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

    private function resolveDamagePurchaseReceipt(Request $request, array $attributes): ?PurchaseReceipt
    {
        $user = $request->user('public_api');

        if (! empty($attributes['purchase_receipt_id'])) {
            $receipt = $user->purchaseReceipts()
                ->whereKey($attributes['purchase_receipt_id'])
                ->first();

            if ($receipt === null) {
                throw ValidationException::withMessages([
                    'purchase_receipt_id' => ['Le recu selectionne est introuvable pour cet usager.'],
                ]);
            }

            return $receipt;
        }

        $hasInlineReceipt = filled($attributes['receipt_material_name'] ?? null)
            || filled($attributes['receipt_purchase_date'] ?? null)
            || filled($attributes['receipt_amount'] ?? null)
            || $request->hasFile('receipt_attachment');

        if (! $hasInlineReceipt) {
            return null;
        }

        $receiptAttributes = [
            'material_name' => $attributes['receipt_material_name'],
            'purchase_date' => $attributes['receipt_purchase_date'],
            'amount' => $attributes['receipt_amount'],
        ];

        if ($request->hasFile('receipt_attachment')) {
            $receiptAttributes['attachment'] = $this->storeReceiptFile($request->file('receipt_attachment'), (string) $user->id);
        }

        return $user->purchaseReceipts()->create($receiptAttributes);
    }

    private function storeReceiptFile(UploadedFile $file, string $userId): array
    {
        $path = $this->wasabiService->uploadFile(
            $file,
            config('wasabi.purchase_receipt_directory', 'purchase-receipts').'/'.$userId,
            'receipt'
        );

        if (! $path) {
            throw ValidationException::withMessages([
                'receipt_attachment' => ['Impossible de televerser le fichier du recu sur le stockage distant.'],
            ]);
        }

        return [
            'name' => $file->getClientOriginalName() ?: 'recu-achat',
            'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
            'size' => $file->getSize(),
            'path' => $path,
        ];
    }
}
