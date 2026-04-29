<?php

use App\Models\Permission;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const INTERNAL_PERMISSION_CODES = [
        'SA_ACCESS_PORTAL',
        'SA_DASHBOARD_VIEW',
        'SA_REPARATION_CASES_MANAGE',
        'SA_ACTIVITY_LOGS_VIEW_SELF',
    ];

    public function up(): void
    {
        DB::table('permissions')
            ->whereIn('code', self::INTERNAL_PERMISSION_CODES)
            ->update([
                'profile_scope' => 'backoffice',
                'category' => DB::raw("CASE
                    WHEN code = 'SA_DASHBOARD_VIEW' THEN 'dashboard'
                    WHEN code = 'SA_REPARATION_CASES_MANAGE' THEN 'reparation_cases'
                    WHEN code = 'SA_ACTIVITY_LOGS_VIEW_SELF' THEN 'activity_logs'
                    ELSE 'settings'
                END"),
            ]);

        $permissionIds = DB::table('permissions')
            ->whereIn('code', self::INTERNAL_PERMISSION_CODES)
            ->pluck('id')
            ->all();

        DB::table('roles')
            ->whereIn('code', ['HUISSIER', 'AVOCAT'])
            ->pluck('id')
            ->each(function (int $roleId) use ($permissionIds): void {
                foreach ($permissionIds as $permissionId) {
                    DB::table('permission_role')->updateOrInsert(
                        [
                            'role_id' => $roleId,
                            'permission_id' => $permissionId,
                        ],
                        [
                            'created_at' => now(),
                            'updated_at' => now(),
                        ],
                    );
                }
            });
    }

    public function down(): void
    {
        $permissionIds = DB::table('permissions')
            ->whereIn('code', self::INTERNAL_PERMISSION_CODES)
            ->pluck('id')
            ->all();

        DB::table('roles')
            ->whereIn('code', ['HUISSIER', 'AVOCAT'])
            ->pluck('id')
            ->each(function (int $roleId) use ($permissionIds): void {
                DB::table('permission_role')
                    ->where('role_id', $roleId)
                    ->whereIn('permission_id', $permissionIds)
                    ->delete();
            });

        DB::table('permissions')
            ->whereIn('code', self::INTERNAL_PERMISSION_CODES)
            ->get(['id', 'code'])
            ->each(function ($permission): void {
                $code = strtoupper((string) $permission->code);

                DB::table('permissions')
                    ->where('id', $permission->id)
                    ->update([
                        'profile_scope' => Permission::inferProfileScope($code),
                        'category' => Permission::inferCategory($code),
                    ]);
            });
    }
};
