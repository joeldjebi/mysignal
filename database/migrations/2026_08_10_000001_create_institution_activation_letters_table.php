<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('institution_activation_letters', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('institution_admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('activation_code', 40)->unique();
            $table->string('activation_url')->nullable();
            $table->string('letter_subject')->default('Désignation du point focal My-Signal');
            $table->longText('letter_content');
            $table->string('logo_position', 20)->default('left');
            $table->string('status', 20)->default('draft');
            $table->timestamp('expires_at')->nullable();
            $table->string('focal_last_name')->nullable();
            $table->string('focal_first_names')->nullable();
            $table->string('focal_position')->nullable();
            $table->string('focal_phone')->nullable();
            $table->string('focal_email')->nullable();
            $table->string('focal_location')->nullable();
            $table->decimal('focal_latitude', 11, 8)->nullable();
            $table->decimal('focal_longitude', 11, 8)->nullable();
            $table->unsignedInteger('location_accuracy')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['organization_id', 'status']);
            $table->index(['institution_admin_id', 'status']);
            $table->index('submitted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('institution_activation_letters');
    }
};
