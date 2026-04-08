<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('incident_reports', function (Blueprint $table): void {
            $table->string('resolution_confirmation_status')->nullable()->after('official_response');
            $table->timestamp('resolution_confirmed_at')->nullable()->after('resolution_confirmation_status');
        });
    }

    public function down(): void
    {
        Schema::table('incident_reports', function (Blueprint $table): void {
            $table->dropColumn([
                'resolution_confirmation_status',
                'resolution_confirmed_at',
            ]);
        });
    }
};
