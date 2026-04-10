<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_log_user_accesses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('viewer_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('target_user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['viewer_user_id', 'target_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_log_user_accesses');
    }
};
