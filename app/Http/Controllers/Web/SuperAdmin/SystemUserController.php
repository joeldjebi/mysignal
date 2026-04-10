<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Support\Audit\ActivityLogger;
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
            ->with(['roles', 'roles.permissions'])
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
            'visibleActivityUsers' => User::query()->whereNull('organization_id')->where('is_super_admin', false)->orderBy('name')->get(['id', 'name', 'email']),
        ]);
    }

    public function store(Request $request, ActivityLogger $activityLogger): RedirectResponse
    {
        $attributes = $this->validateRequest($request);
        $createdUser = null;

        DB::transaction(function () use ($attributes, $request, &$createdUser): void {
            $createdUser = User::query()->create([
                'organization_id' => null,
                'name' => $attributes['name'],
                'email' => $attributes['email'],
                'phone' => $attributes['phone'] ?? null,
                'password' => Hash::make($attributes['password']),
                'is_super_admin' => false,
                'status' => 'active',
                'created_by' => $request->user()->id,
            ]);

            $createdUser->roles()->sync($attributes['role_ids'] ?? []);
            $createdUser->permissions()->sync([]);
            $createdUser->activityLogVisibleUsers()->sync($attributes['activity_visible_user_ids'] ?? []);
        });

        if ($createdUser instanceof User) {
            $activityLogger->log(
                'system_user.created',
                'Creation d un utilisateur interne.',
                $createdUser,
                [
                    'role_ids' => $attributes['role_ids'] ?? [],
                    'activity_visible_user_ids' => $attributes['activity_visible_user_ids'] ?? [],
                ],
                $request,
                $request->user(),
            );
        }

        return redirect()->route('super-admin.system-users.index')
            ->with('success', 'L utilisateur interne a ete cree.');
    }

    public function edit(User $systemUser): View
    {
        $this->abortIfNotManageable($systemUser);

        return view('super-admin.system-users.edit', [
            'systemUser' => $systemUser->load(['roles.permissions', 'activityLogVisibleUsers']),
            'roles' => Role::query()->whereNull('organization_id')->where('status', 'active')->orderBy('name')->get(),
            'visibleActivityUsers' => User::query()
                ->whereNull('organization_id')
                ->where('is_super_admin', false)
                ->whereKeyNot($systemUser->id)
                ->orderBy('name')
                ->get(['id', 'name', 'email']),
        ]);
    }

    public function show(User $systemUser): View
    {
        $this->abortIfNotManageable($systemUser);

        return view('super-admin.system-users.show', [
            'systemUser' => $systemUser->load(['roles.permissions', 'creator', 'activityLogVisibleUsers']),
        ]);
    }

    public function update(Request $request, User $systemUser, ActivityLogger $activityLogger): RedirectResponse
    {
        $this->abortIfNotManageable($systemUser);
        $attributes = $this->validateRequest($request, $systemUser);
        $before = [
            'name' => $systemUser->name,
            'email' => $systemUser->email,
            'phone' => $systemUser->phone,
            'role_ids' => $systemUser->roles()->pluck('roles.id')->all(),
            'activity_visible_user_ids' => $systemUser->activityLogVisibleUsers()->pluck('users.id')->all(),
        ];

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
            $systemUser->permissions()->sync([]);
            $systemUser->activityLogVisibleUsers()->sync($attributes['activity_visible_user_ids'] ?? []);
        });

        $activityLogger->log(
            'system_user.updated',
            'Mise a jour d un utilisateur interne.',
            $systemUser->fresh(['roles.permissions']),
            [
                'before' => $before,
                'after' => [
                    'name' => $systemUser->name,
                    'email' => $systemUser->email,
                    'phone' => $systemUser->phone,
                    'role_ids' => $systemUser->roles()->pluck('roles.id')->all(),
                    'activity_visible_user_ids' => $systemUser->activityLogVisibleUsers()->pluck('users.id')->all(),
                ],
            ],
            $request,
            $request->user(),
        );

        return redirect()->route('super-admin.system-users.index')
            ->with('success', 'L utilisateur interne a ete mis a jour.');
    }

    public function destroy(Request $request, User $systemUser, ActivityLogger $activityLogger): RedirectResponse
    {
        $this->abortIfNotManageable($systemUser);
        $activityLogger->log(
            'system_user.deleted',
            'Suppression d un utilisateur interne.',
            $systemUser,
            [
                'email' => $systemUser->email,
            ],
            $request,
            $request->user(),
        );
        $systemUser->delete();

        return redirect()->route('super-admin.system-users.index')
            ->with('success', 'L utilisateur interne a ete supprime.');
    }

    public function toggleStatus(Request $request, User $systemUser, ActivityLogger $activityLogger): RedirectResponse
    {
        $this->abortIfNotManageable($systemUser);
        $previousStatus = $systemUser->status;

        $systemUser->update([
            'status' => $systemUser->status === 'active' ? 'inactive' : 'active',
        ]);

        $activityLogger->log(
            'system_user.status_toggled',
            'Changement de statut d un utilisateur interne.',
            $systemUser,
            [
                'before' => $previousStatus,
                'after' => $systemUser->status,
            ],
            $request,
            $request->user(),
        );

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
            'activity_visible_user_ids' => ['nullable', 'array'],
            'activity_visible_user_ids.*' => ['integer', 'exists:users,id'],
        ]);
    }

    private function abortIfNotManageable(User $user): void
    {
        abort_if($user->is_super_admin || $user->organization_id !== null, 404);
    }
}
