<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('
                UPDATE reparation_cases
                SET
                    application_id = incident_reports.application_id,
                    organization_id = incident_reports.organization_id,
                    updated_at = ?
                FROM incident_reports
                WHERE incident_reports.id = reparation_cases.incident_report_id
                AND (reparation_cases.application_id IS NULL OR reparation_cases.organization_id IS NULL)
            ', [now()]);

            return;
        }

        DB::table('reparation_cases')
            ->join('incident_reports', 'incident_reports.id', '=', 'reparation_cases.incident_report_id')
            ->where(function ($query): void {
                $query->whereNull('reparation_cases.application_id')
                    ->orWhereNull('reparation_cases.organization_id');
            })
            ->update([
                'reparation_cases.application_id' => DB::raw('incident_reports.application_id'),
                'reparation_cases.organization_id' => DB::raw('incident_reports.organization_id'),
                'reparation_cases.updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        // No rollback: this migration only restores missing scope from the source report.
    }
};
