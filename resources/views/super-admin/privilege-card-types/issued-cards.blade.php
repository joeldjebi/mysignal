@extends('super-admin.layouts.app')

@section('title', config('app.name').' | Cartes émises')
@section('page-title', 'Cartes émises')
@section('page-description', 'Cartes privilèges attribuées aux usagers.')

@section('header-badges')
    <span class="badge-soft">{{ $issuedCards->total() }} carte{{ $issuedCards->total() > 1 ? 's' : '' }}</span>
@endsection

@section('content')
    <div class="d-flex justify-content-end mb-3">
        <button class="btn btn-dark" type="button" data-bs-toggle="modal" data-bs-target="#issuePrivilegeCardModal">
            Émettre une carte à un usager
        </button>
    </div>

    <section class="panel-card">
        <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-3">
            <div>
                <div class="fw-bold">Cartes émises</div>
                <div class="small text-secondary">Cartes privilèges attribuées aux usagers, avec le code présenté au partenaire.</div>
            </div>
            <span class="badge-soft">{{ $issuedCards->total() }} carte{{ $issuedCards->total() > 1 ? 's' : '' }} émise{{ $issuedCards->total() > 1 ? 's' : '' }}</span>
        </div>

        <form method="GET" class="filter-bar">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small text-secondary">Recherche</label>
                    <input class="form-control" name="issued_search" value="{{ request('issued_search') }}" placeholder="Usager, téléphone, carte, code à scanner">
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-secondary">Carte</label>
                    <select class="form-select" name="issued_type_id">
                        <option value="">Toutes</option>
                        @foreach ($cardTypes as $cardType)
                            <option value="{{ $cardType->id }}" @selected((string) request('issued_type_id') === (string) $cardType->id)>{{ $cardType->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-secondary">État</label>
                    <select class="form-select" name="issued_status">
                        <option value="">Tous</option>
                        <option value="active" @selected(request('issued_status') === 'active')>Active</option>
                        <option value="expired" @selected(request('issued_status') === 'expired')>Expirée</option>
                        <option value="suspended" @selected(request('issued_status') === 'suspended')>Suspendue</option>
                        <option value="revoked" @selected(request('issued_status') === 'revoked')>Révoquée</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button class="btn btn-dark w-100">OK</button>
                    <a href="{{ route('super-admin.privilege-card-types.issued-cards') }}" class="btn btn-outline-secondary">Réinitialiser</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-modern align-middle">
                <thead>
                    <tr>
                        <th>Usager</th>
                        <th>Carte</th>
                        <th>Code à scanner</th>
                        <th>Dates</th>
                        <th>État</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($issuedCards as $card)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ trim(($card->publicUser?->first_name ?? '').' '.($card->publicUser?->last_name ?? '')) ?: '-' }}</div>
                                <div class="small text-secondary">{{ $card->publicUser?->phone ?: '-' }}</div>
                                <div class="small text-secondary">{{ $card->publicUser?->email ?: '-' }}</div>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $card->type?->name ?: '-' }}</div>
                                <div class="small text-secondary">{{ $card->card_number }}</div>
                            </td>
                            <td>
                                <span class="font-monospace small">{{ $card->card_uuid }}</span>
                            </td>
                            <td>
                                <div class="small">Émise: {{ $card->issued_at?->format('d/m/Y H:i') ?: '-' }}</div>
                                <div class="small">Activée: {{ $card->activated_at?->format('d/m/Y H:i') ?: '-' }}</div>
                                <div class="small">Expire: {{ $card->expires_at?->format('d/m/Y H:i') ?: '-' }}</div>
                            </td>
                            <td><span class="status-chip">{{ $cardStatusLabels[$card->status] ?? $card->status }}</span></td>
                            <td class="text-end">
                                @php
                                    $links = $walletLinks[$card->id] ?? null;
                                @endphp
                                @if ($links && $links['eligible'])
                                    <div class="d-flex justify-content-end gap-2 flex-wrap">
                                        <a class="btn btn-sm btn-outline-dark" href="{{ $links['apple'] }}" target="_blank" rel="noopener noreferrer">
                                            Tester iPhone
                                        </a>
                                        @if ($links['android'])
                                            <a class="btn btn-sm btn-dark" href="{{ $links['android'] }}" target="_blank" rel="noopener noreferrer">
                                                Tester Android
                                            </a>
                                        @else
                                            <span class="text-secondary small" title="{{ $links['android_error'] ?: 'Lien Android indisponible.' }}">Android indisponible</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-secondary small">Disponible après paiement confirmé</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-secondary">Aucune carte privilège émise.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="table-meta">Page {{ $issuedCards->currentPage() }} sur {{ $issuedCards->lastPage() }}</div>
            {{ $issuedCards->links() }}
        </div>
    </section>

    <div class="modal fade" id="issuePrivilegeCardModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0" style="border-radius: 24px; overflow: hidden;">
                <div class="modal-header border-0 text-white" style="background: linear-gradient(145deg, #0f2738, #1b4867);">
                    <div>
                        <div class="small text-white-50 fw-semibold">Carte privilège</div>
                        <div class="h5 mb-0 fw-bold">Émettre une carte à un usager</div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-info small mb-3">
                        Les champs avec astérisque sont obligatoires. Le numéro de carte et le code à scanner sont générés automatiquement.
                    </div>
                    <form method="POST" action="{{ route('super-admin.privilege-card-types.issue-card') }}" class="row g-3">
                        @csrf
                        <div class="col-md-6">
                            <label class="form-label">Usager <span class="text-danger">*</span></label>
                            <select class="form-select" name="public_user_id" required>
                                <option value="">Sélectionner un usager</option>
                                @foreach ($publicUsers as $modalPublicUser)
                                    @php
                                        $modalPublicUserName = trim(($modalPublicUser->first_name ?? '').' '.($modalPublicUser->last_name ?? ''));
                                    @endphp
                                    <option value="{{ $modalPublicUser->id }}" @selected((string) old('public_user_id') === (string) $modalPublicUser->id)>
                                        {{ $modalPublicUserName ?: 'Usager #'.$modalPublicUser->id }} · {{ $modalPublicUser->phone ?: $modalPublicUser->email ?: '-' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Carte privilège <span class="text-danger">*</span></label>
                            <select class="form-select" name="privilege_card_type_id" required>
                                <option value="">Sélectionner une carte</option>
                                @foreach ($activeCardTypes as $cardType)
                                    <option value="{{ $cardType->id }}" @selected((string) old('privilege_card_type_id') === (string) $cardType->id)>
                                        {{ $cardType->name }} · {{ $cardType->duration_months }} mois
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Date d’expiration</label>
                            <input class="form-control" type="date" name="expires_at" value="{{ old('expires_at') }}">
                            <div class="small text-secondary mt-1">Laisser vide pour utiliser la durée configurée sur la carte.</div>
                        </div>
                        <div class="col-12">
                            <button class="btn btn-dark">Émettre la carte</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
