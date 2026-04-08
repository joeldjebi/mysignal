<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('meters', function (Blueprint $table): void {
            $table->foreignId('application_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->foreignId('organization_id')->nullable()->after('application_id')->constrained()->nullOnDelete();
        });

        Schema::table('incident_reports', function (Blueprint $table): void {
            $table->foreignId('organization_id')->nullable()->after('application_id')->constrained()->nullOnDelete();
        });

        $applicationsByCode = DB::table('applications')->pluck('id', 'code');
        $organizationIdsByApplication = DB::table('organizations')
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'application_id'])
            ->groupBy('application_id')
            ->map(fn ($rows) => $rows->pluck('id')->all());

        $networkMappings = [
            'CIE' => 'MON_NRJ',
            'SODECI' => 'MON_EAU',
        ];

        foreach (DB::table('meters')->select('id', 'network_type')->get() as $meter) {
            $applicationCode = $networkMappings[strtoupper((string) $meter->network_type)] ?? strtoupper((string) $meter->network_type);
            $applicationId = $applicationsByCode[$applicationCode] ?? null;
            $organizationId = $applicationId ? ($organizationIdsByApplication[$applicationId][0] ?? null) : null;

            DB::table('meters')
                ->where('id', $meter->id)
                ->update([
                    'application_id' => $applicationId,
                    'organization_id' => $organizationId,
                ]);
        }

        foreach (DB::table('incident_reports')->select('id', 'application_id', 'meter_id')->get() as $report) {
            $organizationId = null;

            if ($report->meter_id) {
                $organizationId = DB::table('meters')->where('id', $report->meter_id)->value('organization_id');
            }

            if (! $organizationId && $report->application_id) {
                $organizationId = $organizationIdsByApplication[$report->application_id][0] ?? null;
            }

            DB::table('incident_reports')
                ->where('id', $report->id)
                ->update([
                    'organization_id' => $organizationId,
                ]);
        }

        Schema::table('meters', function (Blueprint $table): void {
            $table->dropUnique('meters_network_type_meter_number_unique');
            $table->unique(['organization_id', 'meter_number']);
        });
    }

    public function down(): void
    {
        Schema::table('meters', function (Blueprint $table): void {
            $table->dropUnique(['organization_id', 'meter_number']);
            $table->unique(['network_type', 'meter_number']);
        });

        Schema::table('incident_reports', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('organization_id');
        });

        Schema::table('meters', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('organization_id');
            $table->dropConstrainedForeignId('application_id');
        });
    }
};
