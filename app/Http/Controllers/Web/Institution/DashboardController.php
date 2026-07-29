<?php

namespace App\Http\Controllers\Web\Institution;

use App\Domain\Reports\Enums\IncidentReportStatus;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Institution\Concerns\InteractsWithInstitutionContext;
use App\Models\IncidentReport;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    use InteractsWithInstitutionContext;

    public function __invoke(): View
    {
        $context = $this->institutionContext();
        $featureCodes = $context['feature_codes'];
        $canViewPaymentInfo = in_array('INSTITUTION_PAYMENT_INFO', $featureCodes, true);
        $canViewPaymentBreakdownChart = in_array('INSTITUTION_DASHBOARD_PAYMENT_BREAKDOWN', $featureCodes, true);
        $canViewDamageDeclarationsChart = in_array('INSTITUTION_DASHBOARD_DAMAGE_DECLARATIONS', $featureCodes, true);
        $filters = $this->institutionFilterState();
        $reportsQuery = $this->institutionReportsQuery($context['network_type'], $context['application_id'], $context['organization_id']);
        $this->applyInstitutionFilters($reportsQuery, $filters);

        $baseTable = DB::table('incident_reports');

        if ($context['organization_id'] !== null) {
            $baseTable->where('incident_reports.organization_id', $context['organization_id']);
        }

        if ($context['application_id'] !== null) {
            $baseTable->where('incident_reports.application_id', $context['application_id']);
        }

        if ($context['network_type'] !== null) {
            $baseTable->where('incident_reports.network_type', $context['network_type']);
        }

        $this->applyInstitutionFilters($baseTable, $filters);

        $days = max(0, (int) floor($filters['date_from']->diffInDays($filters['date_to'])));
        $trendDays = collect(range(0, $days))
            ->map(fn (int $offset) => $filters['date_from']->copy()->addDays($offset));

        $trendRaw = (clone $baseTable)
            ->selectRaw('DATE(incident_reports.created_at) as report_day, COUNT(*) as total')
            ->groupByRaw('DATE(incident_reports.created_at)')
            ->orderBy('report_day')
            ->pluck('total', 'report_day');

        $trend = $trendDays->map(function (Carbon $day) use ($trendRaw): array {
            $key = $day->toDateString();

            return [
                'label' => $day->translatedFormat('d M'),
                'value' => (int) ($trendRaw[$key] ?? 0),
            ];
        });

        $damageResolutionBreakdown = [
            'submitted' => 0,
            'in_progress' => 0,
            'resolved' => 0,
            'rejected' => 0,
        ];

        if ($canViewDamageDeclarationsChart) {
            $damageResolutionQuery = DB::table('incident_reports')
                ->whereNotNull('damage_declared_at')
                ->whereBetween('incident_reports.damage_declared_at', [$filters['date_from'], $filters['date_to']]);

            if ($context['organization_id'] !== null) {
                $damageResolutionQuery->where('incident_reports.organization_id', $context['organization_id']);
            }

            if ($context['application_id'] !== null) {
                $damageResolutionQuery->where('incident_reports.application_id', $context['application_id']);
            }

            if ($context['network_type'] !== null) {
                $damageResolutionQuery->where('incident_reports.network_type', $context['network_type']);
            }

            if ($filters['commune_id'] !== null) {
                $damageResolutionQuery->where('incident_reports.commune_id', $filters['commune_id']);
            }

            $damageResolutionRaw = $damageResolutionQuery
                ->selectRaw("COALESCE(damage_resolution_status, 'submitted') as status, COUNT(*) as total")
                ->groupByRaw("COALESCE(damage_resolution_status, 'submitted')")
                ->pluck('total', 'status');

            $damageResolutionBreakdown = [
                'submitted' => (int) ($damageResolutionRaw['submitted'] ?? 0),
                'in_progress' => (int) ($damageResolutionRaw['in_progress'] ?? 0),
                'resolved' => (int) ($damageResolutionRaw['resolved'] ?? 0),
                'rejected' => (int) ($damageResolutionRaw['rejected'] ?? 0),
            ];
        }

        $topCommunes = (clone $baseTable)
            ->leftJoin('communes', 'communes.id', '=', 'incident_reports.commune_id')
            ->selectRaw("COALESCE(communes.name, '-') as label, COUNT(*) as total")
            ->groupBy('communes.name')
            ->orderByDesc('total')
            ->limit(6)
            ->get();

        $topSignals = (clone $baseTable)
            ->selectRaw("COALESCE(signal_label, signal_code, incident_type, 'Signal') as label, COUNT(*) as total")
            ->groupBy('signal_label', 'signal_code', 'incident_type')
            ->orderByDesc('total')
            ->limit(6)
            ->get();

        $paymentBreakdown = [
            'pending' => 0,
            'paid' => 0,
            'failed' => 0,
        ];
        $treatmentBreakdown = [
            'submitted' => (clone $reportsQuery)->where('status', 'submitted')->count(),
            'in_progress' => (clone $reportsQuery)->where('status', 'in_progress')->count(),
            'resolved' => (clone $reportsQuery)->where('status', 'resolved')->count(),
            'rejected' => (clone $reportsQuery)->where('status', 'rejected')->count(),
        ];

        $reportsCount = (clone $reportsQuery)->count();
        $distinctCommunes = (clone $reportsQuery)->whereNotNull('commune_id')->distinct('commune_id')->count('commune_id');
        $slaCandidates = (clone $reportsQuery)
            ->select([
                'id',
                'status',
                'reference',
                'signal_code',
                'signal_label',
                'target_sla_hours',
                'created_at',
                'resolved_at',
                'latitude',
                'longitude',
            ])
            ->get();

        $slaBreakdown = [
            'within' => 0,
            'risk' => 0,
            'breached' => 0,
            'unconfigured' => 0,
        ];

        foreach ($slaCandidates as $report) {
            if (blank($report->target_sla_hours) || blank($report->created_at)) {
                $slaBreakdown['unconfigured']++;
                continue;
            }

            $endReference = $report->resolved_at ?? now();
            $elapsedHours = $report->created_at->diffInMinutes($endReference) / 60;
            $ratio = $report->target_sla_hours > 0 ? $elapsedHours / $report->target_sla_hours : 0;

            if ($ratio >= 1) {
                $slaBreakdown['breached']++;
            } elseif ($ratio >= 0.8) {
                $slaBreakdown['risk']++;
            } else {
                $slaBreakdown['within']++;
            }
        }

        $mapReports = $slaCandidates
            ->filter(fn (IncidentReport $report) => $report->status !== 'resolved' && $report->latitude !== null && $report->longitude !== null)
            ->take(200)
            ->map(function (IncidentReport $report): array {
                return [
                    'id' => $report->id,
                    'reference' => $report->reference,
                    'status' => $report->status,
                    'signal_code' => $report->signal_code,
                    'signal_label' => $report->signal_label,
                    'latitude' => (float) $report->latitude,
                    'longitude' => (float) $report->longitude,
                    'target_sla_hours' => $report->target_sla_hours,
                    'created_at' => $report->created_at?->toIso8601String(),
                    'detail_url' => route('institution.reports.show', $report),
                ];
            })
            ->values();

        $groupingSurfaceSquareMeters = $this->reportGroupingSurfaceSquareMeters();
        $groupingSideMeters = $this->reportGroupingSideMeters($groupingSurfaceSquareMeters);
        $identifierGroups = $this->identifierReportGroups(clone $reportsQuery, $groupingSideMeters, 6);

        $paidReports = 0;
        $pendingReports = 0;
        $collectedAmount = 0;

        if ($canViewPaymentInfo) {
            $paidReports = (clone $reportsQuery)->where('payment_status', 'paid')->count();
            $pendingReports = (clone $reportsQuery)->where('payment_status', 'pending')->count();

            $paymentsQuery = DB::table('payments')
                ->join('incident_reports', 'incident_reports.id', '=', 'payments.incident_report_id')
                ->whereBetween('incident_reports.created_at', [$filters['date_from'], $filters['date_to']])
                ->where('payments.status', 'paid');

            if ($context['organization_id'] !== null) {
                $paymentsQuery->where('incident_reports.organization_id', $context['organization_id']);
            }

            if ($context['application_id'] !== null) {
                $paymentsQuery->where('incident_reports.application_id', $context['application_id']);
            }

            if ($context['network_type'] !== null) {
                $paymentsQuery->where('incident_reports.network_type', $context['network_type']);
            }

            if ($filters['commune_id'] !== null) {
                $paymentsQuery->where('incident_reports.commune_id', $filters['commune_id']);
            }

            $collectedAmount = $paymentsQuery->sum('payments.amount');
        }

        if ($canViewPaymentInfo && $canViewPaymentBreakdownChart) {
            $paymentBreakdown = [
                'pending' => (clone $reportsQuery)->where('payment_status', 'pending')->count(),
                'paid' => (clone $reportsQuery)->where('payment_status', 'paid')->count(),
                'failed' => (clone $reportsQuery)->where('payment_status', 'failed')->count(),
            ];
        }

        return view('institution.dashboard', [
            'organization' => $context['organization'],
            'application' => $context['application'],
            'features' => $featureCodes,
            'activeNav' => 'dashboard',
            'filters' => $filters,
            'communes' => $this->availableInstitutionCommunes($context['network_type'], $context['application_id'], $context['organization_id']),
            'stats' => [
                'reports' => $reportsCount,
                'pending_reports' => $pendingReports,
                'paid_reports' => $paidReports,
                'paid_rate' => $reportsCount > 0 ? (int) round(($paidReports / $reportsCount) * 100) : 0,
                'collected_amount' => (int) $collectedAmount,
                'average_reports_per_commune' => $distinctCommunes > 0 ? round($reportsCount / $distinctCommunes, 1) : 0,
                'active_communes' => $distinctCommunes,
                'sla_breached' => $slaBreakdown['breached'],
                'geo_points' => $mapReports->count(),
                'resolved_reports' => $treatmentBreakdown['resolved'],
                'in_progress_reports' => $treatmentBreakdown['in_progress'],
            ],
            'recentReports' => $reportsQuery->latest()->take(10)->get(),
            'trend' => $trend,
            'topCommunes' => $topCommunes,
            'topSignals' => $topSignals,
            'paymentBreakdown' => $paymentBreakdown,
            'treatmentBreakdown' => $treatmentBreakdown,
            'slaBreakdown' => $slaBreakdown,
            'damageResolutionBreakdown' => $damageResolutionBreakdown,
            'mapReports' => $mapReports,
            'identifierGroups' => $identifierGroups,
            'groupingSurfaceSquareMeters' => $groupingSurfaceSquareMeters,
            'groupingSideMeters' => $groupingSideMeters,
            'dashboardReportFilterQuery' => request()->only(['period', 'date_from', 'date_to', 'commune_id']),
        ]);
    }

    private function identifierReportGroups(Builder $query, int $sideMeters, int $limit = 6): Collection
    {
        $meterAlias = 'dashboard_grouping_meters';
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
            ->selectRaw('MIN(NULLIF('.$meterAlias.'.commune, \'\')) as commune_name')
            ->groupByRaw($latitudeCellExpression.', '.$longitudeCellExpression)
            ->orderByDesc('reports_count')
            ->limit($limit)
            ->get()
            ->map(function ($group, int $index): array {
                $latitudeCell = (int) $group->latitude_cell;
                $longitudeCell = (int) $group->longitude_cell;

                return [
                    'key' => $this->identifierGroupKey($latitudeCell, $longitudeCell),
                    'label' => 'Zone '.($index + 1),
                    'area_label' => $group->commune_name ?: 'Secteur géolocalisé',
                    'reports_count' => (int) $group->reports_count,
                    'open_reports_count' => (int) $group->reports_count,
                ];
            });
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
}
