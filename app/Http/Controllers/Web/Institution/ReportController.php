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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ReportController extends Controller
{
    use InteractsWithInstitutionContext;

    public function index(): View
    {
        $context = $this->institutionContext();
        $query = $this->institutionReportsQuery($context['network_type'], $context['application_id'], $context['organization_id']);
        $canViewPaymentInfo = in_array('INSTITUTION_PAYMENT_INFO', $context['feature_codes'], true);
        $perPage = min(max((int) request()->integer('per_page', 15), 1), 100);

        $this->applyReportListFilters($query, request(), $canViewPaymentInfo);

        $groupingSurfaceSquareMeters = $this->reportGroupingSurfaceSquareMeters();
        $groupingSideMeters = $this->reportGroupingSideMeters($groupingSurfaceSquareMeters);
        $identifierGroups = $this->identifierReportGroups(clone $query, $groupingSideMeters);
        $selectedIdentifierGroup = $this->selectedIdentifierGroup($identifierGroups, (string) request('identifier_group'));

        if ($selectedIdentifierGroup !== null) {
            $this->applyIdentifierGroupFilter($query, $selectedIdentifierGroup['latitude_cell'], $selectedIdentifierGroup['longitude_cell'], $groupingSideMeters);
        }

        $statsQuery = clone $query;
        $reportsStats = [
            'total' => (clone $statsQuery)->count(),
            'submitted' => (clone $statsQuery)->where('incident_reports.status', IncidentReportStatus::Submitted->value)->count(),
            'in_progress' => (clone $statsQuery)->where('incident_reports.status', IncidentReportStatus::InProgress->value)->count(),
            'resolved' => (clone $statsQuery)->where('incident_reports.status', IncidentReportStatus::Resolved->value)->count(),
            'with_damage' => (clone $statsQuery)->whereNotNull('incident_reports.damage_declared_at')->count(),
        ];

        if ($canViewPaymentInfo) {
            $reportsStats['paid'] = (clone $statsQuery)->where('incident_reports.payment_status', 'paid')->count();
            $reportsStats['pending_payment'] = (clone $statsQuery)->where('incident_reports.payment_status', 'pending')->count();
        }

        $query->select('incident_reports.*');

        return view('institution.reports.index', [
            'organization' => $context['organization'],
            'features' => $context['feature_codes'],
            'activeNav' => 'reports',
            'reportsStats' => $reportsStats,
            'reports' => $query->latest('incident_reports.created_at')->paginate($perPage)->withQueryString(),
            'perPage' => $perPage,
            'identifierGroups' => $identifierGroups,
            'selectedIdentifierGroup' => $selectedIdentifierGroup,
            'groupingSurfaceSquareMeters' => $groupingSurfaceSquareMeters,
            'groupingSideMeters' => $groupingSideMeters,
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

        $wasConfirmedByPublicUser = $report->resolution_confirmation_status === 'confirmed';

        $report->update([
            'status' => IncidentReportStatus::Resolved->value,
            'assigned_to_user_id' => $request->user()->id,
            'taken_in_charge_at' => $report->taken_in_charge_at ?? now(),
            'resolved_at' => now(),
            'official_response' => $attributes['official_response'],
            'resolution_confirmation_status' => $wasConfirmedByPublicUser ? 'confirmed' : 'pending',
            'resolution_confirmed_at' => $wasConfirmedByPublicUser ? $report->resolution_confirmed_at : null,
            'resolution_confirmed_without_ai_validation' => false,
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

        return back()->with('success', 'Le signalement a été marqué comme résolu.');
    }

    public function resolveIdentifierGroup(Request $request, ActivityLogger $activityLogger, IncidentReportNotificationService $notificationService): RedirectResponse
    {
        $context = $this->institutionContext();
        $canViewPaymentInfo = in_array('INSTITUTION_PAYMENT_INFO', $context['feature_codes'], true);

        $attributes = $request->validate([
            'identifier_group' => ['required', 'string', 'max:80'],
            'official_response' => ['nullable', 'string', 'max:2000'],
        ]);

        $groupingSideMeters = $this->reportGroupingSideMeters($this->reportGroupingSurfaceSquareMeters());
        $groupCells = $this->parseIdentifierGroupKey($attributes['identifier_group']);

        abort_if($groupCells === null, 422, 'Le regroupement sélectionné est invalide.');

        $query = $this->institutionReportsQuery($context['network_type'], $context['application_id'], $context['organization_id']);
        $this->applyReportListFilters($query, $request, $canViewPaymentInfo, applyStatus: false);
        $this->applyIdentifierGroupFilter($query, $groupCells['latitude_cell'], $groupCells['longitude_cell'], $groupingSideMeters);

        $reports = $query
            ->select('incident_reports.*')
            ->whereIn('incident_reports.status', [
                IncidentReportStatus::Submitted->value,
                IncidentReportStatus::InProgress->value,
            ])
            ->get();

        if ($reports->isEmpty()) {
            return back()->withErrors([
                'identifier_group' => 'Aucun signalement ouvert n’est disponible dans ce regroupement.',
            ]);
        }

        $officialResponse = $attributes['official_response'] ?: 'Signalements résolus par l’institution pour cette zone.';
        $resolvedAt = now();

        $reports->each(function (IncidentReport $report) use ($officialResponse, $request, $notificationService, $resolvedAt): void {
            $wasConfirmedByPublicUser = $report->resolution_confirmation_status === 'confirmed';

            $report->update([
                'status' => IncidentReportStatus::Resolved->value,
                'assigned_to_user_id' => $request->user()->id,
                'taken_in_charge_at' => $report->taken_in_charge_at ?? $resolvedAt,
                'resolved_at' => $resolvedAt,
                'official_response' => $officialResponse,
                'resolution_confirmation_status' => $wasConfirmedByPublicUser ? 'confirmed' : 'pending',
                'resolution_confirmed_at' => $wasConfirmedByPublicUser ? $report->resolution_confirmed_at : null,
                'resolution_confirmed_without_ai_validation' => false,
            ]);

            $notificationService->notifyPublicReportAction(
                $report,
                'report_resolved_by_identifier_group',
                'Signalement résolu',
                'Votre signalement '.$report->reference.' a été marqué comme résolu par l’institution.',
            );
            $notificationService->notifyCommunityReportResolved($report);
        });

        $activityLogger->log(
            'institution.report.identifier_group_resolved',
            'Résolution groupée des signalements liés à des identifiants géolocalisés.',
            'incident_report_identifier_group',
            [
                'identifier_group' => $attributes['identifier_group'],
                'surface_square_meters' => $this->reportGroupingSurfaceSquareMeters(),
                'resolved_report_count' => $reports->count(),
                'resolved_report_ids' => $reports->pluck('id')->values()->all(),
            ],
            $request,
            $request->user(),
            'institution',
        );

        return redirect()
            ->route('institution.reports.index', $request->except(['page']))
            ->with('success', number_format($reports->count(), 0, ',', ' ').' signalement(s) résolu(s) dans ce regroupement.');
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

    private function applyReportListFilters(Builder $query, Request $request, bool $canViewPaymentInfo, bool $applyStatus = true): void
    {
        if (filled($request->input('search'))) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($builder) use ($search): void {
                $builder->where('incident_reports.reference', 'like', '%'.$search.'%')
                    ->orWhere('incident_reports.signal_label', 'like', '%'.$search.'%')
                    ->orWhere('incident_reports.signal_code', 'like', '%'.$search.'%')
                    ->orWhere('incident_reports.description', 'like', '%'.$search.'%');
            });
        }

        if ($canViewPaymentInfo && filled($request->input('payment_status'))) {
            $query->where('incident_reports.payment_status', $request->input('payment_status'));
        }

        if ($applyStatus && filled($request->input('status'))) {
            $query->where('incident_reports.status', $request->input('status'));
        }

        if (filled($request->input('commune_id'))) {
            $query->where('incident_reports.commune_id', $request->input('commune_id'));
        }

        if (filled($request->input('meter_id'))) {
            $query->where('incident_reports.meter_id', $request->input('meter_id'));
        }
    }

    private function identifierReportGroups(Builder $query, int $sideMeters): Collection
    {
        $meterAlias = 'grouping_meters';
        $latitudeExpression = $this->identifierLatitudeExpression($meterAlias);
        $longitudeExpression = $this->identifierLongitudeExpression($meterAlias);
        $latitudeCellExpression = $this->identifierLatitudeCellExpression($sideMeters, $meterAlias);
        $longitudeCellExpression = $this->identifierLongitudeCellExpression($sideMeters, $meterAlias);

        return $query
            ->leftJoin('meters as '.$meterAlias, $meterAlias.'.id', '=', 'incident_reports.meter_id')
            ->whereNotNull('incident_reports.meter_id')
            ->whereIn('incident_reports.status', [
                IncidentReportStatus::Submitted->value,
                IncidentReportStatus::InProgress->value,
            ])
            ->whereRaw($latitudeExpression.' IS NOT NULL')
            ->whereRaw($longitudeExpression.' IS NOT NULL')
            ->selectRaw($latitudeCellExpression.' as latitude_cell')
            ->selectRaw($longitudeCellExpression.' as longitude_cell')
            ->selectRaw('COUNT(*) as reports_count')
            ->selectRaw('MIN('.$latitudeExpression.') as min_latitude')
            ->selectRaw('MAX('.$latitudeExpression.') as max_latitude')
            ->selectRaw('MIN('.$longitudeExpression.') as min_longitude')
            ->selectRaw('MAX('.$longitudeExpression.') as max_longitude')
            ->selectRaw('MIN(NULLIF('.$meterAlias.'.commune, \'\')) as commune_name')
            ->groupByRaw($latitudeCellExpression.', '.$longitudeCellExpression)
            ->orderByDesc('reports_count')
            ->limit(30)
            ->get()
            ->map(function ($group, int $index): array {
                $latitudeCell = (int) $group->latitude_cell;
                $longitudeCell = (int) $group->longitude_cell;

                return [
                    'key' => $this->identifierGroupKey($latitudeCell, $longitudeCell),
                    'label' => 'Zone '.($index + 1),
                    'area_label' => $group->commune_name ?: 'Secteur géolocalisé',
                    'latitude_cell' => $latitudeCell,
                    'longitude_cell' => $longitudeCell,
                    'reports_count' => (int) $group->reports_count,
                    'open_reports_count' => (int) $group->reports_count,
                    'min_latitude' => $group->min_latitude,
                    'max_latitude' => $group->max_latitude,
                    'min_longitude' => $group->min_longitude,
                    'max_longitude' => $group->max_longitude,
                ];
            });
    }

    private function selectedIdentifierGroup(Collection $groups, string $groupKey): ?array
    {
        if (blank($groupKey)) {
            return null;
        }

        $selectedGroup = $groups->firstWhere('key', $groupKey);

        if ($selectedGroup !== null) {
            return $selectedGroup;
        }

        $cells = $this->parseIdentifierGroupKey($groupKey);

        return $cells === null
            ? null
            : [
                'key' => $groupKey,
                'label' => 'Zone sélectionnée',
                'area_label' => 'Secteur géolocalisé',
                'latitude_cell' => $cells['latitude_cell'],
                'longitude_cell' => $cells['longitude_cell'],
                'reports_count' => null,
                'open_reports_count' => null,
            ];
    }

    private function applyIdentifierGroupFilter(Builder $query, int $latitudeCell, int $longitudeCell, int $sideMeters): void
    {
        $meterAlias = 'selected_group_meters';
        $latitudeExpression = $this->identifierLatitudeExpression($meterAlias);
        $longitudeExpression = $this->identifierLongitudeExpression($meterAlias);
        $latitudeCellExpression = $this->identifierLatitudeCellExpression($sideMeters, $meterAlias);
        $longitudeCellExpression = $this->identifierLongitudeCellExpression($sideMeters, $meterAlias);

        $query
            ->leftJoin('meters as '.$meterAlias, $meterAlias.'.id', '=', 'incident_reports.meter_id')
            ->whereNotNull('incident_reports.meter_id')
            ->whereRaw($latitudeExpression.' IS NOT NULL')
            ->whereRaw($longitudeExpression.' IS NOT NULL')
            ->whereRaw($latitudeCellExpression.' = ?', [$latitudeCell])
            ->whereRaw($longitudeCellExpression.' = ?', [$longitudeCell]);
    }

    private function reportGroupingSurfaceSquareMeters(): int
    {
        return max(1, (int) config('app.report_identifier_group_surface_square_meters', 1000));
    }

    private function reportGroupingSideMeters(int $surfaceSquareMeters): int
    {
        return max(5, (int) round(sqrt($surfaceSquareMeters)));
    }

    private function identifierLatitudeExpression(string $meterAlias): string
    {
        return 'COALESCE(incident_reports.latitude, '.$meterAlias.'.latitude)';
    }

    private function identifierLongitudeExpression(string $meterAlias): string
    {
        return 'COALESCE(incident_reports.longitude, '.$meterAlias.'.longitude)';
    }

    private function identifierLatitudeCellExpression(int $sideMeters, string $meterAlias): string
    {
        $delta = number_format($sideMeters / 111_320, 12, '.', '');

        return 'FLOOR(CAST('.$this->identifierLatitudeExpression($meterAlias).' AS NUMERIC) / '.$delta.')';
    }

    private function identifierLongitudeCellExpression(int $sideMeters, string $meterAlias): string
    {
        $delta = number_format($sideMeters / 111_320, 12, '.', '');

        return 'FLOOR(CAST('.$this->identifierLongitudeExpression($meterAlias).' AS NUMERIC) / '.$delta.')';
    }

    private function identifierGroupKey(int $latitudeCell, int $longitudeCell): string
    {
        return $latitudeCell.'_'.$longitudeCell;
    }

    private function parseIdentifierGroupKey(string $groupKey): ?array
    {
        if (! preg_match('/^(-?\d+)_(-?\d+)$/', $groupKey, $matches)) {
            return null;
        }

        return [
            'latitude_cell' => (int) $matches[1],
            'longitude_cell' => (int) $matches[2],
        ];
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
