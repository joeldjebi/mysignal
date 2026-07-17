<?php

namespace App\Domain\Discounts\Actions;

use App\Models\PartnerDiscountOffer;
use App\Models\PrivilegeCard;
use App\Models\PublicUser;
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

        if ($card !== null) {
            return $this->verifyUpDiscountCard($partnerUser, $card, $offerId);
        }

        $privilegeCard = PrivilegeCard::query()
            ->with(['publicUser', 'type'])
            ->where('card_uuid', $cardUuid)
            ->first();

        if ($privilegeCard !== null) {
            return $this->verifyPrivilegeCard($partnerUser, $privilegeCard);
        }

        throw ValidationException::withMessages([
            'card_uuid' => ['Carte introuvable.'],
        ]);
    }

    private function verifyUpDiscountCard(User $partnerUser, UpDiscountCard $card, ?int $offerId = null): array
    {
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
            'card_source' => 'up_discount_card',
            'card' => $card,
            'offer' => $offer,
            'privilege_card_type' => null,
            'discount' => null,
            'member_display_name' => trim((string) ($card->publicUser?->first_name.' '.$card->publicUser?->last_name)),
            'subscription_status' => $subscription?->status ?? 'not_required',
            'message' => 'Carte valide.',
            'verified_at' => $now->toIso8601String(),
        ];
    }

    private function verifyPrivilegeCard(User $partnerUser, PrivilegeCard $card): array
    {
        if ($card->status !== 'active') {
            throw ValidationException::withMessages([
                'card_uuid' => ['Cette carte privilege n est pas active.'],
            ]);
        }

        if ($card->expires_at !== null && $card->expires_at->isPast()) {
            throw ValidationException::withMessages([
                'card_uuid' => ['Cette carte privilege a expire.'],
            ]);
        }

        if ($card->type === null || $card->type->status !== 'active') {
            throw ValidationException::withMessages([
                'card_uuid' => ['Le type de cette carte privilege n est pas actif.'],
            ]);
        }

        if ($this->isSelfPublicUserAttempt($partnerUser, $card->publicUser)) {
            throw ValidationException::withMessages([
                'card_uuid' => ['Un agent partenaire ne peut pas appliquer une reduction sur sa propre carte privilege.'],
            ]);
        }

        $now = CarbonImmutable::now();

        return [
            'card_source' => 'privilege_card',
            'card' => $card,
            'offer' => null,
            'privilege_card_type' => $card->type,
            'discount' => [
                'type' => $card->type->discount_type,
                'value' => $card->type->discount_value !== null ? (float) $card->type->discount_value : null,
                'currency' => $card->type->currency,
            ],
            'member_display_name' => trim((string) ($card->publicUser?->first_name.' '.$card->publicUser?->last_name)),
            'subscription_status' => 'not_required',
            'message' => 'Carte privilege valide.',
            'verified_at' => $now->toIso8601String(),
        ];
    }

    private function isSelfDiscountAttempt(User $partnerUser, UpDiscountCard $card): bool
    {
        return $this->isSelfPublicUserAttempt($partnerUser, $card->publicUser);
    }

    private function isSelfPublicUserAttempt(User $partnerUser, ?PublicUser $publicUser): bool
    {
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
