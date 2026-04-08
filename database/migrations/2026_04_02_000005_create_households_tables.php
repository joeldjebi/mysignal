<?php

use App\Domain\Households\Enums\HouseholdMemberStatus;
use App\Domain\Households\Enums\HouseholdStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('households', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_public_user_id')->constrained('public_users')->cascadeOnDelete();
            $table->string('name', 120)->nullable();
            $table->string('commune', 120)->nullable();
            $table->string('address')->nullable();
            $table->string('status', 30)->default(HouseholdStatus::Active->value)->index();
            $table->timestamps();
        });

        Schema::create('household_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('household_id')->constrained()->cascadeOnDelete();
            $table->foreignId('public_user_id')->constrained('public_users')->cascadeOnDelete();
            $table->string('relationship', 50);
            $table->boolean('is_owner')->default(false)->index();
            $table->string('status', 30)->default(HouseholdMemberStatus::Active->value)->index();
            $table->timestamp('joined_at')->nullable();
            $table->timestamps();

            $table->unique(['household_id', 'public_user_id']);
        });

        Schema::create('household_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('household_id')->constrained()->cascadeOnDelete();
            $table->string('phone', 20)->index();
            $table->string('relationship', 50);
            $table->string('code', 6);
            $table->timestamp('expires_at');
            $table->timestamp('accepted_at')->nullable();
            $table->foreignId('invited_by')->constrained('public_users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('household_invitations');
        Schema::dropIfExists('household_members');
        Schema::dropIfExists('households');
    }
};
