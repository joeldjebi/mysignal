<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        match (DB::getDriverName()) {
            'pgsql' => DB::statement('ALTER TABLE organizations ALTER COLUMN commune TYPE VARCHAR(255), ALTER COLUMN address TYPE VARCHAR(500)'),
            'mysql', 'mariadb' => DB::statement('ALTER TABLE organizations MODIFY commune VARCHAR(255) NULL, MODIFY address VARCHAR(500) NULL'),
            default => null,
        };
    }

    public function down(): void
    {
        match (DB::getDriverName()) {
            'pgsql' => DB::statement('ALTER TABLE organizations ALTER COLUMN commune TYPE VARCHAR(120), ALTER COLUMN address TYPE VARCHAR(255)'),
            'mysql', 'mariadb' => DB::statement('ALTER TABLE organizations MODIFY commune VARCHAR(120) NULL, MODIFY address VARCHAR(255) NULL'),
            default => null,
        };
    }
};
