<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reparation_cases', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('incident_report_id')->constrained()->cascadeOnDelete();
            $table->foreignId('public_user_id')->constrained()->restrictOnDelete();
            $table->foreignId('application_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('organization_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('opened_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('assigned_to_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reference', 60)->unique();
            $table->string('status', 40)->default('submitted')->index();
            $table->string('eligibility_reason', 60)->index();
            $table->text('opening_notes')->nullable();
            $table->text('damage_summary')->nullable();
            $table->decimal('damage_amount_claimed', 12, 2)->nullable();
            $table->decimal('damage_amount_validated', 12, 2)->nullable();
            $table->text('resolution_notes')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique('incident_report_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reparation_cases');
    }
};
