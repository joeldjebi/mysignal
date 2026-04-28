<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('super_admin_push_notifications', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('sent_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('target_scope', 30)->index();
            $table->string('status', 30)->index();
            $table->string('title', 120);
            $table->text('body');
            $table->unsignedInteger('requested_count')->default(0);
            $table->unsignedInteger('sent_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->json('target_user_ids')->nullable();
            $table->json('sent_user_ids')->nullable();
            $table->json('failed_user_ids')->nullable();
            $table->timestamp('sent_at')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('super_admin_push_notifications');
    }
};
