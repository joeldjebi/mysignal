@extends('super-admin.layouts.app')

@section('title', config('app.name').' | Mes utilisateurs')
@section('page-title', 'Mes utilisateurs')
@section('page-description', 'Creer vos propres utilisateurs et leur attribuer vos roles ou permissions directes.')

@section('header-badges')
    <span class="badge-soft">{{ $users->total() }} utilisateurs</span>
    <button class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#createScopedUserModal">Nouvel utilisateur</button>
@endsection

@section('content')
    <section class="panel-card">
        <div class="table-responsive">
            <table class="table table-modern align-middle">
                <thead><tr><th>Utilisateur</th><th>Roles</th><th>Statut</th><th class="text-end">Actions</th></tr></thead>
                <tbody>
                    @forelse ($users as $managedUser)
                        <tr>
                            <td><div class="fw-semibold">{{ $managedUser->name }}</div><div class="small text-secondary">{{ $managedUser->email }}</div></td>
                            <td>{{ $managedUser->roles->pluck('name')->join(', ') ?: '-' }}</td>
                            <td><span class="status-chip">{{ $managedUser->status }}</span></td>
                            <td class="text-end">
                                <div class="actions-wrap">
                                    <a class="btn btn-sm btn-outline-dark" href="{{ route('super-admin.scoped-users.edit', $managedUser) }}">Modifier</a>
                                    <form method="POST" action="{{ route('super-admin.scoped-users.destroy', $managedUser) }}">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Supprimer</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-secondary">Aucun utilisateur cree.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $users->links() }}
    </section>

    <div class="modal fade" id="createScopedUserModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <form method="POST" action="{{ route('super-admin.scoped-users.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">Nouvel utilisateur</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        @include('super-admin.scoped-users.partials.form', ['managedUser' => null, 'roles' => $roles, 'permissions' => $permissions, 'assignedRoleIds' => old('role_ids', []), 'assignedPermissionIds' => old('permission_ids', [])])
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
