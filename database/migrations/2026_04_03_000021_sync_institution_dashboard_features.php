<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
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
        ];

        foreach ($features as $feature) {
            DB::table('features')->updateOrInsert(
                ['code' => $feature['code']],
                [
                    'name' => $feature['name'],
                    'description' => $feature['description'],
                    'status' => 'active',
                    'updated_at' => now(),
                    'created_at' => now(),
                ],
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};