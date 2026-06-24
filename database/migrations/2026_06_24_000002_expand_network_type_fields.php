<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $tables = [
        'incident_reports',
        'meters',
        'organization_type_signal_slas',
        'signal_catalogs',
    ];

    public function up(): void
    {
        $this->resizeNetworkTypeColumns(60);
    }

    public function down(): void
    {
        $this->resizeNetworkTypeColumns(20);
    }

    private function resizeNetworkTypeColumns(int $length): void
    {
        $driver = DB::getDriverName();

        foreach ($this->tables as $table) {
            if ($driver === 'pgsql') {
                DB::statement("ALTER TABLE {$table} ALTER COLUMN network_type TYPE VARCHAR({$length})");

                continue;
            }

            if (in_array($driver, ['mysql', 'mariadb'], true)) {
                DB::statement("ALTER TABLE {$table} MODIFY network_type VARCHAR({$length}) NOT NULL");
            }
        }
    }
};
