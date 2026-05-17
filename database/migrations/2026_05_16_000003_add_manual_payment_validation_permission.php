<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const CODE = 'SA_PAYMENTS_MANUAL_VALIDATE';

    public function up(): void
    {
        DB::table('permissions')->updateOrInsert(
            ['code' => self::CODE],
            [
                'name' => 'Valider paiements manuellement',
                'description' => 'Permet de valider manuellement une session de paiement et de creer le signalement associe.',
                'profile_scope' => 'super_admin',
                'category' => 'payments',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );

        $permissionId = DB::table('permissions')->where('code', self::CODE)->value('id');
        $roleId = DB::table('roles')->where('code', 'SA_ADMIN')->value('id');

        if ($permissionId !== null && $roleId !== null) {
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
    }

    public function down(): void
    {
        $permissionId = DB::table('permissions')->where('code', self::CODE)->value('id');

        if ($permissionId === null) {
            return;
        }

        DB::table('permission_role')->where('permission_id', $permissionId)->delete();
        DB::table('permission_user')->where('permission_id', $permissionId)->delete();
        DB::table('permission_user_access')->where('permission_id', $permissionId)->delete();
        DB::table('permissions')->where('id', $permissionId)->delete();
    }
};
