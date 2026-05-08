<?php

namespace App\Http\Controllers\Web\SuperAdmin\Concerns;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;

trait InteractsWithScopedSaAdminManagement
{
    protected function authorizeScopedManagement(User $user, string $permissionCode): void
    {
        if ($user->status === 'active' && $user->is_super_admin) {
            return;
        }

        abort_unless(
            $user->status === 'active'
                && $user->hasEffectivePermissionCode($permissionCode),
            Response::HTTP_FORBIDDEN
        );
    }

    protected function scopedRoleQuery(User $user): Builder
    {
        $query = Role::query()
            ->whereNull('organization_id')
            ->whereNotNull('created_by');

        if (! $user->is_super_admin) {
            $query->where('created_by', $user->id);
        }

        return $query;
    }

    protected function assignablePermissions(User $user): Collection
    {
        $blockedCodes = [
            'SA_SCOPED_ROLES_MANAGE',
            'SA_SCOPED_USERS_MANAGE',
            'SA_SYSTEM_USERS_MANAGE',
            'SA_ROLES_MANAGE',
            'SA_PERMISSIONS_MANAGE',
        ];

        $query = Permission::query()
            ->where('status', 'active');

        if ($user->is_super_admin) {
            return $query
                ->orderBy('name')
                ->get();
        }

        return $query
            ->whereIn('code', $user->effectivePermissionCodes()->diff($blockedCodes)->values()->all())
            ->orderBy('name')
            ->get();
    }

    protected function validAssignablePermissionIds(User $user, array $permissionIds): array
    {
        if ($permissionIds === []) {
            return [];
        }

        return $this->assignablePermissions($user)
            ->whereIn('id', array_map('intval', $permissionIds))
            ->pluck('id')
            ->all();
    }
}
