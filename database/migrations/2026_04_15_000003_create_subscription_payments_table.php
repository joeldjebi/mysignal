<?php

use App\Domain\Payments\Enums\PaymentStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('public_user_id')->constrained('public_users')->cascadeOnDelete();
            $table->foreignId('up_subscription_id')->constrained('up_subscriptions')->cascadeOnDelete();
            $table->string('reference', 40)->unique();
            $table->unsignedInteger('amount');
            $table->string('currency', 10)->default('FCFA');
            $table->string('status', 30)->default(PaymentStatus::Pending->value)->index();
            $table->string('provider', 30)->default('simulated');
            $table->string('provider_reference')->nullable();
            $table->timestamp('initiated_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['public_user_id', 'status']);
            $table->index('up_subscription_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_payments');
    }
};
