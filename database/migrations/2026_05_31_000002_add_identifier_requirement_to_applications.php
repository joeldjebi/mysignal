<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table): void {
            $table->boolean('requires_public_user_identifier')
                ->default(true)
                ->after('sort_order');
        });

        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('incident_reports', function (Blueprint $table): void {
            $table->dropForeign(['meter_id']);
        });

        DB::statement('ALTER TABLE incident_reports ALTER COLUMN meter_id DROP NOT NULL');

        Schema::table('incident_reports', function (Blueprint $table): void {
            $table->foreign('meter_id')
                ->references('id')
                ->on('meters')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            Schema::table('incident_reports', function (Blueprint $table): void {
                $table->dropForeign(['meter_id']);
            });

            Schema::table('incident_reports', function (Blueprint $table): void {
                $table->foreign('meter_id')
                    ->references('id')
                    ->on('meters')
                    ->cascadeOnDelete();
            });
        }

        Schema::table('applications', function (Blueprint $table): void {
            $table->dropColumn('requires_public_user_identifier');
        });
    }
};
