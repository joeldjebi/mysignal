<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('signal_types')) {
            Schema::table('signal_types', function (Blueprint $table): void {
                if (! Schema::hasColumn('signal_types', 'data_fields')) {
                    return;
                }

                $table->dropColumn('data_fields');
            });
        }

        if (Schema::hasTable('signal_catalogs')) {
            Schema::table('signal_catalogs', function (Blueprint $table): void {
                if (! Schema::hasColumn('signal_catalogs', 'data_fields')) {
                    return;
                }

                $table->dropColumn('data_fields');
            });
        }

        if (Schema::hasTable('incident_reports')) {
            Schema::table('incident_reports', function (Blueprint $table): void {
                if (! Schema::hasColumn('incident_reports', 'signal_payload')) {
                    return;
                }

                $table->dropColumn('signal_payload');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('signal_types')) {
            Schema::table('signal_types', function (Blueprint $table): void {
                if (Schema::hasColumn('signal_types', 'data_fields')) {
                    return;
                }

                $table->json('data_fields')->nullable();
            });
        }

        if (Schema::hasTable('signal_catalogs')) {
            Schema::table('signal_catalogs', function (Blueprint $table): void {
                if (Schema::hasColumn('signal_catalogs', 'data_fields')) {
                    return;
                }

                $table->json('data_fields')->nullable();
            });
        }

        if (Schema::hasTable('incident_reports')) {
            Schema::table('incident_reports', function (Blueprint $table): void {
                if (Schema::hasColumn('incident_reports', 'signal_payload')) {
                    return;
                }

                $table->json('signal_payload')->nullable()->after('description');
            });
        }
    }
};
