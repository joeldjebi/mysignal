@extends('super-admin.layouts.app')

@section('title', config('app.name').' | Permissions')
@section('page-title', 'Permissions')
@section('page-description', 'Gerer les permissions disponibles pour les futurs roles et profils.')

@section('header-badges')
    <span class="badge-soft">{{ $permissions->total() }} permissions</span>
@endsection

@section('content')
    <div class="row g-4">
        <div class="col-xl-4">
            <section class="panel-card sticky-form-card">
                <div class="fw-bold mb-3">Nouvelle permission</div>
                <form method="POST" action="{{ route('super-admin.permissions.store') }}" class="vstack gap-3">
                    @csrf
                    <div>
                        <label class="form-label">Code</label>
                        <input type="text" name="code" class="form-control" placeholder="MANAGE_PRICING" required>
                    </div>
                    <div>
                        <label class="form-label">Nom</label>
                        <input type="text" name="name" class="form-control" placeholder="Gerer la tarification" required>
                    </div>
                    <div>
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                    </div>
                    <button type="submit" class="btn btn-dark">Creer</button>
                </form>
            </section>
        </div>
        <div class="col-xl-8">
            <section class="panel-card">
                <div class="fw-bold mb-3">Liste des permissions</div>
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
                            <a href="{{ route('super-admin.permissions.index') }}" class="btn btn-outline-secondary">RAZ</a>
                        </div>
                    </div>
                </form>
                <div class="table-toolbar">
                    <div class="table-meta">{{ $permissions->total() }} resultat{{ $permissions->total() > 1 ? 's' : '' }}</div>
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
                            @forelse ($permissions as $permission)
                                <tr>
                                    <td>{{ $permission->code }}</td>
                                    <td>{{ $permission->name }}</td>
                                    <td><span class="status-chip">{{ $permission->status }}</span></td>
                                    <td class="text-end">
                                        <div class="actions-wrap">
                                            <a href="{{ route('super-admin.permissions.edit', $permission) }}" class="btn btn-sm btn-outline-dark">Modifier</a>
                                            <form method="POST" action="{{ route('super-admin.permissions.toggle-status', $permission) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button class="btn btn-sm btn-outline-warning">{{ $permission->status === 'active' ? 'Desactiver' : 'Activer' }}</button>
                                            </form>
                                            <form method="POST" action="{{ route('super-admin.permissions.destroy', $permission) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger">Supprimer</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-secondary">Aucune permission enregistree.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="table-meta">Page {{ $permissions->currentPage() }} sur {{ $permissions->lastPage() }}</div>
                    {{ $permissions->links() }}
                </div>
            </section>
        </div>
    </div>
@endsection
