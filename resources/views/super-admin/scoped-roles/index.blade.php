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
