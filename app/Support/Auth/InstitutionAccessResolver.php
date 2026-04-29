<?php

namespace App\Support\Auth;

use App\Models\User;
use App\Models\UserAccess;

class InstitutionAccessResolver
{
    public function resolve(User $user): ?UserAccess
    {
        $access = $user->accesses()
            ->with('organization.organizationType')
            ->where('portal', 'institution')
            ->where('status', 'active')
            ->whereNotNull('organization_id')
            ->latest('id')
            ->first();

        if ($access !== null) {
            return $access;
        }

        $user->loadMissing('organization.organizationType');

        if (
            $user->organization_id !== null
            && $user->organization?->organizationType?->code !== 'PARTNER_ESTABLISHMENT'
        ) {
            return new UserAccess([
                'user_id' => $user->id,
                'organization_id' => $user->organization_id,
                'portal' => 'institution',
                'status' => 'active',
            ]);
        }

        return null;
    }

    public function apply(User $user, UserAccess $access): User
    {
        $access->loadMissing(['organization.organizationType', 'permissions', 'roles.permissions']);

        $user->forceFill([
            'organization_id' => $access->organization_id,
        ]);

        if ($access->organization !== null) {
            $user->setRelation('organization', $access->organization);
        }

        $user->setRelation('activeAccess', $access);

        return $user;
    }
}
