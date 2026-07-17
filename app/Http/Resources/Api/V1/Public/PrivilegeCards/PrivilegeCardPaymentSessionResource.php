<?php

namespace App\Http\Resources\Api\V1\Public\PrivilegeCards;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PrivilegeCardPaymentSessionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sync_ref' => $this->sync_ref,
            'amount' => (float) $this->amount,
            'currency' => $this->currency,
            'status' => $this->status,
            'provider' => $this->provider,
            'provider_reference' => $this->provider_reference,
            'checkout_link' => $this->checkout_link,
            'initiated_at' => $this->initiated_at?->toIso8601String(),
            'paid_at' => $this->paid_at?->toIso8601String(),
            'type' => $this->whenLoaded('type', fn () => new PrivilegeCardTypeResource($this->type)),
            'card' => $this->whenLoaded('card', fn () => $this->card ? new PrivilegeCardResource($this->card->loadMissing('type')) : null),
        ];
    }
}
