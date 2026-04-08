<?php

namespace Tests\Feature\Institution;

use App\Models\Feature;
use App\Models\Organization;
use App\Models\OrganizationType;
use App\Models\User;
use Database\Seeders\Admin\SuperAdminSeeder;
use Database\Seeders\Reference\FeatureSeeder;
use Database\Seeders\Reference\OrganizationTypeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class InstitutionAdminAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_institution_admin_can_login_and_access_common_portal(): void
    {
        $this->seed([
            FeatureSeeder::class,
            OrganizationTypeSeeder::class,
            SuperAdminSeeder::class,
        ]);

        $organizationType = OrganizationType::query()->where('code', 'ENTREPRISE_GO')->firstOrFail();

        $organization = Organization::query()->create([
            'organization_type_id' => $organizationType->id,
            'code' => 'CIE',
            'name' => 'Compagnie Ivoirienne d Electricite',
            'portal_key' => 'portail-cie',
            'status' => 'active',
        ]);

        $user = User::query()->create([
            'organization_id' => $organization->id,
            'name' => 'Admin CIE',
            'email' => 'admin.cie@example.com',
            'password' => Hash::make('12345678'),
            'status' => 'active',
            'is_super_admin' => false,
        ]);

        $user->features()->sync([
            Feature::query()->where('code', 'PUBLIC_REPORTS')->value('id'),
            Feature::query()->where('code', 'PUBLIC_REPORT_STATISTICS')->value('id'),
        ]);

        $response = $this->post('/institution/login', [
            'email' => 'admin.cie@example.com',
            'password' => '12345678',
        ]);

        $response->assertRedirect('/institution/dashboard');

        $this->get('/institution/dashboard')
            ->assertOk()
            ->assertSee('Compagnie Ivoirienne d Electricite')
            ->assertSee('/institution/reports', false)
            ->assertSee('/institution/statistics', false);

        $this->get('/institution/reports')
            ->assertOk()
            ->assertSee('Signalements');

        $this->get('/institution/statistics')
            ->assertOk()
            ->assertSee('Statistiques');

        $this->get('/institution/meters')
            ->assertForbidden();
    }
}
