<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const MODULES = [
        'SA_COUNTRIES' => ['Pays', 'catalog'],
        'SA_CITIES' => ['Villes', 'catalog'],
        'SA_COMMUNES' => ['Communes', 'catalog'],
        'SA_BUSINESS_SECTORS' => ['Secteurs', 'catalog'],
        'SA_ORGANIZATION_TYPES' => ['Types d organisation', 'catalog'],
        'SA_FEATURES' => ['Fonctionnalites', 'catalog'],
        'SA_APPLICATIONS' => ['Applications', 'catalog'],
        'SA_SIGNAL_TYPES' => ['Types de signaux', 'reports'],
        'SA_SLA_POLICIES' => ['TCM cibles', 'settings'],
        'SA_SUBSCRIPTION_PLANS' => ['Plans abonnements UP', 'payments'],
        'SA_PUBLIC_USER_TYPES' => ['Types d usagers publics', 'users'],
        'SA_PUBLIC_USERS' => ['Usagers publics', 'users'],
        'SA_ORGANIZATIONS' => ['Organisations', 'catalog'],
        'SA_INSTITUTION_ADMINS' => ['Admins institutionnels', 'users'],
    ];

    private const ACTIONS = [
        'VIEW' => ['Voir', 'consulter'],
        'CREATE' => ['Creer', 'creer'],
        'UPDATE' => ['Modifier', 'modifier'],
        'DELETE' => ['Supprimer', 'supprimer'],
        'TOGGLE_STATUS' => ['Activer ou desactiver', 'activer ou desactiver'],
    ];

    public function up(): void
    {
        $now = now();

        foreach ($this->granularPermissions() as $permission) {
            DB::table('permissions')->updateOrInsert(
                ['code' => $permission['code']],
                [
                    'name' => $permission['name'],
                    'description' => $permission['description'],
                    'profile_scope' => 'super_admin',
                    'category' => $permission['category'],
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

        $managedCodes = collect(array_keys(self::MODULES))
            ->map(fn (string $prefix): string => $prefix.'_MANAGE')
            ->push('SA_PUBLIC_USERS_TOGGLE_STATUS')
            ->push('SA_INSTITUTION_ADMINS_TOGGLE_STATUS')
            ->all();

        $managedPermissionIds = DB::table('permissions')
            ->whereIn('code', $managedCodes)
            ->pluck('id')
            ->all();

        DB::table('permission_role')
            ->where('role_id', $roleId)
            ->whereIn('permission_id', $managedPermissionIds)
            ->delete();

        $viewPermissionIds = DB::table('permissions')
            ->whereIn('code', collect(array_keys(self::MODULES))->map(fn (string $prefix): string => $prefix.'_VIEW')->all())
            ->pluck('id')
            ->all();

        foreach ($viewPermissionIds as $permissionId) {
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
        $codes = collect($this->granularPermissions())
            ->pluck('code')
            ->all();

        $permissionIds = DB::table('permissions')
            ->whereIn('code', $codes)
            ->pluck('id')
            ->all();

        DB::table('permission_role')->whereIn('permission_id', $permissionIds)->delete();
        DB::table('permission_user')->whereIn('permission_id', $permissionIds)->delete();
        DB::table('permission_user_access')->whereIn('permission_id', $permissionIds)->delete();
        DB::table('permissions')->whereIn('id', $permissionIds)->delete();
    }

    private function granularPermissions(): array
    {
        $permissions = [];

        foreach (self::MODULES as $prefix => [$label, $category]) {
            foreach (self::ACTIONS as $action => [$nameAction, $descriptionAction]) {
                $permissions[] = [
                    'code' => $prefix.'_'.$action,
                    'name' => $nameAction.' '.$label,
                    'description' => 'Permet de '.$descriptionAction.' '.$label.'.',
                    'category' => $category,
                ];
            }
        }

        return $permissions;
    }
};
