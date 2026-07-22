<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\User;
use App\Models\UserAccess;
use App\Support\Audit\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UserAccessController extends Controller
{
    private const PORTALS = [
        'backoffice',
        'super_admin',
        'institution',
        'partner',
        'huissier',
        'aoda',
        'avocat',
    ];

    public function store(Request $request, User $systemUser, ActivityLogger $activityLogger): RedirectResponse
    {
        $this->abortIfNotManageable($systemUser);
        $attributes = $this->validateRequest($request);
        $this->assertPortalOrganizationMatches($attributes);
        $this->assertAccessIsUniqueForUser($systemUser, $attributes);

        $access = null;

        DB::transaction(function () use ($attributes, $request, $systemUser, &$access): void {
            $access = UserAccess::query()->create([
                'user_id' => $systemUser->id,
                'organization_id' => $attributes['organization_id'] ?? null,
                'portal' => $attributes['portal'],
                'status' => $attributes['status'],
                'created_by' => $request->user()->id,
            ]);

            $access->roles()->sync($attributes['role_ids'] ?? []);
            $access->permissions()->sync($attributes['permission_ids'] ?? []);
        });

        $activityLogger->log(
            'system_user.access.created',
            'Création d’un profil d’accès utilisateur.',
            $systemUser,
            [
                'access_id' => $access?->id,
                'portal' => $attributes['portal'],
                'organization_id' => $attributes['organization_id'] ?? null,
                'role_ids' => $attributes['role_ids'] ?? [],
                'permission_ids' => $attributes['permission_ids'] ?? [],
            ],
            $request,
            $request->user(),
        );

        return redirect()
            ->route('super-admin.system-users.show', $systemUser)
            ->with('success', 'Le profil d’accès a été ajouté.');
    }

    public function update(Request $request, User $systemUser, UserAccess $access, ActivityLogger $activityLogger): RedirectResponse
    {
        $this->abortIfNotManageable($systemUser);
        $this->abortIfAccessDoesNotBelongToUser($access, $systemUser);
        $attributes = $this->validateRequest($request, $access);
        $this->assertPortalOrganizationMatches($attributes);
        $this->assertAccessIsUniqueForUser($systemUser, $attributes, $access);

        $before = [
            'portal' => $access->portal,
            'organization_id' => $access->organization_id,
            'status' => $access->status,
            'role_ids' => $access->roles()->pluck('roles.id')->all(),
            'permission_ids' => $access->permissions()->pluck('permissions.id')->all(),
        ];

        DB::transaction(function () use ($attributes, $access): void {
            $access->update([
                'organization_id' => $attributes['organization_id'] ?? null,
                'portal' => $attributes['portal'],
                'status' => $attributes['status'],
            ]);

            $access->roles()->sync($attributes['role_ids'] ?? []);
            $access->permissions()->sync($attributes['permission_ids'] ?? []);
        });

        $activityLogger->log(
            'system_user.access.updated',
            'Mise à jour d’un profil d’accès utilisateur.',
            $systemUser,
            [
                'access_id' => $access->id,
                'before' => $before,
                'after' => [
                    'portal' => $attributes['portal'],
                    'organization_id' => $attributes['organization_id'] ?? null,
                    'status' => $attributes['status'],
                    'role_ids' => $attributes['role_ids'] ?? [],
                    'permission_ids' => $attributes['permission_ids'] ?? [],
                ],
            ],
            $request,
            $request->user(),
        );

        return redirect()
            ->route('super-admin.system-users.show', $systemUser)
            ->with('success', 'Le profil d’accès a été mis à jour.');
    }

    public function destroy(Request $request, User $systemUser, UserAccess $access, ActivityLogger $activityLogger): RedirectResponse
    {
        $this->abortIfNotManageable($systemUser);
        $this->abortIfAccessDoesNotBelongToUser($access, $systemUser);

        $snapshot = [
            'access_id' => $access->id,
            'portal' => $access->portal,
            'organization_id' => $access->organization_id,
            'role_ids' => $access->roles()->pluck('roles.id')->all(),
            'permission_ids' => $access->permissions()->pluck('permissions.id')->all(),
        ];

        $access->delete();

        $activityLogger->log(
            'system_user.access.deleted',
            'Suppression d’un profil d’accès utilisateur.',
            $systemUser,
            $snapshot,
            $request,
            $request->user(),
        );

        return redirect()
            ->route('super-admin.system-users.show', $systemUser)
            ->with('success', 'Le profil d’accès a été supprimé.');
    }

    private function validateRequest(Request $request, ?UserAccess $access = null): array
    {
        return $request->validate([
            'portal' => ['required', 'string', Rule::in(self::PORTALS)],
            'organization_id' => ['nullable', 'integer', 'exists:organizations,id'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'role_ids' => ['nullable', 'array'],
            'role_ids.*' => ['integer', 'exists:roles,id'],
            'permission_ids' => ['nullable', 'array'],
            'permission_ids.*' => ['integer', 'exists:permissions,id'],
        ]);
    }

    private function assertPortalOrganizationMatches(array $attributes): void
    {
        $portal = (string) $attributes['portal'];
        $organizationId = $attributes['organization_id'] ?? null;

        if (in_array($portal, ['backoffice', 'super_admin', 'huissier', 'aoda', 'avocat'], true) && filled($organizationId)) {
            throw ValidationException::withMessages([
                'organization_id' => ['Ce profil ne doit pas etre rattache a une organisation.'],
            ]);
        }

        if (in_array($portal, ['institution', 'partner'], true) && blank($organizationId)) {
            throw ValidationException::withMessages([
                'organization_id' => ['Sélectionnez une institution pour ce profil.'],
            ]);
        }

        if (blank($organizationId)) {
            return;
        }

        $organization = Organization::query()
            ->with('organizationType')
            ->findOrFail($organizationId);

        if ($portal === 'partner' && $organization->organizationType?->code !== 'PARTNER_ESTABLISHMENT') {
            throw ValidationException::withMessages([
                'organization_id' => ['Le profil partenaire doit etre rattache a un etablissement partenaire.'],
            ]);
        }

        if ($portal === 'institution' && $organization->organizationType?->code === 'PARTNER_ESTABLISHMENT') {
            throw ValidationException::withMessages([
                'organization_id' => ['Le profil institution ne peut pas etre rattache a un etablissement partenaire.'],
            ]);
        }
    }

    private function assertAccessIsUniqueForUser(User $user, array $attributes, ?UserAccess $ignoredAccess = null): void
    {
        $exists = $user->accesses()
            ->where('portal', $attributes['portal'])
            ->where(function ($query) use ($attributes): void {
                if (blank($attributes['organization_id'] ?? null)) {
                    $query->whereNull('organization_id');
                } else {
                    $query->where('organization_id', $attributes['organization_id']);
                }
            })
            ->when($ignoredAccess, fn ($query) => $query->whereKeyNot($ignoredAccess->id))
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'portal' => ['Cet utilisateur possède déjà ce profil pour cette institution.'],
            ]);
        }
    }

    private function abortIfAccessDoesNotBelongToUser(UserAccess $access, User $user): void
    {
        abort_if((int) $access->user_id !== (int) $user->id, 404);
    }

    private function abortIfNotManageable(User $user): void
    {
        abort_if($user->is_super_admin, 404);
    }
}
