<?php

namespace App\Http\Resources\Api\V1\Public\Catalogs;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceCategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'code' => $this['code'] ?? null,
            'name' => $this['name'] ?? null,
            'signal_code' => $this['signal_code'] ?? null,
        ];
    }
}
