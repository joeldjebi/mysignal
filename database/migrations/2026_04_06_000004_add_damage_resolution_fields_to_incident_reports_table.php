<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('incident_reports', function (Blueprint $table): void {
            $table->string('damage_resolution_status')->nullable()->after('damage_declared_at');
            $table->text('damage_resolution_notes')->nullable()->after('damage_resolution_status');
            $table->timestamp('damage_resolved_at')->nullable()->after('damage_resolution_notes');
        });

        DB::table('incident_reports')
            ->whereNotNull('damage_declared_at')
            ->whereNull('damage_resolution_status')
            ->update([
                'damage_resolution_status' => 'submitted',
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        Schema::table('incident_reports', function (Blueprint $table): void {
            $table->dropColumn([
                'damage_resolution_status',
                'damage_resolution_notes',
                'damage_resolved_at',
            ]);
        });
    }
};
