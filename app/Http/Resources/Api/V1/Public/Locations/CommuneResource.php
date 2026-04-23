<?php

namespace App\Http\Resources\Api\V1\Public\Locations;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommuneResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'city' => $this->whenLoaded('city', fn () => [
                'id' => $this->city?->id,
                'name' => $this->city?->name,
                'code' => $this->city?->code,
                'country' => $this->city?->country ? [
                    'id' => $this->city->country->id,
                    'name' => $this->city->country->name,
                    'code' => $this->city->country->code,
                ] : null,
            ]),
        ];
    }
}
