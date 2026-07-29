<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('signal_types', function (Blueprint $table): void {
            if (! Schema::hasColumn('signal_types', 'requires_public_user_identifier')) {
                $table->boolean('requires_public_user_identifier')
                    ->default(false)
                    ->after('default_sla_hours');
            }
        });
    }

    public function down(): void
    {
        Schema::table('signal_types', function (Blueprint $table): void {
            if (Schema::hasColumn('signal_types', 'requires_public_user_identifier')) {
                $table->dropColumn('requires_public_user_identifier');
            }
        });
    }
};
