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
            'card_source' => $this->card_source ?? 'up_discount_card',
            'partner_user' => $this->whenLoaded('partnerUser', fn () => [
                'id' => $this->partnerUser?->id,
                'name' => $this->partnerUser?->name,
                'email' => $this->partnerUser?->email,
            ]),
            'offer' => $this->relationLoaded('offer') && $this->offer ? new PartnerDiscountOfferResource($this->offer) : null,
            'card' => $this->cardPayload(),
            'public_user' => $this->whenLoaded('publicUser', fn () => [
                'id' => $this->publicUser?->id,
                'display_name' => trim((string) ($this->publicUser?->first_name.' '.$this->publicUser?->last_name)),
                'phone' => $this->publicUser?->phone,
            ]),
        ];
    }

    private function cardPayload(): ?array
    {
        if (($this->card_source ?? 'up_discount_card') === 'privilege_card') {
            if (! $this->relationLoaded('privilegeCard') || $this->privilegeCard === null) {
                return null;
            }

            $card = $this->privilegeCard;

            return [
                'id' => $card->id,
                'card_number' => $card->card_number,
                'card_uuid' => $card->card_uuid,
                'source' => 'privilege_card',
                'type' => $card->relationLoaded('type') && $card->type ? [
                    'id' => $card->type->id,
                    'name' => $card->type->name,
                    'code' => $card->type->code,
                    'discount_type' => $card->type->discount_type,
                    'discount_value' => $card->type->discount_value !== null ? (float) $card->type->discount_value : null,
                    'currency' => $card->type->currency,
                ] : null,
            ];
        }

        if (! $this->relationLoaded('discountCard') || $this->discountCard === null) {
            return null;
        }

        $card = $this->discountCard;

        return [
            'id' => $card->id,
            'card_number' => $card->card_number,
            'card_uuid' => $card->card_uuid,
            'source' => 'up_discount_card',
            'type' => null,
        ];
    }
}
