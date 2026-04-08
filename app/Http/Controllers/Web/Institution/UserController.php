<?php

namespace App\Http\Controllers\Web\Institution;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Institution\Concerns\InteractsWithInstitutionContext;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    use InteractsWithInstitutionContext;

    public function index(): View
    {
        $context = $this->institutionContext();
        $organization = $context['organization'];
        abort_if($organization === null, 403);
        $authorization = $this->institutionAuthorizationFlags();

        $roles = $this->delegableInstitutionRoles($organization->id, $context['feature_codes']);
        $permissions = $this->delegableInstitutionPermissions($context['feature_codes']);
        $query = User::query()
            ->with(['roles', 'permissions'])
            ->where('organization_id', $organization->id)
            ->where('is_super_admin', false);

        if (filled(request('search'))) {
            $search = trim((string) request('search'));
            $query->where(function ($builder) use ($search): void {
                $builder->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%')
                    ->orWhere('phone', 'like', '%'.$search.'%');
            });
        }

        if (filled(request('status'))) {
            $query->where('status', request('status'));
        }

        if (filled(request('role_id'))) {
            $roleId = (int) request('role_id');
            $query->whereHas('roles', fn ($builder) => $builder->where('roles.id', $roleId));
        }

        return view('institution/users/index', [
            'organization' => $organization,
            'features' => $context['feature_codes'],
            'activeNav' => 'users',
            'users' => $query->latest()->paginate(12)->withQueryString(),
            'roles' => $roles,
            'permissions' => $permissions,
            'groupedPermissions' => $this->groupedInstitutionPermissions($context['feature_codes']),
            'authorization' => $authorization,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $context = $this->institutionContext();
        $organization = $context['organization'];
        abort_if($organization === null, 403);
        $authorization = $this->institutionAuthorizationFlags($request->user());

        $attributes = $this->validateRequest($request, $organization->id);
        $allowedRoleIds = $authorization['canManageInstitutionRoles']
            ? $this->delegableInstitutionRoles($organization->id, $context['feature_codes'], $request->user())->pluck('id')->all()
            : [];
        $allowedPermissionIds = $authorization['canManageInstitutionPermissions']
            ? $this->delegableInstitutionPermissions($context['feature_codes'], $request->user())->pluck('id')->all()
            : [];

        $this->assertAllowedSelections($attributes, $allowedRoleIds, $allowedPermissionIds);

        DB::transaction(function () use ($attributes, $organization, $request, $authorization): void {
            $user = User::query()->create([
                'organization_id' => $organization->id,
                'name' => $attributes['name'],
                'email' => $attributes['email'],
                'phone' => $attributes['phone'] ?? null,
                'password' => Hash::make($attributes['password']),
                'is_super_admin' => false,
                'status' => 'active',
                'created_by' => $request->user()->id,
            ]);

            if ($authorization['canManageInstitutionRoles']) {
                $user->roles()->sync($attributes['role_ids'] ?? []);
            }

            if ($authorization['canManageInstitutionPermissions']) {
                $user->permissions()->sync($attributes['permission_ids'] ?? []);
            }
        });

        return redirect()->route('institution.users.index')
            ->with('success', 'Le collaborateur a ete cree.');
    }

    public function edit(User $user): View
    {
        $context = $this->institutionContext();
        $organization = $context['organization'];
        abort_if($organization === null || $user->organization_id !== $organization->id || $user->is_super_admin, 404);
        $authorization = $this->institutionAuthorizationFlags();
        abort_unless($this->canManageInstitutionUserRecord($user, $context['feature_codes']), 403);

        return view('institution/users/edit', [
            'organization' => $organization,
            'features' => $context['feature_codes'],
            'activeNav' => 'users',
            'userAccount' => $user->load(['roles', 'permissions']),
            'roles' => $this->delegableInstitutionRoles($organization->id, $context['feature_codes']),
            'permissions' => $this->delegableInstitutionPermissions($context['feature_codes']),
            'groupedPermissions' => $this->delegableInstitutionPermissions($context['feature_codes'])->groupBy(function ($permission): string {
                return match (true) {
                    str_starts_with($permission->code, 'INSTITUTION_DASHBOARD_') => 'Dashboard institutionnel',
                    str_starts_with($permission->code, 'INSTITUTION_') => 'Acces institutionnels',
                    str_starts_with($permission->code, 'PUBLIC_') => 'Modules publics',
                    default => 'Autres permissions',
                };
            })->sortKeys(),
            'authorization' => $authorization,
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $context = $this->institutionContext();
        $organization = $context['organization'];
        abort_if($organization === null || $user->organization_id !== $organization->id || $user->is_super_admin, 404);
        $authorization = $this->institutionAuthorizationFlags($request->user());
        abort_unless($this->canManageInstitutionUserRecord($user, $context['feature_codes'], $request->user()), 403);

        $attributes = $this->validateRequest($request, $organization->id, $user);
        $allowedRoleIds = $authorization['canManageInstitutionRoles']
            ? $this->delegableInstitutionRoles($organization->id, $context['feature_codes'], $request->user())->pluck('id')->all()
            : [];
        $allowedPermissionIds = $authorization['canManageInstitutionPermissions']
            ? $this->delegableInstitutionPermissions($context['feature_codes'], $request->user())->pluck('id')->all()
            : [];

        $this->assertAllowedSelections($attributes, $allowedRoleIds, $allowedPermissionIds);

        DB::transaction(function () use ($attributes, $user, $authorization): void {
            $payload = [
                'name' => $attributes['name'],
                'email' => $attributes['email'],
                'phone' => $attributes['phone'] ?? null,
                'status' => $attributes['status'],
            ];

            if (filled($attributes['password'] ?? null)) {
                $payload['password'] = Hash::make($attributes['password']);
            }

            $user->update($payload);

            if ($authorization['canManageInstitutionRoles']) {
                $user->roles()->sync($attributes['role_ids'] ?? []);
            }

            if ($authorization['canManageInstitutionPermissions']) {
                $user->permissions()->sync($attributes['permission_ids'] ?? []);
            }
        });

        return redirect()->route('institution.users.index')
            ->with('success', 'Le collaborateur a ete mis a jour.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $context = $this->institutionContext();
        $organization = $context['organization'];
        abort_if($organization === null || $user->organization_id !== $organization->id || $user->is_super_admin, 404);
        abort_unless($this->canManageInstitutionUserRecord($user, $context['feature_codes']), 403);

        if ($user->id === auth()->id()) {
            return back()->withErrors([
                'email' => 'Vous ne pouvez pas supprimer votre propre compte.',
            ]);
        }

        $user->delete();

        return redirect()->route('institution.users.index')
            ->with('success', 'Le collaborateur a ete supprime.');
    }

    public function toggleStatus(User $user): RedirectResponse
    {
        $context = $this->institutionContext();
        $organization = $context['organization'];
        abort_if($organization === null || $user->organization_id !== $organization->id || $user->is_super_admin, 404);
        abort_unless($this->canManageInstitutionUserRecord($user, $context['feature_codes']), 403);

        if ($user->id === auth()->id()) {
            return back()->withErrors([
                'email' => 'Vous ne pouvez pas modifier le statut de votre propre compte.',
            ]);
        }

        $user->update([
            'status' => $user->status === 'active' ? 'inactive' : 'active',
        ]);

        return back()->with('success', 'Le statut du collaborateur a ete mis a jour.');
    }

    private function validateRequest(Request $request, int $organizationId, ?User $user = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user?->id),
            ],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:8'],
            'status' => [$user ? 'required' : 'nullable', Rule::in(['active', 'inactive'])],
            'role_ids' => ['nullable', 'array'],
            'role_ids.*' => ['integer', 'exists:roles,id'],
            'permission_ids' => ['nullable', 'array'],
            'permission_ids.*' => ['integer', 'exists:permissions,id'],
        ]);
    }

    private function assertAllowedSelections(array $attributes, array $allowedRoleIds, array $allowedPermissionIds): void
    {
        $selectedRoleIds = $attributes['role_ids'] ?? [];
        $selectedPermissionIds = $attributes['permission_ids'] ?? [];

        abort_if(count(array_diff($selectedRoleIds, $allowedRoleIds)) > 0, 403);
        abort_if(count(array_diff($selectedPermissionIds, $allowedPermissionIds)) > 0, 403);
    }
}
