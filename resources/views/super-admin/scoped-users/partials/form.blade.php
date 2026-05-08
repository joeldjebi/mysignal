<div class="row g-3">
    <div class="col-md-6"><label class="form-label">Nom complet</label><input class="form-control" name="name" value="{{ old('name', $managedUser?->name) }}" required></div>
    <div class="col-md-6"><label class="form-label">Email</label><input class="form-control" type="email" name="email" value="{{ old('email', $managedUser?->email) }}" required></div>
    <div class="col-md-6"><label class="form-label">Telephone</label><input class="form-control" name="phone" value="{{ old('phone', $managedUser?->phone) }}"></div>
    <div class="col-md-6"><label class="form-label">Mot de passe</label><input class="form-control" type="password" name="password" @required($managedUser === null)></div>
    <div class="col-md-6">
        <label class="form-label">Roles</label>
        <div class="border rounded-3 p-3" style="max-height: 260px; overflow:auto;">
            @forelse ($roles as $role)
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" name="role_ids[]" value="{{ $role->id }}" id="scoped-user-role-{{ $role->id }}" @checked(in_array($role->id, $assignedRoleIds))>
                    <label class="form-check-label" for="scoped-user-role-{{ $role->id }}">{{ $role->name }}</label>
                </div>
            @empty
                <div class="text-secondary small">Creez d abord un role.</div>
            @endforelse
        </div>
    </div>
    <div class="col-md-6">
        <label class="form-label">Permissions directes</label>
        <div class="border rounded-3 p-3" style="max-height: 260px; overflow:auto;">
            @foreach ($permissions as $permission)
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" name="permission_ids[]" value="{{ $permission->id }}" id="scoped-user-permission-{{ $permission->id }}" @checked(in_array($permission->id, $assignedPermissionIds))>
                    <label class="form-check-label" for="scoped-user-permission-{{ $permission->id }}">
                        <span class="fw-semibold">{{ $permission->name }}</span>
                        <span class="small text-secondary d-block">{{ $permission->code }}</span>
                    </label>
                </div>
            @endforeach
        </div>
    </div>
</div>
