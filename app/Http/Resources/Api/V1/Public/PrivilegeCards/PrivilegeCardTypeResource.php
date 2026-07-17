<?php

namespace App\Http\Resources\Api\V1\Public\PrivilegeCards;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PrivilegeCardTypeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'price' => (float) $this->price,
            'currency' => $this->currency,
            'benefits' => $this->benefits ?? [],
            'discount_type' => $this->discount_type,
            'discount_value' => (float) $this->discount_value,
            'duration_months' => $this->duration_months,
            'status' => $this->status,
            'sort_order' => $this->sort_order,
        ];
    }
}
