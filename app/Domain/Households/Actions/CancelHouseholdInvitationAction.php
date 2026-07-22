<?php

namespace App\Domain\Households\Actions;

use App\Models\HouseholdInvitation;
use App\Models\PublicUser;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CancelHouseholdInvitationAction
{
    public function handle(PublicUser $actor, HouseholdInvitation $invitation): HouseholdInvitation
    {
        $invitation->loadMissing('household');

        if ($invitation->accepted_at !== null) {
            throw ValidationException::withMessages([
                'invitation' => ['Cette invitation a déjà été acceptée.'],
            ]);
        }

        if ($invitation->declined_at !== null) {
            throw ValidationException::withMessages([
                'invitation' => ['Cette invitation n’est plus en attente.'],
            ]);
        }

        if ((int) $invitation->invited_by !== (int) $actor->id && (int) $invitation->household?->owner_public_user_id !== (int) $actor->id) {
            throw ValidationException::withMessages([
                'invitation' => ['Seul l’invitateur ou le titulaire du Gbonhi peut annuler cette invitation.'],
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
