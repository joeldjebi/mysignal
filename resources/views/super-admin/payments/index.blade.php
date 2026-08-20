@extends('super-admin.layouts.app')

@section('title', config('app.name').' | Suivi des paiements')
@section('page-title', 'Suivi des paiements')
@section('page-description', 'Consulter les paiements, suivre les statuts et contrôler les validations en attente.')

@section('header-badges')
    <span class="badge-soft">{{ $transactions->total() }} opération{{ $transactions->total() > 1 ? 's' : '' }}</span>
    <span class="badge-soft">{{ number_format($paymentStats['paid_amount'] ?? 0, 0, ',', ' ') }} FCFA encaissés</span>
@endsection

@section('content')
    @include('partials.page-loader', [
        'title' => 'Chargement des paiements',
        'message' => 'Nous préparons les statuts, graphiques et opérations demandés.',
    ])

    @php
        $statusLabels = [
            'pending' => 'En attente',
            'paid' => 'Payé',
            'failed' => 'Échoué',
            'cancelled' => 'Annulé',
        ];
        $statusClass = fn (?string $status): string => match ($status) {
            'paid' => 'chip-success',
            'failed', 'cancelled' => 'chip-danger',
            'pending' => 'chip-warning',
            default => 'chip-neutral',
        };
        $contextLabels = [
            'report' => 'Signalement',
            'damage' => 'Dommage',
        ];
    @endphp

    <style>
        .payments-dashboard .metric-card {
            border: 1px solid rgba(16,42,67,.08);
            border-radius: 8px;
            background: #fff;
            padding: 1rem;
            height: 100%;
            border-top: 4px solid #6791ff;
            box-shadow: 0 18px 44px rgba(16,42,67,.06);
        }
        .payments-dashboard .row.g-2 > div:nth-child(4n+2) .metric-card { border-top-color: #ff0068; }
        .payments-dashboard .row.g-2 > div:nth-child(4n+3) .metric-card { border-top-color: #ffa117; }
        .payments-dashboard .row.g-2 > div:nth-child(4n+4) .metric-card { border-top-color: #5bebaf; }
        .payments-dashboard .metric-label {
            color: #6b7c93;
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .08em;
        }
        .payments-dashboard .metric-value {
            color: #183447;
            font-size: 1.55rem;
            font-weight: 700;
            line-height: 1.1;
            margin-top: .35rem;
        }
        .payments-dashboard .chart-card {
            border: 1px solid rgba(16,42,67,.08);
            border-radius: 8px;
            background: #fff;
            padding: 1rem;
            height: 100%;
            box-shadow: 0 18px 44px rgba(16,42,67,.06);
        }
        .payments-dashboard .chart-frame {
            min-height: 280px;
        }
    </style>

    <div class="payments-dashboard">
        <section class="row g-2 mb-3">
            <div class="col-md-6 col-xl-3">
                <div class="metric-card">
                    <div class="metric-label">Opérations</div>
                    <div class="metric-value">{{ number_format($paymentStats['total'] ?? 0, 0, ',', ' ') }}</div>
                    <div class="small text-secondary">Paiements et validations à traiter.</div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="metric-card">
                    <div class="metric-label">Payés</div>
                    <div class="metric-value">{{ number_format($paymentStats['paid'] ?? 0, 0, ',', ' ') }}</div>
                    <div class="small text-secondary">{{ number_format($paymentStats['paid_amount'] ?? 0, 0, ',', ' ') }} FCFA encaissés.</div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="metric-card">
                    <div class="metric-label">En attente</div>
                    <div class="metric-value">{{ number_format($paymentStats['pending'] ?? 0, 0, ',', ' ') }}</div>
                    <div class="small text-secondary">Opérations non confirmées.</div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="metric-card">
                    <div class="metric-label">Échoués</div>
                    <div class="metric-value">{{ number_format($paymentStats['failed'] ?? 0, 0, ',', ' ') }}</div>
                    <div class="small text-secondary">Paiements ou sessions non aboutis.</div>
                </div>
            </div>
        </section>

        <section class="row g-3 mb-4">
            <div class="col-xl-4">
                <div class="chart-card">
                    <div class="fw-bold">Statuts des paiements</div>
                    <div class="small text-secondary mb-3">Répartition sur la période sélectionnée.</div>
                    <div id="paymentStatusChart" class="chart-frame"></div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="chart-card">
                    <div class="fw-bold">Types d’opérations</div>
                    <div class="small text-secondary mb-3">Signalements et dommages.</div>
                    <div id="paymentContextChart" class="chart-frame"></div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="chart-card">
                    <div class="fw-bold">Montants encaissés</div>
                    <div class="small text-secondary mb-3">Évolution journalière.</div>
                    <div id="paymentTrendChart" class="chart-frame"></div>
                </div>
            </div>
        </section>

        <section class="panel-card">
            <div class="d-flex align-items-start justify-content-between flex-wrap gap-3 mb-3">
                <div>
                    <div class="fw-bold">Paiements et validations</div>
                    <div class="small text-secondary">Un seul tableau pour les paiements confirmés et les sessions encore à traiter.</div>
                </div>
                <span class="badge-soft">{{ $transactions->total() }} résultat{{ $transactions->total() > 1 ? 's' : '' }}</span>
            </div>

            <form method="GET" class="filter-bar">
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small text-secondary">Recherche</label>
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Référence, usager, fournisseur...">
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
                            @foreach ($statusLabels as $status => $label)
                                <option value="{{ $status }}" @selected(request('status') === $status)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-secondary">Fournisseur</label>
                        <select name="provider" class="form-select">
                            <option value="">Tous</option>
                            @foreach ($providers as $provider)
                                <option value="{{ $provider }}" @selected(request('provider') === $provider)>{{ $provider }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-secondary">Type</label>
                        <select name="payment_context" class="form-select">
                            <option value="">Tous</option>
                            @foreach ($contextLabels as $context => $label)
                                <option value="{{ $context }}" @selected(request('payment_context') === $context)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-secondary">Catégorie</label>
                        <select name="application_id" class="form-select">
                            <option value="">Toutes</option>
                            @foreach ($applications as $application)
                                <option value="{{ $application->id }}" @selected((string) request('application_id') === (string) $application->id)>{{ $application->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-secondary">Institution</label>
                        <select name="organization_id" class="form-select">
                            <option value="">Toutes</option>
                            @foreach ($organizations as $organization)
                                <option value="{{ $organization->id }}" @selected((string) request('organization_id') === (string) $organization->id)>{{ $organization->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-secondary">Par page</label>
                        <select name="per_page" class="form-select" onchange="this.form.submit()">
                            @foreach ([15, 25, 50, 100] as $pageSize)
                                <option value="{{ $pageSize }}" @selected((int) ($perPage ?? 15) === $pageSize)>{{ $pageSize }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button class="btn btn-dark w-100">Filtrer</button>
                        <a href="{{ route('super-admin.payments.index') }}" class="btn btn-outline-secondary">Réinitialiser</a>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-modern align-middle">
                    <thead>
                        <tr>
                            <th>Opération</th>
                            <th>Usager public</th>
                            <th>Dossier</th>
                            <th>Montant</th>
                            <th>Fournisseur</th>
                            <th>Statut</th>
                            <th>Appel UP</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($transactions as $transaction)
                            @php
                                $isSession = $transaction['type'] === 'session';
                                $record = $transaction['model'];
                                $payload = $isSession ? ($record->report_payload ?? []) : [];
                                $payloadApplication = $isSession ? $applications->firstWhere('id', (int) ($payload['application_id'] ?? 0)) : null;
                                $payloadOrganization = $isSession ? $organizations->firstWhere('id', (int) ($payload['organization_id'] ?? 0)) : null;
                                $reference = $isSession ? $record->sync_ref : $record->reference;
                                $report = $record->incidentReport;
                                $context = $record->payment_context ?? 'report';
                                $operationLabel = $isSession
                                    ? 'Validation en attente'
                                    : ($contextLabels[$context] ?? 'Paiement');
                            @endphp
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $reference }}</div>
                                    <div class="small text-secondary">{{ $record->initiated_at?->format('d/m/Y H:i') ?: '-' }}</div>
                                    <div class="small text-secondary">{{ $operationLabel }}</div>
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ trim(($record->publicUser?->first_name ?? '').' '.($record->publicUser?->last_name ?? '')) ?: '-' }}</div>
                                    <div class="small text-secondary">{{ $record->publicUser?->phone ?: '-' }}</div>
                                    <div class="small text-secondary">{{ $record->publicUser?->publicUserType?->name ?: '-' }}</div>
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $report?->reference ?: ($payload['signal_label'] ?? $payload['signal_code'] ?? '-') }}</div>
                                    <div class="small text-secondary">{{ $report?->signal_label ?: $payload['incident_type'] ?? '-' }}</div>
                                    <div class="small text-secondary">
                                        {{ $report?->application?->name ?: $payloadApplication?->name ?: '-' }}
                                        /
                                        {{ $report?->organization?->name ?: $payloadOrganization?->name ?: '-' }}
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ number_format((float) $record->amount, 0, ',', ' ') }} {{ $record->currency ?: 'XOF' }}</div>
                                    <div class="small text-secondary">{{ $record->pricingRule?->label ?: 'Tarification non renseignée' }}</div>
                                </td>
                                <td>
                                    <div>{{ $record->provider ?: '-' }}</div>
                                    <div class="small text-secondary">Réf. fournisseur : {{ $record->provider_reference ?: '-' }}</div>
                                    <div class="small text-secondary">{{ $record->paid_at?->format('d/m/Y H:i') ?: 'Non confirmé' }}</div>
                                </td>
                                <td>
                                    <span class="status-chip {{ $statusClass($record->status) }}">{{ $statusLabels[$record->status] ?? $record->status }}</span>
                                </td>
                                <td>
                                    @include('super-admin.call-center.partials.call-status', ['contact' => $record->latestCallCenterContact])
                                </td>
                                <td class="text-end">
                                    <div class="actions-wrap">
                                        @include('super-admin.call-center.partials.call-dropdown', [
                                            'route' => $isSession
                                                ? route('super-admin.payments.sessions.call-center-contact', $record)
                                                : route('super-admin.payments.call-center-contact', $record),
                                            'buttonLabel' => 'Appel',
                                            'placeholder' => 'Résumé de l’appel lié au paiement...',
                                        ])
                                        @if ($isSession && ($record->status !== 'paid' || $record->incident_report_id === null) && $canManuallyValidatePayments)
                                            <form method="POST" action="{{ route('super-admin.payments.sessions.validate', $record) }}" class="d-inline-flex flex-column gap-2 align-items-end" onsubmit="return confirm('Valider manuellement ce paiement et traiter cette session ?');">
                                                @csrf
                                                <input type="text" name="reason" class="form-control form-control-sm" placeholder="Motif optionnel" style="max-width: 220px;">
                                                <div class="d-inline-flex gap-2">
                                                    <input type="text" name="confirmation" class="form-control form-control-sm" placeholder="VALIDER" style="width: 110px;" required>
                                                    <button class="btn btn-sm btn-outline-danger">Valider</button>
                                                </div>
                                            </form>
                                        @endif
                                        @if ($record->publicUser)
                                            <a href="{{ route('super-admin.public-users.show', $record->publicUser) }}" class="btn btn-sm btn-outline-dark">Voir l’usager</a>
                                        @endif
                                        @if (! $isSession && $report?->reparationCase)
                                            <a href="{{ route('super-admin.reparation-cases.show', $report->reparationCase) }}" class="btn btn-sm btn-outline-secondary">Voir le dossier</a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-secondary py-4">Aucun paiement trouvé.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="table-meta">Page {{ $transactions->currentPage() }} sur {{ $transactions->lastPage() }}</div>
                {{ $transactions->links() }}
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const statusBreakdown = @json($statusBreakdown);
            const contextBreakdown = @json($contextBreakdown);
            const trend = @json($trend);
            const palette = ['#ffa117', '#5bebaf', '#ff0068', '#6791ff'];

            new ApexCharts(document.querySelector('#paymentStatusChart'), {
                chart: { type: 'donut', height: 280 },
                labels: statusBreakdown.map((item) => item.label),
                series: statusBreakdown.map((item) => Number(item.value || 0)),
                colors: palette,
                legend: { position: 'bottom' },
                dataLabels: { enabled: false },
            }).render();

            new ApexCharts(document.querySelector('#paymentContextChart'), {
                chart: { type: 'bar', height: 280, toolbar: { show: false } },
                series: [{ name: 'Opérations', data: contextBreakdown.map((item) => Number(item.value || 0)) }],
                xaxis: { categories: contextBreakdown.map((item) => item.label) },
                colors: ['#6791ff'],
                plotOptions: { bar: { borderRadius: 6, columnWidth: '44%' } },
                dataLabels: { enabled: false },
            }).render();

            new ApexCharts(document.querySelector('#paymentTrendChart'), {
                chart: { type: 'area', height: 280, toolbar: { show: false }, zoom: { enabled: false } },
                series: [{ name: 'Montant', data: trend.map((item) => Number(item.amount || 0)) }],
                xaxis: { categories: trend.map((item) => item.label) },
                colors: ['#5bebaf'],
                stroke: { curve: 'smooth', width: 3 },
                dataLabels: { enabled: false },
                yaxis: {
                    labels: {
                        formatter: (value) => `${Number(value || 0).toLocaleString('fr-FR')} FCFA`,
                    },
                },
            }).render();
        });
    </script>
@endpush
