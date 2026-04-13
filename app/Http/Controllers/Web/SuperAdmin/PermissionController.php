<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Support\Audit\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PermissionController extends Controller
{
    public function index(): View
    {
        $query = Permission::query();

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

        return view('super-admin.permissions.index', [
            'permissions' => $query->latest()->paginate(12)->withQueryString(),
        ]);
    }

    public function store(Request $request, ActivityLogger $activityLogger): RedirectResponse
    {
        $attributes = $request->validate([
            'code' => ['required', 'string', 'max:60', 'unique:permissions,code'],
            'name' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string'],
        ]);

        $permission = Permission::query()->create([
            'code' => strtoupper($attributes['code']),
            'name' => $attributes['name'],
            'description' => $attributes['description'] ?? null,
            'status' => 'active',
        ]);

        $activityLogger->log(
            'permission.created',
            'Creation d une permission.',
            $permission,
            [
                'code' => $permission->code,
                'name' => $permission->name,
                'status' => $permission->status,
            ],
            $request
        );

        return redirect()->route('super-admin.permissions.index')
            ->with('success', 'La permission a ete creee.');
    }

    public function edit(Permission $permission): View
    {
        return view('super-admin.permissions.edit', [
            'permission' => $permission,
        ]);
    }

    public function update(Request $request, Permission $permission, ActivityLogger $activityLogger): RedirectResponse
    {
        $attributes = $request->validate([
            'code' => ['required', 'string', 'max:60', 'unique:permissions,code,'.$permission->id],
            'name' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string'],
        ]);

        $before = $permission->only(['code', 'name', 'description', 'status']);

        $permission->update([
            'code' => strtoupper($attributes['code']),
            'name' => $attributes['name'],
            'description' => $attributes['description'] ?? null,
        ]);

        $activityLogger->log(
            'permission.updated',
            'Mise a jour d une permission.',
            $permission,
            [
                'before' => $before,
                'after' => $permission->only(['code', 'name', 'description', 'status']),
            ],
            $request
        );

        return redirect()->route('super-admin.permissions.index')
            ->with('success', 'La permission a ete mise a jour.');
    }

    public function destroy(Request $request, Permission $permission, ActivityLogger $activityLogger): RedirectResponse
    {
        $snapshot = $permission->only(['id', 'code', 'name', 'description', 'status']);

        $permission->delete();

        $activityLogger->log(
            'permission.deleted',
            'Suppression d une permission.',
            Permission::class,
            $snapshot,
            $request
        );

        return redirect()->route('super-admin.permissions.index')
            ->with('success', 'La permission a ete supprimee.');
    }

    public function toggleStatus(Request $request, Permission $permission, ActivityLogger $activityLogger): RedirectResponse
    {
        $permission->update([
            'status' => $permission->status === 'active' ? 'inactive' : 'active',
        ]);

        $activityLogger->log(
            'permission.status_toggled',
            'Changement de statut d une permission.',
            $permission,
            [
                'status' => $permission->status,
            ],
            $request
        );

        return back()->with('success', 'Le statut de la permission a ete mis a jour.');
    }
}
