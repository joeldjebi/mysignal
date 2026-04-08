@extends('institution.layouts.app')

@section('title', config('app.name').' | Modifier un role')
@section('page-title', 'Modifier un role')
@section('page-description', 'Mettre a jour un role local et ses permissions autorisees.')

@section('content')
    <section class="panel-card">
        <div class="fw-bold mb-3">Edition de {{ $role->name }}</div>
        <form method="POST" action="{{ route('institution.roles.update', $role) }}" class="row g-3">
            @csrf
            @method('PUT')
            <div class="col-md-3">
                <label class="form-label">Code</label>
                <input type="text" name="code" value="{{ old('code', $displayCode) }}" class="form-control" required>
            </div>
            <div class="col-md-5">
                <label class="form-label">Nom</label>
                <input type="text" name="name" value="{{ old('name', $role->name) }}" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Statut</label>
                <select name="status" class="form-select" required>
                    <option value="active" @selected(old('status', $role->status) === 'active')>Actif</option>
                    <option value="inactive" @selected(old('status', $role->status) === 'inactive')>Inactif</option>
                </select>
            </div>
            <div class="col-12">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="2">{{ old('description', $role->description) }}</textarea>
            </div>
            @if ($authorization['canManageInstitutionPermissions'])
                <div class="col-12">
                    <label class="form-label">Permissions</label>
                    <div class="border rounded-3 p-3" style="max-height: 320px; overflow:auto;">
                        @foreach ($groupedPermissions as $groupLabel => $groupPermissions)
                            <div class="mb-3">
                                <div class="small text-uppercase fw-bold text-secondary mb-2">{{ $groupLabel }}</div>
                                <div class="row row-cols-1 row-cols-md-2 g-2">
                                    @foreach ($groupPermissions as $permission)
                                        <div class="col">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="{{ $permission->id }}" name="permission_ids[]" id="role-permission-edit-{{ $permission->id }}" @checked(in_array($permission->id, old('permission_ids', $role->permissions->pluck('id')->all())))>
                                                <label class="form-check-label" for="role-permission-edit-{{ $permission->id }}">{{ $permission->name }}</label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-dark">Enregistrer</button>
                <a href="{{ route('institution.roles.index') }}" class="btn btn-outline-secondary">Retour</a>
            </div>
        </form>
    </section>
@endsection
