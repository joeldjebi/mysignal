<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('institution_activation_letters', function (Blueprint $table): void {
            $table->string('footer_logo_path')->nullable()->after('signature_path');
        });
    }

    public function down(): void
    {
        Schema::table('institution_activation_letters', function (Blueprint $table): void {
            $table->dropColumn('footer_logo_path');
        });
    }
};
