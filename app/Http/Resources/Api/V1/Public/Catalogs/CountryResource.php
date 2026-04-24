<?php

namespace App\Http\Resources\Api\V1\Public\Catalogs;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CountryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'dial_code' => $this->dial_code,
            'flag' => $this->flag,
        ];
    }
}
