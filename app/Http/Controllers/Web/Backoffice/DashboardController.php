<?php

namespace App\Http\Controllers\Web\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\ReparationCase;
use App\Models\ReparationCaseStep;
use App\Support\Auth\SuperAdminAccessResolver;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $portal = $this->activePortal($request);
        $baseQuery = $this->caseQueryForPortal($request);
        $cases = (clone $baseQuery)
            ->with(['incidentReport', 'publicUser', 'organization', 'bailiff', 'lawyer'])
            ->latest('id')
            ->get();

        $total = $cases->count();
        $closedStatuses = ['approved', 'rejected', 'compensated', 'closed'];
        $activeCases = $cases->whereNull('closed_at')->count();
        $closedCases = $cases->whereNotNull('closed_at')->count();
        $thisMonthCases = $cases
            ->filter(fn (ReparationCase $case): bool => $case->created_at?->isSameMonth(now()) ?? false)
            ->count();

        return view('backoffice.dashboard', [
            'portal' => $portal,
            'dashboardTitle' => $this->dashboardTitle($portal),
            'dashboardDescription' => $this->dashboardDescription($portal),
            'stats' => [
                ['label' => 'Dossiers suivis', 'value' => $total, 'hint' => $this->scopeHint($portal)],
                ['label' => 'En cours', 'value' => $activeCases, 'hint' => 'Dossiers non conclus'],
                ['label' => 'Conclus', 'value' => $closedCases, 'hint' => 'Clotures ou compenses'],
                ['label' => 'Nouveaux ce mois', 'value' => $thisMonthCases, 'hint' => now()->format('m/Y')],
            ],
            'focusStats' => $this->focusStats($portal, $cases),
            'statusBars' => $this->statusBars($cases, $closedStatuses),
            'monthlyTrend' => $this->monthlyTrend($cases),
            'pipeline' => $this->pipeline($cases),
            'organizationReport' => $this->organizationReport($cases),
            'actorReport' => $this->actorReport($portal, $cases),
            'recentCases' => $cases->take(8),
            'recentSteps' => $this->recentSteps($request),
        ]);
    }

    private function caseQueryForPortal(Request $request): Builder
    {
        $portal = $this->activePortal($request);
        $userId = (int) $request->user()->id;

        $query = ReparationCase::query();

        return match ($portal) {
            'huissier' => $query->where('bailiff_user_id', $userId),
            'aoda' => $query->whereNotNull('bailiff_completed_at'),
            'avocat' => $query->where('lawyer_user_id', $userId),
            default => abort(403),
        };
    }

    private function recentSteps(Request $request): Collection
    {
        return ReparationCaseStep::query()
            ->with(['reparationCase.publicUser', 'reparationCase.organization', 'createdBy'])
            ->whereHas('reparationCase', fn (Builder $query) => $this->applyCaseScope($query, $request))
            ->latest('id')
            ->limit(8)
            ->get();
    }

    private function applyCaseScope(Builder $query, Request $request): Builder
    {
        $portal = $this->activePortal($request);
        $userId = (int) $request->user()->id;

        return match ($portal) {
            'huissier' => $query->where('bailiff_user_id', $userId),
            'aoda' => $query->whereNotNull('bailiff_completed_at'),
            'avocat' => $query->where('lawyer_user_id', $userId),
            default => abort(403),
        };
    }

    private function activePortal(Request $request): string
    {
        $portal = app(SuperAdminAccessResolver::class)->resolveLegalPortal(
            $request->user(),
            $request->attributes->get('super_admin_access'),
        );

        abort_if(! in_array($portal, ['huissier', 'aoda', 'avocat'], true), 403);

        return $portal;
    }

    private function focusStats(string $portal, Collection $cases): array
    {
        return match ($portal) {
            'huissier' => [
                ['label' => 'Constats a finaliser', 'value' => $cases->whereNull('bailiff_completed_at')->count()],
                ['label' => 'Constats termines', 'value' => $cases->whereNotNull('bailiff_completed_at')->count()],
            ],
            'aoda' => [
                ['label' => 'A attribuer avocat', 'value' => $cases->whereNotNull('bailiff_completed_at')->whereNull('lawyer_user_id')->whereNull('closed_at')->count()],
                ['label' => 'A conclure', 'value' => $cases->whereNotNull('lawyer_completed_at')->whereNull('closed_at')->count()],
            ],
            'avocat' => [
                ['label' => 'Procedures en cours', 'value' => $cases->whereNull('lawyer_completed_at')->whereNull('closed_at')->count()],
                ['label' => 'Procedures terminees', 'value' => $cases->whereNotNull('lawyer_completed_at')->count()],
            ],
            default => [],
        };
    }

    private function statusBars(Collection $cases, array $closedStatuses): Collection
    {
        $labels = [
            'submitted' => 'Soumis',
            'under_review' => 'En analyse',
            'awaiting_lawyer_assignment' => 'Attente AODA',
            'lawyer_assigned' => 'Avocat attribue',
            'judicial_in_progress' => 'Procedure',
            'approved' => 'Valide',
            'rejected' => 'Rejete',
            'compensated' => 'Compense',
            'closed' => 'Clos',
        ];

        $counts = $cases->groupBy('status')->map->count();
        $max = max(1, (int) $counts->max());

        return $counts
            ->sortDesc()
            ->map(fn (int $count, string $status): array => [
                'label' => $labels[$status] ?? $status,
                'count' => $count,
                'percent' => (int) round(($count / $max) * 100),
                'closed' => in_array($status, $closedStatuses, true),
            ])
            ->values();
    }

    private function monthlyTrend(Collection $cases): Collection
    {
        $months = collect(range(5, 0))
            ->map(fn (int $offset): CarbonImmutable => CarbonImmutable::now()->startOfMonth()->subMonths($offset));

        $max = max(1, $months
            ->map(fn (CarbonImmutable $month): int => $cases
                ->filter(fn (ReparationCase $case): bool => $case->created_at?->isSameMonth($month) ?? false)
                ->count())
            ->max());

        return $months->map(function (CarbonImmutable $month) use ($cases, $max): array {
            $count = $cases
                ->filter(fn (ReparationCase $case): bool => $case->created_at?->isSameMonth($month) ?? false)
                ->count();

            return [
                'label' => $month->format('M'),
                'count' => $count,
                'height' => max(8, (int) round(($count / $max) * 100)),
            ];
        });
    }

    private function pipeline(Collection $cases): array
    {
        return [
            ['label' => 'Constat en cours', 'count' => $cases->whereNull('bailiff_completed_at')->count()],
            ['label' => 'Attente AODA', 'count' => $cases->whereNotNull('bailiff_completed_at')->whereNull('lawyer_user_id')->whereNull('closed_at')->count()],
            ['label' => 'Chez avocat', 'count' => $cases->whereNotNull('lawyer_user_id')->whereNull('lawyer_completed_at')->whereNull('closed_at')->count()],
            ['label' => 'A conclure', 'count' => $cases->whereNotNull('lawyer_completed_at')->whereNull('closed_at')->count()],
            ['label' => 'Conclus', 'count' => $cases->whereNotNull('closed_at')->count()],
        ];
    }

    private function organizationReport(Collection $cases): Collection
    {
        return $cases
            ->groupBy(fn (ReparationCase $case): string => $case->organization?->name ?: 'Sans organisation')
            ->map(fn (Collection $items, string $name): array => [
                'name' => $name,
                'total' => $items->count(),
                'open' => $items->whereNull('closed_at')->count(),
                'closed' => $items->whereNotNull('closed_at')->count(),
            ])
            ->sortByDesc('total')
            ->take(6)
            ->values();
    }

    private function actorReport(string $portal, Collection $cases): Collection
    {
        $relation = $portal === 'huissier' ? 'lawyer' : 'bailiff';
        $fallback = $portal === 'huissier' ? 'Avocat non attribue' : 'Huissier non attribue';

        if ($portal === 'aoda') {
            $relation = 'lawyer';
            $fallback = 'Avocat non attribue';
        }

        return $cases
            ->groupBy(fn (ReparationCase $case): string => $case->{$relation}?->name ?: $fallback)
            ->map(fn (Collection $items, string $name): array => [
                'name' => $name,
                'total' => $items->count(),
                'closed' => $items->whereNotNull('closed_at')->count(),
            ])
            ->sortByDesc('total')
            ->take(6)
            ->values();
    }

    private function dashboardTitle(string $portal): string
    {
        return match ($portal) {
            'huissier' => 'Tableau de bord huissier',
            'aoda' => 'Tableau de bord AODA',
            'avocat' => 'Tableau de bord avocat',
            default => 'Tableau de bord',
        };
    }

    private function dashboardDescription(string $portal): string
    {
        return match ($portal) {
            'huissier' => 'Vue de charge des constats, rapports et dossiers transmis.',
            'aoda' => 'Pilotage des attributions avocat, conclusions et dossiers a arbitrer.',
            'avocat' => 'Suivi des procedures judiciaires, etapes recentes et dossiers a finaliser.',
            default => 'Vue operationnelle des dossiers.',
        };
    }

    private function scopeHint(string $portal): string
    {
        return match ($portal) {
            'huissier' => 'Vos dossiers attribues',
            'aoda' => 'Dossiers apres constat',
            'avocat' => 'Vos dossiers attribues',
            default => 'Perimetre courant',
        };
    }
}
