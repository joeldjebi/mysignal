<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const ROLE_CODE = 'SA_ADMIN';

    private const EXCLUDED_PERMISSION_CODES = [
        'SA_SYSTEM_USERS_MANAGE',
        'SA_ROLES_VIEW',
        'SA_ROLES_MANAGE',
        'SA_PERMISSIONS_VIEW',
        'SA_PERMISSIONS_MANAGE',
    ];

    public function up(): void
    {
        $now = now();

        foreach ($this->viewPermissions() as $permission) {
            DB::table('permissions')->updateOrInsert(
                ['code' => $permission['code']],
                [
                    'name' => $permission['name'],
                    'description' => $permission['description'],
                    'profile_scope' => 'super_admin',
                    'category' => 'roles_permissions',
                    'status' => 'active',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            );
        }

        $permissionIds = DB::table('permissions')
            ->where('code', 'like', 'SA_%')
            ->whereNotIn('code', self::EXCLUDED_PERMISSION_CODES)
            ->pluck('id')
            ->all();

        // Fresh installs seed the full SA permission catalog after migrations.
        // In that case the RoleSeeder creates SA_ADMIN with the complete default set.
        if (count($permissionIds) < 10) {
            return;
        }

        $existingRole = DB::table('roles')
            ->where('code', self::ROLE_CODE)
            ->first();

        if ($existingRole === null) {
            DB::table('roles')->insert([
                'code' => self::ROLE_CODE,
                'organization_id' => null,
                'name' => 'Administrateur SA',
                'description' => 'Role administrateur interne avec toutes les permissions SA sauf la gestion CRUD des utilisateurs internes, roles et permissions.',
                'status' => 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        } else {
            DB::table('roles')
                ->where('id', $existingRole->id)
                ->update([
                    'organization_id' => null,
                    'name' => 'Administrateur SA',
                    'description' => 'Role administrateur interne avec toutes les permissions SA sauf la gestion CRUD des utilisateurs internes, roles et permissions.',
                    'status' => 'active',
                    'updated_at' => $now,
                ]);
        }

        $roleId = DB::table('roles')
            ->where('code', self::ROLE_CODE)
            ->value('id');

        if ($roleId === null) {
            return;
        }

        $alreadyHasPermissions = DB::table('permission_role')
            ->where('role_id', $roleId)
            ->exists();

        if ($alreadyHasPermissions) {
            return;
        }

        foreach ($permissionIds as $permissionId) {
            DB::table('permission_role')->updateOrInsert(
                [
                    'role_id' => $roleId,
                    'permission_id' => $permissionId,
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
        $roleId = DB::table('roles')
            ->where('code', self::ROLE_CODE)
            ->value('id');

        if ($roleId === null) {
            return;
        }

        DB::table('permission_role')
            ->where('role_id', $roleId)
            ->delete();

        DB::table('role_user')
            ->where('role_id', $roleId)
            ->delete();

        DB::table('roles')
            ->where('id', $roleId)
            ->delete();

        $viewPermissionCodes = collect($this->viewPermissions())
            ->pluck('code')
            ->all();

        $viewPermissionIds = DB::table('permissions')
            ->whereIn('code', $viewPermissionCodes)
            ->pluck('id')
            ->all();

        DB::table('permission_role')
            ->whereIn('permission_id', $viewPermissionIds)
            ->delete();

        DB::table('permission_user')
            ->whereIn('permission_id', $viewPermissionIds)
            ->delete();

        DB::table('permission_user_access')
            ->whereIn('permission_id', $viewPermissionIds)
            ->delete();

        DB::table('permissions')
            ->whereIn('id', $viewPermissionIds)
            ->delete();
    }

    private function viewPermissions(): array
    {
        return [
            [
                'code' => 'SA_ROLES_VIEW',
                'name' => 'Voir roles SA',
                'description' => 'Permet de consulter les roles SA sans pouvoir les creer, modifier ou supprimer.',
            ],
            [
                'code' => 'SA_PERMISSIONS_VIEW',
                'name' => 'Voir permissions SA',
                'description' => 'Permet de consulter les permissions SA sans pouvoir les creer, modifier ou supprimer.',
            ],
            [
                'code' => 'SA_SYSTEM_USERS_VIEW',
                'name' => 'Voir utilisateurs internes',
                'description' => 'Permet de consulter les utilisateurs internes du portail SA sans pouvoir les creer, modifier ou supprimer.',
            ],
        ];
    }
};
