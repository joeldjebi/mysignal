<?php

namespace App\Http\Resources\Api\V1\Public\Discounts;

use App\Http\Resources\Api\V1\Public\Subscriptions\UpSubscriptionResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UpDiscountCardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'card_uuid' => $this->card_uuid,
            'card_number' => $this->card_number,
            'status' => $this->status,
            'issued_at' => $this->issued_at?->toIso8601String(),
            'activated_at' => $this->activated_at?->toIso8601String(),
            'expires_at' => $this->expires_at?->toIso8601String(),
            'last_used_at' => $this->last_used_at?->toIso8601String(),
            'qr_payload' => $this->card_uuid,
            'subscription' => $this->whenLoaded('subscription', fn () => new UpSubscriptionResource($this->subscription)),
        ];
    }
}
