<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('device_tokens', function (Blueprint $table): void {
            $table->id();
            $table->string('recipient_type', 30)->index();
            $table->unsignedBigInteger('recipient_id')->index();
            $table->string('guard', 40)->index();
            $table->text('token');
            $table->string('token_hash', 64)->unique();
            $table->string('platform', 20)->nullable()->index();
            $table->string('device_name', 120)->nullable();
            $table->string('app_version', 40)->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamp('revoked_at')->nullable()->index();
            $table->timestamps();

            $table->index(['recipient_type', 'recipient_id', 'revoked_at'], 'device_tokens_recipient_active_idx');
        });

        Schema::create('user_notifications', function (Blueprint $table): void {
            $table->id();
            $table->string('recipient_type', 30)->index();
            $table->unsignedBigInteger('recipient_id')->index();
            $table->string('type', 80)->index();
            $table->string('title', 180);
            $table->text('body')->nullable();
            $table->json('data')->nullable();
            $table->timestamp('read_at')->nullable()->index();
            $table->timestamps();

            $table->index(['recipient_type', 'recipient_id', 'read_at'], 'user_notifications_recipient_read_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_notifications');
        Schema::dropIfExists('device_tokens');
    }
};
