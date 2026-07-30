<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\IncidentReport;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class PublicIncidentReportController extends Controller
{
    public function index(): View
    {
        $perPage = min(max((int) request()->integer('per_page', 15), 1), 100);
        $query = IncidentReport::query()
            ->with(['publicUser.publicUserType', 'application', 'organization', 'commune', 'reparationCase'])
            ->whereNotNull('public_user_id');

        if (filled(request('search'))) {
            $search = trim((string) request('search'));
            $query->where(function ($builder) use ($search): void {
                $builder->where('reference', 'like', '%'.$search.'%')
                    ->orWhere('signal_label', 'like', '%'.$search.'%')
                    ->orWhere('signal_code', 'like', '%'.$search.'%')
                    ->orWhere('incident_type', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%')
                    ->orWhereHas('publicUser', fn ($userQuery) => $userQuery
                        ->where('first_name', 'like', '%'.$search.'%')
                        ->orWhere('last_name', 'like', '%'.$search.'%')
                        ->orWhere('phone', 'like', '%'.$search.'%'))
                    ->orWhereHas('organization', fn ($organizationQuery) => $organizationQuery->where('name', 'like', '%'.$search.'%'))
                    ->orWhereHas('application', fn ($applicationQuery) => $applicationQuery->where('name', 'like', '%'.$search.'%'));
            });
        }

        if (filled(request('status'))) {
            $query->where('status', request('status'));
        }

        if (filled(request('application_id'))) {
            $query->where('application_id', request('application_id'));
        }

        if (filled(request('organization_id'))) {
            $query->where('organization_id', request('organization_id'));
        }

        if (filled(request('damage'))) {
            if (request('damage') === 'with_damage') {
                $query->whereNotNull('damage_declared_at');
            }

            if (request('damage') === 'without_damage') {
                $query->whereNull('damage_declared_at');
            }
        }

        if (filled(request('resolution_confirmation'))) {
            if (request('resolution_confirmation') === 'ai_validated_and_confirmed') {
                $query->where('resolution_confirmation_status', 'confirmed')
                    ->where('resolution_confirmed_without_ai_validation', false);
            }

            if (request('resolution_confirmation') === 'confirmed_without_ai_validation') {
                $query->where('resolution_confirmation_status', 'confirmed')
                    ->where('resolution_confirmed_without_ai_validation', true);
            }

            if (request('resolution_confirmation') === 'waiting_up_confirmation') {
                $query->where('status', 'resolved')
                    ->where(function ($builder): void {
                        $builder->whereNull('resolution_confirmation_status')
                            ->orWhere('resolution_confirmation_status', '!=', 'confirmed');
                    });
            }
        }

        if (filled(request('reparation_case'))) {
            if (request('reparation_case') === 'opened') {
                $query->whereHas('reparationCase');
            }

            if (request('reparation_case') === 'missing') {
                $query->whereDoesntHave('reparationCase');
            }
        }

        $this->applyPeriodFilter($query, request(), 'incident_reports.created_at');

        $statsQuery = clone $query;
        $reportStats = [
            'total' => (clone $statsQuery)->count(),
            'submitted' => (clone $statsQuery)->where('status', 'submitted')->count(),
            'in_progress' => (clone $statsQuery)->where('status', 'in_progress')->count(),
            'resolved' => (clone $statsQuery)->where('status', 'resolved')->count(),
            'with_damage' => (clone $statsQuery)->whereNotNull('damage_declared_at')->count(),
            'with_case' => (clone $statsQuery)->whereHas('reparationCase')->count(),
        ];
        $statusBreakdown = [
            ['label' => 'Soumis', 'value' => $reportStats['submitted']],
            ['label' => 'En cours', 'value' => $reportStats['in_progress']],
            ['label' => 'Résolus', 'value' => $reportStats['resolved']],
            ['label' => 'Rejetés', 'value' => (clone $statsQuery)->where('status', 'rejected')->count()],
        ];
        $damageBreakdown = [
            ['label' => 'Avec dommage', 'value' => $reportStats['with_damage']],
            ['label' => 'Sans dommage', 'value' => max(0, $reportStats['total'] - $reportStats['with_damage'])],
        ];
        $confirmationBreakdown = [
            [
                'label' => 'Confirmés UP',
                'value' => (clone $statsQuery)->where('resolution_confirmation_status', 'confirmed')->count(),
            ],
            [
                'label' => 'En attente UP',
                'value' => (clone $statsQuery)
                    ->where('status', 'resolved')
                    ->where(function ($builder): void {
                        $builder->whereNull('resolution_confirmation_status')
                            ->orWhere('resolution_confirmation_status', '!=', 'confirmed');
                    })
                    ->count(),
            ],
            [
                'label' => 'Sans confirmation',
                'value' => (clone $statsQuery)->where('status', '!=', 'resolved')->count(),
            ],
        ];
        $trend = (clone $statsQuery)
            ->selectRaw('DATE(incident_reports.created_at) as period_label')
            ->selectRaw('COUNT(*) as total')
            ->groupByRaw('DATE(incident_reports.created_at)')
            ->orderByRaw('DATE(incident_reports.created_at)')
            ->get()
            ->map(fn ($row): array => [
                'label' => Carbon::parse($row->period_label)->format('d/m'),
                'value' => (int) $row->total,
            ])
            ->all();

        return view('super-admin.public-reports.index', [
            'reports' => $query->latest()->paginate($perPage)->withQueryString(),
            'reportStats' => $reportStats,
            'statusBreakdown' => $statusBreakdown,
            'damageBreakdown' => $damageBreakdown,
            'confirmationBreakdown' => $confirmationBreakdown,
            'trend' => $trend,
            'perPage' => $perPage,
            'applications' => Application::query()->where('status', 'active')->orderBy('name')->get(['id', 'name']),
            'organizations' => Organization::query()->where('status', 'active')->orderBy('name')->get(['id', 'name']),
        ]);
    }

    private function applyPeriodFilter($query, Request $request, string $column): void
    {
        [$startDate, $endDate] = $this->periodBounds($request);

        if ($startDate !== null) {
            $query->where($column, '>=', $startDate);
        }

        if ($endDate !== null) {
            $query->where($column, '<=', $endDate);
        }
    }

    private function periodBounds(Request $request): array
    {
        $period = (string) $request->input('period', '30d');

        if ($period === 'today') {
            return [now()->startOfDay(), now()->endOfDay()];
        }

        if ($period === '7d') {
            return [now()->subDays(6)->startOfDay(), now()->endOfDay()];
        }

        if ($period === 'month') {
            return [now()->startOfMonth(), now()->endOfMonth()];
        }

        if ($period === 'year') {
            return [now()->startOfYear(), now()->endOfYear()];
        }

        if ($period === 'custom') {
            $start = filled($request->input('date_from')) ? Carbon::parse($request->input('date_from'))->startOfDay() : null;
            $end = filled($request->input('date_to')) ? Carbon::parse($request->input('date_to'))->endOfDay() : null;

            return [$start, $end];
        }

        return [now()->subDays(29)->startOfDay(), now()->endOfDay()];
    }

    public function show(IncidentReport $report): View
    {
        $report->load([
            'publicUser.publicUserType',
            'application',
            'organization.organizationType',
            'meter.organization',
            'country',
            'city',
            'commune',
            'purchaseReceipt',
            'payments.pricingRule',
            'reparationCase',
        ]);

        return view('super-admin.public-reports.show', [
            'report' => $report,
        ]);
    }
}
