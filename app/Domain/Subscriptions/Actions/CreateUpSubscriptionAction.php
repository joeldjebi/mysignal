<?php

namespace App\Domain\Subscriptions\Actions;

use App\Domain\Subscriptions\Enums\UpSubscriptionStatus;
use App\Models\PublicUser;
use App\Models\SubscriptionPlan;
use App\Models\UpSubscription;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateUpSubscriptionAction
{
    public function handle(PublicUser $publicUser): UpSubscription
    {
        $existingSubscription = $publicUser->subscriptions()
            ->whereIn('status', [
                UpSubscriptionStatus::Pending->value,
                UpSubscriptionStatus::Active->value,
            ])
            ->latest('id')
            ->first();

        if ($existingSubscription?->status === UpSubscriptionStatus::Active->value) {
            throw ValidationException::withMessages([
                'subscription' => ['Vous avez deja un abonnement actif.'],
            ]);
        }

        if ($existingSubscription?->status === UpSubscriptionStatus::Pending->value) {
            return $existingSubscription;
        }

        $plan = SubscriptionPlan::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->first();

        if ($plan === null) {
            throw ValidationException::withMessages([
                'subscription_plan' => ['Aucun plan d abonnement actif n est disponible.'],
            ]);
        }

        return DB::transaction(function () use ($publicUser, $plan): UpSubscription {
            return UpSubscription::query()->create([
                'public_user_id' => $publicUser->id,
                'subscription_plan_id' => $plan->id,
                'status' => UpSubscriptionStatus::Pending->value,
                'grace_period_days' => 1,
                'amount' => $plan->price,
                'currency' => $plan->currency,
                'metadata' => [
                    'plan_code' => $plan->code,
                    'plan_name' => $plan->name,
                    'plan_duration_months' => $plan->duration_months,
                ],
            ]);
        });
    }
}
