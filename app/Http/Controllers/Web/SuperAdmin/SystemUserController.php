<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SystemUserController extends Controller
{
    public function index(): View
    {
        $query = User::query()
            ->with(['roles', 'permissions'])
            ->whereNull('organization_id')
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

        return view('super-admin.system-users.index', [
            'systemUsers' => $query->latest()->paginate(12)->withQueryString(),
            'roles' => Role::query()->whereNull('organization_id')->where('status', 'active')->orderBy('name')->get(),
            'permissions' => Permission::query()->where('status', 'active')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $attributes = $this->validateRequest($request);

        DB::transaction(function () use ($attributes, $request): void {
            $user = User::query()->create([
                'organization_id' => null,
                'name' => $attributes['name'],
                'email' => $attributes['email'],
                'phone' => $attributes['phone'] ?? null,
                'password' => Hash::make($attributes['password']),
                'is_super_admin' => false,
                'status' => 'active',
                'created_by' => $request->user()->id,
            ]);

            $user->roles()->sync($attributes['role_ids'] ?? []);
            $user->permissions()->sync($attributes['permission_ids'] ?? []);
        });

        return redirect()->route('super-admin.system-users.index')
            ->with('success', 'L utilisateur interne a ete cree.');
    }

    public function edit(User $systemUser): View
    {
        $this->abortIfNotManageable($systemUser);

        return view('super-admin.system-users.edit', [
            'systemUser' => $systemUser->load(['roles', 'permissions']),
            'roles' => Role::query()->whereNull('organization_id')->where('status', 'active')->orderBy('name')->get(),
            'permissions' => Permission::query()->where('status', 'active')->orderBy('name')->get(),
        ]);
    }

    public function show(User $systemUser): View
    {
        $this->abortIfNotManageable($systemUser);

        return view('super-admin.system-users.show', [
            'systemUser' => $systemUser->load(['roles.permissions', 'permissions', 'creator']),
        ]);
    }

    public function update(Request $request, User $systemUser): RedirectResponse
    {
        $this->abortIfNotManageable($systemUser);
        $attributes = $this->validateRequest($request, $systemUser);

        DB::transaction(function () use ($attributes, $systemUser): void {
            $payload = [
                'name' => $attributes['name'],
                'email' => $attributes['email'],
                'phone' => $attributes['phone'] ?? null,
            ];

            if (filled($attributes['password'] ?? null)) {
                $payload['password'] = Hash::make($attributes['password']);
            }

            $systemUser->update($payload);
            $systemUser->roles()->sync($attributes['role_ids'] ?? []);
            $systemUser->permissions()->sync($attributes['permission_ids'] ?? []);
        });

        return redirect()->route('super-admin.system-users.index')
            ->with('success', 'L utilisateur interne a ete mis a jour.');
    }

    public function destroy(User $systemUser): RedirectResponse
    {
        $this->abortIfNotManageable($systemUser);
        $systemUser->delete();

        return redirect()->route('super-admin.system-users.index')
            ->with('success', 'L utilisateur interne a ete supprime.');
    }

    public function toggleStatus(User $systemUser): RedirectResponse
    {
        $this->abortIfNotManageable($systemUser);

        $systemUser->update([
            'status' => $systemUser->status === 'active' ? 'inactive' : 'active',
        ]);

        return back()->with('success', 'Le statut de l utilisateur interne a ete mis a jour.');
    }

    private function validateRequest(Request $request, ?User $user = null): array
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

    private function abortIfNotManageable(User $user): void
    {
        abort_if($user->is_super_admin || $user->organization_id !== null, 404);
    }
}
