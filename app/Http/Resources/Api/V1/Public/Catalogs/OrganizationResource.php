<?php

namespace App\Http\Resources\Api\V1\Public\Catalogs;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrganizationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'application' => $this->whenLoaded('application', fn () => [
                'id' => $this->application?->id,
                'code' => $this->application?->code,
                'name' => $this->application?->name,
            ]),
            'organization_type' => $this->whenLoaded('organizationType', fn () => [
                'id' => $this->organizationType?->id,
                'code' => $this->organizationType?->code,
                'name' => $this->organizationType?->name,
            ]),
        ];
    }
}
