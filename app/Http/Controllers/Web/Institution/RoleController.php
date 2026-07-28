<?php

namespace App\Http\Controllers\Web\Institution;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Institution\Concerns\InteractsWithInstitutionContext;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RoleController extends Controller
{
    use InteractsWithInstitutionContext;

    public function index(): View
    {
        $context = $this->institutionContext();
        $organization = $context['organization'];
        abort_if($organization === null, 403);
        $authorization = $this->institutionAuthorizationFlags();

        $permissions = $this->delegableInstitutionPermissions($context['feature_codes']);
        $query = $this->institutionRolesQuery($organization->id);

        if (filled(request('search'))) {
            $search = trim((string) request('search'));
            $query->where(function ($builder) use ($search): void {
                $builder->where('code', 'like', '%'.$search.'%')
                    ->orWhere('name', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%');
            });
        }

        if (filled(request('status'))) {
            $query->where('status', request('status'));
        }

        return view('institution/roles/index', [
            'organization' => $organization,
            'features' => $context['feature_codes'],
            'activeNav' => 'roles',
            'roles' => $query->latest()->paginate(12)->withQueryString(),
            'permissions' => $permissions,
            'groupedPermissions' => $permissions->groupBy(function ($permission): string {
                return match (true) {
                    str_starts_with($permission->code, 'INSTITUTION_DASHBOARD_') => 'Tableau de bord institutionnel',
                    str_starts_with($permission->code, 'INSTITUTION_') => 'Accès institutionnels',
                    str_starts_with($permission->code, 'PUBLIC_') => 'Modules publics',
                    default => 'Autres permissions',
                };
            })->sortKeys(),
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
        $allowedPermissionIds = $authorization['canManageInstitutionPermissions']
            ? $this->delegableInstitutionPermissions($context['feature_codes'], $request->user())->pluck('id')->all()
            : [];
        abort_if(count(array_diff($attributes['permission_ids'] ?? [], $allowedPermissionIds)) > 0, 403);
        $roleCode = $this->uniqueRoleCode($organization->code, $attributes['code'] ?? $attributes['name']);

        if (Role::query()->where('code', $roleCode)->exists()) {
            return back()
                ->withErrors(['name' => 'Ce nom de rôle existe déjà pour cette institution.'])
                ->withInput();
        }

        DB::transaction(function () use ($attributes, $organization, $authorization, $roleCode): void {
            $role = Role::query()->create([
                'organization_id' => $organization->id,
                'code' => $roleCode,
                'name' => $attributes['name'],
                'description' => $attributes['description'] ?? null,
                'status' => 'active',
            ]);

            if ($authorization['canManageInstitutionPermissions']) {
                $role->permissions()->sync($attributes['permission_ids'] ?? []);
            }
        });

        return redirect()->route('institution.roles.index')
            ->with('success', 'Le rôle a été créé.');
    }

    public function edit(Role $role): View
    {
        $context = $this->institutionContext();
        $organization = $context['organization'];
        abort_if($organization === null || $role->organization_id !== $organization->id, 404);
        $authorization = $this->institutionAuthorizationFlags();
        abort_unless($this->canManageInstitutionRoleRecord($role, $context['feature_codes']), 403);

        $role->load('permissions');
        $permissions = $this->delegableInstitutionPermissions($context['feature_codes']);

        return view('institution/roles/edit', [
            'organization' => $organization,
            'features' => $context['feature_codes'],
            'activeNav' => 'roles',
            'role' => $role,
            'permissions' => $permissions,
            'groupedPermissions' => $permissions->groupBy(function ($permission): string {
                return match (true) {
                    str_starts_with($permission->code, 'INSTITUTION_DASHBOARD_') => 'Tableau de bord institutionnel',
                    str_starts_with($permission->code, 'INSTITUTION_') => 'Accès institutionnels',
                    str_starts_with($permission->code, 'PUBLIC_') => 'Modules publics',
                    default => 'Autres permissions',
                };
            })->sortKeys(),
            'displayCode' => Str::after($role->code, strtoupper((string) $organization->code).'_'),
            'authorization' => $authorization,
            'assignedPermissionIds' => $role->permissions->pluck('id')->all(),
        ]);
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $context = $this->institutionContext();
        $organization = $context['organization'];
        abort_if($organization === null || $role->organization_id !== $organization->id, 404);
        $authorization = $this->institutionAuthorizationFlags($request->user());
        abort_unless($this->canManageInstitutionRoleRecord($role, $context['feature_codes'], $request->user()), 403);

        $attributes = $this->validateRequest($request, $organization->id, $role);
        $allowedPermissionIds = $authorization['canManageInstitutionPermissions']
            ? $this->delegableInstitutionPermissions($context['feature_codes'], $request->user())->pluck('id')->all()
            : [];
        abort_if(count(array_diff($attributes['permission_ids'] ?? [], $allowedPermissionIds)) > 0, 403);
        $roleCode = filled($attributes['code'] ?? null)
            ? $this->buildRoleCode($organization->code, $attributes['code'])
            : $role->code;

        if (Role::query()->where('code', $roleCode)->whereKeyNot($role->id)->exists()) {
            return back()
                ->withErrors(['name' => 'Ce nom de rôle existe déjà pour cette institution.'])
                ->withInput();
        }

        DB::transaction(function () use ($attributes, $role, $organization, $authorization): void {
            $role->update([
                'code' => $this->buildRoleCode($organization->code, $attributes['code']),
                'name' => $attributes['name'],
                'description' => $attributes['description'] ?? null,
                'status' => $attributes['status'],
            ]);

            if ($authorization['canManageInstitutionPermissions']) {
                $role->permissions()->sync($attributes['permission_ids'] ?? []);
            }
        });

        return redirect()->route('institution.roles.index')
            ->with('success', 'Le rôle a été mis à jour.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        $context = $this->institutionContext();
        $organization = $context['organization'];
        abort_if($organization === null || $role->organization_id !== $organization->id, 404);
        abort_unless($this->canManageInstitutionRoleRecord($role, $context['feature_codes']), 403);

        $role->delete();

        return redirect()->route('institution.roles.index')
            ->with('success', 'Le rôle a été supprimé.');
    }

    public function toggleStatus(Role $role): RedirectResponse
    {
        $context = $this->institutionContext();
        $organization = $context['organization'];
        abort_if($organization === null || $role->organization_id !== $organization->id, 404);
        abort_unless($this->canManageInstitutionRoleRecord($role, $context['feature_codes']), 403);

        $role->update([
            'status' => $role->status === 'active' ? 'inactive' : 'active',
        ]);

        return back()->with('success', 'Le statut du rôle a été mis à jour.');
    }

    private function validateRequest(Request $request, int $organizationId, ?Role $role = null): array
    {
        return $request->validate([
            'code' => ['nullable', 'string', 'max:60'],
            'name' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string'],
            'status' => [$role ? 'required' : 'nullable', 'in:active,inactive'],
            'permission_ids' => ['nullable', 'array'],
            'permission_ids.*' => ['integer', 'exists:permissions,id'],
        ]);
    }

    private function buildRoleCode(?string $organizationCode, string $code): string
    {
        $prefix = strtoupper((string) $organizationCode);
        $normalized = strtoupper(Str::slug($code, '_'));

        return trim($prefix.'_'.$normalized, '_');
    }

    private function uniqueRoleCode(?string $organizationCode, string $name): string
    {
        $base = $this->buildRoleCode($organizationCode, $name);
        $candidate = Str::limit($base, 60, '');
        $index = 1;

        while (Role::query()->where('code', $candidate)->exists()) {
            $suffix = '_'.str_pad((string) $index, 2, '0', STR_PAD_LEFT);
            $candidate = Str::limit($base, 60 - strlen($suffix), '').$suffix;
            $index++;
        }

        return $candidate;
    }
}
