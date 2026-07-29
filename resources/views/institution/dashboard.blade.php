@extends('institution.layouts.app')

@section('title', config('app.name').' | Portail '.$organization?->name)
@section('page-title', $organization?->name)
@section('page-description', 'Suivez les signalements et les actions de votre institution.')
@php
    $label = \App\Support\Ui\InstitutionLabel::class;
    $canViewReports = in_array('PUBLIC_REPORTS', $features ?? [], true);
    $canViewStatistics = in_array('PUBLIC_REPORT_STATISTICS', $features ?? [], true);
    $canViewPaymentInfo = in_array('INSTITUTION_PAYMENT_INFO', $features ?? [], true);
    $canViewReportsTrendChart = in_array('INSTITUTION_DASHBOARD_REPORTS_TREND', $features ?? [], true);
    $canViewPaymentBreakdownChart = in_array('INSTITUTION_DASHBOARD_PAYMENT_BREAKDOWN', $features ?? [], true) && $canViewPaymentInfo;
    $canViewTreatmentBreakdownChart = in_array('INSTITUTION_DASHBOARD_TREATMENT_BREAKDOWN', $features ?? [], true);
    $canViewSlaBreakdownChart = in_array('INSTITUTION_DASHBOARD_SLA_BREAKDOWN', $features ?? [], true);
    $canViewTopCommunesChart = in_array('INSTITUTION_DASHBOARD_TOP_COMMUNES', $features ?? [], true);
    $canViewTopSignalsChart = in_array('INSTITUTION_DASHBOARD_TOP_SIGNALS', $features ?? [], true);
    $canViewDamageDeclarationsChart = in_array('INSTITUTION_DASHBOARD_DAMAGE_DECLARATIONS', $features ?? [], true);
    $canViewReportsMap = in_array('INSTITUTION_DASHBOARD_REPORTS_MAP', $features ?? [], true);
    $hasDashboardCharts = $canViewReportsTrendChart
        || $canViewPaymentBreakdownChart
        || $canViewTreatmentBreakdownChart
        || $canViewSlaBreakdownChart
        || $canViewTopCommunesChart
        || $canViewTopSignalsChart
        || $canViewDamageDeclarationsChart;
    $canViewDashboardKpis = $canViewReports || $canViewStatistics || $canViewPaymentInfo || $canViewReportsMap;
    $canViewRecentReports = $canViewReports;
    $canViewInsightPanel = $canViewStatistics || $canViewPaymentInfo || $canViewReports;
@endphp
@section('header-badges')
    @if ($canViewPaymentInfo)
        <span class="badge-soft">{{ $stats['paid_rate'] }}% taux de paiement</span>
        <span class="badge-soft">{{ number_format($stats['collected_amount'], 0, ',', ' ') }} FCFA collectés</span>
    @endif
    @if ($canViewStatistics)
        <span class="badge-soft">{{ $stats['sla_breached'] }} délai(s) dépassé(s)</span>
        <span class="badge-soft">{{ $stats['resolved_reports'] }} résolus</span>
    @endif
@endsection

@section('content')
    <style>
        .compact-ai-dashboard {
            --dash-blue: #6791ff;
            --dash-pink: #ff0068;
            --dash-orange: #ffa117;
            --dash-green: #5bebaf;
        }
        .compact-ai-dashboard .panel-card,
        .compact-ai-dashboard .stat-card,
        .compact-ai-dashboard .chart-card {
            padding: .9rem;
        }
        .compact-ai-dashboard .hero-card {
            padding: 1.1rem !important;
        }
        .compact-ai-dashboard .hero-strip {
            padding: .65rem;
        }
        .compact-ai-dashboard .h2 {
            font-size: 1.55rem;
            margin-bottom: .4rem !important;
        }
        .compact-ai-dashboard .h4 {
            font-size: 1.15rem;
        }
        .compact-ai-dashboard .stat-value {
            font-size: 1.3rem;
            margin: .25rem 0 .15rem;
        }
        .compact-ai-dashboard .text-secondary.small,
        .compact-ai-dashboard .small {
            font-size: .74rem !important;
        }
        .compact-ai-dashboard .chart-frame {
            min-height: 240px;
        }
        .compact-ai-dashboard .map-frame {
            min-height: 480px;
        }
        .compact-ai-dashboard .map-modal-frame {
            height: calc(100vh - 140px);
            min-height: 640px;
            border-radius: 18px;
            overflow: hidden;
            border: 1px solid rgba(16,42,67,.08);
        }
        .compact-ai-dashboard .map-actions {
            display: flex;
            align-items: center;
            gap: .5rem;
            flex-wrap: wrap;
        }
        .compact-ai-dashboard .table-modern tbody td {
            padding-top: .55rem;
            padding-bottom: .55rem;
            font-size: .84rem;
        }
        .compact-ai-dashboard .hero-card {
            background: #6791ff !important;
        }
        .compact-ai-dashboard .hero-strip {
            background: rgba(255,255,255,.12);
            border: 1px solid rgba(255,255,255,.14);
            border-radius: 22px;
        }
        .compact-ai-dashboard .stat-card {
            position: relative;
            overflow: hidden;
            background: rgba(255,255,255,.96);
            border-top: 4px solid var(--dash-blue);
        }
        .compact-ai-dashboard .stat-card::after {
            content: "";
            position: absolute;
            width: 86px;
            height: 86px;
            right: -38px;
            top: -42px;
            border-radius: 50%;
            background: rgba(103,145,255,.12);
        }
        .compact-ai-dashboard .row.g-2 > div:nth-child(4n+2) .stat-card { border-top-color: var(--dash-pink); }
        .compact-ai-dashboard .row.g-2 > div:nth-child(4n+3) .stat-card { border-top-color: var(--dash-orange); }
        .compact-ai-dashboard .row.g-2 > div:nth-child(4n+4) .stat-card { border-top-color: var(--dash-green); }
        .compact-ai-dashboard .row.g-2 > div:nth-child(4n+2) .stat-card::after { background: rgba(255,0,104,.10); }
        .compact-ai-dashboard .row.g-2 > div:nth-child(4n+3) .stat-card::after { background: rgba(255,161,23,.14); }
        .compact-ai-dashboard .row.g-2 > div:nth-child(4n+4) .stat-card::after { background: rgba(91,235,175,.16); }
        .compact-ai-dashboard .stat-card > * {
            position: relative;
            z-index: 1;
        }
        .compact-ai-dashboard .filter-bar {
            background: rgba(255,161,23,.08);
            border-color: rgba(255,161,23,.20);
        }
        .compact-ai-dashboard .chart-card {
            position: relative;
            overflow: hidden;
        }
        .compact-ai-dashboard .chart-card::before {
            content: "";
            position: absolute;
            inset: 0 0 auto 0;
            height: 4px;
            background: var(--dash-blue);
        }
        .compact-ai-dashboard .row.g-3 > div:nth-child(4n+2) .chart-card::before { background: var(--dash-pink); }
        .compact-ai-dashboard .row.g-3 > div:nth-child(4n+3) .chart-card::before { background: var(--dash-orange); }
        .compact-ai-dashboard .row.g-3 > div:nth-child(4n+4) .chart-card::before { background: var(--dash-green); }
        .compact-ai-dashboard .insight-row {
            background: rgba(103,145,255,.08);
            border-color: rgba(103,145,255,.12);
        }
    </style>

    <div class="compact-ai-dashboard">
    <section class="panel-card mb-3">
        <form method="GET" class="filter-bar mb-0">
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small text-secondary">Période</label>
                    <select name="period" class="form-select" onchange="this.form.submit()">
                        <option value="today" @selected($filters['period'] === 'today')>Aujourd'hui</option>
                        <option value="7d" @selected($filters['period'] === '7d')>7 jours</option>
                        <option value="14d" @selected($filters['period'] === '14d')>14 jours</option>
                        <option value="30d" @selected($filters['period'] === '30d')>30 jours</option>
                        <option value="month" @selected($filters['period'] === 'month')>Mois en cours</option>
                        <option value="custom" @selected($filters['period'] === 'custom')>Personnalisée</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-secondary">Commune</label>
                    <select name="commune_id" class="form-select">
                        <option value="">Toutes les communes</option>
                        @foreach ($communes as $commune)
                            <option value="{{ $commune->id }}" @selected((string) $filters['commune_id'] === (string) $commune->id)>{{ $commune->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-secondary">Du</label>
                    <input type="date" name="date_from" value="{{ $filters['date_from']->toDateString() }}" class="form-control" @disabled($filters['period'] !== 'custom')>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-secondary">Au</label>
                    <input type="date" name="date_to" value="{{ $filters['date_to']->toDateString() }}" class="form-control" @disabled($filters['period'] !== 'custom')>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button class="btn btn-dark w-100">Appliquer</button>
                    <a href="{{ route('institution.dashboard') }}" class="btn btn-outline-secondary">Réinitialiser</a>
                </div>
            </div>
        </form>
    </section>

    @if ($canViewDashboardKpis)
        <section class="mb-3">
            <div class="row g-2">
                @if ($canViewReports)
                    <div class="col-md-3">
                        <div class="stat-card h-100">
                            <div class="stat-kicker">Signalements</div>
                            <div class="stat-value">{{ $stats['reports'] }}</div>
                            <div class="text-secondary small">Volume total visible sur ce portail.</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card h-100">
                            <div class="stat-kicker">{{ $canViewPaymentInfo ? 'En attente' : 'En cours' }}</div>
                            <div class="stat-value">{{ $canViewPaymentInfo ? $stats['pending_reports'] : $stats['in_progress_reports'] }}</div>
                            <div class="text-secondary small">
                                {{ $canViewPaymentInfo ? 'Signalements encore en attente de paiement.' : 'Signalements actuellement en traitement.' }}
                            </div>
                        </div>
                    </div>
                @endif
                @if ($canViewPaymentInfo)
                    <div class="col-md-3">
                        <div class="stat-card h-100">
                            <div class="stat-kicker">Payés</div>
                            <div class="stat-value">{{ $stats['paid_reports'] }}</div>
                            <div class="text-secondary small">Signalements payés.</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card h-100">
                            <div class="stat-kicker">Montant collecté</div>
                            <div class="stat-value">{{ number_format($stats['collected_amount'], 0, ',', ' ') }}</div>
                            <div class="text-secondary small">Montant total reçu.</div>
                        </div>
                    </div>
                @endif
                @if ($canViewStatistics)
                    <div class="col-md-3">
                        <div class="stat-card h-100">
                            <div class="stat-kicker">Moyenne par zone</div>
                            <div class="stat-value">{{ $stats['average_reports_per_commune'] }}</div>
                            <div class="text-secondary small">Moyenne de signalements par commune active.</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card h-100">
                            <div class="stat-kicker">Délais dépassés</div>
                            <div class="stat-value">{{ $stats['sla_breached'] }}</div>
                            <div class="text-secondary small">Signalements hors délai sur la période.</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card h-100">
                            <div class="stat-kicker">Points localisés</div>
                            <div class="stat-value">{{ $stats['geo_points'] }}</div>
                            <div class="text-secondary small">Signalements avec position GPS.</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card h-100">
                            <div class="stat-kicker">Résolus</div>
                            <div class="stat-value">{{ $stats['resolved_reports'] }}</div>
                            <div class="text-secondary small">Signalements clôturés par l’institution.</div>
                        </div>
                    </div>
                @endif
            </div>
        </section>
    @endif

    @if ($hasDashboardCharts)
        <section class="mb-3">
            <div class="row g-3">
                @if ($canViewReportsTrendChart)
                    <div class="col-xl-6">
                        <div class="chart-card">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div>
                                    <div class="fw-bold">Tendance des signalements</div>
                                    <div class="text-secondary small">Évolution quotidienne sur les 14 derniers jours.</div>
                                </div>
                            </div>
                            <div id="reportsTrendChart" class="chart-frame"></div>
                        </div>
                    </div>
                @endif
                @if ($canViewPaymentBreakdownChart)
                    <div class="col-xl-6">
                        <div class="chart-card">
                            <div class="fw-bold mb-1">Paiements</div>
                            <div class="text-secondary small mb-3">Répartition des signalements selon leur paiement.</div>
                            <div id="paymentBreakdownChart" class="chart-frame"></div>
                        </div>
                    </div>
                @endif
                @if ($canViewTreatmentBreakdownChart)
                    <div class="col-xl-6">
                        <div class="chart-card">
                            <div class="fw-bold mb-1">Traitement</div>
                            <div class="text-secondary small mb-3">Répartition par niveau de traitement.</div>
                            <div id="treatmentBreakdownChart" class="chart-frame"></div>
                        </div>
                    </div>
                @endif
                @if ($canViewSlaBreakdownChart)
                    <div class="col-xl-6">
                        <div class="chart-card">
                            <div class="fw-bold mb-1">Délais de traitement</div>
                            <div class="text-secondary small mb-3">Respect des délais attendus.</div>
                            <div id="slaBreakdownChart" class="chart-frame"></div>
                        </div>
                    </div>
                @endif
                @if ($canViewTopCommunesChart)
                    <div class="col-xl-6">
                        <div class="chart-card">
                            <div class="fw-bold mb-1">Top communes</div>
                            <div class="text-secondary small mb-3">Zones avec le plus de signalements.</div>
                            <div id="topCommunesChart" class="chart-frame"></div>
                        </div>
                    </div>
                @endif
                @if ($canViewTopSignalsChart)
                    <div class="col-xl-6">
                        <div class="chart-card">
                            <div class="fw-bold mb-1">Top types de signaux</div>
                            <div class="text-secondary small mb-3">Signalements les plus fréquents.</div>
                            <div id="topSignalsChart" class="chart-frame"></div>
                        </div>
                    </div>
                @endif
                @if ($canViewDamageDeclarationsChart)
                    <div class="col-xl-6">
                        <div class="chart-card">
                            <div class="fw-bold mb-1">Traitement des dommages</div>
                            <div class="text-secondary small mb-3">Répartition des demandes de dommage.</div>
                            <div id="damageDeclarationsChart" class="chart-frame"></div>
                        </div>
                    </div>
                @endif
            </div>
        </section>
    @endif

    @if ($canViewReportsMap)
        <section class="mb-3">
            <div class="chart-card">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <div class="fw-bold mb-1">Carte des signalements</div>
                        <div class="text-secondary small">Localisation des signalements de la période sélectionnée.</div>
                    </div>
                    <div class="map-actions">
                        <span class="status-chip">{{ $stats['geo_points'] }} point(s)</span>
                        <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="modal" data-bs-target="#reportsMapModal">Agrandir</button>
                    </div>
                </div>
                <div id="reportsMap" class="map-frame"></div>
            </div>
        </section>

        <div class="modal fade" id="reportsMapModal" tabindex="-1" aria-labelledby="reportsMapModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-fullscreen-lg-down modal-xl modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title" id="reportsMapModalLabel">Carte des signalements</h5>
                            <div class="text-secondary small">Cliquez sur un numéro de signalement pour ouvrir son détail.</div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                    </div>
                    <div class="modal-body">
                        <div id="reportsMapLarge" class="map-modal-frame"></div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if (($identifierGroups ?? collect())->isNotEmpty())
        <section class="panel-card mb-3">
            <div class="d-flex align-items-start justify-content-between flex-wrap gap-3 mb-3">
                <div>
                    <div class="fw-bold mb-1">Regroupements par identifiant</div>
                    <div class="text-secondary small">
                        Signalements ouverts liés aux identifiants géolocalisés sur une surface de {{ number_format($groupingSurfaceSquareMeters, 0, ',', ' ') }} m².
                    </div>
                </div>
                <a href="{{ route('institution.reports.index', $dashboardReportFilterQuery ?? []) }}" class="btn btn-sm btn-outline-secondary">Voir tous les signalements</a>
            </div>

            <div class="row g-3">
                @foreach ($identifierGroups as $group)
                    <div class="col-md-6 col-xl-4">
                        <div class="surface-soft h-100 p-3 border rounded-3">
                            <div class="d-flex justify-content-between gap-3 mb-2">
                                <div>
                                    <div class="fw-semibold">{{ $group['label'] }}</div>
                                    <div class="small text-secondary">{{ $group['area_label'] }}</div>
                                </div>
                                <span class="status-chip chip-warning">{{ number_format($group['open_reports_count'], 0, ',', ' ') }} à traiter</span>
                            </div>
                            <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between">
                                <span class="status-chip chip-neutral">{{ number_format($groupingSideMeters, 0, ',', ' ') }} m de côté</span>
                                <a href="{{ route('institution.reports.index', array_merge($dashboardReportFilterQuery ?? [], ['identifier_group' => $group['key']])) }}" class="btn btn-sm btn-dark">Voir la liste</a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    @if ($canViewInsightPanel || $canViewRecentReports)
        <section class="row g-3">
            @if ($canViewInsightPanel)
                <div class="col-xl-5">
                    <div class="panel-card h-100">
                        <div class="fw-bold mb-1">Points importants</div>
                        <div class="insight-list">
                            @if ($canViewPaymentInfo || $canViewStatistics)
                                <div class="insight-row">
                                    <div>
                                        <div class="fw-semibold">{{ $canViewPaymentInfo ? 'Taux de paiement' : 'Taux de résolution' }}</div>
                                        <div class="small text-secondary">
                                            {{ $canViewPaymentInfo ? 'Part des signalements déjà payés.' : 'Part des signalements clôturés sur la période.' }}
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        @php
                                            $primaryInsightValue = $canViewPaymentInfo
                                                ? $stats['paid_rate']
                                                : ($stats['reports'] > 0 ? (int) round(($stats['resolved_reports'] / $stats['reports']) * 100) : 0);
                                        @endphp
                                        <div class="fw-bold">{{ $primaryInsightValue }}%</div>
                                        <div class="progress-soft mt-2" style="width: 120px;"><span style="width: {{ $primaryInsightValue }}%"></span></div>
                                    </div>
                                </div>
                            @endif
                            @if ($canViewStatistics)
                                <div class="insight-row">
                                    <div>
                                        <div class="fw-semibold">Pression par commune</div>
                                        <div class="small text-secondary">Permet d’identifier rapidement les zones critiques.</div>
                                    </div>
                                    <div class="fw-bold">{{ $topCommunes->first()->label ?? '-' }}</div>
                                </div>
                                <div class="insight-row">
                                    <div>
                                        <div class="fw-semibold">Communes actives</div>
                                        <div class="small text-secondary">Nombre de zones ayant au moins un signalement sur la période.</div>
                                    </div>
                                    <div class="fw-bold">{{ $stats['active_communes'] }}</div>
                                </div>
                            @endif
                            @if ($canViewPaymentInfo)
                                <div class="insight-row">
                                    <div>
                                        <div class="fw-semibold">Montant collecté</div>
                                        <div class="small text-secondary">Total des paiements liés aux signalements.</div>
                                    </div>
                                    <div class="fw-bold">{{ number_format($stats['collected_amount'], 0, ',', ' ') }} FCFA</div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
            @if ($canViewRecentReports)
                <div class="col-xl-7">
                    <section class="panel-card h-100">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div>
                                <div class="fw-bold">Derniers signalements visibles</div>
                                <div class="text-secondary small">Les signalements les plus récents.</div>
                            </div>
                        </div>

                        @if ($recentReports->isEmpty())
                            <div class="text-secondary">Aucun signalement disponible pour ce portail pour le moment.</div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-modern align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Référence</th>
                                            <th>Signal</th>
                                            <th>Commune</th>
                                            @if ($canViewPaymentInfo)
                                                <th>Paiement</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($recentReports as $report)
                                            <tr>
                                                <td class="fw-semibold">{{ $report->reference }}</td>
                                                <td>{{ $report->signal_label ?? $report->incident_type }}</td>
                                                <td>{{ $report->commune?->name ?: '-' }}</td>
                                                @if ($canViewPaymentInfo)
                                                    <td><span class="status-chip">{{ $label::payment($report->payment_status) }}</span></td>
                                                @endif
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </section>
                </div>
            @endif
        </section>
    @endif

    @if (! $canViewDashboardKpis && ! $hasDashboardCharts && ! $canViewInsightPanel && ! $canViewRecentReports && ! $canViewReportsMap)
        <section class="panel-card">
            <div class="fw-bold mb-2">Accès limité</div>
            <div class="text-secondary small">
                Ce compte ne dispose pas encore des autorisations nécessaires pour consulter ces informations.
            </div>
        </section>
    @endif
    </div>
@endsection

@section('scripts')
    @if ($hasDashboardCharts)
        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    @endif
    @if ($canViewReportsMap)
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.css" crossorigin="">
        <script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
    @endif
    <script>
        @if ($canViewReportsMap)
            const mapReports = @json($mapReports);
        @endif
        @if ($canViewReportsTrendChart)
            const trendSeries = @json($trend->pluck('value')->all());
            const trendLabels = @json($trend->pluck('label')->all());

            new ApexCharts(document.querySelector('#reportsTrendChart'), {
                chart: { type: 'area', height: 300, toolbar: { show: false }, zoom: { enabled: false } },
                series: [{ name: 'Signalements', data: trendSeries }],
                xaxis: {
                    categories: trendLabels,
                    labels: { style: { colors: '#6b7c93' } },
                    axisBorder: { show: false },
                    axisTicks: { show: false }
                },
                yaxis: { labels: { style: { colors: '#6b7c93' } } },
                stroke: { curve: 'smooth', width: 3 },
                dataLabels: { enabled: false },
                colors: ['#6791ff'],
                fill: {
                    type: 'gradient',
                    gradient: { shadeIntensity: 1, opacityFrom: 0.34, opacityTo: 0.04 }
                },
                grid: { borderColor: 'rgba(16,42,67,.08)', strokeDashArray: 4 },
                tooltip: { theme: 'light' },
                legend: { show: false }
            }).render();
        @endif

        @if ($canViewPaymentBreakdownChart)
            const paymentBreakdown = @json(array_values($paymentBreakdown));
            const paymentLabels = ['En attente', 'Payés', 'Échoués'];

            new ApexCharts(document.querySelector('#paymentBreakdownChart'), {
                chart: { type: 'donut', height: 300 },
                series: paymentBreakdown,
                labels: paymentLabels,
                colors: ['#ffa117', '#5bebaf', '#ff0068'],
                legend: { position: 'bottom', fontSize: '13px' },
                dataLabels: { enabled: false },
                plotOptions: { pie: { donut: { size: '72%' } } }
            }).render();
        @endif

        @if ($canViewSlaBreakdownChart)
            const slaBreakdown = @json(array_values($slaBreakdown));
            const slaLabels = ['Dans le délai', 'À surveiller', 'Délai dépassé', 'Non configuré'];

            new ApexCharts(document.querySelector('#slaBreakdownChart'), {
                chart: { type: 'donut', height: 300 },
                series: slaBreakdown,
                labels: slaLabels,
                colors: ['#5bebaf', '#ffa117', '#ff0068', '#6791ff'],
                legend: { position: 'bottom', fontSize: '13px' },
                dataLabels: { enabled: false },
                plotOptions: { pie: { donut: { size: '72%' } } }
            }).render();
        @endif

        @if ($canViewTreatmentBreakdownChart)
            const treatmentBreakdown = @json(array_values($treatmentBreakdown));
            const treatmentLabels = ['Soumis', 'En cours', 'Résolus', 'Rejetés'];

            new ApexCharts(document.querySelector('#treatmentBreakdownChart'), {
                chart: { type: 'donut', height: 300 },
                series: treatmentBreakdown,
                labels: treatmentLabels,
                colors: ['#ffa117', '#6791ff', '#5bebaf', '#ff0068'],
                legend: { position: 'bottom', fontSize: '13px' },
                dataLabels: { enabled: false },
                plotOptions: { pie: { donut: { size: '72%' } } }
            }).render();
        @endif

        @if ($canViewTopCommunesChart)
            const topCommuneSeries = @json($topCommunes->pluck('total')->all());
            const topCommuneLabels = @json($topCommunes->pluck('label')->all());

            new ApexCharts(document.querySelector('#topCommunesChart'), {
                chart: { type: 'bar', height: 300, toolbar: { show: false } },
                series: [{ name: 'Signalements', data: topCommuneSeries }],
                xaxis: { categories: topCommuneLabels, labels: { style: { colors: '#6b7c93' } } },
                yaxis: { labels: { style: { colors: '#6b7c93' } } },
                plotOptions: { bar: { borderRadius: 8, columnWidth: '48%' } },
                dataLabels: { enabled: false },
                colors: ['#ffa117'],
                grid: { borderColor: 'rgba(16,42,67,.08)', strokeDashArray: 4 },
                legend: { show: false }
            }).render();
        @endif

        @if ($canViewTopSignalsChart)
            const topSignalSeries = @json($topSignals->pluck('total')->all());
            const topSignalLabels = @json($topSignals->pluck('label')->all());

            new ApexCharts(document.querySelector('#topSignalsChart'), {
                chart: { type: 'bar', height: 300, toolbar: { show: false } },
                series: [{ name: 'Signalements', data: topSignalSeries }],
                xaxis: {
                    categories: topSignalLabels,
                    labels: { style: { colors: '#6b7c93' }, rotate: -15 }
                },
                yaxis: { labels: { style: { colors: '#6b7c93' } } },
                plotOptions: { bar: { borderRadius: 8, columnWidth: '48%' } },
                dataLabels: { enabled: false },
                colors: ['#ff0068'],
                grid: { borderColor: 'rgba(16,42,67,.08)', strokeDashArray: 4 },
                legend: { show: false }
            }).render();
        @endif

        @if ($canViewDamageDeclarationsChart)
            const damageResolutionBreakdown = @json(array_values($damageResolutionBreakdown));
            const damageResolutionLabels = ['Soumis', 'En cours', 'Résolus', 'Rejetés'];

            new ApexCharts(document.querySelector('#damageDeclarationsChart'), {
                chart: { type: 'donut', height: 300 },
                series: damageResolutionBreakdown,
                labels: damageResolutionLabels,
                colors: ['#ffa117', '#6791ff', '#5bebaf', '#ff0068'],
                legend: { position: 'bottom', fontSize: '13px' },
                dataLabels: { enabled: false },
                plotOptions: { pie: { donut: { size: '72%' } } }
            }).render();
        @endif

        @if ($canViewReportsMap)
        const reportStatusMeta = {
            submitted: { label: 'Soumis', color: '#ffa117', shadow: 'rgba(255,161,23,.35)' },
            in_progress: { label: 'En cours', color: '#6791ff', shadow: 'rgba(103,145,255,.35)' },
            rejected: { label: 'Rejeté', color: '#ff0068', shadow: 'rgba(255,0,104,.32)' },
            resolved: { label: 'Résolu', color: '#5bebaf', shadow: 'rgba(91,235,175,.35)' }
        };

        const escapeMapText = (value) => String(value ?? '-')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');

        function buildSignalIcon(status) {
            const meta = reportStatusMeta[status] || reportStatusMeta.submitted;

            return L.divIcon({
                className: 'signal-map-icon',
                html: `
                    <div style="
                        width: 22px;
                        height: 22px;
                        border-radius: 50% 50% 50% 0;
                        background: ${meta.color};
                        transform: rotate(-45deg);
                        border: 2px solid #ffffff;
                        box-shadow: 0 8px 18px ${meta.shadow};
                        position: relative;
                    ">
                        <span style="
                            position: absolute;
                            inset: 0;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            transform: rotate(45deg);
                            color: #ffffff;
                            font-size: 11px;
                            font-weight: 800;
                            line-height: 1;
                        ">!</span>
                    </div>
                `,
                iconSize: [22, 22],
                iconAnchor: [11, 22],
                popupAnchor: [0, -18]
            });
        }

        function renderReportsMap(elementId, options = {}) {
            const element = document.getElementById(elementId);

            if (!element || element.dataset.ready === '1') {
                return element?._leafletMap || null;
            }

            const map = L.map(elementId, {
                scrollWheelZoom: Boolean(options.scrollWheelZoom)
            });

            element.dataset.ready = '1';
            element._leafletMap = map;

            L.tileLayer(@json(url('/map-tiles').'/{s}/{z}/{x}/{y}.png'), {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap'
            }).addTo(map);

            if (mapReports.length) {
                const bounds = [];

                mapReports.forEach((report) => {
                    const statusMeta = reportStatusMeta[report.status] || reportStatusMeta.submitted;
                    const marker = L.marker([report.latitude, report.longitude], {
                        icon: buildSignalIcon(report.status)
                    }).addTo(map);

                    marker.bindPopup(`
                        <div style="min-width: 200px;">
                            <a href="${escapeMapText(report.detail_url)}" target="_blank" rel="noopener" style="font-weight: 700; margin-bottom: 4px; display: inline-block; color: #183447; text-decoration: none;">${escapeMapText(report.reference || ('#' + report.id))}</a>
                            <div style="font-size: 12px; color: #5b6b7a;">${escapeMapText(report.signal_label || 'Signalement')}</div>
                            <div style="font-size: 12px; margin-top: 6px;">État : ${escapeMapText(statusMeta.label)}</div>
                            <div style="font-size: 12px; margin-top: 6px;">Délai attendu : ${escapeMapText(report.target_sla_hours ?? '-')} h</div>
                            <a href="${escapeMapText(report.detail_url)}" target="_blank" rel="noopener" style="font-size: 12px; margin-top: 8px; display: inline-block;">Voir le détail</a>
                        </div>
                    `);

                    bounds.push([report.latitude, report.longitude]);
                });

                map.fitBounds(bounds, { padding: [28, 28], maxZoom: 14 });
            } else {
                map.setView([7.54, -5.55], 7);
            }

            return map;
        }

        renderReportsMap('reportsMap');

        document.getElementById('reportsMapModal')?.addEventListener('shown.bs.modal', () => {
            const map = renderReportsMap('reportsMapLarge', { scrollWheelZoom: true });
            setTimeout(() => map?.invalidateSize(), 120);
        });
        @endif
    </script>
@endsection
