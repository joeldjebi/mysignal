<?php

use App\Domain\Payments\Enums\PaymentStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pricing_rules', function (Blueprint $table) {
            $table->id();
            $table->string('code', 60)->unique();
            $table->string('label');
            $table->unsignedInteger('amount');
            $table->string('currency', 10)->default('FCFA');
            $table->string('status', 30)->default('active')->index();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('public_user_id')->constrained('public_users')->cascadeOnDelete();
            $table->foreignId('incident_report_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pricing_rule_id')->constrained()->restrictOnDelete();
            $table->string('reference', 40)->unique();
            $table->unsignedInteger('amount');
            $table->string('currency', 10)->default('FCFA');
            $table->string('status', 30)->default(PaymentStatus::Pending->value)->index();
            $table->string('provider', 30)->default('simulated');
            $table->string('provider_reference')->nullable();
            $table->timestamp('initiated_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('pricing_rules');
    }
};
