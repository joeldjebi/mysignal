<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reparation_cases', function (Blueprint $table): void {
            if (! Schema::hasColumn('reparation_cases', 'bailiff_started_at')) {
                $table->timestamp('bailiff_started_at')->nullable()->after('lawyer_user_id');
            }

            if (! Schema::hasColumn('reparation_cases', 'bailiff_completed_at')) {
                $table->timestamp('bailiff_completed_at')->nullable()->after('bailiff_started_at');
            }

            if (! Schema::hasColumn('reparation_cases', 'lawyer_assigned_by_user_id')) {
                $table->foreignId('lawyer_assigned_by_user_id')->nullable()->after('bailiff_completed_at')->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('reparation_cases', 'lawyer_assigned_at')) {
                $table->timestamp('lawyer_assigned_at')->nullable()->after('lawyer_assigned_by_user_id');
            }

            if (! Schema::hasColumn('reparation_cases', 'lawyer_completed_at')) {
                $table->timestamp('lawyer_completed_at')->nullable()->after('lawyer_assigned_at');
            }
        });

        foreach ([
            ['code' => 'BO_REPARATION_CASES_HUISSIER', 'name' => 'Traiter dossiers huissier', 'description' => 'Permet a un huissier de traiter ses dossiers de constat.', 'profile_scope' => 'huissier'],
            ['code' => 'BO_REPARATION_CASES_AODA', 'name' => 'Gerer dossiers AODA', 'description' => 'Permet a l admin ordre des avocats de consulter et attribuer les dossiers termines par huissier.', 'profile_scope' => 'aoda'],
            ['code' => 'BO_REPARATION_CASES_AVOCAT', 'name' => 'Traiter dossiers avocat', 'description' => 'Permet a un avocat de traiter ses dossiers judiciaires.', 'profile_scope' => 'avocat'],
        ] as $permission) {
            Permission::query()->updateOrCreate(
                ['code' => $permission['code']],
                [
                    'name' => $permission['name'],
                    'description' => $permission['description'],
                    'profile_scope' => $permission['profile_scope'],
                    'category' => 'reparation_cases',
                    'status' => 'active',
                ],
            );
        }

        $roles = [
            'HUISSIER' => ['BO_REPARATION_CASES_HUISSIER'],
            'AVOCAT' => ['BO_REPARATION_CASES_AVOCAT'],
            'AODA' => ['SA_ACCESS_PORTAL', 'BO_REPARATION_CASES_AODA', 'SA_PUBLIC_USERS_VIEW', 'SA_PUBLIC_REPORTS_VIEW', 'SA_ACTIVITY_LOGS_VIEW_SELF'],
        ];

        foreach ($roles as $roleCode => $permissionCodes) {
            $role = Role::query()->updateOrCreate(
                ['code' => $roleCode, 'organization_id' => null],
                [
                    'name' => $roleCode === 'AODA' ? 'Admin ordre des avocats' : ($roleCode === 'HUISSIER' ? 'Huissier' : 'Avocat'),
                    'description' => match ($roleCode) {
                        'AODA' => 'Role dedie a l attribution des dossiers termines par huissier aux avocats.',
                        'HUISSIER' => 'Role dedie au constat des faits et a la production du rapport de constat.',
                        default => 'Role dedie au suivi de la procedure judiciaire et des actes contentieux.',
                    },
                    'status' => 'active',
                ],
            );

            $permissionIds = Permission::query()
                ->whereIn('code', array_merge(['SA_ACCESS_PORTAL'], $permissionCodes))
                ->pluck('id')
                ->all();

            $role->permissions()->syncWithoutDetaching($permissionIds);

            if (in_array($roleCode, ['HUISSIER', 'AVOCAT'], true)) {
                $legacyPermissionIds = Permission::query()
                    ->whereIn('code', ['SA_REPARATION_CASES_MANAGE', 'SA_DASHBOARD_VIEW'])
                    ->pluck('id')
                    ->all();

                if ($legacyPermissionIds !== []) {
                    $role->permissions()->detach($legacyPermissionIds);
                }
            }
        }
    }

    public function down(): void
    {
        Schema::table('reparation_cases', function (Blueprint $table): void {
            foreach (['lawyer_completed_at', 'lawyer_assigned_at', 'bailiff_completed_at', 'bailiff_started_at'] as $column) {
                if (Schema::hasColumn('reparation_cases', $column)) {
                    $table->dropColumn($column);
                }
            }

            if (Schema::hasColumn('reparation_cases', 'lawyer_assigned_by_user_id')) {
                $table->dropConstrainedForeignId('lawyer_assigned_by_user_id');
            }
        });
    }
};
