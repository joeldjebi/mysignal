<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('signal_types', function (Blueprint $table) {
            $table->id();
            $table->string('network_type', 20)->index();
            $table->string('code', 30);
            $table->string('label', 180);
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('default_sla_hours')->nullable();
            $table->json('data_fields')->nullable();
            $table->string('status', 30)->default('active')->index();
            $table->timestamps();

            $table->unique(['network_type', 'code'], 'signal_types_network_code_unique');
        });

        $signalTypes = collect(config('acepen.reports.signal_types', []))
            ->map(function (array $definition, string $code): array {
                return [
                    'network_type' => $definition['network_type'] ?? 'CIE',
                    'code' => strtoupper($code),
                    'label' => $definition['label'] ?? $code,
                    'description' => $definition['description'] ?? null,
                    'default_sla_hours' => $definition['sla_target']['hours'] ?? null,
                    'data_fields' => json_encode($definition['data_fields'] ?? [], JSON_UNESCAPED_UNICODE),
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })
            ->values()
            ->all();

        if ($signalTypes !== []) {
            DB::table('signal_types')->insert($signalTypes);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('signal_types');
    }
};
