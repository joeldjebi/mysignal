<?php

namespace App\Domain\Discounts\Actions;

use App\Models\PartnerDiscountOffer;
use App\Models\PartnerDiscountTransaction;
use App\Models\User;
use App\Models\UpDiscountCard;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ApplyPartnerDiscountAction
{
    public function __construct(
        private readonly VerifyPartnerDiscountCardAction $verifyPartnerDiscountCardAction,
    ) {
    }

    public function handle(User $partnerUser, array $payload): PartnerDiscountTransaction
    {
        $verification = $this->verifyPartnerDiscountCardAction->handle(
            $partnerUser,
            (string) $payload['card_uuid'],
            (int) $payload['offer_id'],
        );

        /** @var UpDiscountCard $card */
        $card = $verification['card'];
        /** @var PartnerDiscountOffer $offer */
        $offer = $verification['offer'];

        return DB::transaction(function () use ($payload, $partnerUser, $card, $offer): PartnerDiscountTransaction {
            $card = UpDiscountCard::query()
                ->with(['subscription', 'publicUser'])
                ->lockForUpdate()
                ->findOrFail($card->id);

            $offer = PartnerDiscountOffer::query()
                ->lockForUpdate()
                ->findOrFail($offer->id);

            $this->assertUsageLimits($card, $offer);

            $transaction = PartnerDiscountTransaction::query()->create([
                'up_discount_card_id' => $card->id,
                'partner_discount_offer_id' => $offer->id,
                'organization_id' => $partnerUser->organization_id,
                'partner_user_id' => $partnerUser->id,
                'public_user_id' => $card->public_user_id,
                'up_subscription_id' => $card->up_subscription_id,
                'scan_reference' => $this->generateScanReference(),
                'verification_status' => 'verified',
                'status' => 'validated',
                'original_amount' => $payload['original_amount'] ?? null,
                'discount_amount' => $payload['discount_amount'] ?? null,
                'final_amount' => $payload['final_amount'] ?? null,
                'discount_type_snapshot' => $offer->discount_type,
                'discount_value_snapshot' => $offer->discount_value,
                'applied_at' => CarbonImmutable::now(),
                'metadata' => $payload['metadata'] ?? null,
            ]);

            $card->update([
                'last_used_at' => $transaction->applied_at,
            ]);

            return $transaction->load(['offer', 'discountCard', 'partnerUser', 'publicUser']);
        });
    }

    private function assertUsageLimits(UpDiscountCard $card, PartnerDiscountOffer $offer): void
    {
        $validatedTransactions = PartnerDiscountTransaction::query()
            ->where('up_discount_card_id', $card->id)
            ->where('partner_discount_offer_id', $offer->id)
            ->where('status', 'validated');

        if ($offer->max_uses_per_card !== null && $validatedTransactions->count() >= $offer->max_uses_per_card) {
            throw ValidationException::withMessages([
                'offer_id' => ['Le nombre maximal d utilisations pour cette carte a ete atteint.'],
            ]);
        }

        if ($offer->max_uses_per_day !== null) {
            $todayCount = PartnerDiscountTransaction::query()
                ->where('up_discount_card_id', $card->id)
                ->where('partner_discount_offer_id', $offer->id)
                ->where('status', 'validated')
                ->whereDate('applied_at', now()->toDateString())
                ->count();

            if ($todayCount >= $offer->max_uses_per_day) {
                throw ValidationException::withMessages([
                    'offer_id' => ['Le plafond journalier de cette offre est atteint pour cette carte.'],
                ]);
            }
        }
    }

    private function generateScanReference(): string
    {
        do {
            $reference = 'DISC-'.now()->format('YmdHis').'-'.Str::upper(Str::random(8));
        } while (PartnerDiscountTransaction::query()->where('scan_reference', $reference)->exists());

        return $reference;
    }
}
