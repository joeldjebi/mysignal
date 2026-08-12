<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('institution_activation_letters', function (Blueprint $table): void {
            $table->string('signature_path')->nullable()->after('signature_content');
        });
    }

    public function down(): void
    {
        Schema::table('institution_activation_letters', function (Blueprint $table): void {
            $table->dropColumn('signature_path');
        });
    }
};
