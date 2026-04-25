<?php

namespace App\Domain\Households\Actions;

use App\Models\Household;
use App\Models\HouseholdInvitation;
use App\Models\Meter;
use App\Models\PublicUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InviteHouseholdMemberAction
{
    public function handle(PublicUser $actor, Household $household, array $payload): HouseholdInvitation
    {
        if ((int) $household->owner_public_user_id !== (int) $actor->id) {
            throw ValidationException::withMessages([
                'household' => ['Seul le titulaire du foyer peut inviter un membre.'],
            ]);
        }

        if ($payload['phone'] === $actor->phone) {
            throw ValidationException::withMessages([
                'phone' => ['Vous ne pouvez pas vous inviter vous-meme dans votre propre foyer.'],
            ]);
        }

        $invitedUser = PublicUser::query()
            ->where('phone', $payload['phone'])
            ->first();

        if ($invitedUser === null) {
            throw ValidationException::withMessages([
                'phone' => ['Ce numero ne correspond a aucun compte public existant.'],
            ]);
        }

        if ($household->members()->whereHas('publicUser', fn ($query) => $query->where('phone', $payload['phone']))->exists()) {
            throw ValidationException::withMessages([
                'phone' => ['Ce numero appartient deja a un membre du foyer.'],
            ]);
        }

        if (! empty($payload['meter_id'])) {
            $sharedMeter = $actor->meters()
                ->whereKey($payload['meter_id'])
                ->first();

            if (! $sharedMeter instanceof Meter) {
                throw ValidationException::withMessages([
                    'meter_id' => ['Le compteur commun selectionne ne vous appartient pas.'],
                ]);
            }
        }

        $code = (string) random_int(100000, 999999);

        return DB::transaction(function () use ($household, $payload, $actor, $code): HouseholdInvitation {
            HouseholdInvitation::query()
                ->where('household_id', $household->id)
                ->where('phone', $payload['phone'])
                ->whereNull('accepted_at')
                ->delete();

            return HouseholdInvitation::query()->create([
                'household_id' => $household->id,
                'meter_id' => $payload['meter_id'] ?? null,
                'phone' => $payload['phone'],
                'relationship' => $payload['relationship'],
                'code' => $code,
                'expires_at' => null,
                'invited_by' => $actor->id,
            ]);
        });
    }
}
