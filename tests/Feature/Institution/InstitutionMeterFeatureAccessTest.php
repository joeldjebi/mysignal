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

class InstitutionMeterFeatureAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_institution_admin_with_meter_feature_can_access_meter_page(): void
    {
        $this->seed([
            FeatureSeeder::class,
            OrganizationTypeSeeder::class,
            SuperAdminSeeder::class,
        ]);

        $organizationType = OrganizationType::query()->where('code', 'ENTREPRISE_GO')->firstOrFail();

        $organization = Organization::query()->create([
            'organization_type_id' => $organizationType->id,
            'code' => 'SODECI',
            'name' => 'SODECI',
            'portal_key' => 'portail-sodeci',
            'status' => 'active',
        ]);

        $user = User::query()->create([
            'organization_id' => $organization->id,
            'name' => 'Admin SODECI',
            'email' => 'admin.sodeci@example.com',
            'password' => Hash::make('12345678'),
            'status' => 'active',
            'is_super_admin' => false,
        ]);

        $user->features()->sync([
            Feature::query()->where('code', 'PUBLIC_METERS')->value('id'),
        ]);

        $this->post('/institution/login', [
            'email' => 'admin.sodeci@example.com',
            'password' => '12345678',
        ])->assertRedirect('/institution/dashboard');

        $this->get('/institution/meters')
            ->assertOk()
            ->assertSee('Compteurs');
    }
}
