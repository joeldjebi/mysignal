<?php

namespace App\Http\Controllers\Web\Institution;

use App\Domain\Reports\Enums\IncidentReportStatus;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Institution\Concerns\InteractsWithInstitutionContext;
use App\Models\Commune;
use App\Models\IncidentReport;
use App\Models\Meter;
use App\Services\Notifications\IncidentReportNotificationService;
use App\Support\Audit\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ReportController extends Controller
{
    use InteractsWithInstitutionContext;

    private const AUTOMATIC_RESOLUTION_RADIUS_METERS = 1000;

    public function index(): View
    {
        $context = $this->institutionContext();
        $query = $this->institutionReportsQuery($context['network_type'], $context['application_id'], $context['organization_id']);
        $canViewPaymentInfo = in_array('INSTITUTION_PAYMENT_INFO', $context['feature_codes'], true);

        if (filled(request('search'))) {
            $search = trim((string) request('search'));
            $query->where(function ($builder) use ($search): void {
                $builder->where('reference', 'like', '%'.$search.'%')
                    ->orWhere('signal_label', 'like', '%'.$search.'%')
                    ->orWhere('signal_code', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%');
            });
        }

        if ($canViewPaymentInfo && filled(request('payment_status'))) {
            $query->where('payment_status', request('payment_status'));
        }

        if (filled(request('status'))) {
            $query->where('status', request('status'));
        }

        if (filled(request('commune_id'))) {
            $query->where('commune_id', request('commune_id'));
        }

        if (filled(request('meter_id'))) {
            $query->where('meter_id', request('meter_id'));
        }

        return view('institution.reports.index', [
            'organization' => $context['organization'],
            'features' => $context['feature_codes'],
            'activeNav' => 'reports',
            'reports' => $query->latest()->paginate(15)->withQueryString(),
            'meters' => Meter::query()
                ->when($context['organization_id'] !== null, fn ($builder) => $builder->where('organization_id', $context['organization_id']))
                ->when($context['application_id'] !== null, fn ($builder) => $builder->where('application_id', $context['application_id']))
                ->when($context['network_type'] !== null, fn ($builder) => $builder->where('network_type', $context['network_type']))
                ->whereIn(
                    'id',
                    IncidentReport::query()
                        ->when($context['organization_id'] !== null, fn ($builder) => $builder->where('organization_id', $context['organization_id']))
                        ->when($context['application_id'] !== null, fn ($builder) => $builder->where('application_id', $context['application_id']))
                        ->when($context['network_type'] !== null, fn ($builder) => $builder->where('network_type', $context['network_type']))
                        ->whereNotNull('meter_id')
                        ->distinct()
                        ->select('meter_id')
                )
                ->orderBy('meter_number')
                ->orderBy('label')
                ->get(['id', 'meter_number', 'label']),
            'communes' => Commune::query()
                ->whereIn(
                    'id',
                    IncidentReport::query()
                        ->when($context['organization_id'] !== null, fn ($builder) => $builder->where('organization_id', $context['organization_id']))
                        ->when($context['application_id'] !== null, fn ($builder) => $builder->where('application_id', $context['application_id']))
                        ->when($context['network_type'] !== null, fn ($builder) => $builder->where('network_type', $context['network_type']))
                        ->whereNotNull('commune_id')
                        ->distinct()
                        ->select('commune_id')
                )
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }

    public function show(Request $request, IncidentReport $report): View
    {
        abort_unless($this->canManageReport($request, $report), 403);

        $context = $this->institutionContext();
        $report->load([
            'meter',
            'commune',
            'city',
            'country',
            'assignedTo',
            'publicUser.meters',
            'publicUser.ownedHousehold',
        ]);

        if (in_array('INSTITUTION_PAYMENT_INFO', $context['feature_codes'], true)) {
            $report->load('payments.pricingRule');
        }

        return view('institution.reports.show', [
            'organization' => $context['organization'],
            'features' => $context['feature_codes'],
            'activeNav' => 'reports',
            'report' => $report,
            'resolvedSignalPayload' => $report->resolvedSignalPayload(),
            'resolvedSignalAttachment' => $report->resolvedSignalAttachment(),
            'resolvedDamageAttachment' => $report->resolvedDamageAttachment(),
            'slaState' => $this->resolveSlaState($report),
        ]);
    }

    public function takeOver(Request $request, IncidentReport $report, ActivityLogger $activityLogger, IncidentReportNotificationService $notificationService): RedirectResponse
    {
        abort_unless($this->canManageReport($request, $report), 403);

        $report->update([
            'status' => IncidentReportStatus::InProgress->value,
            'assigned_to_user_id' => $request->user()->id,
            'taken_in_charge_at' => $report->taken_in_charge_at ?? now(),
        ]);

        $activityLogger->log(
            'institution.report.take_over',
            'Prise en charge d un signalement.',
            $report,
            [
                'report_reference' => $report->reference,
            ],
            $request,
            $request->user(),
            'institution',
        );

        $notificationService->notifyPublicReportAction(
            $report,
            'report_taken_over',
            'Signalement pris en charge',
            'Votre signalement '.$report->reference.' est maintenant pris en charge.',
        );

        return back()->with('success', 'Le signalement a ete pris en charge.');
    }

    public function resolve(Request $request, IncidentReport $report, ActivityLogger $activityLogger, IncidentReportNotificationService $notificationService): RedirectResponse
    {
        abort_unless($this->canManageReport($request, $report), 403);

        $attributes = $request->validate([
            'official_response' => ['required', 'string', 'max:2000'],
        ]);

        $report->update([
            'status' => IncidentReportStatus::Resolved->value,
            'assigned_to_user_id' => $request->user()->id,
            'taken_in_charge_at' => $report->taken_in_charge_at ?? now(),
            'resolved_at' => now(),
            'official_response' => $attributes['official_response'],
            'resolution_confirmation_status' => 'pending',
            'resolution_confirmed_at' => null,
        ]);

        $activityLogger->log(
            'institution.report.resolved',
            'Resolution d un signalement.',
            $report,
            [
                'report_reference' => $report->reference,
            ],
            $request,
            $request->user(),
            'institution',
        );

        $notificationService->notifyPublicReportAction(
            $report,
            'report_resolved',
            'Signalement resolu',
            'Votre signalement '.$report->reference.' a ete marque comme resolu.',
        );
        $notificationService->notifyCommunityReportResolved($report);

        $nearbyResolvedCount = $this->resolveNearbySimilarReports(
            $report,
            $attributes['official_response'],
            $request,
            $activityLogger,
            $notificationService,
        );

        $message = 'Le signalement a ete marque comme resolu.';

        if ($nearbyResolvedCount > 0) {
            $message .= ' '.$nearbyResolvedCount.' signalement(s) similaire(s) dans un rayon de 1 km ont aussi ete resolu(s).';
        }

        return back()->with('success', $message);
    }

    public function reject(Request $request, IncidentReport $report, ActivityLogger $activityLogger, IncidentReportNotificationService $notificationService): RedirectResponse
    {
        abort_unless($this->canManageReport($request, $report), 403);

        $attributes = $request->validate([
            'official_response' => ['required', 'string', 'max:2000'],
        ]);

        $report->update([
            'status' => IncidentReportStatus::Rejected->value,
            'assigned_to_user_id' => $request->user()->id,
            'taken_in_charge_at' => $report->taken_in_charge_at ?? now(),
            'resolved_at' => now(),
            'official_response' => $attributes['official_response'],
            'resolution_confirmation_status' => null,
            'resolution_confirmed_at' => null,
        ]);

        $activityLogger->log(
            'institution.report.rejected',
            'Rejet d un signalement.',
            $report,
            [
                'report_reference' => $report->reference,
            ],
            $request,
            $request->user(),
            'institution',
        );

        $notificationService->notifyPublicReportAction(
            $report,
            'report_rejected',
            'Signalement rejete',
            'Votre signalement '.$report->reference.' a ete rejete.',
        );

        return back()->with('success', 'Le signalement a ete rejete.');
    }

    public function updateDamageResolution(Request $request, IncidentReport $report, ActivityLogger $activityLogger, IncidentReportNotificationService $notificationService): RedirectResponse
    {
        abort_unless($this->canManageReport($request, $report), 403);
        abort_unless($report->damage_declared_at !== null, 422, 'Aucun dommage n a ete declare sur ce signalement.');

        $attributes = $request->validate([
            'damage_resolution_status' => ['required', 'in:submitted,in_progress,resolved,rejected'],
            'damage_resolution_notes' => ['nullable', 'string', 'max:3000'],
        ]);

        $report->update([
            'damage_resolution_status' => $attributes['damage_resolution_status'],
            'damage_resolution_notes' => $attributes['damage_resolution_notes'] ?? null,
            'damage_resolved_at' => in_array($attributes['damage_resolution_status'], ['resolved', 'rejected'], true) ? now() : null,
        ]);

        $activityLogger->log(
            'institution.damage_resolution.updated',
            'Mise a jour du statut de resolution d un dommage.',
            $report,
            [
                'report_reference' => $report->reference,
                'damage_resolution_status' => $report->damage_resolution_status,
            ],
            $request,
            $request->user(),
            'institution',
        );

        $notificationService->notifyPublicReportAction(
            $report,
            'damage_resolution_updated',
            'Dommage mis a jour',
            'Le statut du dommage lie au signalement '.$report->reference.' a ete mis a jour.',
        );

        return back()->with('success', 'Le statut de resolution du dommage a ete mis a jour.');
    }

    private function canManageReport(Request $request, IncidentReport $report): bool
    {
        $organization = $request->user()?->organization;
        $applicationId = $organization?->application_id;
        $organizationId = $organization?->id;

        if ($organizationId !== null && (int) $report->organization_id !== (int) $organizationId) {
            return false;
        }

        if ($applicationId !== null && (int) $report->application_id !== (int) $applicationId) {
            return false;
        }

        return true;
    }

    private function resolveNearbySimilarReports(
        IncidentReport $sourceReport,
        string $officialResponse,
        Request $request,
        ActivityLogger $activityLogger,
        IncidentReportNotificationService $notificationService,
    ): int {
        $nearbyReports = $this->nearbySimilarOpenReports($sourceReport);

        if ($nearbyReports->isEmpty()) {
            return 0;
        }

        $resolvedAt = now();

        $nearbyReports->each(function (IncidentReport $nearbyReport) use ($officialResponse, $request, $notificationService, $resolvedAt): void {
            $nearbyReport->update([
                'status' => IncidentReportStatus::Resolved->value,
                'assigned_to_user_id' => $request->user()->id,
                'taken_in_charge_at' => $nearbyReport->taken_in_charge_at ?? $resolvedAt,
                'resolved_at' => $resolvedAt,
                'official_response' => $officialResponse,
                'resolution_confirmation_status' => 'pending',
                'resolution_confirmed_at' => null,
            ]);

            $notificationService->notifyPublicReportAction(
                $nearbyReport,
                'report_resolved_nearby',
                'Signalement resolu',
                'Votre signalement '.$nearbyReport->reference.' a ete marque comme resolu car un probleme similaire a ete resolu dans votre zone.',
            );
        });

        $activityLogger->log(
            'institution.report.nearby_auto_resolved',
            'Resolution automatique des signalements similaires dans un rayon de 1 km.',
            $sourceReport,
            [
                'source_report_reference' => $sourceReport->reference,
                'radius_meters' => self::AUTOMATIC_RESOLUTION_RADIUS_METERS,
                'resolved_report_count' => $nearbyReports->count(),
                'resolved_report_ids' => $nearbyReports->pluck('id')->values()->all(),
                'signal_code' => $sourceReport->signal_code,
            ],
            $request,
            $request->user(),
            'institution',
        );

        return $nearbyReports->count();
    }

    /**
     * @return Collection<int, IncidentReport>
     */
    private function nearbySimilarOpenReports(IncidentReport $sourceReport): Collection
    {
        $coordinates = $this->reportCoordinates($sourceReport);

        if (
            $coordinates === null
            || blank($sourceReport->signal_code)
            || $sourceReport->application_id === null
            || $sourceReport->organization_id === null
        ) {
            return collect();
        }

        $latitude = $coordinates['latitude'];
        $longitude = $coordinates['longitude'];
        $deltaLatitude = self::AUTOMATIC_RESOLUTION_RADIUS_METERS / 111_320;
        $deltaLongitude = self::AUTOMATIC_RESOLUTION_RADIUS_METERS / max(1, abs(111_320 * cos(deg2rad($latitude))));

        return IncidentReport::query()
            ->whereKeyNot($sourceReport->id)
            ->where('application_id', $sourceReport->application_id)
            ->where('organization_id', $sourceReport->organization_id)
            ->where('signal_code', $sourceReport->signal_code)
            ->whereIn('status', [
                IncidentReportStatus::Submitted->value,
                IncidentReportStatus::InProgress->value,
            ])
            ->where(function ($query) use ($latitude, $longitude, $deltaLatitude, $deltaLongitude): void {
                $query
                    ->where(function ($reportQuery) use ($latitude, $longitude, $deltaLatitude, $deltaLongitude): void {
                        $reportQuery
                            ->whereNotNull('latitude')
                            ->whereNotNull('longitude')
                            ->whereBetween('latitude', [$latitude - $deltaLatitude, $latitude + $deltaLatitude])
                            ->whereBetween('longitude', [$longitude - $deltaLongitude, $longitude + $deltaLongitude]);
                    })
                    ->orWhereHas('meter', function ($meterQuery) use ($latitude, $longitude, $deltaLatitude, $deltaLongitude): void {
                        $meterQuery
                            ->whereNotNull('latitude')
                            ->whereNotNull('longitude')
                            ->whereBetween('latitude', [$latitude - $deltaLatitude, $latitude + $deltaLatitude])
                            ->whereBetween('longitude', [$longitude - $deltaLongitude, $longitude + $deltaLongitude]);
                    });
            })
            ->when($sourceReport->network_type !== null, fn ($query) => $query->where('network_type', $sourceReport->network_type))
            ->with(['publicUser', 'meter'])
            ->get()
            ->filter(function (IncidentReport $report) use ($latitude, $longitude): bool {
                $reportCoordinates = $this->reportCoordinates($report);

                return $reportCoordinates !== null
                    && $this->distanceInMeters(
                        $latitude,
                        $longitude,
                        $reportCoordinates['latitude'],
                        $reportCoordinates['longitude'],
                    ) <= self::AUTOMATIC_RESOLUTION_RADIUS_METERS;
            })
            ->values();
    }

    private function reportCoordinates(IncidentReport $report): ?array
    {
        $report->loadMissing('meter');

        $latitude = $report->latitude ?? $report->meter?->latitude;
        $longitude = $report->longitude ?? $report->meter?->longitude;

        if ($latitude === null || $longitude === null) {
            return null;
        }

        return [
            'latitude' => (float) $latitude,
            'longitude' => (float) $longitude,
        ];
    }

    private function distanceInMeters(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6_371_000;
        $latDelta = deg2rad($lat2 - $lat1);
        $lngDelta = deg2rad($lng2 - $lng1);
        $a = sin($latDelta / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($lngDelta / 2) ** 2;

        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    private function resolveSlaState(IncidentReport $report): array
    {
        if (blank($report->target_sla_hours) || blank($report->created_at)) {
            return [
                'code' => 'unconfigured',
                'label' => 'Sans configuration TCM',
                'elapsed_hours' => null,
            ];
        }

        $endReference = $report->resolved_at ?? now();
        $elapsedHours = round($report->created_at->diffInMinutes($endReference) / 60, 1);
        $ratio = $report->target_sla_hours > 0 ? ($elapsedHours / $report->target_sla_hours) : 0;

        $label = match (true) {
            $ratio >= 1 => 'SLA depasse',
            $ratio >= 0.8 => 'SLA a risque',
            default => 'Dans le TCM',
        };

        $code = match (true) {
            $ratio >= 1 => 'breached',
            $ratio >= 0.8 => 'risk',
            default => 'within',
        };

        return [
            'code' => $code,
            'label' => $label,
            'elapsed_hours' => $elapsedHours,
        ];
    }
}
