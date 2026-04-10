<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\City;
use App\Models\Commune;
use App\Models\Country;
use App\Models\IncidentReport;
use App\Models\Meter;
use App\Models\Organization;
use App\Models\Payment;
use App\Models\PricingRule;
use App\Models\PublicUser;
use App\Models\PublicUserType;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $reportsCount = IncidentReport::query()->count();
        $resolvedReportsCount = IncidentReport::query()->where('status', 'resolved')->count();
        $damagesCount = IncidentReport::query()->whereNotNull('damage_declared_at')->count();
        $resolvedDamagesCount = IncidentReport::query()
            ->whereNotNull('damage_declared_at')
            ->where('damage_resolution_status', 'resolved')
            ->count();
        $paidPaymentsCount = Payment::query()->where('status', 'paid')->count();
        $collectedAmount = (float) Payment::query()->where('status', 'paid')->sum('amount');

        $slaCandidates = IncidentReport::query()
            ->whereNotNull('target_sla_hours')
            ->whereNotNull('created_at')
            ->get(['id', 'target_sla_hours', 'created_at', 'resolved_at']);

        $slaBreakdown = [
            'within' => 0,
            'risk' => 0,
            'breached' => 0,
            'unconfigured' => IncidentReport::query()
                ->where(function ($query): void {
                    $query->whereNull('target_sla_hours')
                        ->orWhereNull('created_at');
                })
                ->count(),
        ];

        foreach ($slaCandidates as $report) {
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

        $applicationPerformance = DB::table('applications')
            ->leftJoin('incident_reports', 'incident_reports.application_id', '=', 'applications.id')
            ->selectRaw('
                applications.id,
                applications.name,
                applications.code,
                COUNT(incident_reports.id) as reports_count,
                SUM(CASE WHEN incident_reports.status = ? THEN 1 ELSE 0 END) as resolved_count,
                SUM(CASE WHEN incident_reports.damage_declared_at IS NOT NULL THEN 1 ELSE 0 END) as damages_count
            ', ['resolved'])
            ->groupBy('applications.id', 'applications.name', 'applications.code')
            ->orderByDesc('reports_count')
            ->get();

        $organizationPerformance = DB::table('organizations')
            ->leftJoin('incident_reports', 'incident_reports.organization_id', '=', 'organizations.id')
            ->selectRaw('
                organizations.id,
                organizations.name,
                organizations.code,
                COUNT(incident_reports.id) as reports_count,
                SUM(CASE WHEN incident_reports.status = ? THEN 1 ELSE 0 END) as resolved_count,
                SUM(CASE WHEN incident_reports.damage_declared_at IS NOT NULL THEN 1 ELSE 0 END) as damages_count
            ', ['resolved'])
            ->groupBy('organizations.id', 'organizations.name', 'organizations.code')
            ->orderByDesc('reports_count')
            ->limit(8)
            ->get();

        $organizationIds = $organizationPerformance->pluck('id')->filter()->values();
        $organizationSlaCandidates = IncidentReport::query()
            ->whereIn('organization_id', $organizationIds)
            ->whereNotNull('target_sla_hours')
            ->whereNotNull('created_at')
            ->get(['organization_id', 'target_sla_hours', 'created_at', 'resolved_at']);

        $organizationSlaBreachedCounts = $organizationSlaCandidates
            ->groupBy('organization_id')
            ->map(function ($reports): int {
                return $reports->reduce(function (int $carry, IncidentReport $report): int {
                    $endReference = $report->resolved_at ?? now();
                    $elapsedHours = $report->created_at->diffInMinutes($endReference) / 60;

                    return $carry + (($report->target_sla_hours > 0 && $elapsedHours >= $report->target_sla_hours) ? 1 : 0);
                }, 0);
            });

        $organizationPerformance = $organizationPerformance->map(function ($organization) use ($organizationSlaBreachedCounts) {
            $organization->sla_breached_count = (int) ($organizationSlaBreachedCounts[$organization->id] ?? 0);

            return $organization;
        });

        $reportStatusBreakdown = [
            'submitted' => 0,
            'in_progress' => 0,
            'resolved' => 0,
            'rejected' => 0,
        ];

        $reportStatusRaw = IncidentReport::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        foreach (array_keys($reportStatusBreakdown) as $status) {
            $reportStatusBreakdown[$status] = (int) ($reportStatusRaw[$status] ?? 0);
        }

        $damageStatusBreakdown = [
            'submitted' => 0,
            'in_progress' => 0,
            'resolved' => 0,
            'rejected' => 0,
        ];

        $damageStatusRaw = IncidentReport::query()
            ->whereNotNull('damage_declared_at')
            ->selectRaw("COALESCE(damage_resolution_status, 'submitted') as status, COUNT(*) as total")
            ->groupByRaw("COALESCE(damage_resolution_status, 'submitted')")
            ->pluck('total', 'status');

        foreach (array_keys($damageStatusBreakdown) as $status) {
            $damageStatusBreakdown[$status] = (int) ($damageStatusRaw[$status] ?? 0);
        }

        $paymentStatusBreakdown = [
            'pending' => 0,
            'paid' => 0,
            'failed' => 0,
        ];

        $paymentStatusRaw = Payment::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        foreach (array_keys($paymentStatusBreakdown) as $status) {
            $paymentStatusBreakdown[$status] = (int) ($paymentStatusRaw[$status] ?? 0);
        }

        $publicUserTypeBreakdown = PublicUserType::query()
            ->withCount('publicUsers')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $topSignals = IncidentReport::query()
            ->selectRaw("COALESCE(signal_label, signal_code, incident_type, 'Signal') as label, COUNT(*) as total")
            ->groupBy('signal_label', 'signal_code', 'incident_type')
            ->orderByDesc('total')
            ->limit(6)
            ->get();

        $topCommunes = IncidentReport::query()
            ->leftJoin('communes', 'communes.id', '=', 'incident_reports.commune_id')
            ->selectRaw("COALESCE(communes.name, '-') as label, COUNT(*) as total")
            ->groupBy('communes.name')
            ->orderByDesc('total')
            ->limit(6)
            ->get();

        $mapReports = IncidentReport::query()
            ->with('meter')
            ->where(function ($query): void {
                $query->whereNotNull('latitude')
                    ->whereNotNull('longitude')
                    ->orWhereHas('meter', function ($meterQuery): void {
                        $meterQuery->whereNotNull('latitude')
                            ->whereNotNull('longitude');
                    });
            })
            ->latest()
            ->take(500)
            ->get([
                'id',
                'reference',
                'signal_code',
                'signal_label',
                'latitude',
                'longitude',
                'application_id',
                'organization_id',
                'status',
            ])
            ->map(function (IncidentReport $report): ?array {
                $latitude = $report->latitude ?? $report->meter?->latitude;
                $longitude = $report->longitude ?? $report->meter?->longitude;

                if ($latitude === null || $longitude === null) {
                    return null;
                }

                return [
                    'reference' => $report->reference,
                    'signal_code' => $report->signal_code,
                    'signal_label' => $report->signal_label,
                    'status' => $report->status,
                    'latitude' => (float) $latitude,
                    'longitude' => (float) $longitude,
                ];
            })
            ->filter()
            ->values();

        $recentReports = IncidentReport::query()
            ->with(['commune', 'organization', 'application'])
            ->latest()
            ->take(8)
            ->get();

        return view('super-admin.dashboard', [
            'stats' => [
                'super_admins' => User::query()->where('is_super_admin', true)->count(),
                'institution_admins' => User::query()
                    ->whereNotNull('organization_id')
                    ->whereHas('creator', fn ($query) => $query->where('is_super_admin', true))
                    ->count(),
                'public_users' => PublicUser::query()->count(),
                'public_business_users' => PublicUser::query()
                    ->whereHas('publicUserType', fn ($query) => $query->where('profile_kind', 'business'))
                    ->count(),
                'reports' => $reportsCount,
                'resolved_reports' => $resolvedReportsCount,
                'report_resolution_rate' => $reportsCount > 0 ? (int) round(($resolvedReportsCount / $reportsCount) * 100) : 0,
                'damages' => $damagesCount,
                'resolved_damages' => $resolvedDamagesCount,
                'damage_resolution_rate' => $damagesCount > 0 ? (int) round(($resolvedDamagesCount / $damagesCount) * 100) : 0,
                'payments' => Payment::query()->count(),
                'paid_payments' => $paidPaymentsCount,
                'collected_amount' => $collectedAmount,
                'pricing_rules' => PricingRule::query()->count(),
                'applications' => Application::query()->count(),
                'active_applications' => Application::query()->where('status', 'active')->count(),
                'organizations' => Organization::query()->count(),
                'active_organizations' => Organization::query()->where('status', 'active')->count(),
                'meters' => Meter::query()->count(),
                'countries' => Country::query()->count(),
                'cities' => City::query()->count(),
                'communes' => Commune::query()->count(),
                'sla_within' => $slaBreakdown['within'],
                'sla_risk' => $slaBreakdown['risk'],
                'sla_breached' => $slaBreakdown['breached'],
                'sla_compliance_rate' => $reportsCount > 0 ? (int) round(($slaBreakdown['within'] / $reportsCount) * 100) : 0,
            ],
            'recentReports' => $recentReports,
            'pricingRules' => PricingRule::query()->orderBy('label')->get(),
            'applicationPerformance' => $applicationPerformance,
            'organizationPerformance' => $organizationPerformance,
            'reportStatusBreakdown' => $reportStatusBreakdown,
            'damageStatusBreakdown' => $damageStatusBreakdown,
            'paymentStatusBreakdown' => $paymentStatusBreakdown,
            'slaBreakdown' => $slaBreakdown,
            'publicUserTypeBreakdown' => $publicUserTypeBreakdown,
            'topSignals' => $topSignals,
            'topCommunes' => $topCommunes,
            'mapReports' => $mapReports,
        ]);
    }
}
