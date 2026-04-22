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
            ['code' => 'SA_LANDING_PAGE_MANAGE', 'name' => 'Gerer landing page', 'description' => 'Permet de modifier totalement la landing page publique.'],
            ['code' => 'SA_COUNTRIES_MANAGE', 'name' => 'Gerer pays', 'description' => 'Permet de gerer les pays.'],
            ['code' => 'SA_CITIES_MANAGE', 'name' => 'Gerer villes', 'description' => 'Permet de gerer les villes.'],
            ['code' => 'SA_COMMUNES_MANAGE', 'name' => 'Gerer communes', 'description' => 'Permet de gerer les communes.'],
            ['code' => 'SA_BUSINESS_SECTORS_MANAGE', 'name' => 'Gerer secteurs', 'description' => 'Permet de gerer les secteurs d activite.'],
            ['code' => 'SA_ORGANIZATION_TYPES_MANAGE', 'name' => 'Gerer types organisation', 'description' => 'Permet de gerer les types d organisation.'],
            ['code' => 'SA_FEATURES_MANAGE', 'name' => 'Gerer fonctionnalites', 'description' => 'Permet de gerer les fonctionnalites.'],
            ['code' => 'SA_APPLICATIONS_MANAGE', 'name' => 'Gerer applications', 'description' => 'Permet de gerer les applications.'],
            ['code' => 'SA_SIGNAL_TYPES_MANAGE', 'name' => 'Gerer types de signaux', 'description' => 'Permet de gerer les types de signaux.'],
            ['code' => 'SA_SLA_POLICIES_MANAGE', 'name' => 'Gerer TCM', 'description' => 'Permet de gerer les TCM cibles.'],
            ['code' => 'SA_ORGANIZATIONS_MANAGE', 'name' => 'Gerer organisations', 'description' => 'Permet de gerer les organisations.'],
            ['code' => 'SA_INSTITUTION_ADMINS_MANAGE', 'name' => 'Gerer admins institutionnels', 'description' => 'Permet de gerer les admins institutionnels.'],
            ['code' => 'SA_PRICING_MANAGE', 'name' => 'Gerer tarification', 'description' => 'Permet de gerer la tarification.'],
            ['code' => 'SA_SUBSCRIPTION_PLANS_MANAGE', 'name' => 'Gerer plans abonnements UP', 'description' => 'Permet de parametrer les plans d abonnement des usagers publics.'],
            ['code' => 'SA_UP_SUBSCRIPTIONS_VIEW', 'name' => 'Voir abonnements UP', 'description' => 'Permet de consulter l historique des abonnements des usagers publics.'],
            ['code' => 'SA_PARTNER_ORGANIZATIONS_MANAGE', 'name' => 'Gerer etablissements partenaires', 'description' => 'Permet de gerer les etablissements partenaires et leurs activations.'],
            ['code' => 'SA_PARTNER_USERS_MANAGE', 'name' => 'Gerer utilisateurs partenaires', 'description' => 'Permet de gerer les comptes des partenaires.'],
            ['code' => 'SA_DISCOUNT_CARDS_VIEW', 'name' => 'Voir cartes de reduction', 'description' => 'Permet de consulter les cartes de reduction des UP.'],
            ['code' => 'SA_DISCOUNT_OFFERS_MANAGE', 'name' => 'Gerer offres partenaires', 'description' => 'Permet de parametrer les offres de reduction partenaires.'],
            ['code' => 'SA_DISCOUNT_TRANSACTIONS_VIEW', 'name' => 'Voir reductions appliquees', 'description' => 'Permet de consulter l historique des reductions appliquees.'],
            ['code' => 'SA_DISCOUNT_TRANSACTIONS_REVERSE', 'name' => 'Annuler reductions appliquees', 'description' => 'Permet d annuler ou corriger une reduction appliquee.'],
            ['code' => 'SA_PUBLIC_USER_TYPES_MANAGE', 'name' => 'Gerer types usagers publics', 'description' => 'Permet de gerer les types d usagers publics.'],
            ['code' => 'SA_PUBLIC_USERS_MANAGE', 'name' => 'Gerer usagers publics', 'description' => 'Permet de gerer les usagers publics.'],
            ['code' => 'SA_PUBLIC_USERS_TOGGLE_STATUS', 'name' => 'Activer ou desactiver usagers publics', 'description' => 'Permet d activer ou desactiver les comptes des usagers publics.'],
            ['code' => 'SA_PUBLIC_REPORTS_VIEW', 'name' => 'Voir signalements publics', 'description' => 'Permet de consulter la liste des signalements des usagers publics.'],
            ['code' => 'SA_PAYMENTS_VIEW', 'name' => 'Voir historique des paiements', 'description' => 'Permet de consulter l historique des paiements effectues par les usagers publics.'],
            ['code' => 'SA_ACTIVITY_LOGS_VIEW_SELF', 'name' => 'Voir ses activites', 'description' => 'Permet de consulter son propre historique d activite.'],
            ['code' => 'SA_ACTIVITY_LOGS_VIEW_INSTITUTION', 'name' => 'Voir activites AI', 'description' => 'Permet de consulter les activites des admins institutionnels et des collaborateurs institutionnels.'],
            ['code' => 'SA_ACTIVITY_LOGS_VIEW_PUBLIC', 'name' => 'Voir activites UP', 'description' => 'Permet de consulter les activites des usagers publics.'],
            ['code' => 'SA_ACTIVITY_LOGS_VIEW_INTERNAL', 'name' => 'Voir activites utilisateurs internes', 'description' => 'Permet de consulter les activites de certains utilisateurs internes autorises par le super admin.'],
            ['code' => 'SA_REX_FEEDBACKS_VIEW', 'name' => 'Voir REX UP', 'description' => 'Permet de consulter les retours d experience des usagers publics.'],
            ['code' => 'SA_REX_FEEDBACKS_MANAGE', 'name' => 'Parametrer REX UP', 'description' => 'Permet de parametrer le module de retours d experience.'],
            ['code' => 'SA_REPARATION_CASES_MANAGE', 'name' => 'Gerer dossiers contentieux', 'description' => 'Permet de gerer les dossiers contentieux et de reparation.'],
            ['code' => 'SA_ROLES_MANAGE', 'name' => 'Gerer roles SA', 'description' => 'Permet de gerer les roles.'],
            ['code' => 'SA_PERMISSIONS_MANAGE', 'name' => 'Gerer permissions SA', 'description' => 'Permet de gerer les permissions.'],
            ['code' => 'SA_SYSTEM_USERS_MANAGE', 'name' => 'Gerer utilisateurs internes', 'description' => 'Permet de gerer les utilisateurs internes du portail SA.'],
            ['code' => 'SA_SYSTEM_USERS_TOGGLE_STATUS', 'name' => 'Activer ou desactiver utilisateurs internes', 'description' => 'Permet d activer ou desactiver les utilisateurs internes du portail SA.'],
            ['code' => 'SA_INSTITUTION_ADMINS_TOGGLE_STATUS', 'name' => 'Activer ou desactiver admins institutionnels', 'description' => 'Permet d activer ou desactiver les comptes admins institutionnels.'],
            ['code' => 'PARTNER_ACCESS_PORTAL', 'name' => 'Acceder au portail partenaire', 'description' => 'Permet a un utilisateur partenaire de se connecter aux APIs web et mobile du partenaire.'],
            ['code' => 'PARTNER_DASHBOARD_VIEW', 'name' => 'Voir dashboard partenaire', 'description' => 'Permet de consulter le dashboard web partenaire.'],
            ['code' => 'PARTNER_DISCOUNT_SCAN', 'name' => 'Scanner les cartes partenaire', 'description' => 'Permet de verifier une carte de reduction via l application mobile partenaire.'],
            ['code' => 'PARTNER_DISCOUNT_APPLY', 'name' => 'Appliquer une reduction partenaire', 'description' => 'Permet de valider une reduction pour un UP.'],
            ['code' => 'PARTNER_DISCOUNT_HISTORY_VIEW', 'name' => 'Voir historique partenaire', 'description' => 'Permet de consulter l historique des reductions appliquees par le partenaire.'],
            ['code' => 'PARTNER_DISCOUNT_OFFERS_MANAGE', 'name' => 'Gerer offres partenaire', 'description' => 'Permet de gerer les offres de reduction du partenaire.'],
            ['code' => 'PARTNER_USERS_MANAGE', 'name' => 'Gerer utilisateurs partenaire', 'description' => 'Permet de consulter les utilisateurs du partenaire.'],
            ['code' => 'PARTNER_USERS_CREATE', 'name' => 'Creer utilisateurs partenaire', 'description' => 'Permet de creer des comptes web et mobile pour le partenaire.'],
            ['code' => 'PARTNER_USERS_UPDATE', 'name' => 'Modifier utilisateurs partenaire', 'description' => 'Permet de modifier les comptes web et mobile du partenaire.'],
            ['code' => 'PARTNER_USERS_TOGGLE_STATUS', 'name' => 'Activer ou desactiver utilisateurs partenaire', 'description' => 'Permet d activer ou de suspendre les utilisateurs du partenaire.'],
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
