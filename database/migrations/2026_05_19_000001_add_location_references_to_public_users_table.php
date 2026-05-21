<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('public_users', function (Blueprint $table) {
            $table->foreignId('country_id')->nullable()->after('email')->constrained()->nullOnDelete();
            $table->foreignId('city_id')->nullable()->after('country_id')->constrained()->nullOnDelete();
            $table->foreignId('commune_id')->nullable()->after('city_id')->constrained()->nullOnDelete();
            $table->string('country', 120)->nullable()->after('commune');
            $table->string('city', 120)->nullable()->after('country');
        });
    }

    public function down(): void
    {
        Schema::table('public_users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('commune_id');
            $table->dropConstrainedForeignId('city_id');
            $table->dropConstrainedForeignId('country_id');
            $table->dropColumn(['country', 'city']);
        });
    }
};
