<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('features')->updateOrInsert(
            ['code' => 'INSTITUTION_SIGNAL_TYPES_ACCESS'],
            [
                'name' => 'Parametrage des types de signaux',
                'description' => 'Permet a l institution de creer, modifier et activer les types de signaux de son reseau.',
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
