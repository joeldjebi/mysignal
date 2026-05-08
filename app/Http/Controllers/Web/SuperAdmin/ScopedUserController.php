<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\SuperAdmin\Concerns\InteractsWithScopedSaAdminManagement;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ScopedUserController extends Controller
{
    use InteractsWithScopedSaAdminManagement;

    public function index(Request $request): View
    {
        $actor = $request->user()->loadMissing(['roles.permissions', 'permissions']);
        $this->authorizeScopedManagement($actor, 'SA_SCOPED_USERS_MANAGE');

        return view('super-admin.scoped-users.index', [
            'users' => $this->scopedUserQuery($actor)
                ->with(['roles.permissions', 'permissions'])
                ->latest()
                ->paginate(12),
            'roles' => $this->scopedRoleQuery($actor)->where('status', 'active')->orderBy('name')->get(),
            'permissions' => $this->assignablePermissions($actor),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $actor = $request->user()->loadMissing(['roles.permissions', 'permissions']);
        $this->authorizeScopedManagement($actor, 'SA_SCOPED_USERS_MANAGE');
        $attributes = $this->validatePayload($request);

        DB::transaction(function () use ($attributes, $actor): void {
            $user = User::query()->create([
                'organization_id' => null,
                'name' => $attributes['name'],
                'email' => $attributes['email'],
                'phone' => $attributes['phone'] ?? null,
                'password' => Hash::make($attributes['password']),
                'is_super_admin' => false,
                'status' => 'active',
                'created_by' => $actor->id,
            ]);

            $user->roles()->sync($this->validOwnedRoleIds($actor, $attributes['role_ids'] ?? []));
            $user->permissions()->sync($this->validAssignablePermissionIds($actor, $attributes['permission_ids'] ?? []));
        });

        return redirect()->route('super-admin.scoped-users.index')->with('success', 'L utilisateur a ete cree.');
    }

    public function edit(Request $request, User $scopedUser): View
    {
        $actor = $request->user()->loadMissing(['roles.permissions', 'permissions']);
        $this->authorizeScopedManagement($actor, 'SA_SCOPED_USERS_MANAGE');
        $this->abortIfUserIsNotOwnedBy($scopedUser, $actor->id, $actor->is_super_admin);

        return view('super-admin.scoped-users.edit', [
            'managedUser' => $scopedUser->load(['roles', 'permissions']),
            'roles' => $this->scopedRoleQuery($actor)->where('status', 'active')->orderBy('name')->get(),
            'permissions' => $this->assignablePermissions($actor),
            'assignedRoleIds' => $scopedUser->roles->pluck('id')->all(),
            'assignedPermissionIds' => $scopedUser->permissions->pluck('id')->all(),
        ]);
    }

    public function update(Request $request, User $scopedUser): RedirectResponse
    {
        $actor = $request->user()->loadMissing(['roles.permissions', 'permissions']);
        $this->authorizeScopedManagement($actor, 'SA_SCOPED_USERS_MANAGE');
        $this->abortIfUserIsNotOwnedBy($scopedUser, $actor->id, $actor->is_super_admin);
        $attributes = $this->validatePayload($request, $scopedUser);

        DB::transaction(function () use ($attributes, $scopedUser, $actor): void {
            $payload = [
                'name' => $attributes['name'],
                'email' => $attributes['email'],
                'phone' => $attributes['phone'] ?? null,
            ];

            if (filled($attributes['password'] ?? null)) {
                $payload['password'] = Hash::make($attributes['password']);
            }

            $scopedUser->update($payload);
            $scopedUser->roles()->sync($this->validOwnedRoleIds($actor, $attributes['role_ids'] ?? []));
            $scopedUser->permissions()->sync($this->validAssignablePermissionIds($actor, $attributes['permission_ids'] ?? []));
        });

        return redirect()->route('super-admin.scoped-users.index')->with('success', 'L utilisateur a ete mis a jour.');
    }

    public function destroy(Request $request, User $scopedUser): RedirectResponse
    {
        $actor = $request->user()->loadMissing(['roles.permissions', 'permissions']);
        $this->authorizeScopedManagement($actor, 'SA_SCOPED_USERS_MANAGE');
        $this->abortIfUserIsNotOwnedBy($scopedUser, $actor->id, $actor->is_super_admin);

        $scopedUser->delete();

        return back()->with('success', 'L utilisateur a ete supprime.');
    }

    private function validatePayload(Request $request, ?User $user = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user?->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:8'],
            'role_ids' => ['nullable', 'array'],
            'role_ids.*' => ['integer', 'exists:roles,id'],
            'permission_ids' => ['nullable', 'array'],
            'permission_ids.*' => ['integer', 'exists:permissions,id'],
        ]);
    }

    private function validOwnedRoleIds(User $actor, array $roleIds): array
    {
        return $this->scopedRoleQuery($actor)
            ->whereIn('id', array_map('intval', $roleIds))
            ->pluck('id')
            ->all();
    }

    private function scopedUserQuery(User $actor): Builder
    {
        $query = User::query()
            ->whereNull('organization_id')
            ->where('is_super_admin', false)
            ->whereNotNull('created_by');

        if (! $actor->is_super_admin) {
            $query->where('created_by', $actor->id);
        }

        return $query;
    }

    private function abortIfUserIsNotOwnedBy(User $user, int $actorId, bool $actorIsSuperAdmin = false): void
    {
        abort_if(
            $user->is_super_admin
                || $user->organization_id !== null
                || $user->created_by === null
                || (! $actorIsSuperAdmin && (int) $user->created_by !== $actorId),
            404
        );
    }
}
