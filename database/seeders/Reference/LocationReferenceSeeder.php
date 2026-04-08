<?php

namespace Database\Seeders\Reference;

use App\Models\City;
use App\Models\Commune;
use App\Models\Country;
use App\Models\Neighborhood;
use App\Models\SubNeighborhood;
use Illuminate\Database\Seeder;

class LocationReferenceSeeder extends Seeder
{
    public function run(): void
    {
        $country = Country::query()->updateOrCreate(
            ['code' => 'CI'],
            ['name' => "Cote d'Ivoire", 'dial_code' => '225', 'flag' => '🇨🇮', 'status' => 'active'],
        );

        $city = City::query()->updateOrCreate(
            ['code' => 'ABJ'],
            ['country_id' => $country->id, 'name' => 'Abidjan', 'status' => 'active'],
        );

        $communes = [
            ['name' => 'Abobo', 'code' => 'ABJ-ABOBO'],
            ['name' => 'Adjame', 'code' => 'ABJ-ADJAME'],
            ['name' => 'Anyama', 'code' => 'ABJ-ANYAMA'],
            ['name' => 'Attecoube', 'code' => 'ABJ-ATTECOUBE'],
            ['name' => 'Bingerville', 'code' => 'ABJ-BINGERVILLE'],
            ['name' => 'Cocody', 'code' => 'ABJ-COCODY'],
            ['name' => 'Koumassi', 'code' => 'ABJ-KOUMASSI'],
            ['name' => 'Marcory', 'code' => 'ABJ-MARCORY'],
            ['name' => 'Plateau', 'code' => 'ABJ-PLATEAU'],
            ['name' => 'Port-Bouet', 'code' => 'ABJ-PORT-BOUET'],
            ['name' => 'Songon', 'code' => 'ABJ-SONGON'],
            ['name' => 'Treichville', 'code' => 'ABJ-TREICHVILLE'],
            ['name' => 'Yopougon', 'code' => 'ABJ-YOPOUGON'],
        ];

        foreach ($communes as $commune) {
            $createdCommune = Commune::query()->updateOrCreate(
                ['code' => $commune['code']],
                ['city_id' => $city->id, 'name' => $commune['name'], 'status' => 'active'],
            );

            foreach ($this->neighborhoodsFor($createdCommune->code) as $neighborhoodData) {
                $neighborhood = Neighborhood::query()->updateOrCreate(
                    ['code' => $neighborhoodData['code']],
                    [
                        'commune_id' => $createdCommune->id,
                        'name' => $neighborhoodData['name'],
                        'status' => 'active',
                    ],
                );

                foreach ($neighborhoodData['sub_neighborhoods'] as $subNeighborhoodData) {
                    SubNeighborhood::query()->updateOrCreate(
                        ['code' => $subNeighborhoodData['code']],
                        [
                            'neighborhood_id' => $neighborhood->id,
                            'name' => $subNeighborhoodData['name'],
                            'status' => 'active',
                        ],
                    );
                }
            }
        }
    }

    private function neighborhoodsFor(string $communeCode): array
    {
        return match ($communeCode) {
            'ABJ-COCODY' => [
                [
                    'name' => 'Riviera',
                    'code' => 'ABJ-COCODY-RIVIERA',
                    'sub_neighborhoods' => [
                        ['name' => 'Riviera Golf', 'code' => 'ABJ-COCODY-RIVIERA-GOLF'],
                        ['name' => 'Riviera Palmeraie', 'code' => 'ABJ-COCODY-RIVIERA-PALMERAIE'],
                    ],
                ],
                [
                    'name' => 'Angre',
                    'code' => 'ABJ-COCODY-ANGRE',
                    'sub_neighborhoods' => [
                        ['name' => '8eme Tranche', 'code' => 'ABJ-COCODY-ANGRE-8T'],
                        ['name' => 'Cite SIR', 'code' => 'ABJ-COCODY-ANGRE-SIR'],
                    ],
                ],
            ],
            'ABJ-YOPOUGON' => [
                [
                    'name' => 'Niangon',
                    'code' => 'ABJ-YOPOUGON-NIANGON',
                    'sub_neighborhoods' => [
                        ['name' => 'Niangon Nord', 'code' => 'ABJ-YOPOUGON-NIANGON-NORD'],
                        ['name' => 'Niangon Sud', 'code' => 'ABJ-YOPOUGON-NIANGON-SUD'],
                    ],
                ],
                [
                    'name' => 'Sicogi',
                    'code' => 'ABJ-YOPOUGON-SICOGI',
                    'sub_neighborhoods' => [
                        ['name' => 'Sicogi Toits Rouges', 'code' => 'ABJ-YOPOUGON-SICOGI-TR'],
                        ['name' => 'Sicogi Cite Verte', 'code' => 'ABJ-YOPOUGON-SICOGI-CV'],
                    ],
                ],
            ],
            'ABJ-MARCORY' => [
                [
                    'name' => 'Zone 4',
                    'code' => 'ABJ-MARCORY-ZONE4',
                    'sub_neighborhoods' => [
                        ['name' => 'Biétry', 'code' => 'ABJ-MARCORY-ZONE4-BIETRY'],
                        ['name' => 'Rue du Canal', 'code' => 'ABJ-MARCORY-ZONE4-CANAL'],
                    ],
                ],
                [
                    'name' => 'Anoumabo',
                    'code' => 'ABJ-MARCORY-ANOUMABO',
                    'sub_neighborhoods' => [
                        ['name' => 'Village', 'code' => 'ABJ-MARCORY-ANOUMABO-VILLAGE'],
                        ['name' => 'Petit Marche', 'code' => 'ABJ-MARCORY-ANOUMABO-MARCHE'],
                    ],
                ],
            ],
            'ABJ-KOUMASSI' => [
                [
                    'name' => 'Soweto',
                    'code' => 'ABJ-KOUMASSI-SOWETO',
                    'sub_neighborhoods' => [
                        ['name' => 'Campement', 'code' => 'ABJ-KOUMASSI-SOWETO-CAMP'],
                        ['name' => 'Grand Carrefour', 'code' => 'ABJ-KOUMASSI-SOWETO-CARREFOUR'],
                    ],
                ],
                [
                    'name' => 'Remblais',
                    'code' => 'ABJ-KOUMASSI-REMBLAIS',
                    'sub_neighborhoods' => [
                        ['name' => 'Remblais Nord', 'code' => 'ABJ-KOUMASSI-REMBLAIS-NORD'],
                        ['name' => 'Remblais Sud', 'code' => 'ABJ-KOUMASSI-REMBLAIS-SUD'],
                    ],
                ],
            ],
            default => [
                [
                    'name' => 'Quartier Central',
                    'code' => $communeCode.'-CENTRAL',
                    'sub_neighborhoods' => [
                        ['name' => 'Secteur 1', 'code' => $communeCode.'-CENTRAL-S1'],
                        ['name' => 'Secteur 2', 'code' => $communeCode.'-CENTRAL-S2'],
                    ],
                ],
                [
                    'name' => 'Quartier Residentiel',
                    'code' => $communeCode.'-RESIDENTIEL',
                    'sub_neighborhoods' => [
                        ['name' => 'Bloc A', 'code' => $communeCode.'-RESIDENTIEL-A'],
                        ['name' => 'Bloc B', 'code' => $communeCode.'-RESIDENTIEL-B'],
                    ],
                ],
            ],
        };
    }
}
