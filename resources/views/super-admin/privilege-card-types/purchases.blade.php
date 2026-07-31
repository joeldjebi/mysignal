@extends('super-admin.layouts.app')

@section('title', config('app.name').' | Achats de cartes privilèges')
@section('page-title', 'Historique des achats')
@section('page-description', 'Suivi des achats de cartes privilèges et des paiements sécurisés.')

@section('header-badges')
    <span class="badge-soft">{{ $purchases->total() }} achat{{ $purchases->total() > 1 ? 's' : '' }}</span>
    <span class="badge-soft">{{ number_format($purchaseStats['paid_amount'] ?? 0, 0, ',', ' ') }} FCFA encaissés</span>
@endsection

@section('content')
    @include('partials.page-loader', [
        'title' => 'Chargement des achats',
        'message' => 'Nous préparons les indicateurs, graphiques et achats demandés.',
    ])

    <style>
        .privilege-purchases-dashboard .metric-card {
            border: 1px solid rgba(16,42,67,.08);
            border-radius: 8px;
            background: #fff;
            padding: 1rem;
            height: 100%;
            border-top: 4px solid #6791ff;
            box-shadow: 0 18px 44px rgba(16,42,67,.06);
        }
        .privilege-purchases-dashboard .row.g-2 > div:nth-child(4n+2) .metric-card { border-top-color: #ff0068; }
        .privilege-purchases-dashboard .row.g-2 > div:nth-child(4n+3) .metric-card { border-top-color: #ffa117; }
        .privilege-purchases-dashboard .row.g-2 > div:nth-child(4n+4) .metric-card { border-top-color: #5bebaf; }
        .privilege-purchases-dashboard .metric-label {
            color: #6b7c93;
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .08em;
        }
        .privilege-purchases-dashboard .metric-value {
            color: #183447;
            font-size: 1.55rem;
            font-weight: 700;
            line-height: 1.1;
            margin-top: .35rem;
        }
        .privilege-purchases-dashboard .chart-card {
            border: 1px solid rgba(16,42,67,.08);
            border-radius: 8px;
            background: #fff;
            padding: 1rem;
            height: 100%;
            box-shadow: 0 18px 44px rgba(16,42,67,.06);
        }
        .privilege-purchases-dashboard .chart-frame {
            min-height: 280px;
        }
    </style>

    <div class="privilege-purchases-dashboard">
        <section class="row g-2 mb-3">
            <div class="col-md-6 col-xl-3">
                <div class="metric-card">
                    <div class="metric-label">Achats</div>
                    <div class="metric-value">{{ number_format($purchaseStats['total'] ?? 0, 0, ',', ' ') }}</div>
                    <div class="small text-secondary">Achats initiés sur la période.</div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="metric-card">
                    <div class="metric-label">Payés</div>
                    <div class="metric-value">{{ number_format($purchaseStats['paid'] ?? 0, 0, ',', ' ') }}</div>
                    <div class="small text-secondary">{{ number_format($purchaseStats['paid_amount'] ?? 0, 0, ',', ' ') }} FCFA encaissés.</div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="metric-card">
                    <div class="metric-label">En attente</div>
                    <div class="metric-value">{{ number_format($purchaseStats['pending'] ?? 0, 0, ',', ' ') }}</div>
                    <div class="small text-secondary">Paiements non confirmés.</div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="metric-card">
                    <div class="metric-label">Échoués</div>
                    <div class="metric-value">{{ number_format($purchaseStats['failed'] ?? 0, 0, ',', ' ') }}</div>
                    <div class="small text-secondary">Achats non aboutis.</div>
                </div>
            </div>
        </section>

        <section class="row g-3 mb-4">
            <div class="col-xl-4">
                <div class="chart-card">
                    <div class="fw-bold">Statuts des achats</div>
                    <div class="small text-secondary mb-3">Répartition sur la période sélectionnée.</div>
                    <div id="privilegePurchaseStatusChart" class="chart-frame"></div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="chart-card">
                    <div class="fw-bold">Types de cartes</div>
                    <div class="small text-secondary mb-3">Cartes les plus achetées.</div>
                    <div id="privilegePurchaseTypeChart" class="chart-frame"></div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="chart-card">
                    <div class="fw-bold">Montants encaissés</div>
                    <div class="small text-secondary mb-3">Évolution journalière des achats payés.</div>
                    <div id="privilegePurchaseTrendChart" class="chart-frame"></div>
                </div>
            </div>
        </section>

    <section class="panel-card">
        <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-3">
            <div>
                <div class="fw-bold">Historique des achats</div>
                <div class="small text-secondary">Suivi des achats de cartes privilèges, paiements sécurisés et cartes émises.</div>
            </div>
            <span class="badge-soft">{{ $purchases->total() }} achat{{ $purchases->total() > 1 ? 's' : '' }}</span>
        </div>

        <form method="GET" class="filter-bar">
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small text-secondary">Recherche</label>
                    <input class="form-control" name="purchase_search" value="{{ request('purchase_search') }}" placeholder="Usager, téléphone, numéro de paiement, carte">
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
                    <label class="form-label small text-secondary">Carte</label>
                    <select class="form-select" name="purchase_type_id">
                        <option value="">Toutes</option>
                        @foreach ($cardTypes as $cardType)
                            <option value="{{ $cardType->id }}" @selected((string) request('purchase_type_id') === (string) $cardType->id)>{{ $cardType->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-secondary">État du paiement</label>
                    <select class="form-select" name="purchase_status">
                        <option value="">Tous</option>
                        <option value="pending" @selected(request('purchase_status') === 'pending')>En attente</option>
                        <option value="paid" @selected(request('purchase_status') === 'paid')>Payé</option>
                        <option value="failed" @selected(request('purchase_status') === 'failed')>Échoué</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-secondary">Par page</label>
                    <select name="per_page" class="form-select" onchange="this.form.submit()">
                        @foreach ([12, 25, 50, 100] as $pageSize)
                            <option value="{{ $pageSize }}" @selected((int) ($perPage ?? 12) === $pageSize)>{{ $pageSize }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button class="btn btn-dark w-100">Filtrer</button>
                    <a href="{{ route('super-admin.privilege-card-types.purchases') }}" class="btn btn-outline-secondary">Réinitialiser</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-modern align-middle">
                <thead>
                    <tr>
                        <th>Usager</th>
                        <th>Carte</th>
                        <th>Paiement</th>
                        <th>Carte émise</th>
                        <th>Dates</th>
                        <th>État</th>
                    </tr>
                </thead>
                <tbody>
                    @if ($purchases->isNotEmpty())
                        @foreach ($purchases as $purchase)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ trim(($purchase->publicUser?->first_name ?? '').' '.($purchase->publicUser?->last_name ?? '')) ?: '-' }}</div>
                                <div class="small text-secondary">{{ $purchase->publicUser?->phone ?: '-' }}</div>
                                <div class="small text-secondary">{{ $purchase->publicUser?->email ?: '-' }}</div>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $purchase->type?->name ?: '-' }}</div>
                            </td>
                            <td>
                                <div>{{ number_format((float) $purchase->amount, 0, ',', ' ') }} {{ $purchase->currency }}</div>
                                <div class="small text-secondary">{{ $purchase->sync_ref }}</div>
                                <div class="small text-secondary">{{ $purchase->provider_reference ?: '-' }}</div>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $purchase->card?->card_number ?: '-' }}</div>
                                <div class="small text-secondary">Code à scanner: <span class="font-monospace">{{ $purchase->card?->card_uuid ?: '-' }}</span></div>
                                <div class="small text-secondary">État de la carte: {{ $purchase->card ? ($cardStatusLabels[$purchase->card->status] ?? $purchase->card->status) : '-' }}</div>
                            </td>
                            <td>
                                <div class="small">Initié: {{ $purchase->initiated_at?->format('d/m/Y H:i') ?: '-' }}</div>
                                <div class="small">Payé: {{ $purchase->paid_at?->format('d/m/Y H:i') ?: ($purchase->status === 'paid' ? 'Date inconnue' : 'En attente') }}</div>
                                <div class="small">Activé: {{ $purchase->card?->activated_at?->format('d/m/Y H:i') ?: 'Carte non émise' }}</div>
                                <div class="small">Expire: {{ $purchase->card?->expires_at?->format('d/m/Y H:i') ?: 'Carte non émise' }}</div>
                            </td>
                            <td>
                                <span class="status-chip">{{ $paymentStatusLabels[$purchase->status] ?? $purchase->status }}</span>
                                @if ($purchase->card)
                                    <span class="status-chip">{{ $cardStatusLabels[$purchase->card->status] ?? $purchase->card->status }}</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    @else
                        <tr><td colspan="6" class="text-center text-secondary">Aucun achat de carte privilège enregistré.</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="table-meta">Page {{ $purchases->currentPage() }} sur {{ $purchases->lastPage() }}</div>
            {{ $purchases->links() }}
        </div>
    </section>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const statusBreakdown = @json($purchaseStatusBreakdown);
            const typeBreakdown = @json($purchaseTypeBreakdown);
            const trend = @json($purchaseTrend);
            const palette = ['#ffa117', '#5bebaf', '#ff0068', '#6791ff'];

            new ApexCharts(document.querySelector('#privilegePurchaseStatusChart'), {
                chart: { type: 'donut', height: 280 },
                labels: statusBreakdown.map((item) => item.label),
                series: statusBreakdown.map((item) => Number(item.value || 0)),
                colors: palette,
                legend: { position: 'bottom' },
                dataLabels: { enabled: false },
            }).render();

            new ApexCharts(document.querySelector('#privilegePurchaseTypeChart'), {
                chart: { type: 'bar', height: 280, toolbar: { show: false } },
                series: [{ name: 'Achats', data: typeBreakdown.map((item) => Number(item.value || 0)) }],
                xaxis: { categories: typeBreakdown.map((item) => item.label) },
                colors: ['#6791ff'],
                plotOptions: { bar: { borderRadius: 6, columnWidth: '44%' } },
                dataLabels: { enabled: false },
            }).render();

            new ApexCharts(document.querySelector('#privilegePurchaseTrendChart'), {
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
