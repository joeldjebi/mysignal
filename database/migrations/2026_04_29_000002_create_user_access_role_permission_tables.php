<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_access_role', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_access_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_access_id', 'role_id']);
        });

        Schema::create('permission_user_access', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_access_id')->constrained()->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_access_id', 'permission_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permission_user_access');
        Schema::dropIfExists('user_access_role');
    }
};
