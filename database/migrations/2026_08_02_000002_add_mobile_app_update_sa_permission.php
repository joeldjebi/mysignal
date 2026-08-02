<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const PERMISSION_CODE = 'SA_MOBILE_APP_UPDATE_MANAGE';

    public function up(): void
    {
        $now = now();

        DB::table('permissions')->updateOrInsert(
            ['code' => self::PERMISSION_CODE],
            [
                'name' => 'Gérer les mises à jour mobiles',
                'description' => 'Permet de modifier les versions mobiles, les liens stores et les messages de mise à jour.',
                'profile_scope' => 'super_admin',
                'category' => 'settings',
                'status' => 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        );

        $permissionId = DB::table('permissions')->where('code', self::PERMISSION_CODE)->value('id');
        $roleId = DB::table('roles')->where('code', 'SA_ADMIN')->value('id');

        if ($permissionId !== null && $roleId !== null) {
            DB::table('permission_role')->updateOrInsert(
                [
                    'permission_id' => $permissionId,
                    'role_id' => $roleId,
                ],
                [
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            );
        }
    }

    public function down(): void
    {
        $permissionId = DB::table('permissions')->where('code', self::PERMISSION_CODE)->value('id');

        if ($permissionId !== null) {
            DB::table('permission_role')->where('permission_id', $permissionId)->delete();
            DB::table('permission_user')->where('permission_id', $permissionId)->delete();
            DB::table('permissions')->where('id', $permissionId)->delete();
        }
    }
};
