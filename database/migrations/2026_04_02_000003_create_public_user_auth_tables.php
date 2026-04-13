<?php

use App\Domain\Auth\Enums\PublicUserStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('public_users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('phone', 20)->unique();
            $table->string('email')->nullable()->unique();
            $table->string('commune', 120);
            $table->string('password');
            $table->timestamp('phone_verified_at')->nullable();
            $table->string('status', 30)->default(PublicUserStatus::Active->value)->index();
            $table->timestamp('last_login_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('public_user_otps', function (Blueprint $table) {
            $table->id();
            $table->string('phone', 20)->index();
            $table->string('code');
            $table->string('purpose', 50)->index();
            $table->dateTime('expires_at');
            $table->dateTime('verified_at')->nullable();
            $table->unsignedSmallInteger('attempts')->default(0);
            $table->unsignedSmallInteger('max_attempts')->default(5);
            $table->timestamps();
        });

        Schema::create('public_user_phone_verifications', function (Blueprint $table) {
            $table->id();
            $table->string('phone', 20)->index();
            $table->uuid('token')->unique();
            $table->dateTime('verified_at');
            $table->dateTime('expires_at');
            $table->dateTime('used_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('public_user_phone_verifications');
        Schema::dropIfExists('public_user_otps');
        Schema::dropIfExists('public_users');
    }
};
