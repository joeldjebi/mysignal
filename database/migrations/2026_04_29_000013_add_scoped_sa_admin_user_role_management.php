<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const PERMISSIONS = [
        'SA_SCOPED_ROLES_MANAGE' => ['Gerer ses roles SA', 'Permet a un SA Admin de creer et gerer ses propres roles.'],
        'SA_SCOPED_USERS_MANAGE' => ['Gerer ses utilisateurs SA', 'Permet a un SA Admin de creer et gerer ses propres utilisateurs.'],
    ];

    public function up(): void
    {
        if (! Schema::hasColumn('roles', 'created_by')) {
            Schema::table('roles', function (Blueprint $table): void {
                $table->foreignId('created_by')->nullable()->after('status')->constrained('users')->nullOnDelete();
                $table->index(['created_by', 'organization_id', 'status']);
            });
        }

        $now = now();

        foreach (self::PERMISSIONS as $code => [$name, $description]) {
            DB::table('permissions')->updateOrInsert(
                ['code' => $code],
                [
                    'name' => $name,
                    'description' => $description,
                    'profile_scope' => 'super_admin',
                    'category' => 'roles_permissions',
                    'status' => 'active',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            );
        }

        $roleId = DB::table('roles')->where('code', 'SA_ADMIN')->value('id');
        $permissionIds = DB::table('permissions')->whereIn('code', array_keys(self::PERMISSIONS))->pluck('id')->all();

        if ($roleId !== null) {
            foreach ($permissionIds as $permissionId) {
                DB::table('permission_role')->updateOrInsert(
                    ['role_id' => $roleId, 'permission_id' => $permissionId],
                    ['created_at' => $now, 'updated_at' => $now],
                );
            }
        }
    }

    public function down(): void
    {
        $permissionIds = DB::table('permissions')->whereIn('code', array_keys(self::PERMISSIONS))->pluck('id')->all();

        DB::table('permission_role')->whereIn('permission_id', $permissionIds)->delete();
        DB::table('permission_user')->whereIn('permission_id', $permissionIds)->delete();
        DB::table('permission_user_access')->whereIn('permission_id', $permissionIds)->delete();
        DB::table('permissions')->whereIn('id', $permissionIds)->delete();

        if (Schema::hasColumn('roles', 'created_by')) {
            Schema::table('roles', function (Blueprint $table): void {
                $table->dropIndex(['created_by', 'organization_id', 'status']);
                $table->dropConstrainedForeignId('created_by');
            });
        }
    }
};
