<?php

namespace App\Support\Auth;

use App\Models\User;
use App\Models\UserAccess;

class SuperAdminAccessResolver
{
    public function resolve(User $user): ?UserAccess
    {
        $access = $user->accesses()
            ->whereIn('portal', ['super_admin', 'backoffice'])
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
}
