<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('institution_activation_letters', function (Blueprint $table): void {
            $table->string('logo_path')->nullable()->after('logo_position');
        });
    }

    public function down(): void
    {
        Schema::table('institution_activation_letters', function (Blueprint $table): void {
            $table->dropColumn('logo_path');
        });
    }
};
