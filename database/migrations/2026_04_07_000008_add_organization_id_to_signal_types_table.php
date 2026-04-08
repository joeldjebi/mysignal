<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('signal_types', function (Blueprint $table): void {
            $table->foreignId('organization_id')->nullable()->after('application_id')->constrained()->nullOnDelete();
        });

        Schema::table('signal_types', function (Blueprint $table): void {
            $table->dropUnique('signal_types_network_code_unique');
            $table->unique(['application_id', 'organization_id', 'code'], 'signal_types_scope_code_unique');
        });

        foreach (DB::table('signal_types')->select('id', 'application_id', 'network_type')->get() as $signalType) {
            if ($signalType->application_id) {
                continue;
            }

            $applicationCode = strtoupper((string) $signalType->network_type);
            $applicationId = DB::table('applications')->where('code', $applicationCode)->value('id');

            if (! $applicationId) {
                $applicationId = match ($applicationCode) {
                    'CIE' => DB::table('applications')->where('code', 'MON_NRJ')->value('id'),
                    'SODECI' => DB::table('applications')->where('code', 'MON_EAU')->value('id'),
                    default => null,
                };
            }

            DB::table('signal_types')->where('id', $signalType->id)->update([
                'application_id' => $applicationId,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('signal_types', function (Blueprint $table): void {
            $table->dropUnique('signal_types_scope_code_unique');
            $table->unique(['network_type', 'code'], 'signal_types_network_code_unique');
            $table->dropConstrainedForeignId('organization_id');
        });
    }
};
