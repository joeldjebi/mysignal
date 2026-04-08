<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organization_type_signal_slas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_type_id')->constrained()->cascadeOnDelete();
            $table->string('network_type', 20)->index();
            $table->string('signal_code', 30)->index();
            $table->string('signal_label', 180);
            $table->unsignedInteger('sla_hours');
            $table->text('description')->nullable();
            $table->string('status', 30)->default('active')->index();
            $table->timestamps();

            $table->unique(['organization_type_id', 'signal_code'], 'org_type_signal_sla_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organization_type_signal_slas');
    }
};
