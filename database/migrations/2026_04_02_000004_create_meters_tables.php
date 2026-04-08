<?php

use App\Domain\Meters\Enums\MeterStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meters', function (Blueprint $table) {
            $table->id();
            $table->string('network_type', 20)->index();
            $table->string('meter_number', 50);
            $table->string('label', 120)->nullable();
            $table->string('commune', 120)->nullable();
            $table->string('address')->nullable();
            $table->string('status', 30)->default(MeterStatus::Active->value)->index();
            $table->timestamps();

            $table->unique(['network_type', 'meter_number']);
        });

        Schema::create('meter_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meter_id')->constrained()->cascadeOnDelete();
            $table->foreignId('public_user_id')->constrained('public_users')->cascadeOnDelete();
            $table->boolean('is_primary')->default(false)->index();
            $table->timestamps();

            $table->unique(['meter_id', 'public_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meter_assignments');
        Schema::dropIfExists('meters');
    }
};
