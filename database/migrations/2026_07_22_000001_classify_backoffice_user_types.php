<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $types = [
            'SUPER_ADMIN' => ['Super admin', 'Compte principal du portail SA.'],
            'SA_USER' => ['Utilisateur SA', 'Utilisateur créé pour le back-office SA.'],
            'CALLCENTER' => ['Call center', 'Compte interne dédié à l’assistance des usagers publics.'],
            'INSTITUTION_ADMIN' => ['Admin institutionnel', 'Compte admin ou collaborateur rattaché à une institution.'],
            'PARTNER_MANAGER' => ['Responsable partenaire', 'Compte responsable du portail partenaire.'],
            'PARTNER_SCAN_AGENT' => ['Agent de scan partenaire', 'Compte chargé du scan des cartes privilèges.'],
        ];

        foreach ($types as $code => [$name, $description]) {
            DB::table('user_types')->updateOrInsert(
                ['code' => $code],
                [
                    'name' => $name,
                    'description' => $description,
                    'status' => 'active',
                    'updated_at' => $now,
                    'created_at' => $now,
                ],
            );
        }

        $typeIds = DB::table('user_types')->whereIn('code', array_keys($types))->pluck('id', 'code');
        $superAdminTypeId = $typeIds['SUPER_ADMIN'] ?? null;
        $saUserTypeId = $typeIds['SA_USER'] ?? null;
        $institutionAdminTypeId = $typeIds['INSTITUTION_ADMIN'] ?? null;
        $partnerManagerTypeId = $typeIds['PARTNER_MANAGER'] ?? null;
        $scanAgentTypeId = $typeIds['PARTNER_SCAN_AGENT'] ?? null;

        DB::table('users')
            ->where('is_super_admin', true)
            ->update(['user_type_id' => $superAdminTypeId]);

        DB::table('users')
            ->where('is_super_admin', false)
            ->whereNotNull('organization_id')
            ->whereIn('organization_id', function ($query): void {
                $query->select('organizations.id')
                    ->from('organizations')
                    ->join('organization_types', 'organization_types.id', '=', 'organizations.organization_type_id')
                    ->where('organization_types.code', 'PARTNER_ESTABLISHMENT');
            })
            ->update(['user_type_id' => $partnerManagerTypeId]);

        DB::table('users')
            ->where('is_super_admin', false)
            ->whereNotNull('organization_id')
            ->whereNotIn('organization_id', function ($query): void {
                $query->select('organizations.id')
                    ->from('organizations')
                    ->join('organization_types', 'organization_types.id', '=', 'organizations.organization_type_id')
                    ->where('organization_types.code', 'PARTNER_ESTABLISHMENT');
            })
            ->update(['user_type_id' => $institutionAdminTypeId]);

        DB::table('users')
            ->where('is_super_admin', false)
            ->whereNull('organization_id')
            ->whereExists(function ($query): void {
                $query->selectRaw('1')
                    ->from('user_accesses')
                    ->whereColumn('user_accesses.user_id', 'users.id')
                    ->where('user_accesses.portal', 'partner')
                    ->where('user_accesses.status', 'active');
            })
            ->update(['user_type_id' => $partnerManagerTypeId]);

        DB::table('users')
            ->where('is_super_admin', false)
            ->whereNull('organization_id')
            ->where('email', 'like', '%@mysignal.pro')
            ->whereNotExists(function ($query): void {
                $query->selectRaw('1')
                    ->from('role_user')
                    ->whereColumn('role_user.user_id', 'users.id');
            })
            ->whereNotExists(function ($query): void {
                $query->selectRaw('1')
                    ->from('user_accesses')
                    ->whereColumn('user_accesses.user_id', 'users.id')
                    ->where('user_accesses.status', 'active');
            })
            ->update(['user_type_id' => $institutionAdminTypeId]);

        DB::table('users')
            ->where('is_super_admin', false)
            ->whereNull('organization_id')
            ->whereNull('user_type_id')
            ->whereExists(function ($query): void {
                $query->selectRaw('1')
                    ->from('role_user')
                    ->join('roles', 'roles.id', '=', 'role_user.role_id')
                    ->whereColumn('role_user.user_id', 'users.id')
                    ->where('roles.code', 'like', 'PARTNER_%');
            })
            ->update(['user_type_id' => $partnerManagerTypeId]);

        DB::table('users')
            ->where('is_super_admin', false)
            ->whereNull('organization_id')
            ->whereNull('user_type_id')
            ->update(['user_type_id' => $saUserTypeId]);

        DB::table('users')
            ->where('is_super_admin', false)
            ->where('user_type_id', $partnerManagerTypeId)
            ->whereExists(function ($query): void {
                $query->selectRaw('1')
                    ->from('role_user')
                    ->join('roles', 'roles.id', '=', 'role_user.role_id')
                    ->whereColumn('role_user.user_id', 'users.id')
                    ->where('roles.code', 'PARTNER_SCAN_AGENT');
            })
            ->update(['user_type_id' => $scanAgentTypeId]);
    }

    public function down(): void
    {
        DB::table('users')->whereIn('user_type_id', function ($query): void {
            $query->select('id')
                ->from('user_types')
                ->whereIn('code', [
                    'SUPER_ADMIN',
                    'SA_USER',
                    'INSTITUTION_ADMIN',
                    'PARTNER_MANAGER',
                    'PARTNER_SCAN_AGENT',
                ]);
        })->update(['user_type_id' => null]);
    }
};
