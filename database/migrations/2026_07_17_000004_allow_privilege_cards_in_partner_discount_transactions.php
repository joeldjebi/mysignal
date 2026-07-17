<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('partner_discount_transactions', function (Blueprint $table): void {
            $table->foreignId('privilege_card_id')
                ->nullable()
                ->after('up_discount_card_id')
                ->constrained('privilege_cards')
                ->restrictOnDelete();
            $table->string('card_source', 40)
                ->default('up_discount_card')
                ->after('privilege_card_id');
            $table->index(['privilege_card_id', 'status']);
            $table->index(['card_source', 'status']);
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE partner_discount_transactions ALTER COLUMN up_discount_card_id DROP NOT NULL');
            DB::statement('ALTER TABLE partner_discount_transactions ALTER COLUMN partner_discount_offer_id DROP NOT NULL');
            DB::statement('ALTER TABLE partner_discount_transactions ALTER COLUMN up_subscription_id DROP NOT NULL');
        }
    }

    public function down(): void
    {
        Schema::table('partner_discount_transactions', function (Blueprint $table): void {
            $table->dropIndex(['privilege_card_id', 'status']);
            $table->dropIndex(['card_source', 'status']);
            $table->dropConstrainedForeignId('privilege_card_id');
            $table->dropColumn('card_source');
        });
    }
};
