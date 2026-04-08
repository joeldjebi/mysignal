@extends('super-admin.layouts.app')

@section('title', config('app.name').' | Roles')
@section('page-title', 'Roles')
@section('page-description', 'Creer des roles et leur associer des permissions.')

@section('header-badges')
    <span class="badge-soft">{{ $roles->total() }} roles</span>
    <span class="badge-soft">{{ $permissions->count() }} permissions</span>
@endsection

@section('content')
    <div class="row g-4">
        <div class="col-xl-4">
            <section class="panel-card sticky-form-card">
                <div class="fw-bold mb-3">Nouveau role</div>
                <form method="POST" action="{{ route('super-admin.roles.store') }}" class="vstack gap-3">
                    @csrf
                    <div>
                        <label class="form-label">Code</label>
                        <input type="text" name="code" class="form-control" placeholder="ADMIN_ENTREPRISE" required>
                    </div>
                    <div>
                        <label class="form-label">Nom</label>
                        <input type="text" name="name" class="form-control" placeholder="Admin entreprise" required>
                    </div>
                    <div>
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                    </div>
                    <div>
                        <label class="form-label">Permissions</label>
                        <div class="border rounded-3 p-2" style="max-height: 220px; overflow:auto;">
                            @forelse ($permissions as $permission)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="{{ $permission->id }}" name="permission_ids[]" id="permission-create-{{ $permission->id }}">
                                    <label class="form-check-label" for="permission-create-{{ $permission->id }}">{{ $permission->name }}</label>
                                </div>
                            @empty
                                <div class="text-secondary small">Cree d abord des permissions.</div>
                            @endforelse
                        </div>
                    </div>
                    <button type="submit" class="btn btn-dark">Creer</button>
                </form>
            </section>
        </div>
        <div class="col-xl-8">
            <section class="panel-card">
                <div class="fw-bold mb-3">Liste des roles</div>
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
                            <a href="{{ route('super-admin.roles.index') }}" class="btn btn-outline-secondary">RAZ</a>
                        </div>
                    </div>
                </form>
                <div class="table-toolbar">
                    <div class="table-meta">{{ $roles->total() }} resultat{{ $roles->total() > 1 ? 's' : '' }}</div>
                </div>
                <div class="table-responsive">
                    <table class="table table-modern align-middle">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Nom</th>
                                <th>Permissions</th>
                                <th>Statut</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($roles as $role)
                                <tr>
                                    <td>{{ $role->code }}</td>
                                    <td>{{ $role->name }}</td>
                                    <td><span class="small">{{ $role->permissions->pluck('name')->join(', ') ?: '-' }}</span></td>
                                    <td><span class="status-chip">{{ $role->status }}</span></td>
                                    <td class="text-end">
                                        <div class="actions-wrap">
                                            <a href="{{ route('super-admin.roles.edit', $role) }}" class="btn btn-sm btn-outline-dark">Modifier</a>
                                            <form method="POST" action="{{ route('super-admin.roles.toggle-status', $role) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button class="btn btn-sm btn-outline-warning">{{ $role->status === 'active' ? 'Desactiver' : 'Activer' }}</button>
                                            </form>
                                            <form method="POST" action="{{ route('super-admin.roles.destroy', $role) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger">Supprimer</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-secondary">Aucun role enregistre.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="table-meta">Page {{ $roles->currentPage() }} sur {{ $roles->lastPage() }}</div>
                    {{ $roles->links() }}
                </div>
            </section>
        </div>
    </div>
@endsection
