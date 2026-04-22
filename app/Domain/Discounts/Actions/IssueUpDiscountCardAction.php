<?php

namespace App\Domain\Discounts\Actions;

use App\Models\UpDiscountCard;
use App\Models\UpSubscription;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class IssueUpDiscountCardAction
{
    public function handle(UpSubscription $subscription): UpDiscountCard
    {
        return DB::transaction(function () use ($subscription): UpDiscountCard {
            $subscription = UpSubscription::query()
                ->with('publicUser')
                ->lockForUpdate()
                ->findOrFail($subscription->id);

            $card = UpDiscountCard::query()->firstOrNew([
                'up_subscription_id' => $subscription->id,
            ]);

            $card->fill([
                'public_user_id' => $subscription->public_user_id,
                'card_uuid' => $card->card_uuid ?: (string) Str::uuid(),
                'card_number' => $card->card_number ?: $this->generateCardNumber(),
                'status' => 'active',
                'issued_at' => $card->issued_at ?: now(),
                'activated_at' => now(),
                'expires_at' => $subscription->end_date,
                'suspended_at' => null,
                'revoked_at' => null,
                'metadata' => array_filter([
                    'subscription_plan_id' => $subscription->subscription_plan_id,
                    'issued_source' => 'subscription_activation',
                ], fn ($value) => $value !== null),
            ]);

            $card->save();

            return $card->refresh()->load('subscription.plan');
        });
    }

    private function generateCardNumber(): string
    {
        do {
            $number = 'UP-RED-'.now()->format('ymd').'-'.Str::upper(Str::random(8));
        } while (UpDiscountCard::query()->where('card_number', $number)->exists());

        return $number;
    }
}
