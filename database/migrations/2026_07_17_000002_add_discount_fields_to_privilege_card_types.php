<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('privilege_card_types', function (Blueprint $table): void {
            $table->string('discount_type', 30)
                ->default('percentage')
                ->after('benefits');
            $table->decimal('discount_value', 12, 2)
                ->default(0)
                ->after('discount_type');
        });
    }

    public function down(): void
    {
        Schema::table('privilege_card_types', function (Blueprint $table): void {
            $table->dropColumn(['discount_type', 'discount_value']);
        });
    }
};
