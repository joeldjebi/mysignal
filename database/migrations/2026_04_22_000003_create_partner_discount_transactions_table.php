<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partner_discount_transactions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('up_discount_card_id')->constrained('up_discount_cards')->restrictOnDelete();
            $table->foreignId('partner_discount_offer_id')->constrained('partner_discount_offers')->restrictOnDelete();
            $table->foreignId('organization_id')->constrained()->restrictOnDelete();
            $table->foreignId('partner_user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('public_user_id')->constrained('public_users')->restrictOnDelete();
            $table->foreignId('up_subscription_id')->constrained('up_subscriptions')->restrictOnDelete();
            $table->string('scan_reference', 80)->unique();
            $table->string('verification_status', 30)->default('verified');
            $table->string('status', 30)->default('validated')->index();
            $table->decimal('original_amount', 12, 2)->nullable();
            $table->decimal('discount_amount', 12, 2)->nullable();
            $table->decimal('final_amount', 12, 2)->nullable();
            $table->string('discount_type_snapshot', 30)->nullable();
            $table->decimal('discount_value_snapshot', 12, 2)->nullable();
            $table->timestamp('applied_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['organization_id', 'status']);
            $table->index(['partner_user_id', 'status']);
            $table->index(['up_discount_card_id', 'status']);
            $table->index('applied_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partner_discount_transactions');
    }
};
