<?php

namespace Database\Seeders\Reference;

use App\Models\Feature;
use Illuminate\Database\Seeder;

class FeatureSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $features = [
            [
                'code' => 'PUBLIC_METERS',
                'name' => 'Compteurs',
                'description' => 'Acces au module de gestion des compteurs des usagers publics.',
            ],
            [
                'code' => 'PUBLIC_REPORTS',
                'name' => 'Signalements',
                'description' => 'Acces a la file des signalements et a leur consultation detaillee.',
            ],
            [
                'code' => 'PUBLIC_REPORT_STATISTICS',
                'name' => 'Statistiques des signalements',
                'description' => 'Acces aux statistiques et indicateurs autour des signalements publics.',
            ],
            [
                'code' => 'INSTITUTION_SLA_ACCESS',
                'name' => 'Consultation des TCM',
                'description' => 'Acces au referentiel des TCM cibles programmes pour le type d organisation.',
            ],
            [
                'code' => 'INSTITUTION_SIGNAL_TYPES_ACCESS',
                'name' => 'Parametrage des types de signaux',
                'description' => 'Permet a l institution de creer, modifier et activer les types de signaux de son reseau.',
            ],
            [
                'code' => 'INSTITUTION_REPORT_DAMAGE_ACCESS',
                'name' => 'Signalements - Consultation des dommages',
                'description' => 'Permet aux admins institutionnels de consulter les dommages declares par les usagers apres resolution d un sinistre.',
            ],
            [
                'code' => 'INSTITUTION_REPORT_DAMAGE_RESOLUTION',
                'name' => 'Signalements - Resolution des dommages',
                'description' => 'Permet aux admins institutionnels de traiter et mettre a jour les statuts de resolution des dommages declares.',
            ],
            [
                'code' => 'PUBLIC_REPORT_USERS',
                'name' => 'Usagers publics',
                'description' => 'Acces a la liste des usagers publics avec filtre sur presence ou absence de signalement.',
            ],
            [
                'code' => 'INSTITUTION_PAYMENT_INFO',
                'name' => 'Infos de paiement',
                'description' => 'Affiche les statuts, montants, compteurs et tableaux lies aux paiements dans le portail institutionnel.',
            ],
            [
                'code' => 'INSTITUTION_MANAGE_USERS',
                'name' => 'Administration institution - Users',
                'description' => 'Permet de creer, modifier, activer ou supprimer les users de l institution.',
            ],
            [
                'code' => 'INSTITUTION_MANAGE_ROLES',
                'name' => 'Administration institution - Roles',
                'description' => 'Permet de creer, modifier, activer ou supprimer les roles locaux de l institution.',
            ],
            [
                'code' => 'INSTITUTION_MANAGE_PERMISSIONS',
                'name' => 'Administration institution - Permissions',
                'description' => 'Permet de consulter et affecter les permissions autorisees par le super admin.',
            ],
            [
                'code' => 'INSTITUTION_ACTIVITY_LOGS',
                'name' => 'Journal d activite',
                'description' => 'Permet a un admin institutionnel de consulter son propre historique d activite.',
            ],
            [
                'code' => 'INSTITUTION_DASHBOARD_REPORTS_TREND',
                'name' => 'Graphe dashboard - Tendance des signalements',
                'description' => 'Affiche le graphe d evolution quotidienne des signalements sur le dashboard institutionnel.',
            ],
            [
                'code' => 'INSTITUTION_DASHBOARD_PAYMENT_BREAKDOWN',
                'name' => 'Graphe dashboard - Repartition des paiements',
                'description' => 'Affiche le graphe de repartition des signalements selon leur etat de paiement.',
            ],
            [
                'code' => 'INSTITUTION_DASHBOARD_TREATMENT_BREAKDOWN',
                'name' => 'Graphe dashboard - Repartition du traitement',
                'description' => 'Affiche le graphe des statuts de traitement institutionnel.',
            ],
            [
                'code' => 'INSTITUTION_DASHBOARD_SLA_BREAKDOWN',
                'name' => 'Graphe dashboard - Etat des TCM',
                'description' => 'Affiche le graphe de conformite des signalements par rapport aux TCM cibles.',
            ],
            [
                'code' => 'INSTITUTION_DASHBOARD_TOP_COMMUNES',
                'name' => 'Graphe dashboard - Top communes',
                'description' => 'Affiche le graphe des communes concentrant le plus de signalements.',
            ],
            [
                'code' => 'INSTITUTION_DASHBOARD_TOP_SIGNALS',
                'name' => 'Graphe dashboard - Top types de signaux',
                'description' => 'Affiche le graphe des types de signaux les plus remontes.',
            ],
            [
                'code' => 'INSTITUTION_DASHBOARD_DAMAGE_DECLARATIONS',
                'name' => 'Graphe dashboard - Dommages declares',
                'description' => 'Affiche le graphe de tendance des dommages declares par les usagers publics apres resolution.',
            ],
            [
                'code' => 'INSTITUTION_DASHBOARD_REPORTS_MAP',
                'name' => 'Dashboard - Carte des signalements',
                'description' => 'Affiche la carte des signalements geolocalises sur le dashboard institutionnel.',
            ],
            [
                'code' => 'REX_FEEDBACKS_VIEW',
                'name' => 'Consultation des REX',
                'description' => 'Permet aux utilisateurs autorises de consulter les retours d experience des usagers publics.',
            ],
            [
                'code' => 'PARTNER_DASHBOARD_ACCESS',
                'name' => 'Dashboard partenaire',
                'description' => 'Acces au tableau de bord web des etablissements partenaires.',
            ],
            [
                'code' => 'PARTNER_DISCOUNT_SCAN',
                'name' => 'Scan des cartes de reduction',
                'description' => 'Permet de verifier une carte de reduction depuis l application mobile partenaire.',
            ],
            [
                'code' => 'PARTNER_DISCOUNT_APPLY',
                'name' => 'Application des reductions',
                'description' => 'Permet d appliquer une reduction validee par un partenaire.',
            ],
            [
                'code' => 'PARTNER_DISCOUNT_HISTORY',
                'name' => 'Historique des reductions partenaires',
                'description' => 'Permet de consulter l historique des reductions appliquees par un partenaire.',
            ],
            [
                'code' => 'PARTNER_DISCOUNT_OFFERS_MANAGE',
                'name' => 'Gestion des offres partenaires',
                'description' => 'Permet de creer et mettre a jour les offres de reduction d un partenaire.',
            ],
            [
                'code' => 'PARTNER_USERS_MANAGE',
                'name' => 'Gestion des utilisateurs partenaires',
                'description' => 'Permet de gerer les comptes web et mobile des etablissements partenaires.',
            ],
            [
                'code' => 'DISCOUNT_CARDS_MONITORING',
                'name' => 'Supervision des cartes de reduction',
                'description' => 'Expose le module de consultation des cartes de reduction creees pour les UP.',
            ],
            [
                'code' => 'DISCOUNT_TRANSACTIONS_MONITORING',
                'name' => 'Supervision des reductions appliquees',
                'description' => 'Expose le module de consultation de l historique des reductions appliquees par les partenaires.',
            ],
        ];

        foreach ($features as $feature) {
            Feature::query()->updateOrCreate(
                ['code' => $feature['code']],
                [
                    'name' => $feature['name'],
                    'description' => $feature['description'],
                    'status' => 'active',
                ],
            );
        }
    }
}
