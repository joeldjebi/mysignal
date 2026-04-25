<?php

namespace App\Domain\Households\Actions;

use App\Models\HouseholdInvitation;
use App\Models\PublicUser;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DeclineHouseholdInvitationAction
{
    public function handle(PublicUser $user, array $payload): HouseholdInvitation
    {
        $invitation = HouseholdInvitation::query()
            ->whereKey($payload['invitation_id'])
            ->whereNull('accepted_at')
            ->whereNull('declined_at')
            ->first();

        if ($invitation === null) {
            throw ValidationException::withMessages([
                'invitation_id' => ['Invitation introuvable.'],
            ]);
        }

        if ($invitation->phone !== $user->phone) {
            throw ValidationException::withMessages([
                'invitation_id' => ['Cette invitation ne vous est pas destinee.'],
            ]);
        }

        return DB::transaction(function () use ($invitation): HouseholdInvitation {
            $invitation->forceFill([
                'declined_at' => CarbonImmutable::now(),
            ])->save();

            return $invitation->fresh();
        });
    }
}
