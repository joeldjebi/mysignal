<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $roleId = DB::table('roles')->where('code', 'PARTNER_MANAGER')->value('id');

        if (! $roleId) {
            return;
        }

        DB::table('permissions')
            ->whereIn('code', ['PARTNER_USERS_MANAGE', 'PARTNER_USERS_CREATE'])
            ->pluck('id')
            ->each(function (int $permissionId) use ($roleId): void {
                DB::table('permission_role')->updateOrInsert(
                    [
                        'role_id' => $roleId,
                        'permission_id' => $permissionId,
                    ],
                    [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            });
    }

    public function down(): void
    {
        $roleId = DB::table('roles')->where('code', 'PARTNER_MANAGER')->value('id');

        if (! $roleId) {
            return;
        }

        $permissionIds = DB::table('permissions')
            ->whereIn('code', ['PARTNER_USERS_MANAGE', 'PARTNER_USERS_CREATE'])
            ->pluck('id');

        DB::table('permission_role')
            ->where('role_id', $roleId)
            ->whereIn('permission_id', $permissionIds)
            ->delete();
    }
};
