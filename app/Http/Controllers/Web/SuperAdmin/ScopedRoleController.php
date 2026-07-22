<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\SuperAdmin\Concerns\InteractsWithScopedSaAdminManagement;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ScopedRoleController extends Controller
{
    use InteractsWithScopedSaAdminManagement;

    public function index(Request $request): View
    {
        $user = $request->user()->loadMissing(['roles.permissions', 'permissions']);
        $this->authorizeScopedManagement($user, 'SA_SCOPED_ROLES_MANAGE');

        return view('super-admin.scoped-roles.index', [
            'roles' => $this->scopedRoleQuery($user)->with('permissions')->latest()->paginate(12),
            'permissions' => $this->assignablePermissions($user),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user()->loadMissing(['roles.permissions', 'permissions']);
        $this->authorizeScopedManagement($user, 'SA_SCOPED_ROLES_MANAGE');
        $attributes = $this->validatePayload($request);

        DB::transaction(function () use ($attributes, $user): void {
            $role = Role::query()->create([
                'organization_id' => null,
                'created_by' => $user->id,
                'code' => strtoupper($attributes['code']),
                'name' => $attributes['name'],
                'description' => $attributes['description'] ?? null,
                'status' => 'active',
            ]);

            $role->permissions()->sync($this->validAssignablePermissionIds($user, $attributes['permission_ids'] ?? []));
        });

        return redirect()->route('super-admin.scoped-roles.index')->with('success', 'Le rôle a été créé.');
    }

    public function edit(Request $request, Role $role): View
    {
        $user = $request->user()->loadMissing(['roles.permissions', 'permissions']);
        $this->authorizeScopedManagement($user, 'SA_SCOPED_ROLES_MANAGE');
        $this->abortIfRoleIsNotOwnedBy($role, $user->id, $user->is_super_admin);

        return view('super-admin.scoped-roles.edit', [
            'role' => $role->load('permissions'),
            'permissions' => $this->assignablePermissions($user),
            'assignedPermissionIds' => $role->permissions->pluck('id')->all(),
        ]);
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $user = $request->user()->loadMissing(['roles.permissions', 'permissions']);
        $this->authorizeScopedManagement($user, 'SA_SCOPED_ROLES_MANAGE');
        $this->abortIfRoleIsNotOwnedBy($role, $user->id, $user->is_super_admin);
        $attributes = $this->validatePayload($request, $role);

        DB::transaction(function () use ($attributes, $role, $user): void {
            $role->update([
                'code' => strtoupper($attributes['code']),
                'name' => $attributes['name'],
                'description' => $attributes['description'] ?? null,
            ]);

            $role->permissions()->sync($this->validAssignablePermissionIds($user, $attributes['permission_ids'] ?? []));
        });

        return redirect()->route('super-admin.scoped-roles.index')->with('success', 'Le rôle a été mis à jour.');
    }

    public function destroy(Request $request, Role $role): RedirectResponse
    {
        $user = $request->user()->loadMissing(['roles.permissions', 'permissions']);
        $this->authorizeScopedManagement($user, 'SA_SCOPED_ROLES_MANAGE');
        $this->abortIfRoleIsNotOwnedBy($role, $user->id, $user->is_super_admin);

        $role->delete();

        return back()->with('success', 'Le rôle a été supprimé.');
    }

    private function validatePayload(Request $request, ?Role $role = null): array
    {
        return $request->validate([
            'code' => ['required', 'string', 'max:60', Rule::unique('roles', 'code')->ignore($role?->id)],
            'name' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string'],
            'permission_ids' => ['nullable', 'array'],
            'permission_ids.*' => ['integer', 'exists:permissions,id'],
        ]);
    }

    private function abortIfRoleIsNotOwnedBy(Role $role, int $userId, bool $userIsSuperAdmin = false): void
    {
        abort_if(
            $role->organization_id !== null
                || $role->created_by === null
                || (! $userIsSuperAdmin && (int) $role->created_by !== $userId),
            404
        );
    }
}
