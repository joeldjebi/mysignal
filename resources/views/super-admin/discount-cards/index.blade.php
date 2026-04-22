@extends('super-admin.layouts.app')

@section('title', config('app.name').' | Cartes de reduction')
@section('page-title', 'Cartes de reduction')
@section('page-description', 'Consulter les cartes de reduction generees pour les UP actifs et filtrer leur etat ou leur periode de validite.')

@section('header-badges')
    <span class="badge-soft">{{ $cards->total() }} carte{{ $cards->total() > 1 ? 's' : '' }}</span>
@endsection

@section('content')
    <section class="panel-card">
        <div class="fw-bold mb-3">Liste des cartes de reduction</div>
        <form method="GET" class="filter-bar">
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small text-secondary">Recherche</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Carte, UUID, usager">
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-secondary">Statut carte</label>
                    <select name="status" class="form-select">
                        <option value="">Tous</option>
                        @foreach (['pending', 'active', 'suspended', 'expired', 'revoked'] as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-secondary">Statut abonnement</label>
                    <select name="subscription_status" class="form-select">
                        <option value="">Tous</option>
                        @foreach (['pending', 'active', 'expired', 'cancelled', 'suspended'] as $status)
                            <option value="{{ $status }}" @selected(request('subscription_status') === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-secondary">Type UP</label>
                    <select name="public_user_type_id" class="form-select">
                        <option value="">Tous</option>
                        @foreach ($publicUserTypes as $type)
                            <option value="{{ $type->id }}" @selected((string) request('public_user_type_id') === (string) $type->id)>{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1">
                    <label class="form-label small text-secondary">Creee du</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control">
                </div>
                <div class="col-md-1">
                    <label class="form-label small text-secondary">au</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control">
                </div>
                <div class="col-md-1">
                    <label class="form-label small text-secondary">Expire du</label>
                    <input type="date" name="expires_from" value="{{ request('expires_from') }}" class="form-control">
                </div>
            </div>
            <div class="row g-2 align-items-end mt-1">
                <div class="col-md-2">
                    <label class="form-label small text-secondary">Expire au</label>
                    <input type="date" name="expires_to" value="{{ request('expires_to') }}" class="form-control">
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button class="btn btn-dark w-100">Filtrer</button>
                    <a href="{{ route('super-admin.discount-cards.index') }}" class="btn btn-outline-secondary">RAZ</a>
                </div>
            </div>
        </form>

        <div class="table-toolbar">
            <div class="table-meta">{{ $cards->total() }} resultat{{ $cards->total() > 1 ? 's' : '' }}</div>
        </div>
        <div class="table-responsive">
            <table class="table table-modern align-middle">
                <thead>
                    <tr>
                        <th>Carte</th>
                        <th>Usager public</th>
                        <th>Abonnement</th>
                        <th>Validite</th>
                        <th>Statut</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($cards as $card)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $card->card_number }}</div>
                                <div class="small text-secondary">{{ $card->card_uuid }}</div>
                                <div class="small text-secondary">Creee le {{ $card->created_at?->format('d/m/Y H:i') ?: '-' }}</div>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ trim(($card->publicUser?->first_name ?? '').' '.($card->publicUser?->last_name ?? '')) ?: '-' }}</div>
                                <div class="small text-secondary">{{ $card->publicUser?->phone ?: '-' }}</div>
                                <div class="small text-secondary">{{ $card->publicUser?->publicUserType?->name ?: '-' }}</div>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $card->subscription?->plan?->name ?: '-' }}</div>
                                <div class="small text-secondary">Statut: {{ $card->subscription?->status ?: '-' }}</div>
                                <div class="small text-secondary">Du {{ optional($card->subscription?->start_date)->format('d/m/Y') ?: '-' }} au {{ optional($card->subscription?->end_date)->format('d/m/Y') ?: '-' }}</div>
                            </td>
                            <td>
                                <div class="small">Emission: {{ optional($card->issued_at)->format('d/m/Y H:i') ?: '-' }}</div>
                                <div class="small">Activation: {{ optional($card->activated_at)->format('d/m/Y H:i') ?: '-' }}</div>
                                <div class="small">Expiration: {{ optional($card->expires_at)->format('d/m/Y H:i') ?: '-' }}</div>
                                <div class="small">Dernier usage: {{ optional($card->last_used_at)->format('d/m/Y H:i') ?: '-' }}</div>
                            </td>
                            <td><span class="status-chip">{{ $card->status }}</span></td>
                            <td class="text-end">
                                <div class="actions-wrap">
                                    @if ($card->publicUser)
                                        <a href="{{ route('super-admin.public-users.show', $card->publicUser) }}" class="btn btn-sm btn-outline-dark">Voir l usager</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-secondary">Aucune carte de reduction trouvee.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="table-meta">Page {{ $cards->currentPage() }} sur {{ $cards->lastPage() }}</div>
            {{ $cards->links() }}
        </div>
    </section>
@endsection
