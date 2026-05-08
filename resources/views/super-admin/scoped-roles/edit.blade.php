@extends('super-admin.layouts.app')

@section('title', config('app.name').' | Modifier role')
@section('page-title', 'Modifier le role')
@section('page-description', 'Ajuster les permissions heritees par les utilisateurs portant ce role.')

@section('content')
    <section class="panel-card">
        <form method="POST" action="{{ route('super-admin.scoped-roles.update', $role) }}">
            @csrf
            @method('PUT')
            @include('super-admin.scoped-roles.partials.form', ['role' => $role, 'permissions' => $permissions, 'assignedPermissionIds' => old('permission_ids', $assignedPermissionIds)])
            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('super-admin.scoped-roles.index') }}" class="btn btn-outline-secondary">Retour</a>
                <button class="btn btn-dark">Enregistrer</button>
            </div>
        </form>
    </section>
@endsection
