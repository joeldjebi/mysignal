<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partner_discount_offers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('code', 60)->unique();
            $table->string('name', 180);
            $table->text('description')->nullable();
            $table->string('discount_type', 30);
            $table->decimal('discount_value', 12, 2)->nullable();
            $table->string('currency', 10)->nullable();
            $table->decimal('minimum_purchase_amount', 12, 2)->nullable();
            $table->decimal('maximum_discount_amount', 12, 2)->nullable();
            $table->unsignedInteger('max_uses_per_card')->nullable();
            $table->unsignedInteger('max_uses_per_day')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->string('status', 30)->default('draft')->index();
            $table->json('metadata')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['organization_id', 'status']);
            $table->index(['organization_id', 'discount_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partner_discount_offers');
    }
};
