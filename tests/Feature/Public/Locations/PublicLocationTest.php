<?php

namespace Tests\Feature\Public\Locations;

use Database\Seeders\Reference\LocationReferenceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicLocationTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_location_endpoint_returns_seeded_abidjan_tree(): void
    {
        $this->seed(LocationReferenceSeeder::class);

        $response = $this->getJson('/api/v1/public/locations');

        $response->assertOk()
            ->assertJsonPath('data.countries.0.code', 'CI')
            ->assertJsonPath('data.countries.0.cities.0.name', 'Abidjan')
            ->assertJsonPath('data.countries.0.cities.0.communes.0.neighborhoods.0.sub_neighborhoods.0.name', 'Secteur 1');
    }

    public function test_public_communes_endpoint_returns_active_communes_for_mobile_registration(): void
    {
        $this->seed(LocationReferenceSeeder::class);

        $response = $this->getJson('/api/v1/public/communes');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.communes.0.name', 'Abobo')
            ->assertJsonPath('data.communes.0.city.name', 'Abidjan')
            ->assertJsonPath('data.communes.0.city.country.code', 'CI')
            ->assertJsonStructure([
                'data' => [
                    'communes' => [
                        '*' => [
                            'id',
                            'name',
                            'code',
                            'city' => [
                                'id',
                                'name',
                                'code',
                                'country' => [
                                    'id',
                                    'name',
                                    'code',
                                ],
                            ],
                        ],
                    ],
                ],
            ]);
    }
}
