<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('incident_reports', function (Blueprint $table): void {
            $table->boolean('resolution_confirmed_without_ai_validation')
                ->default(false)
                ->after('resolution_confirmed_at')
                ->index();
        });
    }

    public function down(): void
    {
        Schema::table('incident_reports', function (Blueprint $table): void {
            $table->dropColumn('resolution_confirmed_without_ai_validation');
        });
    }
};
