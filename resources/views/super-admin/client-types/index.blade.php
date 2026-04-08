@extends('super-admin.layouts.app')

@section('title', config('app.name').' | Types de client')
@section('page-title', 'Type de client')
@section('page-description', 'Creer et piloter les types d organisations clientes.')

@section('header-badges')
    <span class="badge-soft">{{ $organizationTypes->total() }} types</span>
    <button
        type="button"
        class="btn btn-dark"
        data-bs-toggle="modal"
        data-bs-target="#clientTypeCreateModal"
    >
        Nouveau type
    </button>
@endsection

@section('content')
    <section class="panel-card">
        <div class="fw-bold mb-3">Liste des types de client</div>
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
                    <a href="{{ route('super-admin.client-types.index') }}" class="btn btn-outline-secondary">RAZ</a>
                </div>
            </div>
        </form>
        <div class="table-toolbar">
            <div class="table-meta">{{ $organizationTypes->total() }} resultat{{ $organizationTypes->total() > 1 ? 's' : '' }}</div>
        </div>
        <div class="table-responsive">
            <table class="table table-modern align-middle">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Nom</th>
                        <th>Statut</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($organizationTypes as $organizationType)
                        <tr>
                            <td>{{ $organizationType->code }}</td>
                            <td>{{ $organizationType->name }}</td>
                            <td><span class="status-chip">{{ $organizationType->status }}</span></td>
                            <td class="text-end">
                                <div class="actions-wrap">
                                    <a href="{{ route('super-admin.client-types.edit', $organizationType) }}" class="btn btn-sm btn-outline-dark">Modifier</a>
                                    <form method="POST" action="{{ route('super-admin.client-types.toggle-status', $organizationType) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="btn btn-sm btn-outline-warning">{{ $organizationType->status === 'active' ? 'Desactiver' : 'Activer' }}</button>
                                    </form>
                                    <form method="POST" action="{{ route('super-admin.client-types.destroy', $organizationType) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Supprimer</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-secondary">Aucun type de client enregistre.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="table-meta">Page {{ $organizationTypes->currentPage() }} sur {{ $organizationTypes->lastPage() }}</div>
            {{ $organizationTypes->links() }}
        </div>
    </section>

    <div class="modal fade" id="clientTypeCreateModal" tabindex="-1" aria-labelledby="clientTypeCreateModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <div>
                        <h5 class="modal-title" id="clientTypeCreateModalLabel">Nouveau type de client</h5>
                        <div class="text-secondary small">Ajoutez un type d'organisation cliente utilisable dans les parametrages.</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('super-admin.client-types.store') }}">
                    @csrf
                    <div class="modal-body pt-3">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Code</label>
                                <input type="text" name="code" class="form-control" placeholder="ENTREPRISE_GO" value="{{ old('code') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nom</label>
                                <input type="text" name="name" class="form-control" placeholder="ENTREPRISE GO" value="{{ old('name') }}" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="4" placeholder="Expliquez a quoi correspond ce type de client.">{{ old('description') }}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-dark">Creer le type</button>
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
                var modalElement = document.getElementById('clientTypeCreateModal');
                if (!modalElement || typeof bootstrap === 'undefined') {
                    return;
                }

                bootstrap.Modal.getOrCreateInstance(modalElement).show();
            });
        </script>
    @endif
@endpush
