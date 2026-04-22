<?php

namespace App\Http\Resources\Api\V1\Partner\Discounts;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PartnerDiscountTransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'scan_reference' => $this->scan_reference,
            'verification_status' => $this->verification_status,
            'status' => $this->status,
            'original_amount' => $this->original_amount !== null ? (float) $this->original_amount : null,
            'discount_amount' => $this->discount_amount !== null ? (float) $this->discount_amount : null,
            'final_amount' => $this->final_amount !== null ? (float) $this->final_amount : null,
            'discount_type_snapshot' => $this->discount_type_snapshot,
            'discount_value_snapshot' => $this->discount_value_snapshot !== null ? (float) $this->discount_value_snapshot : null,
            'applied_at' => $this->applied_at?->toIso8601String(),
            'partner_user' => $this->whenLoaded('partnerUser', fn () => [
                'id' => $this->partnerUser?->id,
                'name' => $this->partnerUser?->name,
                'email' => $this->partnerUser?->email,
            ]),
            'offer' => $this->whenLoaded('offer', fn () => new PartnerDiscountOfferResource($this->offer)),
            'card' => $this->whenLoaded('discountCard', fn () => [
                'id' => $this->discountCard?->id,
                'card_number' => $this->discountCard?->card_number,
                'card_uuid' => $this->discountCard?->card_uuid,
            ]),
            'public_user' => $this->whenLoaded('publicUser', fn () => [
                'id' => $this->publicUser?->id,
                'display_name' => trim((string) ($this->publicUser?->first_name.' '.$this->publicUser?->last_name)),
                'phone' => $this->publicUser?->phone,
            ]),
        ];
    }
}
