<?php

namespace App\Support\Auth;

use App\Models\User;
use App\Models\UserAccess;

class SuperAdminAccessResolver
{
    public const INTERNAL_PORTALS = ['super_admin', 'backoffice', 'huissier', 'aoda', 'avocat'];

    private const LEGAL_PORTAL_PERMISSIONS = [
        'huissier' => 'BO_REPARATION_CASES_HUISSIER',
        'aoda' => 'BO_REPARATION_CASES_AODA',
        'avocat' => 'BO_REPARATION_CASES_AVOCAT',
    ];

    public function resolve(User $user): ?UserAccess
    {
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
                'portal' => $user->is_super_admin ? 'super_admin' : 'backoffice',
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
}
