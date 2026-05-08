@extends('super-admin.layouts.app')

@section('title', config('app.name').' | Modifier utilisateur')
@section('page-title', 'Modifier utilisateur')
@section('page-description', 'Mettre a jour le compte, ses roles et ses permissions directes.')

@section('content')
    <section class="panel-card">
        <form method="POST" action="{{ route('super-admin.scoped-users.update', $managedUser) }}">
            @csrf
            @method('PUT')
            @include('super-admin.scoped-users.partials.form', ['managedUser' => $managedUser, 'roles' => $roles, 'permissions' => $permissions, 'assignedRoleIds' => old('role_ids', $assignedRoleIds), 'assignedPermissionIds' => old('permission_ids', $assignedPermissionIds)])
            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('super-admin.scoped-users.index') }}" class="btn btn-outline-secondary">Retour</a>
                <button class="btn btn-dark">Enregistrer</button>
            </div>
        </form>
    </section>
@endsection
