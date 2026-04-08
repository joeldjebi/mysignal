<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('incident_reports', function (Blueprint $table): void {
            $table->string('damage_summary')->nullable()->after('resolution_confirmed_at');
            $table->decimal('damage_amount_estimated', 12, 2)->nullable()->after('damage_summary');
            $table->text('damage_notes')->nullable()->after('damage_amount_estimated');
            $table->json('damage_attachment')->nullable()->after('damage_notes');
            $table->timestamp('damage_declared_at')->nullable()->after('damage_attachment');
        });
    }

    public function down(): void
    {
        Schema::table('incident_reports', function (Blueprint $table): void {
            $table->dropColumn([
                'damage_summary',
                'damage_amount_estimated',
                'damage_notes',
                'damage_attachment',
                'damage_declared_at',
            ]);
        });
    }
};
