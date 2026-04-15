<?php

namespace App\Http\Resources\Api\V1\Public\Subscriptions;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionPaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'status' => $this->status,
            'provider' => $this->provider,
            'provider_reference' => $this->provider_reference,
            'initiated_at' => optional($this->initiated_at)->toISOString(),
            'paid_at' => optional($this->paid_at)->toISOString(),
            'subscription' => $this->whenLoaded('subscription', fn () => new UpSubscriptionResource($this->subscription)),
        ];
    }
}
