<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
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

    public function store(Request $request): RedirectResponse
    {
        $attributes = $this->validateRequest($request);

        DB::transaction(function () use ($attributes): void {
            $role = Role::query()->create([
                'organization_id' => null,
                'code' => strtoupper($attributes['code']),
                'name' => $attributes['name'],
                'description' => $attributes['description'] ?? null,
                'status' => 'active',
            ]);

            $role->permissions()->sync($attributes['permission_ids'] ?? []);
        });

        return redirect()->route('super-admin.roles.index')
            ->with('success', 'Le role a ete cree.');
    }

    public function edit(Role $role): View
    {
        abort_if($role->organization_id !== null, 404);

        return view('super-admin.roles.edit', [
            'role' => $role->load('permissions'),
            'permissions' => Permission::query()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        abort_if($role->organization_id !== null, 404);

        $attributes = $this->validateRequest($request, $role->id);

        DB::transaction(function () use ($attributes, $role): void {
            $role->update([
                'code' => strtoupper($attributes['code']),
                'name' => $attributes['name'],
                'description' => $attributes['description'] ?? null,
            ]);

            $role->permissions()->sync($attributes['permission_ids'] ?? []);
        });

        return redirect()->route('super-admin.roles.index')
            ->with('success', 'Le role a ete mis a jour.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        abort_if($role->organization_id !== null, 404);

        $role->delete();

        return redirect()->route('super-admin.roles.index')
            ->with('success', 'Le role a ete supprime.');
    }

    public function toggleStatus(Role $role): RedirectResponse
    {
        abort_if($role->organization_id !== null, 404);

        $role->update([
            'status' => $role->status === 'active' ? 'inactive' : 'active',
        ]);

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
