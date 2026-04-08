<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('application_feature', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            $table->foreignId('feature_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['application_id', 'feature_id']);
        });

        Schema::table('feature_organization', function (Blueprint $table): void {
            $table->boolean('enabled')->default(true)->after('organization_id');
        });

        DB::table('feature_organization')
            ->whereNull('enabled')
            ->update(['enabled' => true]);
    }

    public function down(): void
    {
        Schema::table('feature_organization', function (Blueprint $table): void {
            $table->dropColumn('enabled');
        });

        Schema::dropIfExists('application_feature');
    }
};
