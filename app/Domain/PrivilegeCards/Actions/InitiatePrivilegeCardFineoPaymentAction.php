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
                'privilege_card_type_id' => ['Cette carte privilege n est pas disponible.'],
            ]);
        }

        $existingPending = PrivilegeCardPaymentSession::query()
            ->where('public_user_id', $publicUser->id)
            ->where('privilege_card_type_id', $type->id)
            ->where('status', PaymentStatus::Pending->value)
            ->latest('id')
            ->first();

        if ($existingPending !== null && filled($existingPending->checkout_link)) {
            return $existingPending->load('type', 'card');
        }

        $syncRef = $this->generateSyncRef();

        $session = PrivilegeCardPaymentSession::query()->create([
            'public_user_id' => $publicUser->id,
            'privilege_card_type_id' => $type->id,
            'sync_ref' => $syncRef,
            'amount' => $type->price,
            'currency' => $type->currency,
            'status' => PaymentStatus::Pending->value,
            'provider' => 'fineopay',
            'initiated_at' => CarbonImmutable::now(),
        ]);

        $checkoutLink = $this->fineoPayClient->createCheckoutLink([
            'title' => 'Achat carte privilege '.$type->name,
            'amount' => (int) $type->price,
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

    private function generateSyncRef(): string
    {
        return 'PVC-'.CarbonImmutable::now()->format('YmdHis').'-'.strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
    }
}
