<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const MODULES = [
        'SA_NEIGHBORHOODS' => ['Quartiers', 'catalog'],
        'SA_SUB_NEIGHBORHOODS' => ['Sous-quartiers', 'catalog'],
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

        foreach ([
            ['code' => 'SA_NEIGHBORHOODS_MANAGE', 'name' => 'Gerer quartiers', 'description' => 'Permet de gerer les quartiers.'],
            ['code' => 'SA_SUB_NEIGHBORHOODS_MANAGE', 'name' => 'Gerer sous-quartiers', 'description' => 'Permet de gerer les sous-quartiers.'],
            ...$this->granularPermissions(),
        ] as $permission) {
            DB::table('permissions')->updateOrInsert(
                ['code' => $permission['code']],
                [
                    'name' => $permission['name'],
                    'description' => $permission['description'],
                    'profile_scope' => 'super_admin',
                    'category' => $permission['category'] ?? 'catalog',
                    'status' => 'active',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            );
        }
    }

    public function down(): void
    {
        $codes = collect([
            'SA_NEIGHBORHOODS_MANAGE',
            'SA_SUB_NEIGHBORHOODS_MANAGE',
            ...collect($this->granularPermissions())->pluck('code')->all(),
        ])->all();

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
