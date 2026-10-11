<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('institution_activation_letter_batches', function (Blueprint $table): void {
            $table->id();
            $table->string('reference', 50)->unique();
            $table->string('label', 180)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('generated_at');
            $table->unsignedInteger('letters_count')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['created_by', 'generated_at']);
        });

        Schema::table('institution_activation_letters', function (Blueprint $table): void {
            $table->foreignId('batch_id')->nullable()->after('id')->constrained('institution_activation_letter_batches')->nullOnDelete();
            $table->timestamp('generated_at')->nullable()->after('submitted_at');
            $table->timestamp('superseded_at')->nullable()->after('generated_at');
            $table->foreignId('superseded_by_id')->nullable()->after('superseded_at')->constrained('users')->nullOnDelete();
            $table->index(['institution_admin_id', 'generated_at']);
            $table->index(['organization_id', 'generated_at']);
        });

        DB::table('institution_activation_letters')
            ->whereNull('generated_at')
            ->update(['generated_at' => DB::raw('created_at')]);
    }

    public function down(): void
    {
        Schema::table('institution_activation_letters', function (Blueprint $table): void {
            $table->dropForeign(['batch_id']);
            $table->dropForeign(['superseded_by_id']);
            $table->dropIndex(['institution_admin_id', 'generated_at']);
            $table->dropIndex(['organization_id', 'generated_at']);
            $table->dropColumn(['batch_id', 'generated_at', 'superseded_at', 'superseded_by_id']);
        });

        Schema::dropIfExists('institution_activation_letter_batches');
    }
};
