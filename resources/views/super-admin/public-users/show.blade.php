@extends('super-admin.layouts.app')

@section('title', config('app.name').' | Signalements de '.$publicUser->first_name.' '.$publicUser->last_name)
@section('page-title', 'Signalements de l usager public')
@section('page-description', 'Consulter les signalements d un usager public et ouvrir des dossiers de reparation si necessaire.')

@section('content')
    <section class="panel-card">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <div class="fw-bold mb-1">{{ $publicUser->first_name }} {{ $publicUser->last_name }}</div>
                <div class="small text-secondary">{{ $publicUser->phone }}{{ $publicUser->email ? ' · '.$publicUser->email : '' }}</div>
                <div class="small text-secondary mt-1">{{ $publicUser->publicUserType?->name ?: '-' }} · {{ $publicUser->commune ?: 'Commune non renseignee' }}</div>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('super-admin.public-users.edit', $publicUser) }}" class="btn btn-outline-dark">Modifier le compte</a>
                <a href="{{ route('super-admin.public-users.index') }}" class="btn btn-outline-secondary">Retour</a>
            </div>
        </div>
    </section>

    <section class="panel-card mt-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
            <div>
                <div class="fw-bold">Historique des abonnements</div>
                <div class="small text-secondary">Souscriptions annuelles, statuts et paiements associes.</div>
            </div>
            <span class="badge-soft">{{ $subscriptions->total() }} abonnement{{ $subscriptions->total() > 1 ? 's' : '' }}</span>
        </div>

        <div class="table-responsive">
            <table class="table table-modern align-middle">
                <thead>
                    <tr>
                        <th>Plan</th>
                        <th>Periode</th>
                        <th>Montant</th>
                        <th>Statut</th>
                        <th>Paiement</th>
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
                                <div class="fw-semibold">{{ $subscription->plan?->name ?: 'Abonnement annuel UP' }}</div>
                                <div class="small text-secondary">{{ $subscription->plan?->code ?: '-' }}</div>
                                <div class="small text-secondary">Cree le {{ $subscription->created_at?->format('d/m/Y H:i') ?: '-' }}</div>
                            </td>
                            <td>
                                <div class="small text-secondary">Debut: {{ $subscription->start_date?->format('d/m/Y H:i') ?: '-' }}</div>
                                <div class="small text-secondary">Fin: {{ $subscription->end_date?->format('d/m/Y H:i') ?: '-' }}</div>
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
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-secondary">Aucun abonnement enregistre pour cet usager.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="table-meta">Page {{ $subscriptions->currentPage() }} sur {{ $subscriptions->lastPage() }}</div>
            {{ $subscriptions->links() }}
        </div>
    </section>

    @include('super-admin.public-users.partials.reports-section', ['publicUser' => $publicUser])
@endsection
