<?php

namespace App\Http\Controllers\Api\V1\Public\Reports;

use App\Domain\Reports\Actions\CreateIncidentReportAction;
use App\Domain\Reports\Enums\IncidentReportStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Public\Reports\StoreIncidentReportDamageRequest;
use App\Http\Requests\Api\V1\Public\Reports\StoreIncidentReportRequest;
use App\Http\Resources\Api\V1\Public\Reports\IncidentReportResource;
use App\Models\IncidentReport;
use App\Services\WasabiService;
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

    public function store(StoreIncidentReportRequest $request, CreateIncidentReportAction $action, ActivityLogger $activityLogger)
    {
        $report = $action->handle($request->user('public_api'), $request->validated());
        $report->load(['application', 'organization', 'meter.organization', 'country', 'city', 'commune', 'payments.pricingRule']);

        $activityLogger->log(
            'public.report.created',
            'Creation d un signalement public.',
            $report,
            [
                'reference' => $report->reference,
                'status' => $report->status,
                'application_id' => $report->application_id,
                'organization_id' => $report->organization_id,
                'signal_code' => $report->signal_code,
                'signal_label' => $report->signal_label,
            ],
            $request
        );

        return ApiResponse::success([
            'report' => new IncidentReportResource($report),
        ], 'Signalement enregistre avec succes.', 201);
    }

    public function show(Request $request, IncidentReport $report)
    {
        abort_unless((int) $report->public_user_id === (int) $request->user('public_api')->id, 404);
        $report->load(['application', 'organization', 'meter.organization', 'country', 'city', 'commune', 'payments.pricingRule']);

        return ApiResponse::success([
            'report' => new IncidentReportResource($report),
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

    public function storeDamage(StoreIncidentReportDamageRequest $request, IncidentReport $report, WasabiService $wasabiService, ActivityLogger $activityLogger)
    {
        abort_unless((int) $report->public_user_id === (int) $request->user('public_api')->id, 404);
        abort_unless($report->resolution_confirmation_status === 'confirmed', 422, 'Confirmez d abord la resolution du signalement avant d enregistrer un dommage.');
        abort_unless($report->damage_declared_at === null, 422, 'Le dommage pour ce signalement a deja ete enregistre.');
        abort_unless($report->resolution_confirmed_at !== null, 422, 'La date de confirmation de resolution est introuvable pour ce signalement.');
        abort_unless(now()->lessThanOrEqualTo($report->resolution_confirmed_at->copy()->addDay()), 422, 'Le delai de 24h pour declarer un dommage apres confirmation de resolution est depasse.');

        $attributes = $request->validated();

        $damageAttachment = $attributes['damage_attachment'] ?? null;

        if (is_array($damageAttachment) && ! empty($damageAttachment['data_url'])) {
            $path = $wasabiService->uploadDataUrl(
                (string) $damageAttachment['data_url'],
                config('wasabi.report_damage_directory', 'reports/damages').'/'.$report->reference,
                'damage',
                $damageAttachment['name'] ?? null,
            );

            if (! $path) {
                throw ValidationException::withMessages([
                    'damage_attachment' => ['Impossible de televerser le justificatif sur le stockage distant.'],
                ]);
            }

            $damageAttachment = [
                'name' => $damageAttachment['name'] ?? 'justificatif-dommage',
                'mime_type' => $damageAttachment['mime_type'] ?? 'application/octet-stream',
                'path' => $path,
            ];
        }

        $report->update([
            'damage_summary' => $attributes['damage_summary'],
            'damage_amount_estimated' => $attributes['damage_amount_estimated'] ?? null,
            'damage_notes' => $attributes['damage_notes'] ?? null,
            'damage_attachment' => $damageAttachment,
            'damage_declared_at' => now(),
            'damage_resolution_status' => 'submitted',
            'damage_resolution_notes' => null,
            'damage_resolved_at' => null,
        ]);

        $report->load(['application', 'organization', 'meter.organization', 'country', 'city', 'commune', 'payments.pricingRule']);

        $activityLogger->log(
            'public.report.damage_declared',
            'Declaration d un dommage sur un signalement.',
            $report,
            [
                'reference' => $report->reference,
                'damage_resolution_status' => $report->damage_resolution_status,
                'damage_amount_estimated' => $report->damage_amount_estimated,
                'has_damage_attachment' => filled($report->damage_attachment),
            ],
            $request
        );

        return ApiResponse::success([
            'report' => new IncidentReportResource($report),
        ], 'Le dommage a ete enregistre avec succes.');
    }
}
