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

        foreach ($this->btreeIndexes() as $indexName => [$table, $columns]) {
            DB::statement(sprintf(
                'CREATE INDEX CONCURRENTLY IF NOT EXISTS %s ON %s (%s)',
                $indexName,
                $table,
                $columns,
            ));
        }

        foreach ($this->partialIndexes() as $indexName => [$table, $columns, $where]) {
            DB::statement(sprintf(
                'CREATE INDEX CONCURRENTLY IF NOT EXISTS %s ON %s (%s) WHERE %s',
                $indexName,
                $table,
                $columns,
                $where,
            ));
        }

        foreach ($this->trigramIndexes() as $indexName => [$table, $column]) {
            DB::statement(sprintf(
                'CREATE INDEX CONCURRENTLY IF NOT EXISTS %s ON %s USING gin (%s gin_trgm_ops)',
                $indexName,
                $table,
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

        foreach (array_keys($this->partialIndexes()) as $indexName) {
            DB::statement(sprintf('DROP INDEX CONCURRENTLY IF EXISTS %s', $indexName));
        }

        foreach (array_keys($this->btreeIndexes()) as $indexName) {
            DB::statement(sprintf('DROP INDEX CONCURRENTLY IF EXISTS %s', $indexName));
        }
    }

    private function btreeIndexes(): array
    {
        return [
            'incident_reports_damage_created_idx' => ['incident_reports', 'organization_id, application_id, damage_declared_at DESC, id DESC'],
            'incident_reports_damage_status_created_idx' => ['incident_reports', 'organization_id, application_id, damage_resolution_status, damage_declared_at DESC, id DESC'],
            'incident_reports_damage_commune_created_idx' => ['incident_reports', 'organization_id, application_id, commune_id, damage_declared_at DESC, id DESC'],
            'incident_reports_sa_status_created_idx' => ['incident_reports', 'status, created_at DESC, id DESC'],
            'incident_reports_sa_application_created_idx' => ['incident_reports', 'application_id, created_at DESC, id DESC'],
            'incident_reports_sa_organization_created_idx' => ['incident_reports', 'organization_id, created_at DESC, id DESC'],
            'incident_reports_sa_resolution_created_idx' => ['incident_reports', 'resolution_confirmation_status, resolution_confirmed_without_ai_validation, status, created_at DESC, id DESC'],
            'incident_reports_inst_meter_status_idx' => ['incident_reports', 'organization_id, application_id, meter_id, status, created_at DESC, id DESC'],

            'reparation_cases_inst_created_idx' => ['reparation_cases', 'organization_id, application_id, created_at DESC, id DESC'],
            'reparation_cases_inst_status_created_idx' => ['reparation_cases', 'organization_id, application_id, status, created_at DESC, id DESC'],
            'reparation_cases_inst_type_created_idx' => ['reparation_cases', 'organization_id, application_id, case_type, created_at DESC, id DESC'],

            'meters_inst_created_idx' => ['meters', 'organization_id, application_id, created_at DESC, id DESC'],
            'meters_inst_status_created_idx' => ['meters', 'organization_id, application_id, status, created_at DESC, id DESC'],
            'meters_inst_network_created_idx' => ['meters', 'organization_id, application_id, network_type, created_at DESC, id DESC'],
            'meters_inst_commune_idx' => ['meters', 'organization_id, application_id, network_type, commune'],
            'meters_inst_location_idx' => ['meters', 'organization_id, application_id, latitude, longitude, id'],
            'meter_assignments_public_user_meter_idx' => ['meter_assignments', 'public_user_id, meter_id'],

            'public_users_status_created_idx' => ['public_users', 'status, created_at DESC, id DESC'],
            'public_users_type_status_created_idx' => ['public_users', 'public_user_type_id, status, created_at DESC, id DESC'],

            'payments_status_initiated_idx' => ['payments', 'status, initiated_at DESC, id DESC'],
            'payments_provider_initiated_idx' => ['payments', 'provider, initiated_at DESC, id DESC'],
            'payments_context_initiated_idx' => ['payments', 'payment_context, initiated_at DESC, id DESC'],
            'payments_public_user_initiated_idx' => ['payments', 'public_user_id, initiated_at DESC, id DESC'],
            'payments_report_initiated_idx' => ['payments', 'incident_report_id, initiated_at DESC, id DESC'],

            'payment_sessions_status_initiated_idx' => ['incident_report_payment_sessions', 'status, initiated_at DESC, id DESC'],
            'payment_sessions_context_initiated_idx' => ['incident_report_payment_sessions', 'payment_context, initiated_at DESC, id DESC'],
            'payment_sessions_public_user_initiated_idx' => ['incident_report_payment_sessions', 'public_user_id, initiated_at DESC, id DESC'],
        ];
    }

    private function partialIndexes(): array
    {
        return [
            'incident_reports_damage_declared_partial_idx' => ['incident_reports', 'organization_id, application_id, damage_declared_at DESC, id DESC', 'damage_declared_at IS NOT NULL'],
            'incident_reports_damage_attachment_partial_idx' => ['incident_reports', 'organization_id, application_id, damage_declared_at DESC, id DESC', 'damage_attachment IS NOT NULL'],
            'incident_reports_sa_damage_partial_idx' => ['incident_reports', 'application_id, organization_id, created_at DESC, id DESC', 'damage_declared_at IS NOT NULL'],
            'incident_reports_sa_public_user_partial_idx' => ['incident_reports', 'created_at DESC, id DESC', 'public_user_id IS NOT NULL'],
        ];
    }

    private function trigramIndexes(): array
    {
        return [
            'incident_reports_incident_type_trgm_idx' => ['incident_reports', 'incident_type'],
            'incident_reports_damage_summary_trgm_idx' => ['incident_reports', 'damage_summary'],
            'incident_reports_damage_notes_trgm_idx' => ['incident_reports', 'damage_notes'],

            'reparation_cases_ref_trgm_idx' => ['reparation_cases', 'reference'],

            'meters_number_trgm_idx' => ['meters', 'meter_number'],
            'meters_label_trgm_idx' => ['meters', 'label'],
            'meters_commune_trgm_idx' => ['meters', 'commune'],
            'meters_address_trgm_idx' => ['meters', 'address'],

            'public_users_first_name_trgm_idx' => ['public_users', 'first_name'],
            'public_users_last_name_trgm_idx' => ['public_users', 'last_name'],
            'public_users_phone_trgm_idx' => ['public_users', 'phone'],
            'public_users_email_trgm_idx' => ['public_users', 'email'],
            'public_users_company_name_trgm_idx' => ['public_users', 'company_name'],

            'payments_ref_trgm_idx' => ['payments', 'reference'],
            'payments_provider_ref_trgm_idx' => ['payments', 'provider_reference'],
            'payments_provider_trgm_idx' => ['payments', 'provider'],

            'payment_sessions_sync_ref_trgm_idx' => ['incident_report_payment_sessions', 'sync_ref'],
            'payment_sessions_provider_ref_trgm_idx' => ['incident_report_payment_sessions', 'provider_reference'],
        ];
    }
};
