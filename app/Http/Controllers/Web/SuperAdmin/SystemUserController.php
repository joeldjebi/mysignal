<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\UserAccess;
use App\Models\UserType;
use App\Support\Audit\ActivityLogger;
use Illuminate\Database\Eloquent\Builder;
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
            ->where('is_super_admin', false)
            ->whereIn('user_type_id', array_filter([
                UserType::idFor(UserType::SA_USER),
                UserType::idFor(UserType::PARTNER_MANAGER),
                UserType::idFor(UserType::PARTNER_SCAN_AGENT),
            ]));

        $this->excludeScopedSaUsers($query);

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

        $systemUsers = $query->latest()->paginate(12)->withQueryString();
        $systemUsers->getCollection()->each(function (User $systemUser): void {
            $portal = $this->loginPortalFor($systemUser);

            $systemUser->setAttribute('login_portal_url', $portal['url']);
            $systemUser->setAttribute('login_portal_label', $portal['label']);
        });

        return view('super-admin.system-users.index', [
            'systemUsers' => $systemUsers,
            'roles' => $this->systemAssignableRoles(),
            'partnerOrganizations' => $this->partnerOrganizations(),
            'visibleActivityUsers' => User::query()
                ->whereNull('organization_id')
                ->where('is_super_admin', false)
                ->whereIn('user_type_id', array_filter([
                    UserType::idFor(UserType::SA_USER),
                    UserType::idFor(UserType::PARTNER_MANAGER),
                    UserType::idFor(UserType::PARTNER_SCAN_AGENT),
                ]))
                ->tap(fn (Builder $query) => $this->excludeScopedSaUsers($query))
                ->orderBy('name')
                ->get(['id', 'name', 'email']),
        ]);
    }

    public function store(Request $request, ActivityLogger $activityLogger): RedirectResponse
    {
        $attributes = $this->validateRequest($request);
        $createdUser = null;

        DB::transaction(function () use ($attributes, $request, &$createdUser): void {
            $createdUser = User::query()->create([
                'user_type_id' => $this->userTypeIdForPayload($attributes),
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
            $this->syncPartnerAccess($createdUser, $attributes, $request->user()->id);
            $createdUser->activityLogVisibleUsers()->sync($attributes['activity_visible_user_ids'] ?? []);
        });

        if ($createdUser instanceof User) {
            $activityLogger->log(
                'system_user.created',
                'Création d’un utilisateur interne.',
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
            ->with('success', 'L’utilisateur interne a été créé.');
    }

    public function edit(User $systemUser): View
    {
        $this->abortIfNotManageable($systemUser);

        return view('super-admin.system-users.edit', [
            'systemUser' => $systemUser->load(['roles.permissions', 'activityLogVisibleUsers', 'accesses.organization.organizationType']),
            'roles' => $this->systemAssignableRoles(),
            'partnerOrganizations' => $this->partnerOrganizations(),
            'visibleActivityUsers' => User::query()
                ->whereNull('organization_id')
                ->where('is_super_admin', false)
                ->whereIn('user_type_id', array_filter([
                    UserType::idFor(UserType::SA_USER),
                    UserType::idFor(UserType::PARTNER_MANAGER),
                    UserType::idFor(UserType::PARTNER_SCAN_AGENT),
                ]))
                ->tap(fn (Builder $query) => $this->excludeScopedSaUsers($query))
                ->whereKeyNot($systemUser->id)
                ->orderBy('name')
                ->get(['id', 'name', 'email']),
        ]);
    }

    public function show(User $systemUser): View
    {
        $this->abortIfProfileAccessIsNotManageable($systemUser);

        return view('super-admin.system-users.show', [
            'systemUser' => $systemUser->load([
                'roles.permissions',
                'creator',
                'activityLogVisibleUsers',
                'accesses.organization.organizationType',
                'accesses.roles.permissions',
                'accesses.permissions',
            ]),
            'accessRoles' => $this->systemAssignableRoles(),
            'accessPermissions' => Permission::query()->where('status', 'active')->orderBy('name')->get(),
            'permissionProfileScopes' => Permission::PROFILE_SCOPES,
            'permissionCategories' => Permission::CATEGORIES,
            'accessOrganizations' => Organization::query()->with('organizationType')->where('status', 'active')->orderBy('name')->get(),
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

            $payload['user_type_id'] = $this->userTypeIdForPayload($attributes);

            $systemUser->update($payload);
            $systemUser->roles()->sync($attributes['role_ids'] ?? []);
            $systemUser->permissions()->sync([]);
            $this->syncPartnerAccess($systemUser, $attributes);
            $systemUser->activityLogVisibleUsers()->sync($attributes['activity_visible_user_ids'] ?? []);
        });

        $activityLogger->log(
            'system_user.updated',
            'Mise à jour d’un utilisateur interne.',
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
            ->with('success', 'L’utilisateur interne a été mis à jour.');
    }

    public function destroy(Request $request, User $systemUser, ActivityLogger $activityLogger): RedirectResponse
    {
        $this->abortIfNotManageable($systemUser);
        $activityLogger->log(
            'system_user.deleted',
            'Suppression d’un utilisateur interne.',
            $systemUser,
            [
                'email' => $systemUser->email,
            ],
            $request,
            $request->user(),
        );
        $systemUser->delete();

        return redirect()->route('super-admin.system-users.index')
            ->with('success', 'L’utilisateur interne a été supprimé.');
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
            'Changement de statut d’un utilisateur interne.',
            $systemUser,
            [
                'before' => $previousStatus,
                'after' => $systemUser->status,
            ],
            $request,
            $request->user(),
        );

        return back()->with('success', 'Le statut de l’utilisateur interne a été mis à jour.');
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
            'partner_organization_id' => [
                Rule::requiredIf(fn () => $this->selectedRolesContainPartner($request->input('role_ids', []))),
                'nullable',
                'integer',
                Rule::exists('organizations', 'id')->where(function ($query): void {
                    $query->whereIn('organization_type_id', DB::table('organization_types')
                        ->select('id')
                        ->where('code', 'PARTNER_ESTABLISHMENT'));
                }),
            ],
            'activity_visible_user_ids' => ['nullable', 'array'],
            'activity_visible_user_ids.*' => ['integer', 'exists:users,id'],
        ]);
    }

    private function abortIfNotManageable(User $user): void
    {
        abort_if(
            $user->is_super_admin
                || $user->organization_id !== null
                || (int) $user->user_type_id === (int) UserType::idFor(UserType::INSTITUTION_ADMIN)
                || $this->isScopedSaUser($user),
            404
        );
    }

    private function excludeScopedSaUsers(Builder $query): Builder
    {
        $saUserTypeId = UserType::idFor(UserType::SA_USER);

        return $query->where(function (Builder $builder) use ($saUserTypeId): void {
            $builder->where('user_type_id', '!=', $saUserTypeId)
                ->orWhereHas('roles.permissions', fn (Builder $permissionQuery) => $this->systemPortalPermissions($permissionQuery))
                ->orWhereHas('permissions', fn (Builder $permissionQuery) => $this->systemPortalPermissions($permissionQuery))
                ->orWhere(function (Builder $saUserQuery): void {
                    $saUserQuery
                        ->whereDoesntHave('roles', fn (Builder $roleQuery) => $roleQuery->whereNotNull('roles.created_by'))
                        ->whereDoesntHave('roles', fn (Builder $roleQuery) => $roleQuery->where('roles.code', 'CALLCENTER'))
                        ->whereDoesntHave('permissions');
                });
        });
    }

    private function isScopedSaUser(User $user): bool
    {
        return ! $user->is_super_admin
            && $user->organization_id === null
            && (int) $user->user_type_id === (int) UserType::idFor(UserType::SA_USER)
            && $user->created_by !== null
            && (
                $user->roles()->whereNotNull('roles.created_by')->exists()
                || $user->roles()->where('roles.code', 'CALLCENTER')->exists()
                || $user->permissions()->exists()
            )
            && ! $this->hasSystemPortalAccess($user);
    }

    private function systemPortalPermissions(Builder $query): void
    {
        $query->where(function (Builder $permissionQuery): void {
            $permissionQuery
                ->where('code', 'like', 'BO_%')
                ->orWhere('code', 'like', 'PARTNER_%')
                ->orWhereIn('profile_scope', ['backoffice', 'huissier', 'aoda', 'avocat', 'partner']);
        });
    }

    private function hasSystemPortalAccess(User $user): bool
    {
        return $user->roles()
            ->whereHas('permissions', fn (Builder $permissionQuery) => $this->systemPortalPermissions($permissionQuery))
            ->exists()
            || $user->permissions()
                ->where(fn (Builder $permissionQuery) => $this->systemPortalPermissions($permissionQuery))
                ->exists();
    }

    private function userTypeIdForPayload(array $attributes): ?int
    {
        if (! $this->selectedRolesContainPartner($attributes['role_ids'] ?? [])) {
            return UserType::idFor(UserType::SA_USER);
        }

        if ($this->selectedRolesContainPartnerScanAgent($attributes['role_ids'] ?? [])) {
            return UserType::idFor(UserType::PARTNER_SCAN_AGENT);
        }

        return UserType::idFor(UserType::PARTNER_MANAGER);
    }

    private function syncPartnerAccess(User $user, array $attributes, ?int $createdBy = null): void
    {
        if (! $this->selectedRolesContainPartner($attributes['role_ids'] ?? [])) {
            $user->accesses()->where('portal', 'partner')->update(['status' => 'inactive']);

            return;
        }

        $organizationId = (int) $attributes['partner_organization_id'];

        $access = $user->accesses()->where('portal', 'partner')->latest('id')->first();

        if ($access instanceof UserAccess) {
            $access->update([
                'organization_id' => $organizationId,
                'status' => 'active',
            ]);

            return;
        }

        UserAccess::query()->create([
            'user_id' => $user->id,
            'organization_id' => $organizationId,
            'portal' => 'partner',
            'status' => 'active',
            'created_by' => $createdBy,
        ]);
    }

    private function selectedRolesContainPartner(array $roleIds): bool
    {
        $roleIds = collect($roleIds)->filter()->map(fn ($roleId) => (int) $roleId)->all();

        if ($roleIds === []) {
            return false;
        }

        return Role::query()
            ->whereIn('id', $roleIds)
            ->where('code', 'like', 'PARTNER_%')
            ->exists();
    }

    private function systemAssignableRoles()
    {
        return Role::query()
            ->whereNull('organization_id')
            ->where('status', 'active')
            ->where('code', '!=', 'CALLCENTER')
            ->orderBy('name')
            ->get();
    }

    private function selectedRolesContainPartnerScanAgent(array $roleIds): bool
    {
        $roleIds = collect($roleIds)->filter()->map(fn ($roleId) => (int) $roleId)->all();

        if ($roleIds === []) {
            return false;
        }

        return Role::query()
            ->whereIn('id', $roleIds)
            ->where('code', 'PARTNER_SCAN_AGENT')
            ->exists();
    }

    private function partnerOrganizations()
    {
        return Organization::query()
            ->with('organizationType')
            ->whereHas('organizationType', fn ($query) => $query->where('code', 'PARTNER_ESTABLISHMENT'))
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'organization_type_id']);
    }

    private function loginPortalFor(User $user): array
    {
        $user->loadMissing(['roles.permissions']);

        $permissions = $user->roles
            ->flatMap(fn (Role $role) => $role->permissions)
            ->unique('id');

        $codes = $permissions->pluck('code');
        $scopes = $permissions->pluck('profile_scope')->filter();

        if ($codes->contains(fn (string $code) => str_starts_with($code, 'PARTNER_')) || $scopes->contains('partner')) {
            return ['url' => route('partner.login'), 'label' => 'Connexion partenaire'];
        }

        if ($codes->contains(fn (string $code) => str_starts_with($code, 'INSTITUTION_')) || $scopes->contains('institution')) {
            return ['url' => route('institution.login'), 'label' => 'Connexion institution'];
        }

        if (
            $codes->contains('SA_ACCESS_PORTAL')
            || $codes->contains(fn (string $code) => str_starts_with($code, 'BO_'))
            || $scopes->intersect(['backoffice', 'huissier', 'aoda', 'avocat'])->isNotEmpty()
        ) {
            return ['url' => route('backoffice.login'), 'label' => 'Connexion back-office'];
        }

        if ($codes->contains(fn (string $code) => str_starts_with($code, 'SA_')) || $scopes->contains('super_admin')) {
            return ['url' => route('super-admin.login'), 'label' => 'Connexion SA'];
        }

        return ['url' => route('backoffice.login'), 'label' => 'Connexion back-office'];
    }

    private function abortIfProfileAccessIsNotManageable(User $user): void
    {
        abort_if($user->is_super_admin || $this->isScopedSaUser($user), 404);
    }
}
