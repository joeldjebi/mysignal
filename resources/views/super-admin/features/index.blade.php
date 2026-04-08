@extends('super-admin.layouts.app')

@section('title', config('app.name').' | Fonctionnalites')
@section('page-title', 'Fonctionnalites')
@section('page-description', 'Enregistrer les modules metier qui peuvent etre affectes aux admins institutionnels.')

@section('header-badges')
    <span class="badge-soft">{{ $features->total() }} fonctionnalites</span>
    <button
        type="button"
        class="btn btn-dark"
        data-bs-toggle="modal"
        data-bs-target="#featureCreateModal"
    >
        Nouvelle fonctionnalite
    </button>
@endsection

@section('content')
    <section class="panel-card">
        <div class="fw-bold mb-3">Liste des fonctionnalites</div>
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
                    <a href="{{ route('super-admin.features.index') }}" class="btn btn-outline-secondary">RAZ</a>
                </div>
            </div>
        </form>
        <div class="table-toolbar">
            <div class="table-meta">{{ $features->total() }} resultat{{ $features->total() > 1 ? 's' : '' }}</div>
        </div>
        <div class="table-responsive">
            <table class="table table-modern align-middle">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Nom</th>
                        <th>Description</th>
                        <th>Statut</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($features as $feature)
                        <tr>
                            <td>{{ $feature->code }}</td>
                            <td>{{ $feature->name }}</td>
                            <td><span class="small">{{ $feature->description ?: '-' }}</span></td>
                            <td><span class="status-chip">{{ $feature->status }}</span></td>
                            <td class="text-end">
                                <div class="actions-wrap">
                                    <a href="{{ route('super-admin.features.edit', $feature) }}" class="btn btn-sm btn-outline-dark">Modifier</a>
                                    <form method="POST" action="{{ route('super-admin.features.toggle-status', $feature) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="btn btn-sm btn-outline-warning">{{ $feature->status === 'active' ? 'Desactiver' : 'Activer' }}</button>
                                    </form>
                                    <form method="POST" action="{{ route('super-admin.features.destroy', $feature) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Supprimer</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-secondary">Aucune fonctionnalite enregistree.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="table-meta">Page {{ $features->currentPage() }} sur {{ $features->lastPage() }}</div>
            {{ $features->links() }}
        </div>
    </section>

    <div class="modal fade" id="featureCreateModal" tabindex="-1" aria-labelledby="featureCreateModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <div>
                        <h5 class="modal-title" id="featureCreateModalLabel">Nouvelle fonctionnalite</h5>
                        <div class="text-secondary small">Ajoutez un module metier attribuable aux institutions.</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('super-admin.features.store') }}">
                    @csrf
                    <div class="modal-body pt-3">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Code</label>
                                <input type="text" name="code" class="form-control" placeholder="PUBLIC_METERS" value="{{ old('code') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nom</label>
                                <input type="text" name="name" class="form-control" placeholder="Compteurs" value="{{ old('name') }}" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="4" placeholder="Expliquez simplement ce que cette fonctionnalite permet.">{{ old('description') }}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-dark">Creer la fonctionnalite</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @if ($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var modalElement = document.getElementById('featureCreateModal');
                if (!modalElement || typeof bootstrap === 'undefined') {
                    return;
                }

                bootstrap.Modal.getOrCreateInstance(modalElement).show();
            });
        </script>
    @endif
@endpush
