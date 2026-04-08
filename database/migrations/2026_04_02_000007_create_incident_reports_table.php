<?php

use App\Domain\Reports\Enums\IncidentReportStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incident_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('public_user_id')->constrained('public_users')->cascadeOnDelete();
            $table->foreignId('meter_id')->constrained()->cascadeOnDelete();
            $table->foreignId('country_id')->constrained()->restrictOnDelete();
            $table->foreignId('city_id')->constrained()->restrictOnDelete();
            $table->foreignId('commune_id')->constrained()->restrictOnDelete();
            $table->string('network_type', 20)->index();
            $table->string('incident_type', 50)->index();
            $table->string('reference', 40)->unique();
            $table->text('description')->nullable();
            $table->timestamp('occurred_at')->nullable();
            $table->string('status', 30)->default(IncidentReportStatus::Submitted->value)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incident_reports');
    }
};
