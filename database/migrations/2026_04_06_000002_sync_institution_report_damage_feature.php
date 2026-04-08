<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('features')->updateOrInsert(
            ['code' => 'INSTITUTION_REPORT_DAMAGE_ACCESS'],
            [
                'name' => 'Signalements - Consultation des dommages',
                'description' => 'Permet aux admins institutionnels de consulter les dommages declares par les usagers apres resolution d un sinistre.',
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
