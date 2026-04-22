@extends('partner.layouts.app')

@section('title', config('app.name').' | Offres partenaire')
@section('page-title', 'Offres de reduction')
@section('page-description', 'Creez et mettez a jour les reductions que vos agents mobiles pourront appliquer.')

@section('header-badges')
    <span class="badge-soft">{{ $offers->total() }} offres</span>
    <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#createOfferModal">
        Nouvelle offre
    </button>
@endsection

@section('content')
    <section class="panel-card">
        <div class="fw-bold mb-3">Catalogue des offres</div>
        <form method="GET" class="filter-bar">
            <div class="row g-2 align-items-end">
                <div class="col-md-8">
                    <label class="form-label small text-secondary">Recherche</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Code, nom ou description">
                </div>
                <div class="col-md-4">
                    <label class="form-label small text-secondary">Statut</label>
                    <select name="status" class="form-select">
                        <option value="">Tous</option>
                        <option value="draft" @selected(request('status') === 'draft')>Brouillon</option>
                        <option value="active" @selected(request('status') === 'active')>Active</option>
                        <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                        <option value="archived" @selected(request('status') === 'archived')>Archivee</option>
                    </select>
                </div>
            </div>
            <div class="d-flex gap-2 mt-2">
                <button class="btn btn-dark">Filtrer</button>
                <a href="{{ route('partner.offers.index') }}" class="btn btn-outline-secondary">RAZ</a>
            </div>
        </form>

        <div class="table-toolbar">
            <div class="table-meta">{{ $offers->total() }} resultat{{ $offers->total() > 1 ? 's' : '' }}</div>
        </div>

        <div class="table-card">
            <div class="table-responsive">
                <table class="table table-modern mb-0">
                    <thead>
                        <tr>
                            <th>Offre</th>
                            <th>Type</th>
                            <th>Regles</th>
                            <th>Statut</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($offers as $offer)
                            <tr>
                                <td>
                                    <div class="meta-stack">
                                        <span class="meta-title">{{ $offer->name }}</span>
                                        <span class="meta-subtitle">{{ $offer->code }}</span>
                                        <span class="meta-subtitle">{{ $offer->description ?: '-' }}</span>
                                    </div>
                                </td>
                                <td>{{ $offer->discount_type }} {{ $offer->discount_value !== null ? '(' . $offer->discount_value . ')' : '' }}</td>
                                <td>
                                    <div class="small">Min: {{ $offer->minimum_purchase_amount ?? '-' }}</div>
                                    <div class="small">Max carte: {{ $offer->max_uses_per_card ?? '-' }}</div>
                                    <div class="small">Max jour: {{ $offer->max_uses_per_day ?? '-' }}</div>
                                </td>
                                <td><span class="status-chip">{{ $offer->status }}</span></td>
                                <td class="text-end">
                                    <div class="report-actions">
                                        <a href="{{ route('partner.offers.edit', $offer) }}" class="btn btn-sm btn-outline-dark">Modifier</a>
                                        <form method="POST" action="{{ route('partner.offers.toggle-status', $offer) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button class="btn btn-sm btn-outline-warning">{{ $offer->status === 'active' ? 'Desactiver' : 'Activer' }}</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-secondary">Aucune offre enregistree.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="table-meta">Page {{ $offers->currentPage() }} sur {{ $offers->lastPage() }}</div>
            {{ $offers->links() }}
        </div>
    </section>

    <div class="modal fade" id="createOfferModal" tabindex="-1" aria-labelledby="createOfferModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <div>
                        <h5 class="modal-title" id="createOfferModalLabel">Nouvelle offre</h5>
                        <div class="text-secondary small">Configurez une reduction disponible pour les agents mobiles.</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('partner.offers.store') }}">
                    @csrf
                    <div class="modal-body pt-3">
                        <div class="vstack gap-3">
                            <div class="row g-2">
                                <div class="col-md-5">
                                    <label class="form-label">Code</label>
                                    <input type="text" name="code" value="{{ old('code') }}" class="form-control" required>
                                </div>
                                <div class="col-md-7">
                                    <label class="form-label">Nom</label>
                                    <input type="text" name="name" value="{{ old('name') }}" class="form-control" required>
                                </div>
                            </div>
                            <div>
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="2">{{ old('description') }}</textarea>
                            </div>
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <label class="form-label">Type</label>
                                    <select name="discount_type" class="form-select" required>
                                        <option value="percentage" @selected(old('discount_type') === 'percentage')>Pourcentage</option>
                                        <option value="fixed_amount" @selected(old('discount_type') === 'fixed_amount')>Montant fixe</option>
                                        <option value="custom" @selected(old('discount_type') === 'custom')>Custom</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Valeur</label>
                                    <input type="number" step="0.01" min="0" name="discount_value" value="{{ old('discount_value') }}" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Devise</label>
                                    <input type="text" name="currency" value="{{ old('currency', 'FCFA') }}" class="form-control">
                                </div>
                            </div>
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="form-label">Achat minimum</label>
                                    <input type="number" step="0.01" min="0" name="minimum_purchase_amount" value="{{ old('minimum_purchase_amount') }}" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Reduction max</label>
                                    <input type="number" step="0.01" min="0" name="maximum_discount_amount" value="{{ old('maximum_discount_amount') }}" class="form-control">
                                </div>
                            </div>
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="form-label">Max usages / carte</label>
                                    <input type="number" min="1" name="max_uses_per_card" value="{{ old('max_uses_per_card') }}" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Max usages / jour</label>
                                    <input type="number" min="1" name="max_uses_per_day" value="{{ old('max_uses_per_day') }}" class="form-control">
                                </div>
                            </div>
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="form-label">Debut</label>
                                    <input type="date" name="starts_at" value="{{ old('starts_at') }}" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Fin</label>
                                    <input type="date" name="ends_at" value="{{ old('ends_at') }}" class="form-control">
                                </div>
                            </div>
                            <div>
                                <label class="form-label">Statut</label>
                                <select name="status" class="form-select">
                                    <option value="draft" @selected(old('status') === 'draft')>Brouillon</option>
                                    <option value="active" @selected(old('status') === 'active')>Active</option>
                                    <option value="inactive" @selected(old('status') === 'inactive')>Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-dark">Creer l offre</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @if ($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const modalElement = document.getElementById('createOfferModal');

                if (!modalElement || typeof bootstrap === 'undefined') {
                    return;
                }

                bootstrap.Modal.getOrCreateInstance(modalElement).show();
            });
        </script>
    @endif
@endsection
