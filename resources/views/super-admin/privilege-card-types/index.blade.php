@extends('super-admin.layouts.app')

@section('title', config('app.name').' | Cartes privilèges')
@section('page-title', 'Cartes privilèges')
@section('page-description', 'Paramétrer les cartes privilèges disponibles à l’achat pour les usagers.')

@section('header-badges')
    <span class="badge-soft">{{ $types->total() }} carte{{ $types->total() > 1 ? 's' : '' }}</span>
@endsection

@section('content')
    <div class="d-flex justify-content-end mb-3">
        <button class="btn btn-dark" type="button" data-bs-toggle="modal" data-bs-target="#issuePrivilegeCardModal">
            Émettre une carte à un usager
        </button>
    </div>

    <div class="row g-4" id="privilege-card-types">
        <div class="col-lg-4">
            <section class="panel-card sticky-form-card">
                <div class="fw-bold mb-3">Nouvelle carte privilège</div>
                <form method="POST" action="{{ route('super-admin.privilege-card-types.store') }}" class="vstack gap-3">
                    @csrf
                    <div>
                        <label class="form-label">Nom <span class="text-danger">*</span></label>
                        <input class="form-control" name="name" value="{{ old('name') }}" placeholder="Standard, Premium, Gold" required>
                    </div>
                    <div>
                        <label class="form-label">Référence interne</label>
                        <input class="form-control" name="code" value="{{ old('code') }}" placeholder="Générée automatiquement">
                    </div>
                    <div class="row g-2">
                        <div class="col-8">
                            <label class="form-label">Prix <span class="text-danger">*</span></label>
                            <input class="form-control" type="number" min="1" step="1" name="price" value="{{ old('price', 1000) }}" required>
                        </div>
                        <div class="col-4">
                            <label class="form-label">Devise <span class="text-danger">*</span></label>
                            <input class="form-control" name="currency" value="{{ old('currency', 'FCFA') }}" required>
                        </div>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label">Durée mois <span class="text-danger">*</span></label>
                            <input class="form-control" type="number" min="1" max="120" name="duration_months" value="{{ old('duration_months', 12) }}" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Ordre</label>
                            <input class="form-control" type="number" min="1" max="999" name="sort_order" value="{{ old('sort_order') }}" placeholder="Auto">
                        </div>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label">Type de réduction <span class="text-danger">*</span></label>
                            <select class="form-select" name="discount_type" required>
                                <option value="percentage" @selected(old('discount_type', 'percentage') === 'percentage')>Pourcentage</option>
                                <option value="fixed_amount" @selected(old('discount_type') === 'fixed_amount')>Montant fixe</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Valeur <span class="text-danger">*</span></label>
                            <input class="form-control" type="number" min="0" step="0.01" name="discount_value" value="{{ old('discount_value', 0) }}" required>
                        </div>
                    </div>
                    <div>
                        <label class="form-label">Avantages</label>
                        <textarea class="form-control" name="benefits_text" rows="5" placeholder="Un avantage par ligne">{{ old('benefits_text') }}</textarea>
                    </div>
                    <button class="btn btn-dark">Créer la carte</button>
                </form>
            </section>
        </div>
        <div class="col-lg-8">
            <section class="panel-card">
                <div class="fw-bold mb-3">Types de cartes privilèges</div>
                <form method="GET" class="filter-bar">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-6">
                            <label class="form-label small text-secondary">Recherche</label>
                            <input class="form-control" name="search" value="{{ request('search') }}" placeholder="Nom de la carte">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-secondary">État</label>
                            <select class="form-select" name="status">
                                <option value="">Tous</option>
                                <option value="active" @selected(request('status') === 'active')>Actif</option>
                                <option value="inactive" @selected(request('status') === 'inactive')>Inactif</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex gap-2">
                            <button class="btn btn-dark w-100">OK</button>
                            <a href="{{ route('super-admin.privilege-card-types.index') }}" class="btn btn-outline-secondary">Réinitialiser</a>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-modern align-middle">
                        <thead>
                            <tr>
                                <th>Carte</th>
                                <th>Prix</th>
                                <th>Réduction</th>
                                <th>Avantages</th>
                                <th>Ventes</th>
                                <th>État</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($types as $type)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $type->name }}</div>
                                        <div class="small text-secondary">Ordre {{ $type->sort_order }} · {{ $type->duration_months }} mois</div>
                                    </td>
                                    <td>{{ number_format((float) $type->price, 0, ',', ' ') }} {{ $type->currency }}</td>
                                    <td>
                                        <span class="status-chip">
                                            {{ $type->discount_type === 'fixed_amount' ? number_format((float) $type->discount_value, 0, ',', ' ').' '.$type->currency : number_format((float) $type->discount_value, 0, ',', ' ').'%' }}
                                        </span>
                                    </td>
                                    <td>
                                        @forelse (($type->benefits ?? []) as $benefit)
                                            <div class="small">- {{ $benefit }}</div>
                                        @empty
                                            <span class="text-secondary small">Aucun avantage</span>
                                        @endforelse
                                    </td>
                                    <td><span class="status-chip">{{ $type->cards_count }}</span></td>
                                    <td><span class="status-chip">{{ $cardStatusLabels[$type->status] ?? $type->status }}</span></td>
                                    <td class="text-end">
                                        <div class="actions-wrap">
                                            <button class="btn btn-sm btn-outline-dark" type="button" data-bs-toggle="modal" data-bs-target="#editPrivilegeCardType{{ $type->id }}">Modifier</button>
                                            <form method="POST" action="{{ route('super-admin.privilege-card-types.toggle-status', $type) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button class="btn btn-sm btn-outline-warning">{{ $type->status === 'active' ? 'Désactiver' : 'Activer' }}</button>
                                            </form>
                                            <form method="POST" action="{{ route('super-admin.privilege-card-types.destroy', $type) }}" onsubmit="return confirm('Supprimer cette carte privilège ?');">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger">Supprimer</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center text-secondary">Aucune carte privilège enregistrée.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="table-meta">Page {{ $types->currentPage() }} sur {{ $types->lastPage() }}</div>
                    {{ $types->links() }}
                </div>
            </section>
        </div>
    </div>

    @foreach ($types as $type)
        <div class="modal fade" id="editPrivilegeCardType{{ $type->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0" style="border-radius: 24px; overflow: hidden;">
                    <div class="modal-header border-0 text-white" style="background: linear-gradient(145deg, #0f2738, #1b4867);">
                        <div>
                            <div class="small text-white-50 fw-semibold">Carte privilège</div>
                            <div class="h5 mb-0 fw-bold">Modifier {{ $type->name }}</div>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <form method="POST" action="{{ route('super-admin.privilege-card-types.update', $type) }}" class="row g-3">
                            @csrf
                            @method('PUT')
                            <div class="col-md-6">
                                <label class="form-label">Nom <span class="text-danger">*</span></label>
                                <input class="form-control" name="name" value="{{ old('name', $type->name) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Référence interne</label>
                                <input class="form-control" name="code" value="{{ old('code', $type->code) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Prix <span class="text-danger">*</span></label>
                                <input class="form-control" type="number" min="1" step="1" name="price" value="{{ old('price', (int) $type->price) }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Devise <span class="text-danger">*</span></label>
                                <input class="form-control" name="currency" value="{{ old('currency', $type->currency) }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Durée mois <span class="text-danger">*</span></label>
                                <input class="form-control" type="number" min="1" max="120" name="duration_months" value="{{ old('duration_months', $type->duration_months) }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Type de réduction <span class="text-danger">*</span></label>
                                <select class="form-select" name="discount_type" required>
                                    <option value="percentage" @selected(old('discount_type', $type->discount_type) === 'percentage')>Pourcentage</option>
                                    <option value="fixed_amount" @selected(old('discount_type', $type->discount_type) === 'fixed_amount')>Montant fixe</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Valeur réduction <span class="text-danger">*</span></label>
                                <input class="form-control" type="number" min="0" step="0.01" name="discount_value" value="{{ old('discount_value', (float) $type->discount_value) }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Ordre</label>
                                <input class="form-control" type="number" min="1" max="999" name="sort_order" value="{{ old('sort_order', $type->sort_order) }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Avantages</label>
                                <textarea class="form-control" name="benefits_text" rows="5">{{ old('benefits_text', collect($type->benefits ?? [])->join("\n")) }}</textarea>
                            </div>
                            <div class="col-12">
                                <button class="btn btn-dark">Enregistrer</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

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
                                @foreach ($publicUsers as $publicUser)
                                    @php
                                        $publicUserName = trim(($publicUser->first_name ?? '').' '.($publicUser->last_name ?? ''));
                                    @endphp
                                    <option value="{{ $publicUser->id }}" @selected((string) old('public_user_id') === (string) $publicUser->id)>
                                        {{ $publicUserName ?: 'Usager #'.$publicUser->id }} · {{ $publicUser->phone ?: $publicUser->email ?: '-' }}
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
