<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('landing_page_sections', function (Blueprint $table): void {
            $table->id();
            $table->string('key', 80)->unique();
            $table->string('label', 120);
            $table->string('title', 180)->nullable();
            $table->string('subtitle', 255)->nullable();
            $table->text('body')->nullable();
            $table->json('meta')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedSmallInteger('sort_order')->default(1);
            $table->timestamps();
        });

        Schema::create('landing_page_section_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('landing_page_section_id')
                ->constrained('landing_page_sections')
                ->cascadeOnDelete();
            $table->string('item_key', 80)->default('items')->index();
            $table->string('title', 180)->nullable();
            $table->string('subtitle', 255)->nullable();
            $table->text('body')->nullable();
            $table->string('icon', 80)->nullable();
            $table->string('url', 255)->nullable();
            $table->string('value', 120)->nullable();
            $table->json('meta')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedSmallInteger('sort_order')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('landing_page_section_items');
        Schema::dropIfExists('landing_page_sections');
    }
};
