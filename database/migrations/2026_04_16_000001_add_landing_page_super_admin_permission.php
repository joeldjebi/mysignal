<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('permissions')->updateOrInsert(
            ['code' => 'SA_LANDING_PAGE_MANAGE'],
            [
                'name' => 'Gerer landing page',
                'description' => 'Permet de modifier totalement la landing page publique.',
                'status' => 'active',
                'updated_at' => now(),
                'created_at' => now(),
            ],
        );
    }

    public function down(): void
    {
        DB::table('permissions')->where('code', 'SA_LANDING_PAGE_MANAGE')->delete();
    }
};
