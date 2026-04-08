@extends('super-admin.layouts.app')

@section('title', config('app.name').' | Secteurs d activite')
@section('page-title', 'Secteurs d activite')
@section('page-description', 'Gerer les secteurs d activite proposes aux usagers publics entreprise.')

@section('header-badges')
    <span class="badge-soft">{{ $businessSectors->total() }} secteurs</span>
    <button class="btn btn-sm btn-dark rounded-pill px-3" type="button" data-bs-toggle="modal" data-bs-target="#businessSectorCreateModal">Nouveau secteur</button>
@endsection

@section('content')
    <div class="panel-card">
        <form method="GET" class="filter-bar">
            <div class="row g-3 align-items-end">
                <div class="col-md-6">
                    <label class="form-label">Recherche</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Code, nom, description">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Statut</label>
                    <select name="status" class="form-select">
                        <option value="">Tous</option>
                        <option value="active" @selected(request('status') === 'active')>Actif</option>
                        <option value="inactive" @selected(request('status') === 'inactive')>Inactif</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button class="btn btn-dark flex-fill">Filtrer</button>
                    <a href="{{ route('super-admin.business-sectors.index') }}" class="btn btn-outline-secondary flex-fill">RAZ</a>
                </div>
            </div>
        </form>

        <div class="table-toolbar">
            <div class="table-meta">{{ $businessSectors->total() }} resultat{{ $businessSectors->total() > 1 ? 's' : '' }}</div>
        </div>

        <div class="table-responsive">
            <table class="table table-modern align-middle mb-0">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Libelle</th>
                        <th>Description</th>
                        <th>Ordre</th>
                        <th>Statut</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($businessSectors as $businessSector)
                        <tr>
                            <td>{{ $businessSector->code }}</td>
                            <td class="fw-semibold">{{ $businessSector->name }}</td>
                            <td class="text-secondary">{{ $businessSector->description ?: '-' }}</td>
                            <td>{{ $businessSector->sort_order }}</td>
                            <td><span class="status-chip">{{ $businessSector->status }}</span></td>
                            <td>
                                <div class="actions-wrap">
                                    <a href="{{ route('super-admin.business-sectors.edit', $businessSector) }}" class="btn btn-sm btn-outline-dark">Modifier</a>
                                    <form method="POST" action="{{ route('super-admin.business-sectors.toggle-status', $businessSector) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="btn btn-sm btn-outline-warning">{{ $businessSector->status === 'active' ? 'Desactiver' : 'Activer' }}</button>
                                    </form>
                                    <form method="POST" action="{{ route('super-admin.business-sectors.destroy', $businessSector) }}" onsubmit="return confirm('Supprimer ce secteur ?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Supprimer</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-secondary">Aucun secteur d activite enregistre.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="table-meta">Page {{ $businessSectors->currentPage() }} sur {{ $businessSectors->lastPage() }}</div>
            {{ $businessSectors->links() }}
        </div>
    </div>

    <div class="modal fade" id="businessSectorCreateModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0" style="border-radius: 24px;">
                <div class="modal-header border-0 pb-0">
                    <div>
                        <div class="fw-bold fs-5">Nouveau secteur d activite</div>
                        <div class="text-secondary small">Ajoutez une valeur propre et compréhensible pour les profils entreprise.</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body pt-3">
                    <form method="POST" action="{{ route('super-admin.business-sectors.store') }}" class="row g-3">
                        @csrf
                        <div class="col-md-4">
                            <label class="form-label">Code</label>
                            <input type="text" name="code" class="form-control" value="{{ old('code') }}" required>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">Libelle</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Ordre</label>
                            <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', 0) }}" min="0">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="4">{{ old('description') }}</textarea>
                        </div>
                        <div class="col-12 d-flex justify-content-end gap-2 pt-2">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button class="btn btn-dark px-4">Enregistrer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @if ($errors->hasAny(['code', 'name', 'description', 'sort_order']))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const modalElement = document.getElementById('businessSectorCreateModal');
                if (modalElement) {
                    bootstrap.Modal.getOrCreateInstance(modalElement).show();
                }
            });
        </script>
    @endif
@endsection
