<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('incident_reports', function (Blueprint $table) {
            $table->foreignId('assigned_to_user_id')->nullable()->after('public_user_id')->constrained('users')->nullOnDelete();
            $table->timestamp('taken_in_charge_at')->nullable()->after('paid_at');
            $table->timestamp('resolved_at')->nullable()->after('taken_in_charge_at');
            $table->text('official_response')->nullable()->after('resolved_at');
        });
    }

    public function down(): void
    {
        Schema::table('incident_reports', function (Blueprint $table) {
            $table->dropConstrainedForeignId('assigned_to_user_id');
            $table->dropColumn([
                'taken_in_charge_at',
                'resolved_at',
                'official_response',
            ]);
        });
    }
};
