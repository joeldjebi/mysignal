<?php

namespace App\Domain\Subscriptions\Actions;

use App\Domain\Payments\Enums\PaymentStatus;
use App\Domain\Subscriptions\Enums\UpSubscriptionStatus;
use App\Models\PublicUser;
use App\Models\SubscriptionPayment;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ConfirmUpSubscriptionPaymentAction
{
    public function handle(PublicUser $publicUser, SubscriptionPayment $payment): SubscriptionPayment
    {
        if ((int) $payment->public_user_id !== (int) $publicUser->id) {
            throw ValidationException::withMessages([
                'payment' => ['Vous ne pouvez pas confirmer ce paiement.'],
            ]);
        }

        if ($payment->status === PaymentStatus::Paid->value) {
            return $payment->load('subscription.plan');
        }

        if ($payment->status !== PaymentStatus::Pending->value) {
            throw ValidationException::withMessages([
                'payment' => ['Ce paiement ne peut pas etre confirme.'],
            ]);
        }

        return DB::transaction(function () use ($payment): SubscriptionPayment {
            $payment = SubscriptionPayment::query()
                ->with('subscription.plan')
                ->lockForUpdate()
                ->findOrFail($payment->id);

            if ($payment->status === PaymentStatus::Paid->value) {
                return $payment;
            }

            $subscription = $payment->subscription;

            if ($subscription->status !== UpSubscriptionStatus::Pending->value) {
                throw ValidationException::withMessages([
                    'subscription' => ['Cet abonnement ne peut pas etre active.'],
                ]);
            }

            $now = CarbonImmutable::now();
            $durationMonths = (int) $subscription->plan->duration_months;

            $payment->update([
                'status' => PaymentStatus::Paid->value,
                'paid_at' => $now,
                'provider_reference' => $payment->provider_reference ?: 'SIM-'.$payment->reference,
            ]);

            $subscription->update([
                'status' => UpSubscriptionStatus::Active->value,
                'start_date' => $now,
                'end_date' => $now->addMonthsNoOverflow($durationMonths),
                'activated_at' => $now,
            ]);

            return $payment->refresh()->load('subscription.plan');
        });
    }
}
