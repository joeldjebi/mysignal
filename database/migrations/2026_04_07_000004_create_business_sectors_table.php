<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_sectors', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 60)->unique();
            $table->string('name', 180);
            $table->text('description')->nullable();
            $table->string('status', 20)->default('active');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        $now = now();

        DB::table('business_sectors')->insert([
            ['code' => 'ENERGIE', 'name' => 'Energie', 'description' => 'Production, distribution et services lies a l energie.', 'status' => 'active', 'sort_order' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'EAU', 'name' => 'Eau potable', 'description' => 'Production, transport et distribution d eau potable.', 'status' => 'active', 'sort_order' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'BANQUE', 'name' => 'Banque et microfinance', 'description' => 'Banques, etablissements financiers et microfinance.', 'status' => 'active', 'sort_order' => 3, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'ASSURANCE', 'name' => 'Assurance', 'description' => 'Assurances, remboursements et gestion de sinistres.', 'status' => 'active', 'sort_order' => 4, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'TELECOM', 'name' => 'Telecommunications', 'description' => 'Voix, SMS, internet et services telecom.', 'status' => 'active', 'sort_order' => 5, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'ENVIRONNEMENT', 'name' => 'Environnement', 'description' => 'Protection de l environnement et cadre de vie.', 'status' => 'active', 'sort_order' => 6, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'COMMERCE', 'name' => 'Commerce et services', 'description' => 'Activites commerciales et services aux entreprises.', 'status' => 'active', 'sort_order' => 7, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('business_sectors');
    }
};
