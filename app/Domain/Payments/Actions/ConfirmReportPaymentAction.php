<?php

namespace App\Domain\Payments\Actions;

use App\Domain\Payments\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\PublicUser;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ConfirmReportPaymentAction
{
    public function handle(PublicUser $user, Payment $payment): Payment
    {
        if ((int) $payment->public_user_id !== (int) $user->id) {
            throw ValidationException::withMessages([
                'payment' => ['Vous ne pouvez pas confirmer ce paiement.'],
            ]);
        }

        if ($payment->status === PaymentStatus::Paid->value) {
            return $payment->fresh(['pricingRule', 'incidentReport']);
        }

        return DB::transaction(function () use ($payment): Payment {
            $paidAt = CarbonImmutable::now();

            $payment->forceFill([
                'status' => PaymentStatus::Paid->value,
                'paid_at' => $paidAt,
                'provider_reference' => 'SIM-'.$payment->reference,
                'metadata' => [
                    'mode' => 'simulated',
                    'confirmed_at' => $paidAt->toIso8601String(),
                ],
            ])->save();

            $payment->incidentReport()->update([
                'payment_status' => PaymentStatus::Paid->value,
                'paid_at' => $paidAt,
            ]);

            return $payment->fresh(['pricingRule', 'incidentReport']);
        });
    }
}
