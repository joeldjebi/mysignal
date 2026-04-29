<?php

namespace Tests\Feature\Auth;

use App\Models\Organization;
use App\Models\OrganizationType;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\UserAccess;
use Database\Seeders\Reference\RoleSeeder;
use Database\Seeders\Reference\SuperAdminPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserAccessProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_same_user_can_login_as_partner_and_institution_with_distinct_profile_permissions(): void
    {
        $this->seedAccessReferences();

        $institution = $this->organization('ELECTRICITE', 'CIE');
        $partner = $this->organization('PARTNER_ESTABLISHMENT', 'PARTNER_DEMO');
        $user = $this->user([
            'organization_id' => $institution->id,
            'email' => 'multi.profile@example.com',
            'phone' => '2250758754662',
        ]);

        $globalPartnerAdminRole = Role::query()->where('code', 'PARTNER_ADMIN')->firstOrFail();
        $partnerAgentRole = Role::query()->where('code', 'PARTNER_AGENT')->firstOrFail();

        $user->roles()->sync([$globalPartnerAdminRole->id]);

        $partnerAccess = UserAccess::query()->create([
            'user_id' => $user->id,
            'organization_id' => $partner->id,
            'portal' => 'partner',
            'status' => 'active',
        ]);
        $partnerAccess->roles()->sync([$partnerAgentRole->id]);

        $partnerResponse = $this->postJson('/api/v1/partner/auth/login', [
            'phone' => '+2250758754662',
            'password' => '12345678',
        ]);

        $partnerResponse->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.id', $user->id)
            ->assertJsonPath('data.user.organization.id', $partner->id)
            ->assertJsonPath('data.user.organization.organization_type.code', 'PARTNER_ESTABLISHMENT')
            ->assertJsonFragment(['PARTNER_DISCOUNT_SCAN']);

        $permissions = $partnerResponse->json('data.user.permissions');

        $this->assertContains('PARTNER_ACCESS_PORTAL', $permissions);
        $this->assertContains('PARTNER_DISCOUNT_APPLY', $permissions);
        $this->assertNotContains('PARTNER_USERS_MANAGE', $permissions);

        $institutionResponse = $this->post('/institution/login', [
            'email' => 'multi.profile@example.com',
            'password' => '12345678',
        ]);

        $institutionResponse->assertRedirect('/institution/dashboard');

        $this->assertAuthenticatedAs($user);
        $this->assertSame($institution->id, auth()->user()->organization_id);
    }

    public function test_partner_login_chooses_the_account_with_partner_access_when_phone_is_shared(): void
    {
        $this->seedAccessReferences();

        $institution = $this->organization('ELECTRICITE', 'CIE');
        $partner = $this->organization('PARTNER_ESTABLISHMENT', 'PARTNER_DEMO');
        $wrongProfile = $this->user([
            'organization_id' => $institution->id,
            'email' => 'institution.only@example.com',
            'phone' => '2250102030405',
        ]);
        $partnerProfile = $this->user([
            'organization_id' => null,
            'email' => 'partner.profile@example.com',
            'phone' => '2250102030405',
        ]);

        $partnerAccess = UserAccess::query()->create([
            'user_id' => $partnerProfile->id,
            'organization_id' => $partner->id,
            'portal' => 'partner',
            'status' => 'active',
        ]);
        $partnerAccess->roles()->sync([Role::query()->where('code', 'PARTNER_AGENT')->value('id')]);

        $response = $this->postJson('/api/v1/partner/auth/login', [
            'phone' => '+2250102030405',
            'password' => '12345678',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.user.id', $partnerProfile->id)
            ->assertJsonPath('data.user.organization.id', $partner->id);

        $this->assertNotSame($wrongProfile->id, $response->json('data.user.id'));
    }

    public function test_backoffice_access_can_be_attached_to_a_user_that_also_has_an_institution_profile(): void
    {
        $this->seedAccessReferences();

        $institution = $this->organization('ELECTRICITE', 'CIE');
        $user = $this->user([
            'organization_id' => $institution->id,
            'email' => 'backoffice.profile@example.com',
            'phone' => '22599999999',
        ]);

        $saRole = Role::query()->create([
            'code' => 'SA_TEST_DASHBOARD',
            'name' => 'SA test dashboard',
            'status' => 'active',
        ]);
        $saRole->permissions()->sync(Permission::query()
            ->whereIn('code', ['SA_ACCESS_PORTAL', 'SA_DASHBOARD_VIEW'])
            ->pluck('id')
            ->all());

        $backofficeAccess = UserAccess::query()->create([
            'user_id' => $user->id,
            'organization_id' => null,
            'portal' => 'backoffice',
            'status' => 'active',
        ]);
        $backofficeAccess->roles()->sync([$saRole->id]);

        $response = $this->post('/sa/login', [
            'email' => 'backoffice.profile@example.com',
            'password' => '12345678',
        ]);

        $response->assertRedirect('/sa/dashboard');

        $this->assertAuthenticatedAs($user);
        $this->assertNull(auth()->user()->organization_id);
        $this->assertTrue(auth()->user()->hasEffectivePermissionCode('SA_DASHBOARD_VIEW'));
    }

    public function test_super_admin_can_manage_user_access_profiles_from_system_user_screen(): void
    {
        $this->seedAccessReferences();

        $admin = $this->user([
            'email' => 'sa@example.com',
            'phone' => null,
            'is_super_admin' => true,
        ]);
        $systemUser = $this->user([
            'email' => 'managed@example.com',
            'phone' => '2250101010101',
            'organization_id' => null,
        ]);
        $partner = $this->organization('PARTNER_ESTABLISHMENT', 'PARTNER_UI');
        $agentRole = Role::query()->where('code', 'PARTNER_AGENT')->firstOrFail();
        $historyPermission = Permission::query()->where('code', 'PARTNER_DISCOUNT_HISTORY_VIEW')->firstOrFail();

        $this->actingAs($admin);

        $this->get(route('super-admin.system-users.show', $systemUser))
            ->assertOk()
            ->assertSee('Profils d acces')
            ->assertSee('Ajouter un profil');

        $this->post(route('super-admin.system-users.accesses.store', $systemUser), [
            'portal' => 'partner',
            'organization_id' => $partner->id,
            'status' => 'active',
            'role_ids' => [$agentRole->id],
            'permission_ids' => [$historyPermission->id],
        ])->assertRedirect(route('super-admin.system-users.show', $systemUser));

        $access = $systemUser->accesses()->where('portal', 'partner')->firstOrFail();

        $this->assertSame($partner->id, $access->organization_id);
        $this->assertTrue($access->roles()->whereKey($agentRole->id)->exists());
        $this->assertTrue($access->permissions()->whereKey($historyPermission->id)->exists());

        $this->put(route('super-admin.system-users.accesses.update', [$systemUser, $access]), [
            'portal' => 'partner',
            'organization_id' => $partner->id,
            'status' => 'inactive',
            'role_ids' => [],
            'permission_ids' => [$historyPermission->id],
        ])->assertRedirect(route('super-admin.system-users.show', $systemUser));

        $access->refresh();

        $this->assertSame('inactive', $access->status);
        $this->assertFalse($access->roles()->whereKey($agentRole->id)->exists());
        $this->assertTrue($access->permissions()->whereKey($historyPermission->id)->exists());

        $this->delete(route('super-admin.system-users.accesses.destroy', [$systemUser, $access]))
            ->assertRedirect(route('super-admin.system-users.show', $systemUser));

        $this->assertDatabaseMissing('user_accesses', ['id' => $access->id]);
    }

    private function seedAccessReferences(): void
    {
        $this->seed([
            SuperAdminPermissionSeeder::class,
            RoleSeeder::class,
        ]);
    }

    private function organization(string $typeCode, string $organizationCode): Organization
    {
        $type = OrganizationType::query()->firstOrCreate(
            ['code' => $typeCode],
            [
                'name' => $typeCode,
                'status' => 'active',
            ],
        );

        return Organization::query()->create([
            'organization_type_id' => $type->id,
            'code' => $organizationCode,
            'name' => $organizationCode,
            'status' => 'active',
        ]);
    }

    private function user(array $overrides = []): User
    {
        return User::query()->create(array_merge([
            'name' => 'Profil test',
            'email' => 'profile@example.com',
            'phone' => '22500000000',
            'password' => Hash::make('12345678'),
            'status' => 'active',
            'is_super_admin' => false,
        ], $overrides));
    }
}
