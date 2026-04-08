@extends('institution.layouts.app')

@section('title', config('app.name').' | Modifier un user')
@section('page-title', 'Modifier un user')
@section('page-description', 'Mettre a jour un collaborateur, ses roles et ses permissions directes.')

@section('content')
    <section class="panel-card">
        <div class="fw-bold mb-3">Edition de {{ $userAccount->name }}</div>
        <form method="POST" action="{{ route('institution.users.update', $userAccount) }}" class="row g-3">
            @csrf
            @method('PUT')
            <div class="col-md-6">
                <label class="form-label">Nom complet</label>
                <input type="text" name="name" value="{{ old('name', $userAccount->name) }}" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" value="{{ old('email', $userAccount->email) }}" class="form-control" required>
            </div>
            <div class="col-md-3">
                @include('partials.phone-field', ['value' => old('phone', $userAccount->phone), 'placeholder' => '0700000000'])
            </div>
            <div class="col-md-6">
                <label class="form-label">Nouveau mot de passe</label>
                <input type="password" name="password" class="form-control" placeholder="Laisser vide pour ne pas changer">
            </div>
            <div class="col-md-3">
                <label class="form-label">Statut</label>
                <select name="status" class="form-select" required>
                    <option value="active" @selected(old('status', $userAccount->status) === 'active')>Actif</option>
                    <option value="inactive" @selected(old('status', $userAccount->status) === 'inactive')>Inactif</option>
                </select>
            </div>
            @if ($authorization['canManageInstitutionRoles'])
                <div class="col-md-6">
                    <label class="form-label">Roles</label>
                    <div class="border rounded-3 p-2" style="max-height: 220px; overflow:auto;">
                        @forelse ($roles as $role)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="{{ $role->id }}" name="role_ids[]" id="user-role-edit-{{ $role->id }}" @checked(in_array($role->id, old('role_ids', $userAccount->roles->pluck('id')->all())))>
                                <label class="form-check-label" for="user-role-edit-{{ $role->id }}">{{ $role->name }}</label>
                            </div>
                        @empty
                            <div class="text-secondary small">Aucun role local disponible.</div>
                        @endforelse
                    </div>
                </div>
            @endif
            @if ($authorization['canManageInstitutionPermissions'])
                <div class="col-12">
                    <label class="form-label">Permissions directes</label>
                    <div class="border rounded-3 p-3" style="max-height: 320px; overflow:auto;">
                        @foreach ($groupedPermissions as $groupLabel => $groupPermissions)
                            <div class="mb-3">
                                <div class="small text-uppercase fw-bold text-secondary mb-2">{{ $groupLabel }}</div>
                                <div class="row row-cols-1 row-cols-md-2 g-2">
                                    @foreach ($groupPermissions as $permission)
                                        <div class="col">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="{{ $permission->id }}" name="permission_ids[]" id="user-permission-edit-{{ $permission->id }}" @checked(in_array($permission->id, old('permission_ids', $userAccount->permissions->pluck('id')->all())))>
                                                <label class="form-check-label" for="user-permission-edit-{{ $permission->id }}">
                                                    <span class="d-block">{{ $permission->name }}</span>
                                                    <span class="small text-secondary">{{ $permission->description ?: $permission->code }}</span>
                                                </label>
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
                <a href="{{ route('institution.users.index') }}" class="btn btn-outline-secondary">Retour</a>
            </div>
        </form>
    </section>
@endsection
