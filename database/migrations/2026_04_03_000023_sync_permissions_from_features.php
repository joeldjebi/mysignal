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
        $features = DB::table('features')->get(['code', 'name', 'description', 'status']);

        foreach ($features as $feature) {
            DB::table('permissions')->updateOrInsert(
                ['code' => $feature->code],
                [
                    'name' => $feature->name,
                    'description' => $feature->description,
                    'status' => $feature->status ?: 'active',
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
