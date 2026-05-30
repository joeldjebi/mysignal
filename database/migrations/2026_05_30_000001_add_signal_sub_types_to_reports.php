<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('signal_sub_types', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('signal_type_id')->constrained()->cascadeOnDelete();
            $table->string('code', 60);
            $table->string('label', 180);
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('status', 30)->default('active')->index();
            $table->timestamps();

            $table->unique(['signal_type_id', 'code']);
        });

        Schema::table('incident_reports', function (Blueprint $table): void {
            $table->foreignId('signal_sub_type_id')->nullable()->after('signal_label')->constrained('signal_sub_types')->nullOnDelete();
            $table->string('signal_sub_type_code', 60)->nullable()->after('signal_sub_type_id');
            $table->string('signal_sub_type_label', 180)->nullable()->after('signal_sub_type_code');
        });
    }

    public function down(): void
    {
        Schema::table('incident_reports', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('signal_sub_type_id');
            $table->dropColumn(['signal_sub_type_code', 'signal_sub_type_label']);
        });

        Schema::dropIfExists('signal_sub_types');
    }
};
