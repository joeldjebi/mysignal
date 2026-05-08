<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label">Code</label>
        <input class="form-control" name="code" value="{{ old('code', $role?->code) }}" required>
    </div>
    <div class="col-md-8">
        <label class="form-label">Nom</label>
        <input class="form-control" name="name" value="{{ old('name', $role?->name) }}" required>
    </div>
    <div class="col-12">
        <label class="form-label">Description</label>
        <textarea class="form-control" name="description" rows="2">{{ old('description', $role?->description) }}</textarea>
    </div>
    <div class="col-12">
        <label class="form-label">Permissions</label>
        <div class="border rounded-3 p-3" style="max-height: 420px; overflow:auto;">
            <div class="row g-2">
                @foreach ($permissions as $permission)
                    <div class="col-md-6">
                        <div class="form-check border rounded-3 p-2 ps-5 h-100">
                            <input class="form-check-input" type="checkbox" name="permission_ids[]" value="{{ $permission->id }}" id="scoped-role-permission-{{ $permission->id }}" @checked(in_array($permission->id, $assignedPermissionIds))>
                            <label class="form-check-label" for="scoped-role-permission-{{ $permission->id }}">
                                <span class="fw-semibold d-block">{{ $permission->name }}</span>
                                <span class="small text-secondary">{{ $permission->code }}</span>
                            </label>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
