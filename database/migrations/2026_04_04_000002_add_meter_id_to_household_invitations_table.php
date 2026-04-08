<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('household_invitations', function (Blueprint $table): void {
            $table->foreignId('meter_id')->nullable()->after('household_id')->constrained('meters')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('household_invitations', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('meter_id');
        });
    }
};
