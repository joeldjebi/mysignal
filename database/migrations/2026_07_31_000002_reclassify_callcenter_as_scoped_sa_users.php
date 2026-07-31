<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $saUserTypeId = DB::table('user_types')->where('code', 'SA_USER')->value('id');
        $callCenterTypeId = DB::table('user_types')->where('code', 'CALLCENTER')->value('id');

        if ($saUserTypeId === null || $callCenterTypeId === null) {
            return;
        }

        DB::table('users')
            ->where('user_type_id', $callCenterTypeId)
            ->where('is_super_admin', false)
            ->whereNull('organization_id')
            ->update(['user_type_id' => $saUserTypeId]);
    }

    public function down(): void
    {
        $callCenterTypeId = DB::table('user_types')->where('code', 'CALLCENTER')->value('id');
        $callCenterRoleId = DB::table('roles')->where('code', 'CALLCENTER')->value('id');

        if ($callCenterTypeId === null || $callCenterRoleId === null) {
            return;
        }

        DB::table('users')
            ->where('is_super_admin', false)
            ->whereNull('organization_id')
            ->whereExists(function ($query) use ($callCenterRoleId): void {
                $query->selectRaw('1')
                    ->from('role_user')
                    ->whereColumn('role_user.user_id', 'users.id')
                    ->where('role_user.role_id', $callCenterRoleId);
            })
            ->update(['user_type_id' => $callCenterTypeId]);
    }
};
