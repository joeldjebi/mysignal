<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('CREATE EXTENSION IF NOT EXISTS pg_trgm');

        foreach ($this->btreeIndexes() as $indexName => $columns) {
            DB::statement(sprintf(
                'CREATE INDEX CONCURRENTLY IF NOT EXISTS %s ON incident_reports (%s)',
                $indexName,
                $columns,
            ));
        }

        foreach ($this->trigramIndexes() as $indexName => $column) {
            DB::statement(sprintf(
                'CREATE INDEX CONCURRENTLY IF NOT EXISTS %s ON incident_reports USING gin (%s gin_trgm_ops)',
                $indexName,
                $column,
            ));
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        foreach (array_keys($this->trigramIndexes()) as $indexName) {
            DB::statement(sprintf('DROP INDEX CONCURRENTLY IF EXISTS %s', $indexName));
        }

        foreach (array_keys($this->btreeIndexes()) as $indexName) {
            DB::statement(sprintf('DROP INDEX CONCURRENTLY IF EXISTS %s', $indexName));
        }
    }

    private function btreeIndexes(): array
    {
        return [
            'incident_reports_inst_created_idx' => 'organization_id, application_id, created_at DESC, id DESC',
            'incident_reports_inst_status_created_idx' => 'organization_id, application_id, status, created_at DESC, id DESC',
            'incident_reports_inst_payment_created_idx' => 'organization_id, application_id, payment_status, created_at DESC, id DESC',
            'incident_reports_inst_commune_created_idx' => 'organization_id, application_id, commune_id, created_at DESC, id DESC',
            'incident_reports_inst_meter_created_idx' => 'organization_id, application_id, meter_id, created_at DESC, id DESC',
            'incident_reports_inst_network_created_idx' => 'organization_id, application_id, network_type, created_at DESC, id DESC',
        ];
    }

    private function trigramIndexes(): array
    {
        return [
            'incident_reports_ref_trgm_idx' => 'reference',
            'incident_reports_signal_code_trgm_idx' => 'signal_code',
            'incident_reports_signal_label_trgm_idx' => 'signal_label',
            'incident_reports_description_trgm_idx' => 'description',
        ];
    }
};
