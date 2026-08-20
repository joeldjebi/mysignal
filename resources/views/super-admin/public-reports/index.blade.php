@extends('super-admin.layouts.app')

@section('title', config('app.name').' | Signalements publics')
@section('page-title', 'Signalements des usagers publics')
@section('page-description', 'Consulter la liste des signalements effectués par les usagers publics, filtrer par statut et accéder rapidement au compte usager ou au dossier associé.')

@section('header-badges')
    <span class="badge-soft">{{ $reports->total() }} signalement{{ $reports->total() > 1 ? 's' : '' }}</span>
    <span class="badge-soft">{{ number_format($reportStats['with_damage'] ?? 0, 0, ',', ' ') }} dommage{{ ($reportStats['with_damage'] ?? 0) > 1 ? 's' : '' }}</span>
@endsection

@section('content')
    @php
        $authUser = auth()->user();
        $canDeleteReports = (bool) ($authUser?->is_super_admin || $authUser?->hasEffectivePermissionCode('SA_PUBLIC_REPORTS_DELETE'));
    @endphp

    @include('partials.page-loader', [
        'title' => 'Chargement des signalements',
        'message' => 'Nous préparons les données demandées.',
    ])

    <style>
        .sa-period-dashboard .metric-card,
        .sa-period-dashboard .chart-card {
            border: 1px solid rgba(16,42,67,.08);
            border-radius: 8px;
            background: #fff;
            padding: 1rem;
            height: 100%;
            box-shadow: 0 18px 44px rgba(16,42,67,.06);
        }
        .sa-period-dashboard .metric-card { border-top: 4px solid #6791ff; }
        .sa-period-dashboard .row.g-2 > div:nth-child(4n+2) .metric-card { border-top-color: #ff0068; }
        .sa-period-dashboard .row.g-2 > div:nth-child(4n+3) .metric-card { border-top-color: #ffa117; }
        .sa-period-dashboard .row.g-2 > div:nth-child(4n+4) .metric-card { border-top-color: #5bebaf; }
        .sa-period-dashboard .metric-label {
            color: #6b7c93;
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .08em;
        }
        .sa-period-dashboard .metric-value {
            color: #183447;
            font-size: 1.55rem;
            font-weight: 700;
            line-height: 1.1;
            margin-top: .35rem;
        }
        .sa-period-dashboard .chart-frame { min-height: 280px; }
    </style>

    <div class="sa-period-dashboard">
        <section class="row g-2 mb-3">
            <div class="col-md-6 col-xl-3">
                <div class="metric-card">
                    <div class="metric-label">Signalements</div>
                    <div class="metric-value">{{ number_format($reportStats['total'] ?? 0, 0, ',', ' ') }}</div>
                    <div class="small text-secondary">Signalements créés sur la période.</div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="metric-card">
                    <div class="metric-label">À traiter</div>
                    <div class="metric-value">{{ number_format($reportStats['submitted'] ?? 0, 0, ',', ' ') }}</div>
                    <div class="small text-secondary">Dossiers encore non pris en charge.</div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="metric-card">
                    <div class="metric-label">Résolus</div>
                    <div class="metric-value">{{ number_format($reportStats['resolved'] ?? 0, 0, ',', ' ') }}</div>
                    <div class="small text-secondary">Signalements clôturés par les institutions.</div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="metric-card">
                    <div class="metric-label">Dommages</div>
                    <div class="metric-value">{{ number_format($reportStats['with_damage'] ?? 0, 0, ',', ' ') }}</div>
                    <div class="small text-secondary">{{ number_format($reportStats['with_case'] ?? 0, 0, ',', ' ') }} dossier(s) contentieux.</div>
                </div>
            </div>
        </section>

        <section class="row g-3 mb-4">
            <div class="col-xl-3 col-lg-6">
                <div class="chart-card">
                    <div class="fw-bold">Statuts</div>
                    <div class="small text-secondary mb-3">Répartition sur la période.</div>
                    <div id="publicReportStatusChart" class="chart-frame"></div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6">
                <div class="chart-card">
                    <div class="fw-bold">Dommages</div>
                    <div class="small text-secondary mb-3">Signalements avec ou sans dommage.</div>
                    <div id="publicReportDamageChart" class="chart-frame"></div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6">
                <div class="chart-card">
                    <div class="fw-bold">Confirmation UP</div>
                    <div class="small text-secondary mb-3">Retour de confirmation après résolution.</div>
                    <div id="publicReportConfirmationChart" class="chart-frame"></div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6">
                <div class="chart-card">
                    <div class="fw-bold">Nouveaux signalements</div>
                    <div class="small text-secondary mb-3">Évolution journalière.</div>
                    <div id="publicReportTrendChart" class="chart-frame"></div>
                </div>
            </div>
        </section>
    </div>

    <section class="panel-card">
        <div class="fw-bold mb-3">Liste des signalements publics</div>
        <form method="GET" class="filter-bar">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small text-secondary">Recherche</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Référence, usager, signalement, institution...">
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-secondary">Période</label>
                    <select name="period" class="form-select" onchange="this.form.submit()">
                        <option value="today" @selected(request('period') === 'today')>Aujourd’hui</option>
                        <option value="7d" @selected(request('period') === '7d')>7 jours</option>
                        <option value="30d" @selected(blank(request('period')) || request('period') === '30d')>30 jours</option>
                        <option value="month" @selected(request('period') === 'month')>Mois en cours</option>
                        <option value="year" @selected(request('period') === 'year')>Année en cours</option>
                        <option value="custom" @selected(request('period') === 'custom')>Personnalisée</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-secondary">Du</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-secondary">Au</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-secondary">Statut</label>
                    <select name="status" class="form-select">
                        <option value="">Tous</option>
                        @foreach (['submitted' => 'Soumis', 'in_progress' => 'En cours', 'resolved' => 'Résolus', 'rejected' => 'Rejetés'] as $status => $label)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-secondary">Application</label>
                    <select name="application_id" class="form-select">
                        <option value="">Toutes</option>
                        @foreach ($applications as $application)
                            <option value="{{ $application->id }}" @selected((string) request('application_id') === (string) $application->id)>{{ $application->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-secondary">Organisation</label>
                    <select name="organization_id" class="form-select">
                        <option value="">Toutes</option>
                        @foreach ($organizations as $organization)
                            <option value="{{ $organization->id }}" @selected((string) request('organization_id') === (string) $organization->id)>{{ $organization->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-secondary">Dommage</label>
                    <select name="damage" class="form-select">
                        <option value="">Tous</option>
                        <option value="with_damage" @selected(request('damage') === 'with_damage')>Avec dommage</option>
                        <option value="without_damage" @selected(request('damage') === 'without_damage')>Sans dommage</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-secondary">Confirmation UP</label>
                    <select name="resolution_confirmation" class="form-select">
                        <option value="">Toutes</option>
                        <option value="ai_validated_and_confirmed" @selected(request('resolution_confirmation') === 'ai_validated_and_confirmed')>Validé AI + confirmé UP</option>
                        <option value="confirmed_without_ai_validation" @selected(request('resolution_confirmation') === 'confirmed_without_ai_validation')>Confirmé sans validation AI</option>
                        <option value="waiting_up_confirmation" @selected(request('resolution_confirmation') === 'waiting_up_confirmation')>En attente confirmation UP</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-secondary">Dossier</label>
                    <select name="reparation_case" class="form-select">
                        <option value="">Tous</option>
                        <option value="opened" @selected(request('reparation_case') === 'opened')>Dossier ouvert</option>
                        <option value="missing" @selected(request('reparation_case') === 'missing')>Sans dossier</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-secondary">Par page</label>
                    <select name="per_page" class="form-select" onchange="this.form.submit()">
                        @foreach ([15, 25, 50, 100] as $perPageOption)
                            <option value="{{ $perPageOption }}" @selected((int) ($perPage ?? request('per_page', 15)) === $perPageOption)>{{ $perPageOption }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button class="btn btn-dark w-100">Filtrer</button>
                    <a href="{{ route('super-admin.public-reports.index') }}" class="btn btn-outline-secondary">Réinitialiser</a>
                </div>
            </div>
        </form>

        <div class="table-toolbar">
            <div class="table-meta">{{ $reports->total() }} resultat{{ $reports->total() > 1 ? 's' : '' }}</div>
        </div>
        <div class="table-responsive">
            <table class="table table-modern align-middle">
                <thead>
                    <tr>
                        <th>Signalement</th>
                        <th>Usager public</th>
                        <th>Application / Organisation</th>
                        <th>Localisation</th>
                        <th>Dommage</th>
                        <th>Dossier</th>
                        <th>Appel UP</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reports as $report)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $report->reference }}</div>
                                <div class="small text-secondary">{{ $report->signal_label ?: $report->signal_code ?: $report->incident_type }}</div>
                                <div class="small text-secondary">{{ $report->created_at?->format('d/m/Y H:i') ?: '-' }}</div>
                                <div class="mt-1"><span class="status-chip">{{ $report->status }}</span></div>
                                <div class="mt-2">
                                    @if ($report->resolution_confirmation_status === 'confirmed' && $report->resolution_confirmed_without_ai_validation)
                                        <span class="status-chip bg-warning-subtle text-warning-emphasis">Confirmé UP sans validation AI</span>
                                    @elseif ($report->resolution_confirmation_status === 'confirmed')
                                        <span class="status-chip bg-success-subtle text-success-emphasis">Validé AI + confirmé UP</span>
                                    @elseif ($report->status === 'resolved')
                                        <span class="status-chip bg-info-subtle text-info-emphasis">En attente confirmation UP</span>
                                    @else
                                        <span class="status-chip bg-light text-secondary">Non confirmé UP</span>
                                    @endif
                                </div>
                                @if ($report->resolution_confirmed_at)
                                    <div class="small text-secondary mt-1">Confirmation : {{ $report->resolution_confirmed_at?->format('d/m/Y H:i') }}</div>
                                @endif
                            </td>
                            <td>
                                <div class="fw-semibold">{{ trim(($report->publicUser?->first_name ?? '').' '.($report->publicUser?->last_name ?? '')) ?: '-' }}</div>
                                <div class="small text-secondary">{{ $report->publicUser?->phone ?: '-' }}</div>
                                <div class="small text-secondary">{{ $report->publicUser?->publicUserType?->name ?: '-' }}</div>
                            </td>
                            <td>
                                <div>{{ $report->application?->name ?: '-' }}</div>
                                <div class="small text-secondary">{{ $report->organization?->name ?: '-' }}</div>
                            </td>
                            <td>
                                <div>{{ $report->commune?->name ?: '-' }}</div>
                                <div class="small text-secondary">{{ $report->address ?: 'Adresse non renseignée' }}</div>
                            </td>
                            <td>
                                <span class="status-chip">{{ $report->damage_declared_at ? 'Déclaré' : 'Aucun' }}</span>
                                @if ($report->damage_declared_at)
                                    <div class="small text-secondary mt-1">{{ $report->damage_declared_at?->format('d/m/Y H:i') }}</div>
                                @endif
                            </td>
                            <td>
                                @if ($report->reparationCase)
                                    <div class="fw-semibold">{{ $report->reparationCase->reference }}</div>
                                    <div class="small text-secondary">{{ $report->reparationCase->status }}</div>
                                @else
                                    <span class="small text-secondary">Aucun dossier</span>
                                @endif
                            </td>
                            <td>
                                @include('super-admin.call-center.partials.call-status', ['contact' => $report->latestCallCenterContact])
                            </td>
                            <td class="text-end">
                                <div class="actions-wrap">
                                    @include('super-admin.call-center.partials.call-dropdown', [
                                        'route' => route('super-admin.public-reports.call-center-contact', $report),
                                        'buttonLabel' => 'Appel',
                                        'placeholder' => 'Résumé de l’appel lié à ce signalement...',
                                    ])
                                    @if ($report->publicUser)
                                        <a href="{{ route('super-admin.public-users.show', $report->publicUser) }}" class="btn btn-sm btn-outline-dark">Voir l’usager</a>
                                    @endif
                                    @if ($report->reparationCase)
                                        <a href="{{ route('super-admin.reparation-cases.show', $report->reparationCase) }}" class="btn btn-sm btn-outline-secondary">Voir le dossier</a>
                                    @endif
                                    @if ($canDeleteReports)
                                        <form method="POST" action="{{ route('super-admin.public-reports.destroy', $report) }}" data-delete-report-form data-reference="{{ $report->reference }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Supprimer</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-secondary">Aucun signalement public trouvé.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="table-meta">Page {{ $reports->currentPage() }} sur {{ $reports->lastPage() }}</div>
            {{ $reports->links() }}
        </div>
    </section>

    <div class="modal fade" id="deletePublicReportModal" tabindex="-1" aria-labelledby="deletePublicReportTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-0 pb-0">
                    <div>
                        <h5 class="modal-title fw-bold" id="deletePublicReportTitle">Supprimer le signalement</h5>
                        <div class="small text-secondary">Cette action est définitive.</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <div class="rounded-3 border border-danger-subtle bg-danger-subtle text-danger-emphasis p-3 mb-3">
                        <div class="fw-semibold mb-1" id="deletePublicReportMessage">Confirmer la suppression ?</div>
                        <div class="small">Les paiements, sessions de paiement, dossiers liés, historiques et fichiers Wasabi associés seront supprimés. L’identifiant lié au signalement sera conservé.</div>
                    </div>
                    <div class="small text-secondary">Vérifiez bien le signalement avant de continuer.</div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-danger" id="deletePublicReportSubmit">Supprimer définitivement</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const statusBreakdown = @json($statusBreakdown);
            const damageBreakdown = @json($damageBreakdown);
            const confirmationBreakdown = @json($confirmationBreakdown);
            const trend = @json($trend);
            const deleteModalElement = document.getElementById('deletePublicReportModal');
            const deleteMessageElement = document.getElementById('deletePublicReportMessage');
            const deleteSubmitButton = document.getElementById('deletePublicReportSubmit');
            let pendingDeleteForm = null;

            if (deleteModalElement && deleteSubmitButton && typeof bootstrap !== 'undefined') {
                const deleteModal = bootstrap.Modal.getOrCreateInstance(deleteModalElement);

                document.querySelectorAll('form[data-delete-report-form]').forEach((form) => {
                    form.addEventListener('submit', (event) => {
                        if (form.dataset.confirmed === '1') {
                            return;
                        }

                        event.preventDefault();
                        pendingDeleteForm = form;

                        if (deleteMessageElement) {
                            deleteMessageElement.textContent = `Supprimer le signalement ${form.dataset.reference || ''} ?`;
                        }

                        deleteModal.show();
                    });
                });

                deleteSubmitButton.addEventListener('click', () => {
                    if (!pendingDeleteForm) {
                        return;
                    }

                    pendingDeleteForm.dataset.confirmed = '1';
                    pendingDeleteForm.submit();
                });

                deleteModalElement.addEventListener('hidden.bs.modal', () => {
                    pendingDeleteForm = null;
                });
            }

            new ApexCharts(document.querySelector('#publicReportStatusChart'), {
                chart: { type: 'donut', height: 280 },
                labels: statusBreakdown.map((item) => item.label),
                series: statusBreakdown.map((item) => Number(item.value || 0)),
                colors: ['#ffa117', '#6791ff', '#5bebaf', '#ff0068'],
                legend: { position: 'bottom' },
                dataLabels: { enabled: false },
            }).render();

            new ApexCharts(document.querySelector('#publicReportDamageChart'), {
                chart: { type: 'donut', height: 280 },
                labels: damageBreakdown.map((item) => item.label),
                series: damageBreakdown.map((item) => Number(item.value || 0)),
                colors: ['#ff0068', '#6791ff'],
                legend: { position: 'bottom' },
                dataLabels: { enabled: false },
            }).render();

            new ApexCharts(document.querySelector('#publicReportConfirmationChart'), {
                chart: { type: 'bar', height: 280, toolbar: { show: false } },
                series: [{ name: 'Signalements', data: confirmationBreakdown.map((item) => Number(item.value || 0)) }],
                xaxis: { categories: confirmationBreakdown.map((item) => item.label) },
                colors: ['#5bebaf'],
                plotOptions: { bar: { borderRadius: 6, columnWidth: '44%' } },
                dataLabels: { enabled: false },
            }).render();

            new ApexCharts(document.querySelector('#publicReportTrendChart'), {
                chart: { type: 'area', height: 280, toolbar: { show: false }, zoom: { enabled: false } },
                series: [{ name: 'Signalements', data: trend.map((item) => Number(item.value || 0)) }],
                xaxis: { categories: trend.map((item) => item.label) },
                colors: ['#ffa117'],
                stroke: { curve: 'smooth', width: 3 },
                dataLabels: { enabled: false },
            }).render();
        });
    </script>
@endpush
