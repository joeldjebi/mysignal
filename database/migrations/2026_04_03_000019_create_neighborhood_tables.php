<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('neighborhoods', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('commune_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('status')->default('active');
            $table->timestamps();

            $table->unique(['commune_id', 'name']);
        });

        Schema::create('sub_neighborhoods', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('neighborhood_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('status')->default('active');
            $table->timestamps();

            $table->unique(['neighborhood_id', 'name']);
        });

        Schema::table('meters', function (Blueprint $table): void {
            $table->string('neighborhood')->nullable()->after('commune');
            $table->string('sub_neighborhood')->nullable()->after('neighborhood');
        });
    }

    public function down(): void
    {
        Schema::table('meters', function (Blueprint $table): void {
            $table->dropColumn([
                'neighborhood',
                'sub_neighborhood',
            ]);
        });

        Schema::dropIfExists('sub_neighborhoods');
        Schema::dropIfExists('neighborhoods');
    }
};
