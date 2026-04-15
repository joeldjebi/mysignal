<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rex_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_enabled')->default(true);
            $table->boolean('incident_report_enabled')->default(true);
            $table->boolean('damage_enabled')->default(true);
            $table->boolean('reparation_case_enabled')->default(true);
            $table->unsignedSmallInteger('available_days')->default(30);
            $table->unsignedSmallInteger('editable_hours')->default(24);
            $table->timestamps();
        });

        Schema::create('rex_feedbacks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('public_user_id')->constrained('public_users')->cascadeOnDelete();
            $table->foreignId('incident_report_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('application_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('organization_id')->nullable()->constrained()->nullOnDelete();
            $table->string('context_type', 40);
            $table->unsignedBigInteger('context_id');
            $table->unsignedTinyInteger('rating');
            $table->boolean('is_resolved')->nullable();
            $table->unsignedTinyInteger('response_time_rating')->nullable();
            $table->unsignedTinyInteger('communication_rating')->nullable();
            $table->unsignedTinyInteger('quality_rating')->nullable();
            $table->unsignedTinyInteger('fairness_rating')->nullable();
            $table->text('comment')->nullable();
            $table->string('status', 30)->default('submitted')->index();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->unique(['public_user_id', 'context_type', 'context_id'], 'rex_feedbacks_unique_context_per_user');
            $table->index(['context_type', 'context_id']);
            $table->index(['application_id', 'organization_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rex_feedbacks');
        Schema::dropIfExists('rex_settings');
    }
};
