<?php

namespace Database\Seeders\Reference;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class SuperAdminPermissionSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['code' => 'SA_ACCESS_PORTAL', 'name' => 'Acceder au portail SA', 'description' => 'Permet de se connecter et d acceder au back office super admin.'],
            ['code' => 'SA_DASHBOARD_VIEW', 'name' => 'Voir dashboard SA', 'description' => 'Permet de consulter le dashboard super admin.'],
            ['code' => 'SA_COUNTRIES_MANAGE', 'name' => 'Gerer pays', 'description' => 'Permet de gerer les pays.'],
            ['code' => 'SA_CITIES_MANAGE', 'name' => 'Gerer villes', 'description' => 'Permet de gerer les villes.'],
            ['code' => 'SA_COMMUNES_MANAGE', 'name' => 'Gerer communes', 'description' => 'Permet de gerer les communes.'],
            ['code' => 'SA_BUSINESS_SECTORS_MANAGE', 'name' => 'Gerer secteurs', 'description' => 'Permet de gerer les secteurs d activite.'],
            ['code' => 'SA_ORGANIZATION_TYPES_MANAGE', 'name' => 'Gerer types organisation', 'description' => 'Permet de gerer les types d organisation.'],
            ['code' => 'SA_FEATURES_MANAGE', 'name' => 'Gerer fonctionnalites', 'description' => 'Permet de gerer les fonctionnalites.'],
            ['code' => 'SA_APPLICATIONS_MANAGE', 'name' => 'Gerer applications', 'description' => 'Permet de gerer les applications.'],
            ['code' => 'SA_SIGNAL_TYPES_MANAGE', 'name' => 'Gerer types de signaux', 'description' => 'Permet de gerer les types de signaux.'],
            ['code' => 'SA_SLA_POLICIES_MANAGE', 'name' => 'Gerer SLA', 'description' => 'Permet de gerer les SLA cibles.'],
            ['code' => 'SA_ORGANIZATIONS_MANAGE', 'name' => 'Gerer organisations', 'description' => 'Permet de gerer les organisations.'],
            ['code' => 'SA_INSTITUTION_ADMINS_MANAGE', 'name' => 'Gerer admins institutionnels', 'description' => 'Permet de gerer les admins institutionnels.'],
            ['code' => 'SA_PRICING_MANAGE', 'name' => 'Gerer tarification', 'description' => 'Permet de gerer la tarification.'],
            ['code' => 'SA_PUBLIC_USER_TYPES_MANAGE', 'name' => 'Gerer types usagers publics', 'description' => 'Permet de gerer les types d usagers publics.'],
            ['code' => 'SA_PUBLIC_USERS_MANAGE', 'name' => 'Gerer usagers publics', 'description' => 'Permet de gerer les usagers publics.'],
            ['code' => 'SA_PUBLIC_USERS_TOGGLE_STATUS', 'name' => 'Activer ou desactiver usagers publics', 'description' => 'Permet d activer ou desactiver les comptes des usagers publics.'],
            ['code' => 'SA_PUBLIC_REPORTS_VIEW', 'name' => 'Voir signalements publics', 'description' => 'Permet de consulter la liste des signalements des usagers publics.'],
            ['code' => 'SA_PAYMENTS_VIEW', 'name' => 'Voir historique des paiements', 'description' => 'Permet de consulter l historique des paiements effectues par les usagers publics.'],
            ['code' => 'SA_REPARATION_CASES_MANAGE', 'name' => 'Gerer dossiers contentieux', 'description' => 'Permet de gerer les dossiers contentieux et de reparation.'],
            ['code' => 'SA_ROLES_MANAGE', 'name' => 'Gerer roles SA', 'description' => 'Permet de gerer les roles.'],
            ['code' => 'SA_PERMISSIONS_MANAGE', 'name' => 'Gerer permissions SA', 'description' => 'Permet de gerer les permissions.'],
            ['code' => 'SA_SYSTEM_USERS_MANAGE', 'name' => 'Gerer utilisateurs internes', 'description' => 'Permet de gerer les utilisateurs internes du portail SA.'],
            ['code' => 'SA_SYSTEM_USERS_TOGGLE_STATUS', 'name' => 'Activer ou desactiver utilisateurs internes', 'description' => 'Permet d activer ou desactiver les utilisateurs internes du portail SA.'],
            ['code' => 'SA_INSTITUTION_ADMINS_TOGGLE_STATUS', 'name' => 'Activer ou desactiver admins institutionnels', 'description' => 'Permet d activer ou desactiver les comptes admins institutionnels.'],
        ] as $permission) {
            Permission::query()->updateOrCreate(
                ['code' => $permission['code']],
                [
                    'name' => $permission['name'],
                    'description' => $permission['description'],
                    'status' => 'active',
                ]
            );
        }
    }
}
