<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incident_report_notification_contexts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('incident_report_id')->constrained()->cascadeOnDelete();
            $table->string('context_type', 30)->index();
            $table->foreignId('household_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('organization_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('meter_id')->nullable()->constrained()->nullOnDelete();
            $table->string('signal_code', 80)->nullable()->index();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->unsignedInteger('radius_meters')->nullable();
            $table->json('recipient_public_user_ids')->nullable();
            $table->timestamp('notified_at')->nullable()->index();
            $table->timestamp('resolved_notified_at')->nullable()->index();
            $table->timestamps();

            $table->index(['context_type', 'household_id', 'meter_id'], 'incident_context_household_idx');
            $table->index(['context_type', 'organization_id', 'signal_code'], 'incident_context_nearby_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incident_report_notification_contexts');
    }
};
