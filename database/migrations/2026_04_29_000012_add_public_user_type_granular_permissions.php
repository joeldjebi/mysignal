<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const PERMISSIONS = [
        'SA_PUBLIC_USER_TYPES_VIEW' => ['Voir Types d usagers publics', 'Permet de consulter Types d usagers publics.'],
        'SA_PUBLIC_USER_TYPES_CREATE' => ['Creer Types d usagers publics', 'Permet de creer Types d usagers publics.'],
        'SA_PUBLIC_USER_TYPES_UPDATE' => ['Modifier Types d usagers publics', 'Permet de modifier Types d usagers publics.'],
        'SA_PUBLIC_USER_TYPES_DELETE' => ['Supprimer Types d usagers publics', 'Permet de supprimer Types d usagers publics.'],
        'SA_PUBLIC_USER_TYPES_TOGGLE_STATUS' => ['Activer ou desactiver Types d usagers publics', 'Permet d activer ou desactiver Types d usagers publics.'],
    ];

    public function up(): void
    {
        $now = now();

        foreach (self::PERMISSIONS as $code => [$name, $description]) {
            DB::table('permissions')->updateOrInsert(
                ['code' => $code],
                [
                    'name' => $name,
                    'description' => $description,
                    'profile_scope' => 'super_admin',
                    'category' => 'users',
                    'status' => 'active',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            );
        }

        $roleId = DB::table('roles')->where('code', 'SA_ADMIN')->value('id');

        if ($roleId === null) {
            return;
        }

        $managePermissionId = DB::table('permissions')
            ->where('code', 'SA_PUBLIC_USER_TYPES_MANAGE')
            ->value('id');

        if ($managePermissionId !== null) {
            DB::table('permission_role')
                ->where('role_id', $roleId)
                ->where('permission_id', $managePermissionId)
                ->delete();
        }

        $viewPermissionId = DB::table('permissions')
            ->where('code', 'SA_PUBLIC_USER_TYPES_VIEW')
            ->value('id');

        if ($viewPermissionId !== null) {
            DB::table('permission_role')->updateOrInsert(
                [
                    'role_id' => $roleId,
                    'permission_id' => $viewPermissionId,
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
        $permissionIds = DB::table('permissions')
            ->whereIn('code', array_keys(self::PERMISSIONS))
            ->pluck('id')
            ->all();

        DB::table('permission_role')->whereIn('permission_id', $permissionIds)->delete();
        DB::table('permission_user')->whereIn('permission_id', $permissionIds)->delete();
        DB::table('permission_user_access')->whereIn('permission_id', $permissionIds)->delete();
        DB::table('permissions')->whereIn('id', $permissionIds)->delete();
    }
};
