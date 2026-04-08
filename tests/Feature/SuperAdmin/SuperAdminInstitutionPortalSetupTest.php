<?php

namespace Tests\Feature\SuperAdmin;

use App\Models\Feature;
use App\Models\OrganizationType;
use App\Models\User;
use Database\Seeders\Admin\SuperAdminSeeder;
use Database\Seeders\Reference\FeatureSeeder;
use Database\Seeders\Reference\OrganizationTypeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminInstitutionPortalSetupTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_create_organization_and_institution_admin(): void
    {
        $this->seed([
            FeatureSeeder::class,
            OrganizationTypeSeeder::class,
            SuperAdminSeeder::class,
        ]);

        $superAdmin = User::query()->where('email', 'jo.djebi@gmail.com')->firstOrFail();
        $organizationType = OrganizationType::query()->where('code', 'ENTREPRISE_GO')->firstOrFail();
        $reportsFeature = Feature::query()->where('code', 'PUBLIC_REPORTS')->firstOrFail();

        $this->actingAs($superAdmin)
            ->post('/sa/organizations', [
                'organization_type_id' => $organizationType->id,
                'code' => 'CIE',
                'name' => 'Compagnie Ivoirienne d Electricite',
                'portal_key' => 'portail-cie',
                'email' => 'contact@cie.ci',
            ])
            ->assertRedirect('/sa/organizations');

        $organizationId = \App\Models\Organization::query()->where('code', 'CIE')->value('id');

        $this->actingAs($superAdmin)
            ->post('/sa/institution-admins', [
                'organization_id' => $organizationId,
                'name' => 'Admin CIE',
                'email' => 'admin.cie@example.com',
                'phone' => '0700000888',
                'password' => '12345678',
                'feature_ids' => [$reportsFeature->id],
            ])
            ->assertRedirect('/sa/institution-admins');

        $this->assertDatabaseHas('organizations', [
            'code' => 'CIE',
            'portal_key' => 'portail-cie',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'admin.cie@example.com',
            'organization_id' => $organizationId,
            'status' => 'active',
        ]);

        $adminId = User::query()->where('email', 'admin.cie@example.com')->value('id');

        $this->assertDatabaseHas('feature_user', [
            'user_id' => $adminId,
            'feature_id' => $reportsFeature->id,
        ]);
    }
}
