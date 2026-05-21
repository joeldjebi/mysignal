<?php

namespace Tests\Feature\Public\Catalogs;

use App\Models\BusinessSector;
use App\Models\Organization;
use App\Models\SignalType;
use Database\Seeders\Reference\ApplicationSeeder;
use Database\Seeders\Reference\LocationReferenceSeeder;
use Database\Seeders\Reference\OrganizationTypeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_catalog_endpoints_return_active_flat_data_for_mobile_forms(): void
    {
        $this->seed(ApplicationSeeder::class);
        $this->seed(OrganizationTypeSeeder::class);
        $this->seed(LocationReferenceSeeder::class);

        $applicationId = \App\Models\Application::query()->where('code', 'ÉLÈCTRICITÉ')->value('id');
        $organizationTypeId = \App\Models\OrganizationType::query()->where('code', 'PARTNER_ESTABLISHMENT')->value('id');

        $organization = Organization::query()->create([
            'application_id' => $applicationId,
            'organization_type_id' => $organizationTypeId,
            'code' => 'SHOP_PARTNER',
            'name' => 'Shop Partner',
            'email' => 'partner@example.test',
            'phone' => '0700000000',
            'status' => 'active',
        ]);

        SignalType::query()->create([
            'application_id' => $applicationId,
            'organization_id' => $organization->id,
            'network_type' => 'ÉLÈCTRICITÉ',
            'code' => 'NETWORK_OUTAGE',
            'label' => 'Coupure internet',
            'description' => 'Test catalog service category',
            'default_sla_hours' => 4,
            'status' => 'active',
        ]);

        $applicationsResponse = $this->getJson('/api/v1/public/applications');
        $applicationTypesResponse = $this->getJson('/api/v1/public/application-types');
        $organizationsResponse = $this->getJson('/api/v1/public/organizations?application_id='.$applicationId);
        $countriesResponse = $this->getJson('/api/v1/public/countries');
        $citiesResponse = $this->getJson('/api/v1/public/cities?country_id=1');
        $serviceCategoriesResponse = $this->getJson('/api/v1/public/service-categories?application_id='.$applicationId.'&organization_id='.$organization->id);

        BusinessSector::query()->where('code', 'ENERGIE')->update(['status' => 'inactive']);

        $businessSectorsResponse = $this->getJson('/api/v1/public/business-sectors');

        $applicationsResponse->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.applications.0.code', 'ÉLÈCTRICITÉ');

        $applicationTypesResponse->assertOk()
            ->assertJsonPath('data.application_types.0.code', 'ASSURANCE');

        $organizationsResponse->assertOk()
            ->assertJsonPath('data.organizations.0.code', 'SHOP_PARTNER')
            ->assertJsonPath('data.organizations.0.application.code', 'ÉLÈCTRICITÉ')
            ->assertJsonPath('data.organizations.0.organization_type.code', 'PARTNER_ESTABLISHMENT');

        $countriesResponse->assertOk()
            ->assertJsonPath('data.countries.0.code', 'CI');

        $citiesResponse->assertOk()
            ->assertJsonPath('data.cities.0.name', 'Abidjan')
            ->assertJsonPath('data.cities.0.country.code', 'CI');

        $serviceCategoriesResponse->assertOk()
            ->assertJsonCount(0, 'data.service_categories')
            ->assertJsonStructure([
                'data' => [
                    'service_categories',
                ],
            ]);

        $businessSectorsResponse->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonMissingPath('data.business_sectors.0.status')
            ->assertJsonMissing(['code' => 'ENERGIE'])
            ->assertJsonStructure([
                'data' => [
                    'business_sectors' => [
                        '*' => [
                            'id',
                            'code',
                            'name',
                            'description',
                        ],
                    ],
                ],
            ]);
    }
}
