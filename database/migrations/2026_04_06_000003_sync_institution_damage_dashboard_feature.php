<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('features')->updateOrInsert(
            ['code' => 'INSTITUTION_DASHBOARD_DAMAGE_DECLARATIONS'],
            [
                'name' => 'Graphe dashboard - Dommages declares',
                'description' => 'Affiche le graphe de tendance des dommages declares par les usagers publics apres resolution.',
                'status' => 'active',
                'updated_at' => now(),
                'created_at' => now(),
            ],
        );
    }

    public function down(): void
    {
        //
    }
};
