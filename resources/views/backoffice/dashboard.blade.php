@extends('super-admin.layouts.app')

@section('title', config('app.name').' | '.$dashboardTitle)
@section('page-title', $dashboardTitle)
@section('page-description', $dashboardDescription)

@section('header-badges')
    <span class="badge-soft">{{ strtoupper($portal) }}</span>
    <span class="badge-soft">Pilotage contentieux</span>
@endsection

@push('styles')
    <style>
        .legal-dashboard-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 1rem; }
        .legal-dashboard-main { display: grid; grid-template-columns: minmax(0, 1.2fr) minmax(320px, .8fr); gap: 1rem; align-items: start; }
        .metric-card { border: 1px solid rgba(16,42,67,.08); border-radius: 12px; background: rgba(255,255,255,.82); padding: 1rem; min-height: 126px; }
        .metric-value { font-size: 2rem; line-height: 1; font-weight: 800; color: var(--acepen-navy); }
        .metric-hint { color: var(--acepen-muted); font-size: .82rem; }
        .chart-bars { display: grid; gap: .82rem; }
        .chart-row { display: grid; grid-template-columns: minmax(130px, 180px) minmax(0, 1fr) 42px; gap: .75rem; align-items: center; }
        .chart-track { height: 12px; border-radius: 999px; background: rgba(16,42,67,.08); overflow: hidden; }
        .chart-fill { height: 100%; border-radius: 999px; background: linear-gradient(90deg, var(--acepen-blue), var(--acepen-gold)); }
        .chart-fill.closed { background: linear-gradient(90deg, #427a5b, #8ab17d); }
        .trend-chart { height: 220px; display: grid; grid-template-columns: repeat(6, minmax(0, 1fr)); gap: .8rem; align-items: end; padding-top: .7rem; }
        .trend-bar-wrap { min-width: 0; text-align: center; }
        .trend-bar { width: 100%; min-height: 8px; border-radius: 10px 10px 4px 4px; background: linear-gradient(180deg, var(--acepen-gold), #86651f); }
        .pipeline-grid { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: .75rem; }
        .pipeline-item { border: 1px solid rgba(16,42,67,.08); border-radius: 12px; padding: .85rem; background: rgba(255,255,255,.68); min-height: 94px; }
        .report-list { display: grid; gap: .7rem; }
        .report-item { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: .8rem; align-items: center; border-bottom: 1px solid rgba(16,42,67,.07); padding-bottom: .7rem; }
        .report-item:last-child { border-bottom: 0; padding-bottom: 0; }
        .case-report-table td { vertical-align: middle; }
        @media (max-width: 1199.98px) {
            .legal-dashboard-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .legal-dashboard-main { grid-template-columns: 1fr; }
            .pipeline-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (max-width: 575.98px) {
            .legal-dashboard-grid, .pipeline-grid { grid-template-columns: 1fr; }
            .chart-row { grid-template-columns: 1fr; gap: .35rem; }
        }
    </style>
@endpush

@section('content')
    <div class="legal-dashboard-grid mb-4">
        @foreach ($stats as $stat)
            <div class="metric-card">
                <div class="metric-hint mb-2">{{ $stat['label'] }}</div>
                <div class="metric-value mb-2">{{ number_format($stat['value'], 0, ',', ' ') }}</div>
                <div class="metric-hint">{{ $stat['hint'] }}</div>
            </div>
        @endforeach
    </div>

    <div class="pipeline-grid mb-4">
        @foreach ($pipeline as $item)
            <div class="pipeline-item">
                <div class="small text-secondary mb-2">{{ $item['label'] }}</div>
                <div class="h4 fw-bold mb-0">{{ $item['count'] }}</div>
            </div>
        @endforeach
    </div>

    <div class="legal-dashboard-main mb-4">
        <section class="panel-card">
            <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                <div>
                    <div class="fw-bold">Repartition par statut</div>
                    <div class="small text-secondary">Lecture rapide de la charge et des dossiers conclus</div>
                </div>
                <a href="{{ route('backoffice.legal-cases.index') }}" class="btn btn-sm btn-outline-dark">Voir dossiers</a>
            </div>
            <div class="chart-bars">
                @forelse ($statusBars as $bar)
                    <div class="chart-row">
                        <div class="small fw-semibold text-truncate">{{ $bar['label'] }}</div>
                        <div class="chart-track">
                            <div class="chart-fill {{ $bar['closed'] ? 'closed' : '' }}" style="width: {{ $bar['percent'] }}%"></div>
                        </div>
                        <div class="small fw-bold text-end">{{ $bar['count'] }}</div>
                    </div>
                @empty
                    <div class="text-secondary">Aucun dossier disponible pour le moment.</div>
                @endforelse
            </div>
        </section>

        <section class="panel-card">
            <div class="fw-bold">Evolution mensuelle</div>
            <div class="small text-secondary mb-2">Nouveaux dossiers sur les 6 derniers mois</div>
            <div class="trend-chart">
                @foreach ($monthlyTrend as $month)
                    <div class="trend-bar-wrap">
                        <div class="small fw-bold mb-2">{{ $month['count'] }}</div>
                        <div class="trend-bar" style="height: {{ $month['height'] }}%"></div>
                        <div class="small text-secondary mt-2">{{ $month['label'] }}</div>
                    </div>
                @endforeach
            </div>
        </section>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-4">
            <section class="panel-card h-100">
                <div class="fw-bold mb-3">Points d attention</div>
                <div class="report-list">
                    @foreach ($focusStats as $stat)
                        <div class="report-item">
                            <div>
                                <div class="fw-semibold">{{ $stat['label'] }}</div>
                                <div class="small text-secondary">Action prioritaire selon votre role</div>
                            </div>
                            <span class="status-chip">{{ $stat['value'] }}</span>
                        </div>
                    @endforeach
                </div>
            </section>
        </div>
        <div class="col-xl-4">
            <section class="panel-card h-100">
                <div class="fw-bold mb-3">Rapport organisations</div>
                <div class="report-list">
                    @forelse ($organizationReport as $item)
                        <div class="report-item">
                            <div>
                                <div class="fw-semibold text-truncate">{{ $item['name'] }}</div>
                                <div class="small text-secondary">{{ $item['open'] }} ouvert{{ $item['open'] > 1 ? 's' : '' }} · {{ $item['closed'] }} conclu{{ $item['closed'] > 1 ? 's' : '' }}</div>
                            </div>
                            <span class="status-chip">{{ $item['total'] }}</span>
                        </div>
                    @empty
                        <div class="text-secondary">Aucune organisation a afficher.</div>
                    @endforelse
                </div>
            </section>
        </div>
        <div class="col-xl-4">
            <section class="panel-card h-100">
                <div class="fw-bold mb-3">Rapport acteurs</div>
                <div class="report-list">
                    @forelse ($actorReport as $item)
                        <div class="report-item">
                            <div>
                                <div class="fw-semibold text-truncate">{{ $item['name'] }}</div>
                                <div class="small text-secondary">{{ $item['closed'] }} dossier{{ $item['closed'] > 1 ? 's' : '' }} conclu{{ $item['closed'] > 1 ? 's' : '' }}</div>
                            </div>
                            <span class="status-chip">{{ $item['total'] }}</span>
                        </div>
                    @empty
                        <div class="text-secondary">Aucun acteur a afficher.</div>
                    @endforelse
                </div>
            </section>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-7">
            <section class="panel-card h-100">
                <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
                    <div>
                        <div class="fw-bold">Dossiers recents</div>
                        <div class="small text-secondary">Derniers dossiers de votre perimetre</div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-modern case-report-table mb-0">
                        <thead>
                            <tr>
                                <th>Dossier</th>
                                <th>Victime</th>
                                <th>Statut</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentCases as $case)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $case->reference }}</div>
                                        <div class="small text-secondary">{{ $case->organization?->name ?: '-' }}</div>
                                    </td>
                                    <td>{{ trim(($case->publicUser?->first_name ?? '').' '.($case->publicUser?->last_name ?? '')) ?: '-' }}</td>
                                    <td><span class="status-chip">{{ $case->status }}</span></td>
                                    <td class="text-end">
                                        <a href="{{ route('backoffice.legal-cases.show', $case) }}" class="btn btn-sm btn-outline-dark">Ouvrir</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-secondary">Aucun dossier recent.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
        <div class="col-xl-5">
            <section class="panel-card h-100">
                <div class="fw-bold mb-3">Activite recente</div>
                <div class="report-list">
                    @forelse ($recentSteps as $step)
                        <div class="report-item">
                            <div>
                                <div class="fw-semibold">{{ $step->title }}</div>
                                <div class="small text-secondary">
                                    {{ $step->reparationCase?->reference ?: '-' }} · {{ $step->createdBy?->name ?: 'Systeme' }}
                                </div>
                            </div>
                            <div class="small text-secondary text-end">{{ $step->created_at?->format('d/m H:i') }}</div>
                        </div>
                    @empty
                        <div class="text-secondary">Aucune activite recente.</div>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
@endsection
