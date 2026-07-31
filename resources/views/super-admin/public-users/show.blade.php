@extends('super-admin.layouts.app')

@section('title', config('app.name').' | Details de '.$publicUser->first_name.' '.$publicUser->last_name)
@section('page-title', 'Details de l usager public')
@section('page-description', 'Consulter le profil, les notifications, les abonnements et les signalements de l usager.')

@section('content')
    @php
        $authUser = auth()->user();
        $canUpdatePublicUser = ($authUser?->hasEffectivePermissionCode('SA_PUBLIC_USERS_UPDATE') ?? false)
            || ($authUser?->hasEffectivePermissionCode('SA_PUBLIC_USERS_MANAGE') ?? false);
    @endphp
    <section class="panel-card">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <div class="fw-bold mb-1">{{ $publicUser->first_name }} {{ $publicUser->last_name }}</div>
                <div class="small text-secondary">{{ $publicUser->phone }}{{ $publicUser->email ? ' · '.$publicUser->email : '' }}</div>
                <div class="small text-secondary mt-1">{{ $publicUser->publicUserType?->name ?: '-' }} · {{ $publicUser->commune ?: 'Commune non renseignee' }}</div>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                @if ($canUpdatePublicUser)
                    <a href="{{ route('super-admin.public-users.edit', $publicUser) }}" class="btn btn-outline-dark">Modifier le compte</a>
                @endif
                <a href="{{ route('super-admin.public-users.index') }}" class="btn btn-outline-secondary">Retour</a>
            </div>
        </div>
    </section>

    <section class="panel-card mt-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
            <div>
                <div class="fw-bold">Informations du compte</div>
                <div class="small text-secondary">Profil, contact et statut de l usager.</div>
            </div>
            <span class="status-chip">{{ $publicUser->status }}</span>
        </div>

        <div class="row g-3">
            <div class="col-md-3">
                <div class="small text-secondary">Type</div>
                <div class="fw-semibold">{{ $publicUser->publicUserType?->name ?: '-' }}</div>
            </div>
            <div class="col-md-3">
                <div class="small text-secondary">Telephone</div>
                <div class="fw-semibold">{{ $publicUser->phone }}</div>
            </div>
            <div class="col-md-3">
                <div class="small text-secondary">Email</div>
                <div class="fw-semibold">{{ $publicUser->email ?: '-' }}</div>
            </div>
            <div class="col-md-3">
                <div class="small text-secondary">Derniere connexion</div>
                <div class="fw-semibold">{{ $publicUser->last_login_at?->format('d/m/Y H:i') ?: '-' }}</div>
            </div>
            <div class="col-md-3">
                <div class="small text-secondary">Commune</div>
                <div class="fw-semibold">{{ $publicUser->commune ?: '-' }}</div>
            </div>
            <div class="col-md-3">
                <div class="small text-secondary">Adresse</div>
                <div class="fw-semibold">{{ $publicUser->address ?: '-' }}</div>
            </div>
            <div class="col-md-3">
                <div class="small text-secondary">WhatsApp</div>
                <div class="fw-semibold">{{ $publicUser->is_whatsapp_number ? 'Oui' : 'Non' }}</div>
            </div>
            <div class="col-md-3">
                <div class="small text-secondary">Cree le</div>
                <div class="fw-semibold">{{ $publicUser->created_at?->format('d/m/Y H:i') ?: '-' }}</div>
            </div>
            @if ($publicUser->company_name)
                <div class="col-md-4">
                    <div class="small text-secondary">Entreprise</div>
                    <div class="fw-semibold">{{ $publicUser->company_name }}</div>
                </div>
                <div class="col-md-4">
                    <div class="small text-secondary">Secteur</div>
                    <div class="fw-semibold">{{ $publicUser->business_sector ?: '-' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="small text-secondary">Adresse entreprise</div>
                    <div class="fw-semibold">{{ $publicUser->company_address ?: '-' }}</div>
                </div>
            @endif
        </div>
    </section>

    <section class="panel-card mt-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
            <div>
                <div class="fw-bold">Tokens notifications</div>
                <div class="small text-secondary">Appareils web ou mobiles capables de recevoir les push.</div>
            </div>
            <span class="badge-soft">{{ $publicUser->deviceTokens->whereNull('revoked_at')->count() }} actif{{ $publicUser->deviceTokens->whereNull('revoked_at')->count() > 1 ? 's' : '' }}</span>
        </div>

        <div class="table-responsive">
            <table class="table table-modern align-middle">
                <thead>
                    <tr>
                        <th>Plateforme</th>
                        <th>Appareil</th>
                        <th>Version</th>
                        <th>Dernier enregistrement</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    @if ($publicUser->deviceTokens->isNotEmpty())
                        @foreach ($publicUser->deviceTokens as $deviceToken)
                        <tr>
                            <td>{{ strtoupper($deviceToken->platform ?: '-') }}</td>
                            <td>
                                <div class="fw-semibold">{{ $deviceToken->device_name ?: '-' }}</div>
                                <div class="small text-secondary">{{ substr($deviceToken->token_hash, 0, 16) }}...</div>
                            </td>
                            <td>{{ $deviceToken->app_version ?: '-' }}</td>
                            <td>{{ $deviceToken->last_seen_at?->format('d/m/Y H:i') ?: '-' }}</td>
                            <td><span class="status-chip">{{ $deviceToken->revoked_at ? 'Revoque' : 'Actif' }}</span></td>
                        </tr>
                        @endforeach
                    @else
                        <tr><td colspan="5" class="text-center text-secondary">Aucun token notification enregistre.</td></tr>
                    @endif
                </tbody>
            </table>
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
                    @if ($subscriptions->isNotEmpty())
                        @foreach ($subscriptions as $subscription)
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
                        @endforeach
                    @else
                        <tr><td colspan="5" class="text-center text-secondary">Aucun abonnement enregistre pour cet usager.</td></tr>
                    @endif
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
