<?php

namespace App\Http\Resources\Api\V1\Public\Subscriptions;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UpSubscriptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'start_date' => optional($this->start_date)->toISOString(),
            'end_date' => optional($this->end_date)->toISOString(),
            'grace_period_days' => $this->grace_period_days,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'plan' => [
                'id' => $this->plan?->id,
                'code' => $this->plan?->code,
                'name' => $this->plan?->name,
                'duration_months' => $this->plan?->duration_months,
            ],
            'activated_at' => optional($this->activated_at)->toISOString(),
            'expired_at' => optional($this->expired_at)->toISOString(),
            'created_at' => optional($this->created_at)->toISOString(),
            'payments' => SubscriptionPaymentResource::collection($this->whenLoaded('payments')),
        ];
    }
}
