<?php

namespace App\Support\Auth;

use App\Models\User;
use App\Models\UserAccess;

class SuperAdminAccessResolver
{
    public const INTERNAL_PORTALS = ['super_admin', 'backoffice', 'callcenter', 'huissier', 'aoda', 'avocat'];

    private const LEGAL_PORTAL_PERMISSIONS = [
        'huissier' => 'BO_REPARATION_CASES_HUISSIER',
        'aoda' => 'BO_REPARATION_CASES_AODA',
        'avocat' => 'BO_REPARATION_CASES_AVOCAT',
    ];

    public function resolve(User $user): ?UserAccess
    {
        if (! $user->is_super_admin && $this->isCallCenterUser($user) && $user->organization_id === null && $user->hasPermissionCode('SA_ACCESS_PORTAL')) {
            return new UserAccess([
                'user_id' => $user->id,
                'organization_id' => null,
                'portal' => 'callcenter',
                'status' => 'active',
            ]);
        }

        $access = $user->accesses()
            ->whereIn('portal', self::INTERNAL_PORTALS)
            ->where('status', 'active')
            ->whereNull('organization_id')
            ->latest('id')
            ->first();

        if ($access !== null) {
            return $access;
        }

        if ($user->is_super_admin || ($user->organization_id === null && $user->hasPermissionCode('SA_ACCESS_PORTAL'))) {
            return new UserAccess([
                'user_id' => $user->id,
                'organization_id' => null,
                'portal' => $this->defaultPortalFor($user),
                'status' => 'active',
            ]);
        }

        return null;
    }

    public function apply(User $user, UserAccess $access): User
    {
        $access->loadMissing(['permissions', 'roles.permissions']);

        $user->forceFill([
            'organization_id' => null,
        ]);

        $user->setRelation('activeAccess', $access);

        return $user;
    }

    public function resolveLegalPortal(?User $user, ?UserAccess $access = null): ?string
    {
        if ($user === null) {
            return null;
        }

        $portal = $access?->portal ?: $user->getRelationValue('activeAccess')?->portal;

        if (in_array($portal, array_keys(self::LEGAL_PORTAL_PERMISSIONS), true)) {
            return $portal;
        }

        if ($portal !== 'backoffice') {
            return null;
        }

        $permissionCodes = $user->effectivePermissionCodes();

        foreach (self::LEGAL_PORTAL_PERMISSIONS as $legalPortal => $permissionCode) {
            if ($permissionCodes->contains($permissionCode)) {
                return $legalPortal;
            }
        }

        return null;
    }

    private function defaultPortalFor(User $user): string
    {
        if ($user->is_super_admin) {
            return 'super_admin';
        }

        if ($this->isCallCenterUser($user)) {
            return 'callcenter';
        }

        return 'backoffice';
    }

    private function isCallCenterUser(User $user): bool
    {
        $user->loadMissing('roles');

        return $user->roles->contains('code', 'CALLCENTER');
    }
}
