<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('features')->updateOrInsert(
            ['code' => 'INSTITUTION_REPORT_DAMAGE_RESOLUTION'],
            [
                'name' => 'Signalements - Resolution des dommages',
                'description' => 'Permet aux admins institutionnels de traiter et mettre a jour les statuts de resolution des dommages declares.',
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
