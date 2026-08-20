<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('call_center_contacts', function (Blueprint $table): void {
            $table->id();
            $table->morphs('contactable');
            $table->foreignId('public_user_id')->nullable()->constrained('public_users')->nullOnDelete();
            $table->foreignId('called_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('context', 40)->index();
            $table->text('comment')->nullable();
            $table->timestamp('called_at')->index();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['public_user_id', 'context', 'called_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('call_center_contacts');
    }
};
