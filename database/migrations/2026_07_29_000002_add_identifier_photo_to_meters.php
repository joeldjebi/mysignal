<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('meters', function (Blueprint $table): void {
            if (! Schema::hasColumn('meters', 'identifier_photo_path')) {
                $table->string('identifier_photo_path')->nullable()->after('location_source');
            }
        });
    }

    public function down(): void
    {
        Schema::table('meters', function (Blueprint $table): void {
            if (Schema::hasColumn('meters', 'identifier_photo_path')) {
                $table->dropColumn('identifier_photo_path');
            }
        });
    }
};
