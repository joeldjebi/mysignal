<?php

namespace App\Domain\Subscriptions\Actions;

use App\Domain\Payments\Enums\PaymentStatus;
use App\Domain\Subscriptions\Enums\UpSubscriptionStatus;
use App\Models\PublicUser;
use App\Models\SubscriptionPayment;
use App\Models\UpSubscription;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateUpSubscriptionPaymentAction
{
    public function __construct(
        private readonly CreateUpSubscriptionAction $createSubscription,
    ) {}

    public function handle(PublicUser $publicUser): SubscriptionPayment
    {
        $subscription = $this->createSubscription->handle($publicUser);

        if ($subscription->status !== UpSubscriptionStatus::Pending->value) {
            throw ValidationException::withMessages([
                'subscription' => ['Cet abonnement ne peut pas etre paye.'],
            ]);
        }

        $existingPending = $subscription->payments()
            ->where('status', PaymentStatus::Pending->value)
            ->latest('id')
            ->first();

        if ($existingPending !== null) {
            return $existingPending;
        }

        return DB::transaction(function () use ($publicUser, $subscription): SubscriptionPayment {
            return SubscriptionPayment::query()->create([
                'public_user_id' => $publicUser->id,
                'up_subscription_id' => $subscription->id,
                'reference' => $this->generateReference(),
                'amount' => $subscription->amount,
                'currency' => $subscription->currency,
                'status' => PaymentStatus::Pending->value,
                'provider' => 'simulated',
                'initiated_at' => CarbonImmutable::now(),
            ]);
        });
    }

    private function generateReference(): string
    {
        return 'SUB-'.CarbonImmutable::now()->format('YmdHis').'-'.strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
    }
}
