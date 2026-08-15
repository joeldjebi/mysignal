<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('super_admin_push_notifications', function (Blueprint $table): void {
            $table->json('target_filters')->nullable()->after('requested_count');
        });
    }

    public function down(): void
    {
        Schema::table('super_admin_push_notifications', function (Blueprint $table): void {
            $table->dropColumn('target_filters');
        });
    }
};
