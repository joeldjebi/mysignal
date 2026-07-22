@extends('super-admin.layouts.app')

@section('title', config('app.name').' | Mes roles')
@section('page-title', 'Mes roles')
@section('page-description', 'Creer des roles avec les permissions que le SA vous a attribuees.')

@section('header-badges')
    <span class="badge-soft">{{ $roles->total() }} roles</span>
    <button class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#createScopedRoleModal">Nouveau role</button>
@endsection

@section('content')
    <section class="panel-card">
        <div class="table-responsive">
            <table class="table table-modern align-middle">
                <thead><tr><th>Role</th><th>Permissions</th><th class="text-end">Actions</th></tr></thead>
                <tbody>
                    @forelse ($roles as $role)
                        <tr>
                            <td><div class="fw-semibold">{{ $role->name }}</div><div class="small text-secondary">{{ $role->code }}</div></td>
                            <td>{{ $role->permissions->count() }}</td>
                            <td class="text-end">
                                <div class="actions-wrap">
                                    <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="modal" data-bs-target="#scopedRoleDetailsModal-{{ $role->id }}">Détails</button>
                                    <a class="btn btn-sm btn-outline-dark" href="{{ route('super-admin.scoped-roles.edit', $role) }}">Modifier</a>
                                    <form method="POST" action="{{ route('super-admin.scoped-roles.destroy', $role) }}">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Supprimer</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center text-secondary">Aucun role cree.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $roles->links() }}
    </section>

    @foreach ($roles as $role)
        <div class="modal fade" id="scopedRoleDetailsModal-{{ $role->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title fw-bold mb-0">{{ $role->name }}</h5>
                            <div class="small text-secondary">{{ $role->description ?: 'Aucune description renseignée.' }}</div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                    </div>
                    <div class="modal-body">
                        <div class="table-responsive">
                            <table class="table table-modern align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Permission</th>
                                        <th>Catégorie</th>
                                        <th>Profil</th>
                                        <th>Statut</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($role->permissions->sortBy('name') as $permission)
                                        <tr>
                                            <td>
                                                <div class="fw-semibold">{{ $permission->name }}</div>
                                                <div class="small text-secondary">{{ $permission->description ?: '-' }}</div>
                                            </td>
                                            <td>{{ method_exists($permission, 'categoryLabel') ? $permission->categoryLabel() : ($permission->category ?: '-') }}</td>
                                            <td><span class="status-chip">{{ method_exists($permission, 'profileScopeLabel') ? $permission->profileScopeLabel() : ($permission->profile_scope ?: '-') }}</span></td>
                                            <td><span class="status-chip">{{ $permission->status === 'active' ? 'Actif' : 'Inactif' }}</span></td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="text-center text-secondary">Aucune permission attribuée.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Fermer</button>
                        <a class="btn btn-dark" href="{{ route('super-admin.scoped-roles.edit', $role) }}">Modifier</a>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    <div class="modal fade" id="createScopedRoleModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <form method="POST" action="{{ route('super-admin.scoped-roles.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">Nouveau role</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        @include('super-admin.scoped-roles.partials.form', ['role' => null, 'permissions' => $permissions, 'assignedPermissionIds' => old('permission_ids', [])])
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button class="btn btn-dark">Creer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
