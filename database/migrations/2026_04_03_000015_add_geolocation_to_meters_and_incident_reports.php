<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('meters', function (Blueprint $table) {
            $table->decimal('latitude', 10, 7)->nullable()->after('address');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->unsignedSmallInteger('location_accuracy')->nullable()->after('longitude');
            $table->string('location_source', 30)->nullable()->after('location_accuracy');
        });

        Schema::table('incident_reports', function (Blueprint $table) {
            $table->decimal('latitude', 10, 7)->nullable()->after('commune_id');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->unsignedSmallInteger('location_accuracy')->nullable()->after('longitude');
            $table->string('location_source', 30)->nullable()->after('location_accuracy');
        });
    }

    public function down(): void
    {
        Schema::table('incident_reports', function (Blueprint $table) {
            $table->dropColumn([
                'latitude',
                'longitude',
                'location_accuracy',
                'location_source',
            ]);
        });

        Schema::table('meters', function (Blueprint $table) {
            $table->dropColumn([
                'latitude',
                'longitude',
                'location_accuracy',
                'location_source',
            ]);
        });
    }
};
