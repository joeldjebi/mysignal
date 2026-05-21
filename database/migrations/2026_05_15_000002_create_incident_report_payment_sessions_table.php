<?php

use App\Domain\Payments\Enums\PaymentStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incident_report_payment_sessions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('public_user_id')->constrained('public_users')->cascadeOnDelete();
            $table->foreignId('pricing_rule_id')->constrained()->restrictOnDelete();
            $table->foreignId('incident_report_id')->nullable()->constrained()->nullOnDelete();
            $table->string('sync_ref', 60)->unique();
            $table->unsignedInteger('amount');
            $table->string('currency', 10)->default('FCFA');
            $table->string('status', 30)->default(PaymentStatus::Pending->value)->index();
            $table->string('provider', 30)->default('fineopay');
            $table->string('provider_reference')->nullable();
            $table->text('checkout_link')->nullable();
            $table->string('payment_context', 30)->default('report')->index();
            $table->json('report_payload');
            $table->json('signal_attachment')->nullable();
            $table->json('damage_payload')->nullable();
            $table->json('damage_attachment')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('initiated_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['public_user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incident_report_payment_sessions');
    }
};
