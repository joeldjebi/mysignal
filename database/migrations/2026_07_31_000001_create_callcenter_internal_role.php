<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const ROLE_CODE = 'CALLCENTER';

    private const PERMISSIONS = [
        ['SA_ACCESS_PORTAL', 'Accéder au portail SA', 'Permet de se connecter au portail interne.'],
        ['SA_DASHBOARD_VIEW', 'Voir le tableau de bord SA', 'Permet de consulter le tableau de bord global.'],
        ['SA_PUBLIC_USERS_VIEW', 'Voir les usagers publics', 'Permet de consulter les comptes des usagers publics.'],
        ['SA_PUBLIC_USERS_CREATE', 'Créer des usagers publics', 'Permet de créer un compte usager public.'],
        ['SA_PUBLIC_USERS_UPDATE', 'Modifier des usagers publics', 'Permet de mettre à jour les informations d’un usager public.'],
        ['SA_PUBLIC_REPORTS_VIEW', 'Voir les signalements publics', 'Permet de consulter les signalements des usagers publics.'],
        ['SA_PAYMENTS_VIEW', 'Voir les paiements', 'Permet de consulter les paiements et leurs statuts.'],
        ['SA_PRIVILEGE_CARD_TYPES_VIEW', 'Voir les cartes privilèges', 'Permet de consulter les cartes privilèges et leurs historiques.'],
        ['SA_UP_SUBSCRIPTIONS_VIEW', 'Voir les abonnements usagers', 'Permet de consulter les abonnements des usagers publics.'],
        ['SA_DISCOUNT_CARDS_VIEW', 'Voir les cartes de réduction', 'Permet de consulter les cartes de réduction des usagers.'],
        ['SA_DISCOUNT_TRANSACTIONS_VIEW', 'Voir les réductions appliquées', 'Permet de consulter les réductions appliquées par les partenaires.'],
        ['SA_REX_FEEDBACKS_VIEW', 'Voir les avis des usagers', 'Permet de consulter les retours des usagers publics.'],
        ['SA_ACTIVITY_LOGS_VIEW_SELF', 'Voir ses activités', 'Permet de consulter son propre historique d’activité.'],
    ];

    public function up(): void
    {
        $now = now();

        DB::table('user_types')->updateOrInsert(
            ['code' => self::ROLE_CODE],
            [
                'name' => 'Call center',
                'description' => 'Compte interne dédié à l’assistance des usagers publics.',
                'status' => 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        );

        foreach (self::PERMISSIONS as [$code, $name, $description]) {
            DB::table('permissions')->updateOrInsert(
                ['code' => $code],
                [
                    'name' => $name,
                    'description' => $description,
                    'profile_scope' => 'super_admin',
                    'category' => $this->permissionCategory($code),
                    'status' => 'active',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            );
        }

        DB::table('roles')->updateOrInsert(
            ['code' => self::ROLE_CODE],
            [
                'organization_id' => null,
                'name' => 'Call center',
                'description' => 'Rôle interne dédié à l’assistance des usagers publics.',
                'status' => 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        );

        $roleId = DB::table('roles')->where('code', self::ROLE_CODE)->value('id');

        if ($roleId === null) {
            return;
        }

        $permissionIds = DB::table('permissions')
            ->whereIn('code', collect(self::PERMISSIONS)->pluck(0)->all())
            ->pluck('id')
            ->all();

        foreach ($permissionIds as $permissionId) {
            DB::table('permission_role')->updateOrInsert(
                [
                    'role_id' => $roleId,
                    'permission_id' => $permissionId,
                ],
                [
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            );
        }

        $callCenterTypeId = DB::table('user_types')->where('code', self::ROLE_CODE)->value('id');

        if ($callCenterTypeId === null) {
            return;
        }

        DB::table('users')
            ->where('is_super_admin', false)
            ->whereNull('organization_id')
            ->whereExists(function ($query) use ($roleId): void {
                $query->selectRaw('1')
                    ->from('role_user')
                    ->whereColumn('role_user.user_id', 'users.id')
                    ->where('role_user.role_id', $roleId);
            })
            ->update(['user_type_id' => $callCenterTypeId]);
    }

    public function down(): void
    {
        $roleId = DB::table('roles')->where('code', self::ROLE_CODE)->value('id');

        if ($roleId !== null) {
            DB::table('permission_role')->where('role_id', $roleId)->delete();
            DB::table('role_user')->where('role_id', $roleId)->delete();
            DB::table('roles')->where('id', $roleId)->delete();
        }

        DB::table('users')
            ->whereIn('user_type_id', DB::table('user_types')->select('id')->where('code', self::ROLE_CODE))
            ->update(['user_type_id' => null]);

        DB::table('user_types')->where('code', self::ROLE_CODE)->delete();
    }

    private function permissionCategory(string $code): string
    {
        return match (true) {
            str_contains($code, 'PUBLIC_USERS') => 'users',
            str_contains($code, 'PUBLIC_REPORTS') => 'reports',
            str_contains($code, 'PAYMENTS'),
            str_contains($code, 'PRIVILEGE'),
            str_contains($code, 'SUBSCRIPTIONS'),
            str_contains($code, 'DISCOUNT') => 'payments',
            str_contains($code, 'ACTIVITY') => 'activity_logs',
            default => 'dashboard',
        };
    }
};
