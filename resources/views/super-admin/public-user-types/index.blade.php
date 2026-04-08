@extends('super-admin.layouts.app')

@section('title', config('app.name').' | Types d usagers publics')
@section('page-title', 'Types d usagers publics')
@section('page-description', 'Definir les profils d usagers publics et la tarification associee a chacun.')

@section('header-badges')
    <span class="badge-soft">{{ $publicUserTypes->total() }} types</span>
    <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#createPublicUserTypeModal">
        Nouveau type
    </button>
@endsection

@section('content')
    <section class="panel-card">
        <div class="fw-bold mb-3">Catalogue des types d usagers publics</div>
        <form method="GET" class="filter-bar">
            <div class="row g-2 align-items-end">
                <div class="col-md-7">
                    <label class="form-label small text-secondary">Recherche</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Code, nom, description">
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-secondary">Statut</label>
                    <select name="status" class="form-select">
                        <option value="">Tous</option>
                        <option value="active" @selected(request('status') === 'active')>Actif</option>
                        <option value="inactive" @selected(request('status') === 'inactive')>Inactif</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button class="btn btn-dark w-100">Filtrer</button>
                    <a href="{{ route('super-admin.public-user-types.index') }}" class="btn btn-outline-secondary">RAZ</a>
                </div>
            </div>
        </form>

        <div class="table-responsive mt-3">
            <table class="table table-modern align-middle">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Profil</th>
                        <th>Tarification</th>
                        <th>Statut</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($publicUserTypes as $publicUserType)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $publicUserType->name }}</div>
                                <div class="small text-secondary">{{ $publicUserType->code }}</div>
                                <div class="small text-secondary">{{ $publicUserType->description ?: '-' }}</div>
                            </td>
                            <td><span class="status-chip">{{ $publicUserType->profile_kind === 'business' ? 'Entreprise' : 'Particulier' }}</span></td>
                            <td>
                                <div class="fw-semibold">{{ $publicUserType->pricingRule?->label ?: '-' }}</div>
                                <div class="small text-secondary">{{ number_format((int) ($publicUserType->pricingRule?->amount ?? 0), 0, ',', ' ') }} {{ $publicUserType->pricingRule?->currency ?: '' }}</div>
                            </td>
                            <td><span class="status-chip">{{ $publicUserType->status }}</span></td>
                            <td class="text-end">
                                <div class="actions-wrap">
                                    <a href="{{ route('super-admin.public-user-types.edit', $publicUserType) }}" class="btn btn-sm btn-outline-dark">Modifier</a>
                                    <form method="POST" action="{{ route('super-admin.public-user-types.toggle-status', $publicUserType) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="btn btn-sm btn-outline-warning">{{ $publicUserType->status === 'active' ? 'Desactiver' : 'Activer' }}</button>
                                    </form>
                                    <form method="POST" action="{{ route('super-admin.public-user-types.destroy', $publicUserType) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Supprimer</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-secondary">Aucun type d usager public enregistre.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="table-meta">Page {{ $publicUserTypes->currentPage() }} sur {{ $publicUserTypes->lastPage() }}</div>
            {{ $publicUserTypes->links() }}
        </div>
    </section>

    <div class="modal fade" id="createPublicUserTypeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title fw-bold mb-0">Nouveau type d usager public</h5>
                        <div class="small text-secondary">Associez obligatoirement un type d usager a une tarification.</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <form method="POST" action="{{ route('super-admin.public-user-types.store') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-4"><label class="form-label">Code</label><input class="form-control" name="code" value="{{ old('code') }}" placeholder="UP" required></div>
                            <div class="col-md-8"><label class="form-label">Nom</label><input class="form-control" name="name" value="{{ old('name') }}" placeholder="Usager public" required></div>
                            <div class="col-md-6">
                                <label class="form-label">Type de profil</label>
                                <select class="form-select" name="profile_kind" required>
                                    <option value="individual" @selected(old('profile_kind') === 'individual')>Particulier</option>
                                    <option value="business" @selected(old('profile_kind') === 'business')>Entreprise</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tarification associee</label>
                                <select class="form-select" name="pricing_rule_id" required>
                                    <option value="">Selectionner</option>
                                    @foreach ($pricingRules as $pricingRule)
                                        <option value="{{ $pricingRule->id }}" @selected((string) old('pricing_rule_id') === (string) $pricingRule->id)>{{ $pricingRule->label }} · {{ number_format($pricingRule->amount, 0, ',', ' ') }} {{ $pricingRule->currency }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4"><label class="form-label">Ordre</label><input class="form-control" type="number" min="1" max="999" name="sort_order" value="{{ old('sort_order', 1) }}"></div>
                            <div class="col-12"><label class="form-label">Description</label><textarea class="form-control" rows="3" name="description">{{ old('description') }}</textarea></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-dark">Creer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @if ($errors->any() && old('code'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                bootstrap.Modal.getOrCreateInstance(document.getElementById('createPublicUserTypeModal')).show();
            });
        </script>
    @endif
@endpush
