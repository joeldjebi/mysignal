<?php

namespace App\Domain\PrivilegeCards\Actions;

use App\Domain\Payments\Enums\PaymentStatus;
use App\Models\PrivilegeCardPaymentSession;
use App\Models\PrivilegeCardType;
use App\Models\PublicUser;
use App\Services\Payments\FineoPayClient;
use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;

class InitiatePrivilegeCardFineoPaymentAction
{
    public function __construct(
        private readonly FineoPayClient $fineoPayClient,
    ) {}

    public function handle(PublicUser $publicUser, PrivilegeCardType $type): PrivilegeCardPaymentSession
    {
        if ($type->status !== 'active') {
            throw ValidationException::withMessages([
                'privilege_card_type_id' => ['Cette carte privilège n’est pas disponible.'],
            ]);
        }

        $cardAmount = $this->cardAmount($type);

        $existingPending = PrivilegeCardPaymentSession::query()
            ->where('public_user_id', $publicUser->id)
            ->where('privilege_card_type_id', $type->id)
            ->where('status', PaymentStatus::Pending->value)
            ->latest('id')
            ->first();

        if ($existingPending !== null && filled($existingPending->checkout_link)) {
            if ((int) $existingPending->amount === $cardAmount && $existingPending->currency === $type->currency) {
                return $existingPending->load('type', 'card');
            }

            $existingPending->update([
                'status' => PaymentStatus::Failed->value,
                'metadata' => [
                    ...($existingPending->metadata ?? []),
                    'failure_reason' => 'card_price_changed',
                    'expected_card_amount' => $cardAmount,
                    'previous_session_amount' => (int) $existingPending->amount,
                ],
            ]);
        }

        $syncRef = $this->generateSyncRef();

        $session = PrivilegeCardPaymentSession::query()->create([
            'public_user_id' => $publicUser->id,
            'privilege_card_type_id' => $type->id,
            'sync_ref' => $syncRef,
            'amount' => $cardAmount,
            'currency' => $type->currency,
            'status' => PaymentStatus::Pending->value,
            'provider' => 'fineopay',
            'initiated_at' => CarbonImmutable::now(),
        ]);

        $checkoutLink = $this->fineoPayClient->createCheckoutLink([
            'title' => 'Achat carte privilege '.$type->name,
            'amount' => $cardAmount,
            'callbackUrl' => $this->callbackUrl(),
            'syncRef' => $syncRef,
        ]);

        $session->update([
            'checkout_link' => $checkoutLink,
            'metadata' => [
                'fineopay_checkout_created_at' => now()->toIso8601String(),
                'card_type_code' => $type->code,
            ],
        ]);

        return $session->fresh(['type', 'card']);
    }

    private function callbackUrl(): string
    {
        $url = route('api.public.privilege-card-payments.fineopay.callback');
        $token = (string) config('services.fineopay.callback_token');

        return $token !== '' ? $url.'?token='.urlencode($token) : $url;
    }

    private function cardAmount(PrivilegeCardType $type): int
    {
        $amount = (int) round((float) $type->price);

        if ($amount < 1) {
            throw ValidationException::withMessages([
                'privilege_card_type_id' => ['Le montant de cette carte privilège est invalide.'],
            ]);
        }

        return $amount;
    }

    private function generateSyncRef(): string
    {
        return 'PVC-'.CarbonImmutable::now()->format('YmdHis').'-'.strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
    }
}
