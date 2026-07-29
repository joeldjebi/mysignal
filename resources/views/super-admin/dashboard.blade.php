@extends('super-admin.layouts.app')

@section('title', config('app.name').' | Tableau de bord Super Admin')
@section('page-title', 'Tableau de bord Super Admin')
@section('page-description', 'Vue globale des catégories, institutions, usagers, signalements, dommages, paiements et délais de traitement.')

@section('header-badges')
    <span class="badge-soft">{{ $stats['active_applications'] }} catégorie{{ $stats['active_applications'] > 1 ? 's' : '' }} active{{ $stats['active_applications'] > 1 ? 's' : '' }}</span>
    <span class="badge-soft">{{ $stats['active_organizations'] }} institution{{ $stats['active_organizations'] > 1 ? 's' : '' }} active{{ $stats['active_organizations'] > 1 ? 's' : '' }}</span>
    <span class="badge-soft">{{ number_format($stats['collected_amount'], 0, ',', ' ') }} FCFA collectés</span>
@endsection

@section('content')
    <style>
        .compact-sa-dashboard {
            --dash-blue: #6791ff;
            --dash-pink: #ff0068;
            --dash-orange: #ffa117;
            --dash-green: #5bebaf;
            --dash-text: #183447;
            --dash-muted: #6b7c93;
        }
        .compact-sa-dashboard .panel-card,
        .compact-sa-dashboard .chart-card {
            padding: .95rem;
        }
        .compact-sa-dashboard .small {
            font-size: .73rem !important;
        }
        .compact-sa-dashboard .metric-value {
            font-size: 1.45rem;
            font-weight: 650;
            line-height: 1.1;
            margin: .2rem 0 .15rem;
            color: var(--dash-text);
        }
        .compact-sa-dashboard .metric-kicker {
            color: var(--dash-muted);
            font-size: .7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .08em;
        }
        .compact-sa-dashboard .fw-bold {
            font-weight: 600 !important;
        }
        .compact-sa-dashboard .fw-semibold {
            font-weight: 500 !important;
        }
        .compact-sa-dashboard .h4 {
            font-weight: 600 !important;
        }
        .compact-sa-dashboard .metric-card {
            border: 1px solid rgba(16,42,67,.08);
            border-radius: 20px;
            background: rgba(255,255,255,.88);
            padding: .9rem;
            height: 100%;
            border-top: 4px solid var(--dash-blue);
        }
        .compact-sa-dashboard .row.g-2 > div:nth-child(4n+2) .metric-card { border-top-color: var(--dash-pink); }
        .compact-sa-dashboard .row.g-2 > div:nth-child(4n+3) .metric-card { border-top-color: var(--dash-orange); }
        .compact-sa-dashboard .row.g-2 > div:nth-child(4n+4) .metric-card { border-top-color: var(--dash-green); }
        .compact-sa-dashboard .chart-card {
            border: 1px solid rgba(16,42,67,.08);
            border-radius: 22px;
            background: rgba(255,255,255,.94);
            box-shadow: 0 18px 44px rgba(16,42,67,.07);
            height: 100%;
            position: relative;
            overflow: hidden;
        }
        .compact-sa-dashboard .chart-card::before {
            content: "";
            position: absolute;
            inset: 0 0 auto 0;
            height: 4px;
            background: var(--dash-blue);
        }
        .compact-sa-dashboard .chart-frame {
            min-height: 280px;
        }
        .compact-sa-dashboard .map-frame {
            min-height: 360px;
            border-radius: 18px;
            overflow: hidden;
            border: 1px solid rgba(16,42,67,.08);
        }
        .compact-sa-dashboard .map-modal-frame {
            height: calc(100vh - 170px);
            min-height: 520px;
            border-radius: 18px;
            overflow: hidden;
            border: 1px solid rgba(16,42,67,.08);
        }
        .compact-sa-dashboard .map-actions {
            display: flex;
            align-items: center;
            gap: .5rem;
            flex-wrap: wrap;
        }
        .compact-sa-dashboard .table-modern tbody td {
            padding-top: .58rem;
            padding-bottom: .58rem;
            font-size: .84rem;
        }
    </style>

    <div class="compact-sa-dashboard">
        <section class="row g-2 mb-3">
            <div class="col-md-6 col-xl-3">
                <div class="metric-card">
                    <div class="metric-kicker">Usagers publics</div>
                    <div class="metric-value">{{ $stats['public_users'] }}</div>
                    <div class="small text-secondary">{{ $stats['public_business_users'] }} comptes entreprise.</div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="metric-card">
                    <div class="metric-kicker">Signalements</div>
                    <div class="metric-value">{{ $stats['reports'] }}</div>
                    <div class="small text-secondary">{{ $stats['report_resolution_rate'] }}% résolus.</div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="metric-card">
                    <div class="metric-kicker">Dommages</div>
                    <div class="metric-value">{{ $stats['damages'] }}</div>
                    <div class="small text-secondary">{{ $stats['damage_resolution_rate'] }}% résolus.</div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="metric-card">
                    <div class="metric-kicker">Paiements</div>
                    <div class="metric-value">{{ $stats['payments'] }}</div>
                    <div class="small text-secondary">{{ $stats['paid_payments'] }} paiements confirmés.</div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="metric-card">
                    <div class="metric-kicker">Catégories</div>
                    <div class="metric-value">{{ $stats['applications'] }}</div>
                    <div class="small text-secondary">{{ $stats['active_applications'] }} actives dans la plateforme.</div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="metric-card">
                    <div class="metric-kicker">Institutions</div>
                    <div class="metric-value">{{ $stats['organizations'] }}</div>
                    <div class="small text-secondary">{{ $stats['active_organizations'] }} institutions actives.</div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="metric-card">
                    <div class="metric-kicker">Compteurs</div>
                    <div class="metric-value">{{ $stats['meters'] }}</div>
                    <div class="small text-secondary">Base terrain attachée aux usagers.</div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="metric-card">
                    <div class="metric-kicker">Respect des délais</div>
                    <div class="metric-value">{{ $stats['sla_compliance_rate'] }}%</div>
                    <div class="small text-secondary">{{ $stats['sla_breached'] }} dossier(s) hors délai.</div>
                </div>
            </div>
        </section>

        <section class="row g-3 mb-3">
            <div class="col-xl-6">
                <div class="chart-card">
                    <div class="fw-bold mb-1">État des signalements</div>
                    <div class="text-secondary small mb-3">Répartition globale des dossiers dans la plateforme.</div>
                    <div id="saReportStatusChart" class="chart-frame"></div>
                </div>
            </div>
            <div class="col-xl-6">
                <div class="chart-card">
                    <div class="fw-bold mb-1">État des dommages</div>
                    <div class="text-secondary small mb-3">État de traitement des dommages déclarés par les usagers.</div>
                    <div id="saDamageStatusChart" class="chart-frame"></div>
                </div>
            </div>
            <div class="col-xl-6">
                <div class="chart-card">
                    <div class="fw-bold mb-1">État des paiements</div>
                    <div class="text-secondary small mb-3">Vue globale des paiements enregistrés.</div>
                    <div id="saPaymentStatusChart" class="chart-frame"></div>
                </div>
            </div>
            <div class="col-xl-6">
                <div class="chart-card">
                    <div class="fw-bold mb-1">Respect des délais</div>
                    <div class="text-secondary small mb-3">Dossiers dans les délais, à surveiller ou en retard.</div>
                    <div id="saSlaStatusChart" class="chart-frame"></div>
                </div>
            </div>
            <div class="col-xl-12">
                <div class="chart-card">
                    <div class="fw-bold mb-1">Performance par catégorie</div>
                    <div class="text-secondary small mb-3">Catégories qui concentrent le plus de signalements.</div>
                    <div id="saApplicationPerformanceChart" class="chart-frame"></div>
                    @if ($applicationPerformance->isNotEmpty())
                        <div class="d-flex flex-wrap gap-2 mt-3">
                            @foreach ($applicationPerformance as $applicationItem)
                                <a href="{{ route('super-admin.organizations.index', ['application_id' => $applicationItem->id]) }}" class="btn btn-sm btn-outline-dark">
                                    {{ $applicationItem->name }} · Voir les institutions
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
            <div class="col-xl-12">
                <div class="chart-card">
                    <div class="fw-bold mb-1">Top institutions</div>
                    <div class="text-secondary small mb-3">Institutions avec le plus grand volume de dossiers.</div>
                    <div id="saOrganizationPerformanceChart" class="chart-frame"></div>
                </div>
            </div>
            <div class="col-xl-12">
                <div class="chart-card">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <div class="fw-bold mb-1">Carte des signalements</div>
                            <div class="text-secondary small">Visualisation globale des positions disponibles sur la plateforme.</div>
                        </div>
                        <div class="map-actions">
                            <span class="status-chip">{{ count($mapReports) }} point(s)</span>
                            <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="modal" data-bs-target="#saReportsMapModal">Agrandir</button>
                        </div>
                    </div>
                    <div id="saReportsMap" class="map-frame"></div>
                </div>
            </div>
        </section>

        <div class="modal fade" id="saReportsMapModal" tabindex="-1" aria-labelledby="saReportsMapModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-fullscreen-lg-down modal-xl modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title" id="saReportsMapModalLabel">Carte des signalements</h5>
                            <div class="text-secondary small">Cliquez sur un numéro de signalement pour ouvrir son détail.</div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                    </div>
                    <div class="modal-body">
                        <div id="saReportsMapLarge" class="map-modal-frame"></div>
                    </div>
                </div>
            </div>
        </div>

        <section class="row g-3 mb-3">
            <div class="col-xl-4">
                <div class="panel-card h-100">
                    <div class="fw-bold mb-2">Top types de signaux</div>
                    <div class="small text-secondary mb-3">Les incidents les plus fréquemment déclarés.</div>
                    <div class="vstack gap-2">
                        @forelse ($topSignals as $signal)
                            <div class="d-flex justify-content-between align-items-center border rounded-4 p-3">
                                <div class="fw-semibold">{{ $signal->label }}</div>
                                <span class="status-chip">{{ $signal->total }}</span>
                            </div>
                        @empty
                            <div class="text-secondary">Aucune donnée disponible.</div>
                        @endforelse
                    </div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="panel-card h-100">
                    <div class="fw-bold mb-2">Top communes</div>
                    <div class="small text-secondary mb-3">Les zones qui concentrent le plus de signalements.</div>
                    <div class="vstack gap-2">
                        @forelse ($topCommunes as $commune)
                            <div class="d-flex justify-content-between align-items-center border rounded-4 p-3">
                                <div class="fw-semibold">{{ $commune->label }}</div>
                                <span class="status-chip">{{ $commune->total }}</span>
                            </div>
                        @empty
                            <div class="text-secondary">Aucune donnée disponible.</div>
                        @endforelse
                    </div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="panel-card h-100">
                    <div class="fw-bold mb-2">Usagers par type</div>
                    <div class="small text-secondary mb-3">Répartition des comptes publics selon le type d’usager.</div>
                    <div class="vstack gap-2">
                        @forelse ($publicUserTypeBreakdown as $publicUserType)
                            <div class="d-flex justify-content-between align-items-center border rounded-4 p-3">
                                <div>
                                    <div class="fw-semibold">{{ $publicUserType->name }}</div>
                                    <div class="small text-secondary">{{ $publicUserType->pricingRule?->label ?: 'Sans tarification' }}</div>
                                </div>
                                <span class="status-chip">{{ $publicUserType->public_users_count }}</span>
                            </div>
                        @empty
                            <div class="text-secondary">Aucun type d’usager configuré.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </section>

        <section class="row g-3">
            <div class="col-xl-8">
                <div class="panel-card h-100">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div>
                            <div class="fw-bold">Derniers signalements</div>
                            <div class="text-secondary small">Vue rapide sur les derniers incidents remontés dans la plateforme.</div>
                        </div>
                        <span class="status-chip">Supervision globale</span>
                    </div>

                    @if ($recentReports->isEmpty())
                        <div class="text-secondary">Aucun signalement enregistré pour le moment.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-modern align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Numéro</th>
                                        <th>Catégorie</th>
                                        <th>Institution</th>
                                        <th>Signal</th>
                                        <th>Commune</th>
                                        <th>Paiement</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($recentReports as $report)
                                        <tr>
                                            <td class="fw-semibold">{{ $report->reference ?: '#'.$report->id }}</td>
                                            <td>{{ $report->application?->name ?: '-' }}</td>
                                            <td>{{ $report->organization?->name ?: '-' }}</td>
                                            <td>{{ $report->display_signal_label }}</td>
                                            <td>{{ $report->commune?->name ?: '-' }}</td>
                                            <td><span class="status-chip">{{ $report->display_payment_status }}</span></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
            <div class="col-xl-4">
                <div class="panel-card mb-3">
                    <div class="fw-bold mb-2">Pilotage plateforme</div>
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="border rounded-4 p-3">
                                <div class="small text-secondary">Admins institutionnels</div>
                                <div class="h4 mb-0 fw-bold">{{ $stats['institution_admins'] }}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded-4 p-3">
                                <div class="small text-secondary">Super administrateurs</div>
                                <div class="h4 mb-0 fw-bold">{{ $stats['super_admins'] }}</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border rounded-4 p-3">
                                <div class="small text-secondary">Pays</div>
                                <div class="h4 mb-0 fw-bold">{{ $stats['countries'] }}</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border rounded-4 p-3">
                                <div class="small text-secondary">Villes</div>
                                <div class="h4 mb-0 fw-bold">{{ $stats['cities'] }}</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border rounded-4 p-3">
                                <div class="small text-secondary">Communes</div>
                                <div class="h4 mb-0 fw-bold">{{ $stats['communes'] }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="panel-card">
                    <div class="fw-bold mb-2">Tarification active</div>
                    @if ($pricingRules->isEmpty())
                        <div class="text-secondary">Aucune règle de tarification disponible.</div>
                    @else
                        <div class="vstack gap-2">
                            @foreach ($pricingRules as $pricingRule)
                                <div class="border rounded-4 p-3">
                                    <div class="fw-bold">{{ $pricingRule->label }}</div>
                                    <div class="mt-2 fw-semibold">{{ number_format($pricingRule->amount, 0, ',', ' ') }} {{ $pricingRule->currency }}</div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </section>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.css" crossorigin="">
    <script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
    <script>
        const saReportStatusSeries = @json(array_values($reportStatusBreakdown));
        const saDamageStatusSeries = @json(array_values($damageStatusBreakdown));
        const saPaymentStatusSeries = @json(array_values($paymentStatusBreakdown));
        const saSlaSeries = @json(array_values($slaBreakdown));
        const saApplicationLabels = @json($applicationPerformance->pluck('name')->all());
        const saApplicationSeries = @json($applicationPerformance->pluck('reports_count')->map(fn ($value) => (int) $value)->all());
        const saOrganizationLabels = @json($organizationPerformance->pluck('name')->all());
        const saOrganizationSeries = @json($organizationPerformance->pluck('reports_count')->map(fn ($value) => (int) $value)->all());
        const saMapReports = @json($mapReports);

        new ApexCharts(document.querySelector('#saReportStatusChart'), {
            chart: { type: 'donut', height: 300 },
            series: saReportStatusSeries,
            labels: ['Soumis', 'En cours', 'Résolus', 'Rejetés'],
            colors: ['#ffa117', '#6791ff', '#5bebaf', '#ff0068'],
            legend: { position: 'bottom', fontSize: '13px' },
            dataLabels: { enabled: false },
            plotOptions: { pie: { donut: { size: '72%' } } }
        }).render();

        new ApexCharts(document.querySelector('#saDamageStatusChart'), {
            chart: { type: 'donut', height: 300 },
            series: saDamageStatusSeries,
            labels: ['Soumis', 'En cours', 'Résolus', 'Rejetés'],
            colors: ['#ffa117', '#6791ff', '#5bebaf', '#ff0068'],
            legend: { position: 'bottom', fontSize: '13px' },
            dataLabels: { enabled: false },
            plotOptions: { pie: { donut: { size: '72%' } } }
        }).render();

        new ApexCharts(document.querySelector('#saPaymentStatusChart'), {
            chart: { type: 'donut', height: 300 },
            series: saPaymentStatusSeries,
            labels: ['En attente', 'Payés', 'Échoués'],
            colors: ['#ffa117', '#5bebaf', '#ff0068'],
            legend: { position: 'bottom', fontSize: '13px' },
            dataLabels: { enabled: false },
            plotOptions: { pie: { donut: { size: '72%' } } }
        }).render();

        new ApexCharts(document.querySelector('#saSlaStatusChart'), {
            chart: { type: 'donut', height: 300 },
            series: saSlaSeries,
            labels: ['Dans le délai', 'À surveiller', 'En retard', 'Sans délai défini'],
            colors: ['#5bebaf', '#ffa117', '#ff0068', '#6791ff'],
            legend: { position: 'bottom', fontSize: '13px' },
            dataLabels: { enabled: false },
            plotOptions: { pie: { donut: { size: '72%' } } }
        }).render();

        new ApexCharts(document.querySelector('#saApplicationPerformanceChart'), {
            chart: { type: 'bar', height: 300, toolbar: { show: false } },
            series: [{ name: 'Signalements', data: saApplicationSeries }],
            xaxis: { categories: saApplicationLabels, labels: { style: { colors: '#6b7c93' }, rotate: -12 } },
            yaxis: { labels: { style: { colors: '#6b7c93' } } },
            plotOptions: { bar: { borderRadius: 8, columnWidth: '48%' } },
            dataLabels: { enabled: false },
            colors: ['#6791ff'],
            grid: { borderColor: 'rgba(16,42,67,.08)', strokeDashArray: 4 },
            legend: { show: false }
        }).render();

        new ApexCharts(document.querySelector('#saOrganizationPerformanceChart'), {
            chart: { type: 'bar', height: 300, toolbar: { show: false } },
            series: [{ name: 'Signalements', data: saOrganizationSeries }],
            xaxis: { categories: saOrganizationLabels, labels: { style: { colors: '#6b7c93' }, rotate: -12 } },
            yaxis: { labels: { style: { colors: '#6b7c93' } } },
            plotOptions: { bar: { borderRadius: 8, columnWidth: '48%' } },
            dataLabels: { enabled: false },
            colors: ['#ff0068'],
            grid: { borderColor: 'rgba(16,42,67,.08)', strokeDashArray: 4 },
            legend: { show: false }
        }).render();

        const escapeMapText = (value) => String(value ?? '-')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');

        const buildSaSignalIcon = (status) => {
            const color = {
                resolved: '#5bebaf',
                in_progress: '#6791ff',
                rejected: '#ff0068',
                submitted: '#ffa117',
            }[status] || '#ffa117';

            return L.divIcon({
                className: 'signal-map-icon',
                html: `
                    <div style="
                        width: 22px;
                        height: 22px;
                        border-radius: 50% 50% 50% 0;
                        background: ${color};
                        transform: rotate(-45deg);
                        border: 2px solid #ffffff;
                        box-shadow: 0 8px 18px rgba(16,42,67,.25);
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
        };

        const renderSaReportsMap = (elementId, options = {}) => {
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

            if (saMapReports.length) {
                const bounds = [];

                saMapReports.forEach((report) => {
                    const marker = L.marker([report.latitude, report.longitude], {
                        icon: buildSaSignalIcon(report.status)
                    }).addTo(map);

                    marker.bindPopup(`
                        <div style="min-width: 200px;">
                            <a href="${escapeMapText(report.detail_url)}" target="_blank" rel="noopener" style="font-weight: 700; margin-bottom: 4px; display: inline-block; color: #183447; text-decoration: none;">${escapeMapText(report.reference || ('#' + report.id))}</a>
                            <div style="font-size: 12px; color: #5b6b7a;">${escapeMapText(report.signal_label || 'Signalement')}</div>
                            <div style="font-size: 12px; margin-top: 6px;">État : ${escapeMapText(report.status_label || '-')}</div>
                            <a href="${escapeMapText(report.detail_url)}" target="_blank" rel="noopener" style="font-size: 12px; margin-top: 8px; display: inline-block;">Voir le détail</a>
                        </div>
                    `);

                    bounds.push([report.latitude, report.longitude]);
                });

                map.fitBounds(bounds, { padding: [30, 30] });
            } else {
                map.setView([5.3364, -4.0267], 6);
            }

            return map;
        };

        renderSaReportsMap('saReportsMap');

        document.getElementById('saReportsMapModal')?.addEventListener('shown.bs.modal', () => {
            const map = renderSaReportsMap('saReportsMapLarge', { scrollWheelZoom: true });
            setTimeout(() => map?.invalidateSize(), 120);
        });
    </script>
@endsection
