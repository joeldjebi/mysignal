<?php

namespace Database\Seeders\Reference;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            [
                'code' => 'HUISSIER',
                'name' => 'Huissier',
                'description' => 'Role dedie au constat des faits et a la production du rapport de constat.',
                'permission_codes' => [],
            ],
            [
                'code' => 'AVOCAT',
                'name' => 'Avocat',
                'description' => 'Role dedie au suivi de la procedure judiciaire et des actes contentieux.',
                'permission_codes' => [],
            ],
            [
                'code' => 'PARTNER_ADMIN',
                'name' => 'Administrateur partenaire',
                'description' => 'Role permettant de gerer le dashboard partenaire, les offres et les utilisateurs mobiles.',
                'permission_codes' => [
                    'PARTNER_ACCESS_PORTAL',
                    'PARTNER_DASHBOARD_VIEW',
                    'PARTNER_DISCOUNT_SCAN',
                    'PARTNER_DISCOUNT_APPLY',
                    'PARTNER_DISCOUNT_HISTORY_VIEW',
                    'PARTNER_DISCOUNT_OFFERS_MANAGE',
                    'PARTNER_USERS_MANAGE',
                    'PARTNER_USERS_CREATE',
                    'PARTNER_USERS_UPDATE',
                    'PARTNER_USERS_TOGGLE_STATUS',
                ],
            ],
            [
                'code' => 'PARTNER_MANAGER',
                'name' => 'Manager partenaire',
                'description' => 'Role permettant de suivre les reductions appliquees et l activite du partenaire.',
                'permission_codes' => [
                    'PARTNER_ACCESS_PORTAL',
                    'PARTNER_DASHBOARD_VIEW',
                    'PARTNER_DISCOUNT_HISTORY_VIEW',
                ],
            ],
            [
                'code' => 'PARTNER_AGENT',
                'name' => 'Agent partenaire mobile',
                'description' => 'Role dedie aux utilisateurs de l application mobile de scan et de reduction.',
                'permission_codes' => [
                    'PARTNER_ACCESS_PORTAL',
                    'PARTNER_DISCOUNT_SCAN',
                    'PARTNER_DISCOUNT_APPLY',
                    'PARTNER_DISCOUNT_HISTORY_VIEW',
                ],
            ],
        ] as $roleData) {
            $role = Role::query()->updateOrCreate(
                ['code' => $roleData['code']],
                [
                    'name' => $roleData['name'],
                    'description' => $roleData['description'],
                    'status' => 'active',
                ]
            );

            if ($role->organization_id === null && $roleData['permission_codes'] !== []) {
                $permissionIds = Permission::query()
                    ->whereIn('code', $roleData['permission_codes'])
                    ->pluck('id')
                    ->all();

                $role->permissions()->sync($permissionIds);
            }
        }
    }
}
