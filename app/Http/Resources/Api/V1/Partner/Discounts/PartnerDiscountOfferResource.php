<?php

namespace App\Http\Resources\Api\V1\Partner\Discounts;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PartnerDiscountOfferResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'discount_type' => $this->discount_type,
            'discount_value' => $this->discount_value !== null ? (float) $this->discount_value : null,
            'currency' => $this->currency,
            'minimum_purchase_amount' => $this->minimum_purchase_amount !== null ? (float) $this->minimum_purchase_amount : null,
            'maximum_discount_amount' => $this->maximum_discount_amount !== null ? (float) $this->maximum_discount_amount : null,
            'max_uses_per_card' => $this->max_uses_per_card,
            'max_uses_per_day' => $this->max_uses_per_day,
            'starts_at' => $this->starts_at?->toIso8601String(),
            'ends_at' => $this->ends_at?->toIso8601String(),
            'status' => $this->status,
        ];
    }
}
