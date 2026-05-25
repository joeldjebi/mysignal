<?php

namespace App\Domain\Households\Actions;

use App\Models\Household;
use App\Models\HouseholdInvitation;
use App\Models\HouseholdMember;
use App\Models\MeterAssignment;
use App\Models\PublicUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RemoveHouseholdMemberAction
{
    public function handle(PublicUser $actor, Household $household, HouseholdMember $member): void
    {
        if ((int) $household->owner_public_user_id !== (int) $actor->id) {
            throw ValidationException::withMessages([
                'household' => ['Seul le titulaire du Gbonhi peut retirer un membre.'],
            ]);
        }

        if ((int) $member->household_id !== (int) $household->id) {
            throw ValidationException::withMessages([
                'member' => ['Ce membre n appartient pas a ce Gbonhi.'],
            ]);
        }

        if ($member->is_owner || (int) $member->public_user_id === (int) $household->owner_public_user_id) {
            throw ValidationException::withMessages([
                'member' => ['Le titulaire du Gbonhi ne peut pas etre retire.'],
            ]);
        }

        DB::transaction(function () use ($household, $member): void {
            $member->loadMissing('publicUser');
            $memberPhone = $member->publicUser?->phone;

            if ($memberPhone !== null) {
                $acceptedSharedMeters = HouseholdInvitation::query()
                    ->where('household_id', $household->id)
                    ->where('phone', $memberPhone)
                    ->whereNotNull('accepted_at')
                    ->whereNotNull('meter_id')
                    ->get(['meter_id']);

                foreach ($acceptedSharedMeters as $invitation) {
                    if ($this->hasAnotherAcceptedSharedMeter((int) $household->id, $memberPhone, (int) $invitation->meter_id)) {
                        continue;
                    }

                    MeterAssignment::query()
                        ->where('public_user_id', $member->public_user_id)
                        ->where('meter_id', $invitation->meter_id)
                        ->where('assignment_source', 'gbonhi')
                        ->delete();
                }
            }

            $member->delete();
        });
    }

    private function hasAnotherAcceptedSharedMeter(int $householdId, string $phone, int $meterId): bool
    {
        return HouseholdInvitation::query()
            ->where('household_id', '!=', $householdId)
            ->where('phone', $phone)
            ->where('meter_id', $meterId)
            ->whereNotNull('accepted_at')
            ->exists();
    }
}
