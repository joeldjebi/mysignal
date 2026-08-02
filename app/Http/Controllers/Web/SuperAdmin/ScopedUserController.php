<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\SuperAdmin\Concerns\InteractsWithScopedSaAdminManagement;
use App\Models\Role;
use App\Models\User;
use App\Models\UserType;
use App\Services\SmsService;
use App\Support\Audit\ActivityLogger;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class ScopedUserController extends Controller
{
    use InteractsWithScopedSaAdminManagement;

    public function index(Request $request): View
    {
        $actor = $request->user()->loadMissing(['roles.permissions', 'permissions']);
        $this->authorizeScopedManagement($actor, 'SA_SCOPED_USERS_MANAGE');
        $perPage = min(max((int) $request->integer('per_page', 12), 1), 100);
        $query = $this->scopedUserQuery($actor)
            ->with(['roles.permissions', 'permissions']);

        if (filled($request->input('search'))) {
            $search = trim((string) $request->input('search'));
            $query->where(function (Builder $builder) use ($search): void {
                $builder->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%')
                    ->orWhere('phone', 'like', '%'.$search.'%');
            });
        }

        if (filled($request->input('status'))) {
            $query->where('status', $request->input('status'));
        }

        if (filled($request->input('role_id'))) {
            $roleId = (int) $request->input('role_id');
            $query->whereHas('roles', fn (Builder $roleQuery) => $roleQuery->where('roles.id', $roleId));
        }

        return view('super-admin.scoped-users.index', [
            'users' => $query->latest()->paginate($perPage)->withQueryString(),
            'roles' => $this->assignableScopedRoles($actor),
            'permissions' => $this->assignablePermissions($actor),
        ]);
    }

    public function store(Request $request, SmsService $smsService, ActivityLogger $activityLogger): RedirectResponse
    {
        $actor = $request->user()->loadMissing(['roles.permissions', 'permissions']);
        $this->authorizeScopedManagement($actor, 'SA_SCOPED_USERS_MANAGE');
        $attributes = $this->validatePayload($request);
        $isCallCenterUser = $this->selectedRolesContainCallCenter($actor, $attributes['role_ids'] ?? []);
        $password = $isCallCenterUser ? $this->temporaryPassword() : $attributes['password'];
        $createdUser = null;

        DB::transaction(function () use ($attributes, $actor, $password, &$createdUser): void {
            $createdUser = User::query()->create([
                'user_type_id' => UserType::idFor(UserType::SA_USER),
                'organization_id' => null,
                'name' => $attributes['name'],
                'email' => $attributes['email'],
                'phone' => $attributes['phone'] ?? null,
                'password' => Hash::make($password),
                'is_super_admin' => false,
                'status' => 'active',
                'created_by' => $actor->id,
            ]);

            $createdUser->roles()->sync($this->validOwnedRoleIds($actor, $attributes['role_ids'] ?? []));
            $createdUser->permissions()->sync($this->validAssignablePermissionIds($actor, $attributes['permission_ids'] ?? []));
        });

        if ($isCallCenterUser && $createdUser instanceof User) {
            return $this->sendGeneratedAccessToCallCenterUser($request, $createdUser, $password, $smsService, $activityLogger);
        }

        return redirect()->route('super-admin.scoped-users.index')->with('success', 'L’utilisateur a été créé.');
    }

    public function edit(Request $request, User $scopedUser): View
    {
        $actor = $request->user()->loadMissing(['roles.permissions', 'permissions']);
        $this->authorizeScopedManagement($actor, 'SA_SCOPED_USERS_MANAGE');
        $this->abortIfUserIsNotOwnedBy($scopedUser, $actor->id, $actor->is_super_admin);

        return view('super-admin.scoped-users.edit', [
            'managedUser' => $scopedUser->load(['roles', 'permissions']),
            'roles' => $this->assignableScopedRoles($actor),
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

        return redirect()->route('super-admin.scoped-users.index')->with('success', 'L’utilisateur a été mis à jour.');
    }

    public function destroy(Request $request, User $scopedUser): RedirectResponse
    {
        $actor = $request->user()->loadMissing(['roles.permissions', 'permissions']);
        $this->authorizeScopedManagement($actor, 'SA_SCOPED_USERS_MANAGE');
        $this->abortIfUserIsNotOwnedBy($scopedUser, $actor->id, $actor->is_super_admin);

        $scopedUser->delete();

        return back()->with('success', 'L’utilisateur a été supprimé.');
    }

    public function sendAccess(Request $request, User $scopedUser, SmsService $smsService, ActivityLogger $activityLogger): RedirectResponse
    {
        $actor = $request->user()->loadMissing(['roles.permissions', 'permissions']);
        $this->authorizeScopedManagement($actor, 'SA_SCOPED_USERS_MANAGE');
        $this->abortIfUserIsNotOwnedBy($scopedUser, $actor->id, $actor->is_super_admin);

        if (blank($scopedUser->phone)) {
            return back()->withErrors([
                'access' => 'Le compte doit avoir un numéro de téléphone avant l’envoi des accès.',
            ]);
        }

        $password = $this->temporaryPassword();
        $loginUrl = $this->loginUrlFor($scopedUser);
        $previousPasswordHash = $scopedUser->password;
        $previousStatus = $scopedUser->status;

        $scopedUser->update([
            'password' => Hash::make($password),
            'status' => 'active',
        ]);

        $message = "My-Signal: accès portail. Lien: {$loginUrl} Identifiant: {$scopedUser->email} Mot de passe temporaire: {$password}";

        try {
            $smsService->sendSmsMtarget($message, (string) $scopedUser->phone);
        } catch (Throwable $exception) {
            $scopedUser->forceFill([
                'password' => $previousPasswordHash,
                'status' => $previousStatus,
            ])->save();

            $activityLogger->log(
                'scoped_user.access_sms_failed',
                'Échec d’envoi des accès utilisateur SA par SMS.',
                $scopedUser,
                [
                    'phone' => $scopedUser->phone,
                    'error' => $exception->getMessage(),
                ],
                $request,
                $actor,
            );

            return back()->withErrors([
                'access' => 'L’envoi SMS a échoué. Le mot de passe précédent a été conservé.',
            ]);
        }

        $activityLogger->log(
            'scoped_user.access_sent',
            'Envoi des accès utilisateur SA par SMS.',
            $scopedUser,
            [
                'phone' => $scopedUser->phone,
                'email' => $scopedUser->email,
                'login_url' => $loginUrl,
                'mail_sent' => false,
            ],
            $request,
            $actor,
        );

        return back()
            ->with('success', 'Les accès ont été envoyés par SMS. L’email sera activé dès que le service d’envoi sera configuré.')
            ->with('access_credentials', $this->temporaryAccessCredentials($scopedUser, $password, $loginUrl));
    }

    private function validatePayload(Request $request, ?User $user = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user?->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => [$user ? 'nullable' : Rule::requiredIf(fn () => ! $this->selectedRolesContainCallCenter($request->user(), $request->input('role_ids', []))), 'nullable', 'string', 'min:8'],
            'role_ids' => ['nullable', 'array'],
            'role_ids.*' => ['integer', 'exists:roles,id'],
            'permission_ids' => ['nullable', 'array'],
            'permission_ids.*' => ['integer', 'exists:permissions,id'],
        ]);
    }

    private function validOwnedRoleIds(User $actor, array $roleIds): array
    {
        $roleIds = array_map('intval', $roleIds);

        if ($roleIds === []) {
            return [];
        }

        return $this->assignableScopedRoles($actor)
            ->whereIn('id', $roleIds)
            ->pluck('id')
            ->all();
    }

    private function selectedRolesContainCallCenter(User $actor, array $roleIds): bool
    {
        $roleIds = collect($roleIds)->filter()->map(fn ($roleId) => (int) $roleId)->all();

        if ($roleIds === []) {
            return false;
        }

        return $this->assignableScopedRoles($actor)
            ->whereIn('id', $roleIds)
            ->contains('code', 'CALLCENTER');
    }

    private function assignableScopedRoles(User $actor)
    {
        $query = Role::query()
            ->whereNull('organization_id')
            ->where('status', 'active')
            ->where(function (Builder $builder) use ($actor): void {
                $builder->whereNotNull('created_by');

                if ($actor->is_super_admin) {
                    $builder->orWhere('code', 'CALLCENTER');
                }
            });

        if (! $actor->is_super_admin) {
            $query->where('created_by', $actor->id);
        }

        return $query
            ->orderBy('name')
            ->get();
    }

    private function temporaryPassword(): string
    {
        return 'MS-'.Str::upper(Str::random(4)).'-'.random_int(1000, 9999);
    }

    private function sendGeneratedAccessToCallCenterUser(
        Request $request,
        User $user,
        string $password,
        SmsService $smsService,
        ActivityLogger $activityLogger
    ): RedirectResponse {
        if (blank($user->phone)) {
            return redirect()
                ->route('super-admin.scoped-users.index')
                ->with('warning', 'Le compte centre d’appels a été créé, mais aucun SMS n’a été envoyé car le numéro de téléphone est absent.')
                ->with('access_credentials', $this->temporaryAccessCredentials($user, $password, $this->loginUrlFor($user)));
        }

        $loginUrl = $this->loginUrlFor($user);
        $message = "My-Signal: accès centre d’appels. Lien: {$loginUrl} Identifiant: {$user->email} Mot de passe temporaire: {$password}";

        try {
            $smsService->sendSmsMtarget($message, (string) $user->phone);
        } catch (Throwable $exception) {
            $activityLogger->log(
                'scoped_user.callcenter_access_sms_failed',
                'Échec d’envoi des accès centre d’appels par SMS.',
                $user,
                [
                    'phone' => $user->phone,
                    'error' => $exception->getMessage(),
                ],
                $request,
                $request->user(),
            );

            return redirect()
                ->route('super-admin.scoped-users.index')
                ->with('warning', 'Le compte centre d’appels a été créé, mais l’envoi SMS a échoué. Utilisez le bouton d’envoi des accès pour générer un nouveau mot de passe.')
                ->with('access_credentials', $this->temporaryAccessCredentials($user, $password, $loginUrl));
        }

        $activityLogger->log(
            'scoped_user.callcenter_access_sent',
            'Envoi des accès centre d’appels par SMS.',
            $user,
            [
                'phone' => $user->phone,
                'email' => $user->email,
                'login_url' => $loginUrl,
            ],
            $request,
            $request->user(),
        );

        return redirect()
            ->route('super-admin.scoped-users.index')
            ->with('success', 'Le compte centre d’appels a été créé et les accès ont été envoyés par SMS.')
            ->with('access_credentials', $this->temporaryAccessCredentials($user, $password, $loginUrl));
    }

    private function loginUrlFor(User $user): string
    {
        $user->loadMissing('roles');

        if ($user->roles->contains('code', 'CALLCENTER')) {
            return route('callcenter.login');
        }

        return route('super-admin.login');
    }

    private function temporaryAccessCredentials(User $user, string $password, string $loginUrl): array
    {
        return [
            'label' => $user->roles->contains('code', 'CALLCENTER') ? 'Accès centre d’appels' : 'Accès utilisateur SA',
            'login_url' => $loginUrl,
            'email' => $user->email,
            'password' => $password,
        ];
    }

    private function scopedUserQuery(User $actor): Builder
    {
        $query = User::query()
            ->where('user_type_id', UserType::idFor(UserType::SA_USER))
            ->where('is_super_admin', false)
            ->whereNotNull('created_by')
            ->where(function (Builder $builder): void {
                $builder->whereHas('roles', fn (Builder $roleQuery) => $roleQuery->whereNotNull('roles.created_by'))
                    ->orWhereHas('roles', fn (Builder $roleQuery) => $roleQuery->where('roles.code', 'CALLCENTER'))
                    ->orWhereHas('permissions');
            })
            ->whereDoesntHave('roles.permissions', fn (Builder $permissionQuery) => $this->nonScopedPortalPermissions($permissionQuery))
            ->whereDoesntHave('permissions', fn (Builder $permissionQuery) => $this->nonScopedPortalPermissions($permissionQuery));

        if (! $actor->is_super_admin) {
            $query->where('created_by', $actor->id);
        }

        return $query;
    }

    private function abortIfUserIsNotOwnedBy(User $user, int $actorId, bool $actorIsSuperAdmin = false): void
    {
        abort_if(
            $user->is_super_admin
                || (int) $user->user_type_id !== (int) UserType::idFor(UserType::SA_USER)
                || $user->created_by === null
                || (! $user->roles()->whereNotNull('roles.created_by')->exists()
                    && ! $user->roles()->where('roles.code', 'CALLCENTER')->exists()
                    && ! $user->permissions()->exists())
                || $this->hasNonScopedPortalAccess($user)
                || (! $actorIsSuperAdmin && (int) $user->created_by !== $actorId),
            404
        );
    }

    private function nonScopedPortalPermissions(Builder $query): void
    {
        $query->where(function (Builder $permissionQuery): void {
            $permissionQuery
                ->where('code', 'like', 'BO_%')
                ->orWhere('code', 'like', 'PARTNER_%')
                ->orWhereIn('profile_scope', ['backoffice', 'huissier', 'aoda', 'avocat', 'partner']);
        });
    }

    private function hasNonScopedPortalAccess(User $user): bool
    {
        return $user->roles()
            ->whereHas('permissions', fn (Builder $permissionQuery) => $this->nonScopedPortalPermissions($permissionQuery))
            ->exists()
            || $user->permissions()
                ->where(fn (Builder $permissionQuery) => $this->nonScopedPortalPermissions($permissionQuery))
                ->exists();
    }
}
