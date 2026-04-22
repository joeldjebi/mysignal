@extends('partner.layouts.app')

@section('title', config('app.name').' | Dashboard partenaire')
@section('page-title', 'Dashboard partenaire')
@section('page-description', 'Suivez l activite des reductions, des offres et des utilisateurs mobiles de votre etablissement.')

@section('header-badges')
    <span class="badge-soft">{{ $stats['transactions'] }} reductions</span>
    <span class="badge-soft">{{ $stats['active_offers'] }} offres actives</span>
    <span class="badge-soft">{{ $stats['active_users'] }} users actifs</span>
@endsection

@section('content')
    <form method="GET" class="filter-bar">
        <div class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small text-secondary">Date debut</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label small text-secondary">Date fin</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control">
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button class="btn btn-dark w-100">Filtrer</button>
                <a href="{{ route('partner.dashboard') }}" class="btn btn-outline-secondary w-100">RAZ</a>
            </div>
        </div>
    </form>

    <div class="row g-4 mb-4">
        <div class="col-md-4 col-xl-2">
            <section class="stat-card">
                <div class="small text-secondary text-uppercase fw-semibold">Transactions</div>
                <div class="display-6 fw-bold">{{ $stats['transactions'] }}</div>
            </section>
        </div>
        <div class="col-md-4 col-xl-2">
            <section class="stat-card">
                <div class="small text-secondary text-uppercase fw-semibold">Validees</div>
                <div class="display-6 fw-bold">{{ $stats['validated_transactions'] }}</div>
            </section>
        </div>
        <div class="col-md-4 col-xl-2">
            <section class="stat-card">
                <div class="small text-secondary text-uppercase fw-semibold">Offres</div>
                <div class="display-6 fw-bold">{{ $stats['offers'] }}</div>
            </section>
        </div>
        <div class="col-md-4 col-xl-2">
            <section class="stat-card">
                <div class="small text-secondary text-uppercase fw-semibold">Offres actives</div>
                <div class="display-6 fw-bold">{{ $stats['active_offers'] }}</div>
            </section>
        </div>
        <div class="col-md-4 col-xl-2">
            <section class="stat-card">
                <div class="small text-secondary text-uppercase fw-semibold">Users</div>
                <div class="display-6 fw-bold">{{ $stats['users'] }}</div>
            </section>
        </div>
        <div class="col-md-4 col-xl-2">
            <section class="stat-card">
                <div class="small text-secondary text-uppercase fw-semibold">Users actifs</div>
                <div class="display-6 fw-bold">{{ $stats['active_users'] }}</div>
            </section>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-8">
            <section class="panel-card">
                <div class="table-toolbar">
                    <div class="fw-bold">Reductions recentes</div>
                    <div class="table-meta">10 dernieres operations</div>
                </div>
                <div class="table-card">
                    <div class="table-responsive">
                        <table class="table table-modern mb-0">
                            <thead>
                                <tr>
                                    <th>Reference</th>
                                    <th>Agent</th>
                                    <th>Offre</th>
                                    <th>UP</th>
                                    <th>Montants</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($recentTransactions as $transaction)
                                    <tr>
                                        <td>
                                            <div class="meta-stack">
                                                <span class="meta-title">{{ $transaction->scan_reference }}</span>
                                                <span class="meta-subtitle">{{ $transaction->status }}</span>
                                            </div>
                                        </td>
                                        <td>{{ $transaction->partnerUser?->name ?? '-' }}</td>
                                        <td>{{ $transaction->offer?->name ?? '-' }}</td>
                                        <td>
                                            <div class="meta-stack">
                                                <span class="meta-title">{{ trim(($transaction->publicUser?->first_name ?? '').' '.($transaction->publicUser?->last_name ?? '')) ?: '-' }}</span>
                                                <span class="meta-subtitle">{{ $transaction->discountCard?->card_number ?? '-' }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="small">Initial: {{ $transaction->original_amount ?? '-' }}</div>
                                            <div class="small">Reduction: {{ $transaction->discount_amount ?? '-' }}</div>
                                            <div class="small">Final: {{ $transaction->final_amount ?? '-' }}</div>
                                        </td>
                                        <td>{{ optional($transaction->applied_at)->format('d/m/Y H:i') ?: '-' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-secondary">Aucune reduction enregistree pour le moment.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
        <div class="col-xl-4">
            <section class="panel-card mb-4">
                <div class="fw-bold mb-3">Top agents</div>
                <div class="vstack gap-2">
                    @forelse ($topAgents as $entry)
                        <div class="d-flex justify-content-between align-items-center border rounded-3 p-3">
                            <div>
                                <div class="fw-semibold">{{ $entry->partnerUser?->name ?? 'Agent inconnu' }}</div>
                                <div class="small text-secondary">{{ $entry->partnerUser?->email ?? '-' }}</div>
                            </div>
                            <span class="badge-soft">{{ $entry->total }}</span>
                        </div>
                    @empty
                        <div class="text-secondary">Aucun agent encore actif.</div>
                    @endforelse
                </div>
            </section>

            <section class="panel-card">
                <div class="fw-bold mb-3">Top offres</div>
                <div class="vstack gap-2">
                    @forelse ($topOffers as $entry)
                        <div class="d-flex justify-content-between align-items-center border rounded-3 p-3">
                            <div>
                                <div class="fw-semibold">{{ $entry->offer?->name ?? 'Offre inconnue' }}</div>
                                <div class="small text-secondary">{{ $entry->offer?->code ?? '-' }}</div>
                            </div>
                            <span class="badge-soft">{{ $entry->total }}</span>
                        </div>
                    @empty
                        <div class="text-secondary">Aucune offre encore utilisee.</div>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
@endsection
