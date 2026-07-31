<?php

namespace Database\Seeders\Reference;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    private const SA_ADMIN_EXCLUDED_PERMISSION_CODES = [
        'SA_COUNTRIES_MANAGE',
        'SA_CITIES_MANAGE',
        'SA_COMMUNES_MANAGE',
        'SA_BUSINESS_SECTORS_MANAGE',
        'SA_ORGANIZATION_TYPES_MANAGE',
        'SA_FEATURES_MANAGE',
        'SA_APPLICATIONS_MANAGE',
        'SA_SIGNAL_TYPES_MANAGE',
        'SA_SLA_POLICIES_MANAGE',
        'SA_SUBSCRIPTION_PLANS_MANAGE',
        'SA_PUBLIC_USER_TYPES_MANAGE',
        'SA_PUBLIC_USERS_MANAGE',
        'SA_PUBLIC_USERS_TOGGLE_STATUS',
        'SA_ORGANIZATIONS_MANAGE',
        'SA_INSTITUTION_ADMINS_MANAGE',
        'SA_INSTITUTION_ADMINS_TOGGLE_STATUS',
        'SA_SYSTEM_USERS_MANAGE',
        'SA_ROLES_VIEW',
        'SA_ROLES_MANAGE',
        'SA_PERMISSIONS_VIEW',
        'SA_PERMISSIONS_MANAGE',
    ];

    public function run(): void
    {
        $saAdminPermissionCodes = Permission::query()
            ->where('code', 'like', 'SA_%')
            ->whereNotIn('code', self::SA_ADMIN_EXCLUDED_PERMISSION_CODES)
            ->where(function ($query): void {
                $query->where('code', 'not like', '%_CREATE')
                    ->where('code', 'not like', '%_UPDATE')
                    ->where('code', 'not like', '%_DELETE')
                    ->where('code', 'not like', '%_TOGGLE_STATUS');
            })
            ->orderBy('code')
            ->pluck('code')
            ->all();

        foreach ([
            [
                'code' => 'SA_ADMIN',
                'name' => 'Administrateur SA',
                'description' => 'Role administrateur interne avec toutes les permissions SA sauf la gestion CRUD des utilisateurs internes, roles et permissions.',
                'permission_codes' => array_merge($saAdminPermissionCodes, [
                    'SA_SCOPED_ROLES_MANAGE',
                    'SA_SCOPED_USERS_MANAGE',
                ]),
                'sync_permissions_on_create_only' => true,
            ],
            [
                'code' => 'CALLCENTER',
                'name' => 'Call center',
                'description' => 'Role interne dedie a l assistance des usagers publics.',
                'permission_codes' => [
                    'SA_ACCESS_PORTAL',
                    'SA_DASHBOARD_VIEW',
                    'SA_PUBLIC_USERS_VIEW',
                    'SA_PUBLIC_USERS_CREATE',
                    'SA_PUBLIC_USERS_UPDATE',
                    'SA_PUBLIC_REPORTS_VIEW',
                    'SA_PAYMENTS_VIEW',
                    'SA_PRIVILEGE_CARD_TYPES_VIEW',
                    'SA_UP_SUBSCRIPTIONS_VIEW',
                    'SA_DISCOUNT_CARDS_VIEW',
                    'SA_DISCOUNT_TRANSACTIONS_VIEW',
                    'SA_REX_FEEDBACKS_VIEW',
                    'SA_ACTIVITY_LOGS_VIEW_SELF',
                ],
            ],
            [
                'code' => 'HUISSIER',
                'name' => 'Huissier',
                'description' => 'Role dedie au constat des faits et a la production du rapport de constat.',
                'permission_codes' => [
                    'SA_ACCESS_PORTAL',
                    'BO_REPARATION_CASES_HUISSIER',
                    'SA_ACTIVITY_LOGS_VIEW_SELF',
                ],
            ],
            [
                'code' => 'AODA',
                'name' => 'Admin ordre des avocats',
                'description' => 'Role dedie a l attribution des dossiers termines par huissier aux avocats.',
                'permission_codes' => [
                    'SA_ACCESS_PORTAL',
                    'BO_REPARATION_CASES_AODA',
                    'SA_PUBLIC_USERS_VIEW',
                    'SA_PUBLIC_REPORTS_VIEW',
                    'SA_ACTIVITY_LOGS_VIEW_SELF',
                ],
            ],
            [
                'code' => 'AVOCAT',
                'name' => 'Avocat',
                'description' => 'Role dedie au suivi de la procedure judiciaire et des actes contentieux.',
                'permission_codes' => [
                    'SA_ACCESS_PORTAL',
                    'BO_REPARATION_CASES_AVOCAT',
                    'SA_ACTIVITY_LOGS_VIEW_SELF',
                ],
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
                    'PARTNER_USERS_MANAGE',
                    'PARTNER_USERS_CREATE',
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

            if (
                ($roleData['sync_permissions_on_create_only'] ?? false)
                && ! $role->wasRecentlyCreated
            ) {
                continue;
            }

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
