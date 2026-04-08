<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('features')->updateOrInsert(
            ['code' => 'INSTITUTION_DASHBOARD_REPORTS_MAP'],
            [
                'name' => 'Dashboard - Carte des signalements',
                'description' => 'Affiche la carte des signalements geolocalises sur le dashboard institutionnel.',
                'status' => 'active',
                'updated_at' => now(),
                'created_at' => now(),
            ],
        );

        Schema::create('feature_organization', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('feature_id')->constrained()->cascadeOnDelete();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['feature_id', 'organization_id']);
        });

        $assignments = DB::table('feature_user')
            ->join('users', 'users.id', '=', 'feature_user.user_id')
            ->whereNotNull('users.organization_id')
            ->select('feature_user.feature_id', 'users.organization_id')
            ->distinct()
            ->get()
            ->map(fn ($row) => [
                'feature_id' => $row->feature_id,
                'organization_id' => $row->organization_id,
                'created_at' => now(),
                'updated_at' => now(),
            ])
            ->all();

        if ($assignments !== []) {
            DB::table('feature_organization')->insert($assignments);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feature_organization');
    }
};
