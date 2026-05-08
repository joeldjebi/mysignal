<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const PERMISSIONS = [
        'SA_INSTITUTION_ADMINS_VIEW' => ['Voir admins institutionnels', 'Permet de consulter les admins institutionnels.', 'users'],
        'SA_INSTITUTION_ADMINS_CREATE' => ['Creer admins institutionnels', 'Permet de creer des admins institutionnels.', 'users'],
        'SA_INSTITUTION_ADMINS_UPDATE' => ['Modifier admins institutionnels', 'Permet de modifier des admins institutionnels.', 'users'],
        'SA_INSTITUTION_ADMINS_DELETE' => ['Supprimer admins institutionnels', 'Permet de supprimer des admins institutionnels.', 'users'],
        'SA_INSTITUTION_ADMINS_TOGGLE_STATUS' => ['Activer ou desactiver admins institutionnels', 'Permet d activer ou desactiver des admins institutionnels.', 'users'],
    ];

    public function up(): void
    {
        $now = now();

        foreach (self::PERMISSIONS as $code => [$name, $description, $category]) {
            DB::table('permissions')->updateOrInsert(
                ['code' => $code],
                [
                    'name' => $name,
                    'description' => $description,
                    'profile_scope' => 'super_admin',
                    'category' => $category,
                    'status' => 'active',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            );
        }

        $roleId = DB::table('roles')
            ->where('code', 'SA_ADMIN')
            ->value('id');

        if ($roleId === null) {
            return;
        }

        $removePermissionIds = DB::table('permissions')
            ->whereIn('code', ['SA_INSTITUTION_ADMINS_MANAGE', 'SA_INSTITUTION_ADMINS_TOGGLE_STATUS'])
            ->pluck('id')
            ->all();

        DB::table('permission_role')
            ->where('role_id', $roleId)
            ->whereIn('permission_id', $removePermissionIds)
            ->delete();

        $viewPermissionId = DB::table('permissions')
            ->where('code', 'SA_INSTITUTION_ADMINS_VIEW')
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
