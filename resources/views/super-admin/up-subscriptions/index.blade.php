@extends('super-admin.layouts.app')

@section('title', config('app.name').' | Abonnements UP')
@section('page-title', 'Abonnements UP')
@section('page-description', 'Consulter l historique des abonnements annuels des usagers publics, leurs statuts et leurs paiements.')

@section('header-badges')
    <span class="badge-soft">{{ $subscriptions->total() }} abonnement{{ $subscriptions->total() > 1 ? 's' : '' }}</span>
@endsection

@section('content')
    <section class="panel-card">
        <div class="fw-bold mb-3">Historique des abonnements UP</div>
        <form method="GET" class="filter-bar">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small text-secondary">Recherche</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Usager, telephone, plan, reference paiement...">
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-secondary">Statut abonnement</label>
                    <select name="status" class="form-select">
                        <option value="">Tous</option>
                        @foreach ([
                            'pending' => 'Paiement en attente',
                            'active' => 'Actif',
                            'expired' => 'Expire',
                            'cancelled' => 'Annule',
                            'suspended' => 'Suspendu',
                            'payment_failed' => 'Paiement echoue',
                        ] as $status => $label)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-secondary">Plan</label>
                    <select name="subscription_plan_id" class="form-select">
                        <option value="">Tous</option>
                        @foreach ($plans as $plan)
                            <option value="{{ $plan->id }}" @selected((string) request('subscription_plan_id') === (string) $plan->id)>{{ $plan->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-secondary">Paiement</label>
                    <select name="payment_status" class="form-select">
                        <option value="">Tous</option>
                        @foreach (['pending' => 'En attente', 'paid' => 'Confirme', 'failed' => 'Echoue', 'cancelled' => 'Annule', 'refunded' => 'Rembourse'] as $status => $label)
                            <option value="{{ $status }}" @selected(request('payment_status') === $status)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button class="btn btn-dark w-100">Filtrer</button>
                    <a href="{{ route('super-admin.up-subscriptions.index') }}" class="btn btn-outline-secondary">RAZ</a>
                </div>
            </div>
        </form>

        <div class="table-toolbar">
            <div class="table-meta">{{ $subscriptions->total() }} resultat{{ $subscriptions->total() > 1 ? 's' : '' }}</div>
        </div>

        <div class="table-responsive">
            <table class="table table-modern align-middle">
                <thead>
                    <tr>
                        <th>Usager public</th>
                        <th>Plan</th>
                        <th>Periode</th>
                        <th>Montant</th>
                        <th>Statut</th>
                        <th>Paiement</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($subscriptions as $subscription)
                        @php
                            $latestPayment = $subscription->payments->sortByDesc('id')->first();
                            $subscriptionLabels = [
                                'pending' => 'Paiement en attente',
                                'active' => 'Actif',
                                'expired' => 'Expire',
                                'cancelled' => 'Annule',
                                'suspended' => 'Suspendu',
                                'payment_failed' => 'Paiement echoue',
                            ];
                            $paymentLabels = [
                                'pending' => 'En attente',
                                'paid' => 'Confirme',
                                'failed' => 'Echoue',
                                'cancelled' => 'Annule',
                                'refunded' => 'Rembourse',
                            ];
                        @endphp
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ trim(($subscription->publicUser?->first_name ?? '').' '.($subscription->publicUser?->last_name ?? '')) ?: '-' }}</div>
                                <div class="small text-secondary">{{ $subscription->publicUser?->phone ?: '-' }}</div>
                                <div class="small text-secondary">{{ $subscription->publicUser?->publicUserType?->name ?: '-' }}</div>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $subscription->plan?->name ?: 'Abonnement annuel UP' }}</div>
                                <div class="small text-secondary">{{ $subscription->plan?->code ?: '-' }}</div>
                                <div class="small text-secondary">Cree le {{ $subscription->created_at?->format('d/m/Y H:i') ?: '-' }}</div>
                            </td>
                            <td>
                                <div class="small text-secondary">Debut : {{ $subscription->start_date?->format('d/m/Y H:i') ?: '-' }}</div>
                                <div class="small text-secondary">Fin : {{ $subscription->end_date?->format('d/m/Y H:i') ?: '-' }}</div>
                                <div class="small text-secondary">{{ $subscription->grace_period_days }} jour(s) de grace</div>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ number_format($subscription->amount, 0, ',', ' ') }} {{ $subscription->currency }}</div>
                            </td>
                            <td><span class="status-chip">{{ $subscriptionLabels[$subscription->status] ?? $subscription->status }}</span></td>
                            <td>
                                @if ($latestPayment)
                                    <div class="fw-semibold">{{ $latestPayment->reference }}</div>
                                    <div class="small text-secondary">{{ $paymentLabels[$latestPayment->status] ?? $latestPayment->status }} · {{ number_format($latestPayment->amount, 0, ',', ' ') }} {{ $latestPayment->currency }}</div>
                                    <div class="small text-secondary">{{ $latestPayment->paid_at ? 'Confirme le '.$latestPayment->paid_at->format('d/m/Y H:i') : 'Paiement non confirme' }}</div>
                                @else
                                    <span class="text-secondary small">Aucun paiement associe</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="actions-wrap">
                                    @if ($subscription->publicUser)
                                        <a href="{{ route('super-admin.public-users.show', $subscription->publicUser) }}" class="btn btn-sm btn-outline-dark">Voir l usager</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-secondary">Aucun abonnement trouve.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="table-meta">Page {{ $subscriptions->currentPage() }} sur {{ $subscriptions->lastPage() }}</div>
            {{ $subscriptions->links() }}
        </div>
    </section>
@endsection
