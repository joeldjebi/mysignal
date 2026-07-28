@extends('institution.layouts.app')

@section('title', config('app.name').' | Statistiques')
@section('page-title', 'Statistiques')
@section('page-description', 'Indicateurs des signalements visibles par votre institution.')

@section('content')
    @php
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
    @endphp
    <section class="panel-card mb-4">
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
                    <a href="{{ route('institution.statistics.index') }}" class="btn btn-outline-secondary">Réinitialiser</a>
                </div>
            </div>
        </form>
    </section>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat-card h-100">
                <div class="stat-kicker">Signalements</div>
                <div class="stat-value">{{ $stats['reports'] }}</div>
                <div class="text-secondary small">Volume total de signalements visibles.</div>
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
    </div>

    <div class="row g-4">
        @if ($hasDashboardCharts)
            <div class="col-12">
                <section class="mb-0">
                    <div class="row g-4">
                        @if ($canViewReportsTrendChart)
                            <div class="col-xl-6">
                                <div class="chart-card">
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <div>
                                            <div class="fw-bold">Tendance des signalements</div>
                                            <div class="text-secondary small">Évolution quotidienne sur la période sélectionnée.</div>
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
                                <div class="text-secondary small mb-3">Signalements les plus fréquents sur la période.</div>
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
            </div>
        @endif

        @if ($canViewReportsMap)
            <div class="col-12">
                <section class="mb-0">
                    <div class="chart-card">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div>
                                <div class="fw-bold mb-1">Carte des signalements</div>
                                <div class="text-secondary small">Localisation des signalements de la période sélectionnée.</div>
                            </div>
                            <span class="status-chip">{{ $stats['geo_points'] }} point(s)</span>
                        </div>
                        <div id="reportsMap" class="map-frame"></div>
                    </div>
                </section>
            </div>
        @endif

        <div class="col-xl-6">
            <section class="panel-card">
                <div class="fw-bold mb-3">Top communes</div>
                <div class="table-responsive">
                    <table class="table table-modern align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Commune</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($byCommune as $row)
                                <tr>
                                    <td>{{ $row->commune_name }}</td>
                                    <td class="text-end fw-semibold">{{ $row->total }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="2" class="text-center text-secondary">Aucune donnée disponible.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
        <div class="col-xl-6">
            <section class="panel-card">
                <div class="fw-bold mb-3">Top types de signaux</div>
                <div class="table-responsive">
                    <table class="table table-modern align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Signal</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($bySignal as $row)
                                <tr>
                                    <td>
                                        <div>{{ $row->signal_label ?: 'Signalement' }}</div>
                                    </td>
                                    <td class="text-end fw-semibold">{{ $row->total }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="2" class="text-center text-secondary">Aucune donnée disponible.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
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
                colors: ['#194b70'],
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
                colors: ['#194b70', '#c49b48', '#c95f5f'],
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
                colors: ['#1f7a4f', '#c49b48', '#c95f5f', '#9aa7b3'],
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
                colors: ['#9aa7b3', '#194b70', '#1f7a4f', '#c95f5f'],
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
                colors: ['#c49b48'],
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
                colors: ['#0f2940'],
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
                colors: ['#c49b48', '#194b70', '#1f7a4f', '#c95f5f'],
                legend: { position: 'bottom', fontSize: '13px' },
                dataLabels: { enabled: false },
                plotOptions: { pie: { donut: { size: '72%' } } }
            }).render();
        @endif

        @if ($canViewReportsMap)
        const mapElement = document.querySelector('#reportsMap');

        if (mapElement) {
            const map = L.map('reportsMap', {
                scrollWheelZoom: false
            });

            const reportStatusMeta = {
                submitted: { label: 'Soumis', color: '#ffa117', shadow: 'rgba(255,161,23,.35)' },
                in_progress: { label: 'En cours', color: '#6791ff', shadow: 'rgba(103,145,255,.35)' },
                rejected: { label: 'Rejeté', color: '#ff0068', shadow: 'rgba(255,0,104,.32)' }
            };

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
                        <div style="min-width: 180px;">
                            <div style="font-weight: 700; margin-bottom: 4px;">${report.reference}</div>
                            <div style="font-size: 12px; color: #5b6b7a;">${report.signal_label || 'Signalement'}</div>
                            <div style="font-size: 12px; margin-top: 6px;">Statut: ${statusMeta.label}</div>
                            <div style="font-size: 12px; margin-top: 6px;">Délai attendu: ${report.target_sla_hours ?? '-'} h</div>
                        </div>
                    `);

                    bounds.push([report.latitude, report.longitude]);
                });

                map.fitBounds(bounds, { padding: [30, 30] });
            } else {
                map.setView([5.3364, -4.0267], 11);
            }
        }
        @endif
    </script>
@endsection
