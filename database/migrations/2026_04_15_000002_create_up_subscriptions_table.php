<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('up_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('public_user_id')->constrained('public_users')->cascadeOnDelete();
            $table->foreignId('subscription_plan_id')->constrained()->restrictOnDelete();
            $table->foreignId('renewed_from_subscription_id')->nullable()->constrained('up_subscriptions')->nullOnDelete();
            $table->string('status', 30)->index();
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->unsignedSmallInteger('grace_period_days')->default(1);
            $table->unsignedInteger('amount');
            $table->string('currency', 10)->default('FCFA');
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('suspended_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['public_user_id', 'status']);
            $table->index('end_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('up_subscriptions');
    }
};
