<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('permissions')->updateOrInsert(
            ['code' => 'SA_PUBLIC_REPORTS_DELETE'],
            [
                'name' => 'Supprimer signalements publics',
                'description' => 'Permet de supprimer un signalement public et ses donnees liees.',
                'profile_scope' => 'super_admin',
                'category' => 'reports',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );
    }

    public function down(): void
    {
        DB::table('permissions')->where('code', 'SA_PUBLIC_REPORTS_DELETE')->delete();
    }
};
