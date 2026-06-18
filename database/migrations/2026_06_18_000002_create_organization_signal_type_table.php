<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organization_signal_type', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('signal_type_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['organization_id', 'signal_type_id']);
        });

        $assignments = DB::table('signal_types')
            ->whereNotNull('organization_id')
            ->select('id as signal_type_id', 'organization_id')
            ->get()
            ->map(fn ($row) => [
                'organization_id' => $row->organization_id,
                'signal_type_id' => $row->signal_type_id,
                'created_at' => now(),
                'updated_at' => now(),
            ])
            ->all();

        if ($assignments !== []) {
            DB::table('organization_signal_type')->insertOrIgnore($assignments);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('organization_signal_type');
    }
};
