<?php

namespace App\Http\Controllers\Web\Institution;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Institution\Concerns\InteractsWithInstitutionContext;
use App\Models\IncidentReport;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StatisticController extends Controller
{
    use InteractsWithInstitutionContext;

    public function index(): View
    {
        $context = $this->institutionContext();
        $featureCodes = $context['feature_codes'];
        $canViewPaymentInfo = in_array('INSTITUTION_PAYMENT_INFO', $featureCodes, true);
        $canViewDamageDeclarationsChart = in_array('INSTITUTION_DASHBOARD_DAMAGE_DECLARATIONS', $featureCodes, true);
        $reportsQuery = $this->institutionReportsQuery($context['network_type'], $context['application_id'], $context['organization_id']);
        $filters = $this->institutionFilterState();
        $this->applyInstitutionFilters($reportsQuery, $filters);

        $baseTable = DB::table('incident_reports');

        if ($context['organization_id'] !== null) {
            $baseTable->where('incident_reports.organization_id', $context['organization_id']);
        }

        if ($context['application_id'] !== null) {
            $baseTable->where('incident_reports.application_id', $context['application_id']);
        }

        if ($context['network_type'] !== null) {
            $baseTable->where('network_type', $context['network_type']);
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
            $damageResolutionRaw = DB::table('incident_reports')
                ->when(
                    fn ($query) => $query->where('incident_reports.organization_id', $context['organization_id'])
                )
                ->when(
                    $context['application_id'] !== null,
                    fn ($query) => $query->where('incident_reports.application_id', $context['application_id'])
                )
                ->when(
                    $context['network_type'] !== null,
                    fn ($query) => $query->where('incident_reports.network_type', $context['network_type'])
                )
                ->whereNotNull('damage_declared_at')
                ->whereBetween('incident_reports.damage_declared_at', [$filters['date_from'], $filters['date_to']])
                ->when($filters['commune_id'] !== null, fn ($query) => $query->where('incident_reports.commune_id', $filters['commune_id']))
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

        $byCommune = (clone $baseTable)
            ->leftJoin('communes', 'communes.id', '=', 'incident_reports.commune_id')
            ->select(
                'incident_reports.commune_id',
                DB::raw("COALESCE(communes.name, '-') as commune_name"),
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('incident_reports.commune_id', 'communes.name')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        $bySignal = (clone $baseTable)
            ->select('signal_code', 'signal_label', DB::raw('COUNT(*) as total'))
            ->groupBy('signal_code', 'signal_label')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

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

        $treatmentBreakdown = [
            'submitted' => (clone $reportsQuery)->where('status', 'submitted')->count(),
            'in_progress' => (clone $reportsQuery)->where('status', 'in_progress')->count(),
            'resolved' => (clone $reportsQuery)->where('status', 'resolved')->count(),
            'rejected' => (clone $reportsQuery)->where('status', 'rejected')->count(),
        ];

        $distinctCommunes = (clone $reportsQuery)->whereNotNull('commune_id')->distinct('commune_id')->count('commune_id');
        $reportsCount = (clone $reportsQuery)->count();
        $paidReports = 0;
        $pendingReports = 0;
        $collectedAmount = 0;
        $paymentBreakdown = [
            'pending' => 0,
            'paid' => 0,
            'failed' => 0,
        ];

        if ($canViewPaymentInfo) {
            $paidReports = (clone $reportsQuery)->where('payment_status', 'paid')->count();
            $pendingReports = (clone $reportsQuery)->where('payment_status', 'pending')->count();
            $paymentBreakdown = [
                'pending' => (clone $reportsQuery)->where('payment_status', 'pending')->count(),
                'paid' => (clone $reportsQuery)->where('payment_status', 'paid')->count(),
                'failed' => (clone $reportsQuery)->where('payment_status', 'failed')->count(),
            ];

            $collectedAmount = DB::table('payments')
                ->join('incident_reports', 'incident_reports.id', '=', 'payments.incident_report_id')
                ->when(
                    fn ($query) => $query->where('incident_reports.organization_id', $context['organization_id'])
                )
                ->when(
                    $context['application_id'] !== null,
                    fn ($query) => $query->where('incident_reports.application_id', $context['application_id'])
                )
                ->when(
                    $context['network_type'] !== null,
                    fn ($query) => $query->where('incident_reports.network_type', $context['network_type'])
                )
                ->whereBetween('incident_reports.created_at', [$filters['date_from'], $filters['date_to']])
                ->when($filters['commune_id'] !== null, fn ($query) => $query->where('incident_reports.commune_id', $filters['commune_id']))
                ->where('payments.status', 'paid')
                ->sum('payments.amount');
        }

        $slaCandidates = (clone $reportsQuery)
            ->select([
                'id',
                'status',
                'target_sla_hours',
                'created_at',
                'resolved_at',
                'reference',
                'signal_code',
                'signal_label',
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
                    'reference' => $report->reference,
                    'status' => $report->status,
                    'signal_code' => $report->signal_code,
                    'signal_label' => $report->signal_label,
                    'latitude' => (float) $report->latitude,
                    'longitude' => (float) $report->longitude,
                    'target_sla_hours' => $report->target_sla_hours,
                    'created_at' => $report->created_at?->toIso8601String(),
                ];
            })
            ->values();

        return view('institution.statistics.index', [
            'organization' => $context['organization'],
            'application' => $context['application'],
            'features' => $featureCodes,
            'activeNav' => 'statistics',
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
            'byCommune' => $byCommune,
            'bySignal' => $bySignal,
            'trend' => $trend,
            'topCommunes' => $topCommunes,
            'topSignals' => $topSignals,
            'paymentBreakdown' => $paymentBreakdown,
            'treatmentBreakdown' => $treatmentBreakdown,
            'slaBreakdown' => $slaBreakdown,
            'damageResolutionBreakdown' => $damageResolutionBreakdown,
            'mapReports' => $mapReports,
        ]);
    }
}
