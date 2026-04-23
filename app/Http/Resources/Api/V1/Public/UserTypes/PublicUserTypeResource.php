<?php

namespace App\Http\Resources\Api\V1\Public\UserTypes;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicUserTypeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'profile_kind' => $this->profile_kind,
            'sort_order' => $this->sort_order,
            'pricing_rule' => $this->pricingRule ? [
                'id' => $this->pricingRule->id,
                'code' => $this->pricingRule->code,
                'label' => $this->pricingRule->label,
                'amount' => (int) $this->pricingRule->amount,
                'currency' => $this->pricingRule->currency,
            ] : null,
        ];
    }
}
