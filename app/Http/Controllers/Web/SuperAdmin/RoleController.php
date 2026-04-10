<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Support\Audit\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function index(): View
    {
        $query = Role::query()->with('permissions')->whereNull('organization_id');

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

        return view('super-admin.roles.index', [
            'roles' => $query->latest()->paginate(12)->withQueryString(),
            'permissions' => Permission::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, ActivityLogger $activityLogger): RedirectResponse
    {
        $attributes = $this->validateRequest($request);
        $role = null;

        DB::transaction(function () use ($attributes, &$role): void {
            $role = Role::query()->create([
                'organization_id' => null,
                'code' => strtoupper($attributes['code']),
                'name' => $attributes['name'],
                'description' => $attributes['description'] ?? null,
                'status' => 'active',
            ]);

            $role->permissions()->sync($attributes['permission_ids'] ?? []);
        });

        $activityLogger->log(
            'role.created',
            'Creation d un role.',
            $role,
            [
                'code' => $role->code,
                'name' => $role->name,
                'status' => $role->status,
                'permission_ids' => $role->permissions()->pluck('permissions.id')->all(),
            ],
            $request
        );

        return redirect()->route('super-admin.roles.index')
            ->with('success', 'Le role a ete cree.');
    }

    public function edit(Role $role): View
    {
        abort_if($role->organization_id !== null, 404);

        $role->load('permissions');

        return view('super-admin.roles.edit', [
            'role' => $role,
            'permissions' => Permission::query()->orderBy('name')->get(),
            'assignedPermissionIds' => $role->permissions->pluck('id')->all(),
        ]);
    }

    public function show(Role $role): View
    {
        abort_if($role->organization_id !== null, 404);

        $role->load('permissions');

        return view('super-admin.roles.show', [
            'role' => $role,
            'groupedPermissions' => $role->permissions
                ->sortBy('name')
                ->groupBy(function ($permission) {
                    $segments = explode('_', (string) $permission->code);

                    if (($segments[0] ?? null) === 'SA') {
                        return match ($segments[1] ?? null) {
                            'ACCESS' => 'Acces au portail',
                            'DASHBOARD' => 'Dashboard',
                            'PUBLIC' => 'Usagers publics et signalements',
                            'PAYMENTS' => 'Paiements',
                            'ACTIVITY' => 'Journaux d activite',
                            'INSTITUTION' => 'Admins institutionnels',
                            'SYSTEM' => 'Utilisateurs internes',
                            'ROLES' => 'Roles',
                            'PERMISSIONS' => 'Permissions',
                            'REPARATION' => 'Dossiers contentieux',
                            'APPLICATIONS' => 'Applications',
                            'FEATURES' => 'Fonctionnalites',
                            'SLA' => 'SLA',
                            'ORGANIZATIONS' => 'Organisations',
                            'ORGANIZATION' => 'Types d organisation',
                            'PRICING' => 'Tarification',
                            'COUNTRIES', 'CITIES', 'COMMUNES' => 'Geographie',
                            'BUSINESS' => 'Secteurs',
                            default => 'Autres permissions SA',
                        };
                    }

                    return 'Autres permissions';
                })
                ->sortKeys(),
        ]);
    }

    public function update(Request $request, Role $role, ActivityLogger $activityLogger): RedirectResponse
    {
        abort_if($role->organization_id !== null, 404);

        $attributes = $this->validateRequest($request, $role->id);
        $before = $role->load('permissions');

        DB::transaction(function () use ($attributes, $role): void {
            $role->update([
                'code' => strtoupper($attributes['code']),
                'name' => $attributes['name'],
                'description' => $attributes['description'] ?? null,
            ]);

            $role->permissions()->sync($attributes['permission_ids'] ?? []);
        });

        $role->load('permissions');

        $activityLogger->log(
            'role.updated',
            'Mise a jour d un role.',
            $role,
            [
                'before' => [
                    'code' => $before->code,
                    'name' => $before->name,
                    'description' => $before->description,
                    'status' => $before->status,
                    'permission_ids' => $before->permissions->pluck('id')->all(),
                ],
                'after' => [
                    'code' => $role->code,
                    'name' => $role->name,
                    'description' => $role->description,
                    'status' => $role->status,
                    'permission_ids' => $role->permissions->pluck('id')->all(),
                ],
            ],
            $request
        );

        return redirect()->route('super-admin.roles.index')
            ->with('success', 'Le role a ete mis a jour.');
    }

    public function destroy(Request $request, Role $role, ActivityLogger $activityLogger): RedirectResponse
    {
        abort_if($role->organization_id !== null, 404);

        $snapshot = $role->load('permissions');
        $role->delete();

        $activityLogger->log(
            'role.deleted',
            'Suppression d un role.',
            Role::class,
            [
                'id' => $snapshot->id,
                'code' => $snapshot->code,
                'name' => $snapshot->name,
                'status' => $snapshot->status,
                'permission_ids' => $snapshot->permissions->pluck('id')->all(),
            ],
            $request
        );

        return redirect()->route('super-admin.roles.index')
            ->with('success', 'Le role a ete supprime.');
    }

    public function toggleStatus(Request $request, Role $role, ActivityLogger $activityLogger): RedirectResponse
    {
        abort_if($role->organization_id !== null, 404);

        $role->update([
            'status' => $role->status === 'active' ? 'inactive' : 'active',
        ]);

        $activityLogger->log(
            'role.status_toggled',
            'Changement de statut d un role.',
            $role,
            [
                'status' => $role->status,
            ],
            $request
        );

        return back()->with('success', 'Le statut du role a ete mis a jour.');
    }

    private function validateRequest(Request $request, ?int $roleId = null): array
    {
        return $request->validate([
            'code' => ['required', 'string', 'max:60', 'unique:roles,code,'.($roleId ?? 'NULL').',id'],
            'name' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string'],
            'permission_ids' => ['nullable', 'array'],
            'permission_ids.*' => ['integer', 'exists:permissions,id'],
        ]);
    }
}
