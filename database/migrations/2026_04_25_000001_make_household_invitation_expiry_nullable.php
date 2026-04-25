<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        match (DB::getDriverName()) {
            'pgsql' => DB::statement('ALTER TABLE household_invitations ALTER COLUMN expires_at DROP NOT NULL'),
            'mysql', 'mariadb' => DB::statement('ALTER TABLE household_invitations MODIFY expires_at timestamp NULL'),
            default => null,
        };
    }

    public function down(): void
    {
        DB::table('household_invitations')
            ->whereNull('expires_at')
            ->update(['expires_at' => now()->addYears(10)]);

        match (DB::getDriverName()) {
            'pgsql' => DB::statement('ALTER TABLE household_invitations ALTER COLUMN expires_at SET NOT NULL'),
            'mysql', 'mariadb' => DB::statement('ALTER TABLE household_invitations MODIFY expires_at timestamp NOT NULL'),
            default => null,
        };
    }
};
