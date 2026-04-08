<?php

namespace App\Http\Resources\Api\V1\Public\Locations;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LocationTreeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'cities' => $this->cities->map(fn ($city) => [
                'id' => $city->id,
                'name' => $city->name,
                'code' => $city->code,
                'communes' => $city->communes->map(fn ($commune) => [
                    'id' => $commune->id,
                    'name' => $commune->name,
                    'code' => $commune->code,
                    'neighborhoods' => $commune->neighborhoods->map(fn ($neighborhood) => [
                        'id' => $neighborhood->id,
                        'name' => $neighborhood->name,
                        'code' => $neighborhood->code,
                        'sub_neighborhoods' => $neighborhood->subNeighborhoods->map(fn ($subNeighborhood) => [
                            'id' => $subNeighborhood->id,
                            'name' => $subNeighborhood->name,
                            'code' => $subNeighborhood->code,
                        ])->values(),
                    ])->values(),
                ])->values(),
            ])->values(),
        ];
    }
}
