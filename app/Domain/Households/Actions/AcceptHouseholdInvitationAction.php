<?php

namespace App\Domain\Households\Actions;

use App\Domain\Households\Enums\HouseholdMemberStatus;
use App\Models\HouseholdInvitation;
use App\Models\HouseholdMember;
use App\Models\MeterAssignment;
use App\Models\PublicUser;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AcceptHouseholdInvitationAction
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

        return DB::transaction(function () use ($user, $invitation): HouseholdInvitation {
            HouseholdMember::query()->firstOrCreate(
                [
                    'household_id' => $invitation->household_id,
                    'public_user_id' => $user->id,
                ],
                [
                    'relationship' => $invitation->relationship,
                    'is_owner' => false,
                    'status' => HouseholdMemberStatus::Active->value,
                    'joined_at' => CarbonImmutable::now(),
                ],
            );

            if ($invitation->meter_id !== null) {
                MeterAssignment::query()->firstOrCreate(
                    [
                        'meter_id' => $invitation->meter_id,
                        'public_user_id' => $user->id,
                    ],
                    [
                        'is_primary' => false,
                    ],
                );
            }

            $invitation->forceFill([
                'accepted_at' => CarbonImmutable::now(),
                'declined_at' => null,
            ])->save();

            return $invitation->fresh();
        });
    }
}
