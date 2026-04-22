<?php

namespace App\Domain\Discounts\Actions;

use App\Models\PartnerDiscountOffer;
use App\Models\UpDiscountCard;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class VerifyPartnerDiscountCardAction
{
    public function handle(User $partnerUser, string $cardUuid, ?int $offerId = null): array
    {
        $partnerUser->loadMissing('organization.organizationType');

        $card = UpDiscountCard::query()
            ->with(['publicUser', 'subscription.plan'])
            ->where('card_uuid', $cardUuid)
            ->first();

        if ($card === null) {
            throw ValidationException::withMessages([
                'card_uuid' => ['Carte introuvable.'],
            ]);
        }

        if ($card->status !== 'active') {
            throw ValidationException::withMessages([
                'card_uuid' => ['Cette carte n est pas active.'],
            ]);
        }

        $now = CarbonImmutable::now();

        if ($card->expires_at !== null && $card->expires_at->isPast()) {
            throw ValidationException::withMessages([
                'card_uuid' => ['Cette carte a expire.'],
            ]);
        }

        $subscription = $card->subscription;

        if ($subscription === null || ! $subscription->isActive()) {
            throw ValidationException::withMessages([
                'card_uuid' => ['L abonnement associe n est pas actif.'],
            ]);
        }

        if ($subscription->end_date !== null && $subscription->end_date->isPast()) {
            throw ValidationException::withMessages([
                'card_uuid' => ['L abonnement associe est arrive a expiration.'],
            ]);
        }

        if ($this->isSelfDiscountAttempt($partnerUser, $card)) {
            throw ValidationException::withMessages([
                'card_uuid' => ['Un agent partenaire ne peut pas appliquer une reduction sur sa propre carte UP.'],
            ]);
        }

        $offer = null;

        if ($offerId !== null) {
            $offer = PartnerDiscountOffer::query()
                ->whereKey($offerId)
                ->where('organization_id', $partnerUser->organization_id)
                ->first();

            if ($offer === null) {
                throw ValidationException::withMessages([
                    'offer_id' => ['Cette offre n appartient pas a votre etablissement.'],
                ]);
            }

            if ($offer->status !== 'active') {
                throw ValidationException::withMessages([
                    'offer_id' => ['Cette offre n est pas active.'],
                ]);
            }

            if ($offer->starts_at !== null && $offer->starts_at->isFuture()) {
                throw ValidationException::withMessages([
                    'offer_id' => ['Cette offre n est pas encore disponible.'],
                ]);
            }

            if ($offer->ends_at !== null && $offer->ends_at->isPast()) {
                throw ValidationException::withMessages([
                    'offer_id' => ['Cette offre a expire.'],
                ]);
            }
        }

        return [
            'card' => $card,
            'offer' => $offer,
            'member_display_name' => trim((string) ($card->publicUser?->first_name.' '.$card->publicUser?->last_name)),
            'subscription_status' => $subscription->status,
            'message' => 'Carte valide.',
            'verified_at' => $now->toIso8601String(),
        ];
    }

    private function isSelfDiscountAttempt(User $partnerUser, UpDiscountCard $card): bool
    {
        $publicUser = $card->publicUser;

        if ($publicUser === null) {
            return false;
        }

        $partnerEmail = Str::lower(trim((string) $partnerUser->email));
        $publicEmail = Str::lower(trim((string) $publicUser->email));

        if ($partnerEmail !== '' && $publicEmail !== '' && $partnerEmail === $publicEmail) {
            return true;
        }

        $partnerPhone = $this->normalizePhone((string) $partnerUser->phone);
        $publicPhone = $this->normalizePhone((string) $publicUser->phone);

        return $partnerPhone !== '' && $publicPhone !== '' && $partnerPhone === $publicPhone;
    }

    private function normalizePhone(string $value): string
    {
        return preg_replace('/\D+/', '', $value) ?? '';
    }
}
