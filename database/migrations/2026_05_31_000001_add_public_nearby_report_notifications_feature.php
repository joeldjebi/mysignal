<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const FEATURE_CODE = 'PUBLIC_NEARBY_REPORT_NOTIFICATIONS';

    public function up(): void
    {
        DB::table('features')->updateOrInsert(
            ['code' => self::FEATURE_CODE],
            [
                'name' => 'Notifications UP de proximite',
                'description' => 'Envoie une notification aux UP situes dans un rayon de 1 km lorsqu un signalement compatible est cree.',
                'status' => 'active',
                'updated_at' => now(),
                'created_at' => now(),
            ],
        );
    }

    public function down(): void
    {
        DB::table('features')->where('code', self::FEATURE_CODE)->delete();
    }
};
