<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
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

    public function store(Request $request): RedirectResponse
    {
        $attributes = $request->validate([
            'code' => ['required', 'string', 'max:60', 'unique:permissions,code'],
            'name' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string'],
        ]);

        Permission::query()->create([
            'code' => strtoupper($attributes['code']),
            'name' => $attributes['name'],
            'description' => $attributes['description'] ?? null,
            'status' => 'active',
        ]);

        return redirect()->route('super-admin.permissions.index')
            ->with('success', 'La permission a ete creee.');
    }

    public function edit(Permission $permission): View
    {
        return view('super-admin.permissions.edit', [
            'permission' => $permission,
        ]);
    }

    public function update(Request $request, Permission $permission): RedirectResponse
    {
        $attributes = $request->validate([
            'code' => ['required', 'string', 'max:60', 'unique:permissions,code,'.$permission->id],
            'name' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string'],
        ]);

        $permission->update([
            'code' => strtoupper($attributes['code']),
            'name' => $attributes['name'],
            'description' => $attributes['description'] ?? null,
        ]);

        return redirect()->route('super-admin.permissions.index')
            ->with('success', 'La permission a ete mise a jour.');
    }

    public function destroy(Permission $permission): RedirectResponse
    {
        $permission->delete();

        return redirect()->route('super-admin.permissions.index')
            ->with('success', 'La permission a ete supprimee.');
    }

    public function toggleStatus(Permission $permission): RedirectResponse
    {
        $permission->update([
            'status' => $permission->status === 'active' ? 'inactive' : 'active',
        ]);

        return back()->with('success', 'Le statut de la permission a ete mis a jour.');
    }
}
