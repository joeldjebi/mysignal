<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('institution_activation_letters', function (Blueprint $table): void {
            $table->json('header_settings')->nullable()->after('logo_path');
            $table->json('footer_settings')->nullable()->after('header_settings');
        });
    }

    public function down(): void
    {
        Schema::table('institution_activation_letters', function (Blueprint $table): void {
            $table->dropColumn(['header_settings', 'footer_settings']);
        });
    }
};
