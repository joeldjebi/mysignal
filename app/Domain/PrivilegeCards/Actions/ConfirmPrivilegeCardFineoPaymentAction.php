<?php

namespace App\Domain\PrivilegeCards\Actions;

use App\Domain\Payments\Enums\PaymentStatus;
use App\Models\PrivilegeCard;
use App\Models\PrivilegeCardPaymentSession;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ConfirmPrivilegeCardFineoPaymentAction
{
    public function handle(array $payload, ?Request $request = null): PrivilegeCardPaymentSession
    {
        $syncRef = (string) ($payload['syncRef'] ?? '');
        $status = (string) ($payload['status'] ?? '');
        $amount = (int) ($payload['amount'] ?? 0);

        if ($syncRef === '') {
            throw ValidationException::withMessages([
                'syncRef' => ['La référence de synchronisation est requise.'],
            ]);
        }

        return DB::transaction(function () use ($syncRef, $status, $amount, $payload): PrivilegeCardPaymentSession {
            $session = PrivilegeCardPaymentSession::query()
                ->with('type')
                ->where('sync_ref', $syncRef)
                ->lockForUpdate()
                ->firstOrFail();

            if ($session->status === PaymentStatus::Paid->value && $session->privilege_card_id !== null) {
                return $session->fresh(['type', 'card']);
            }

            if ($amount !== (int) $session->amount) {
                $session->update([
                    'status' => PaymentStatus::Failed->value,
                    'metadata' => [
                        ...($session->metadata ?? []),
                        'failure_reason' => 'amount_mismatch',
                        'callback_payload' => $payload,
                    ],
                ]);

                throw ValidationException::withMessages([
                    'amount' => ['Le montant du paiement ne correspond pas au montant attendu.'],
                ]);
            }

            if ($status !== 'success') {
                $session->update([
                    'status' => PaymentStatus::Failed->value,
                    'provider_reference' => $payload['reference'] ?? null,
                    'metadata' => [
                        ...($session->metadata ?? []),
                        'callback_payload' => $payload,
                    ],
                ]);

                return $session->fresh(['type', 'card']);
            }

            $paidAt = $this->paidAt($payload);
            $type = $session->type()->firstOrFail();
            $expiresAt = $paidAt->addMonthsNoOverflow((int) $type->duration_months);

            PrivilegeCard::query()
                ->where('public_user_id', $session->public_user_id)
                ->where('status', 'active')
                ->update(['status' => 'expired', 'revoked_at' => $paidAt]);

            $card = PrivilegeCard::query()->create([
                'public_user_id' => $session->public_user_id,
                'privilege_card_type_id' => $session->privilege_card_type_id,
                'card_uuid' => (string) Str::uuid(),
                'card_number' => $this->generateCardNumber($type->code),
                'status' => 'active',
                'issued_at' => $paidAt,
                'activated_at' => $paidAt,
                'expires_at' => $expiresAt,
                'metadata' => [
                    'payment_sync_ref' => $syncRef,
                    'payment_provider_reference' => $payload['reference'] ?? null,
                ],
            ]);

            $session->update([
                'privilege_card_id' => $card->id,
                'status' => PaymentStatus::Paid->value,
                'provider_reference' => $payload['reference'] ?? null,
                'paid_at' => $paidAt,
                'metadata' => [
                    ...($session->metadata ?? []),
                    'callback_payload' => $payload,
                ],
            ]);

            return $session->fresh(['type', 'card.type']);
        });
    }

    private function paidAt(array $payload): CarbonImmutable
    {
        return filled($payload['timestamp'] ?? null)
            ? CarbonImmutable::parse($payload['timestamp'])
            : CarbonImmutable::now();
    }

    private function generateCardNumber(string $typeCode): string
    {
        do {
            $number = 'PVC-'.strtoupper(substr($typeCode, 0, 3)).'-'.CarbonImmutable::now()->format('ymd').'-'.strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
        } while (PrivilegeCard::query()->where('card_number', $number)->exists());

        return $number;
    }
}
