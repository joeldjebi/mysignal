<?php

namespace App\Domain\Households\Actions;

use App\Models\Household;
use App\Models\HouseholdInvitation;
use App\Models\MeterAssignment;
use App\Models\PublicUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DeleteHouseholdAction
{
    public function handle(PublicUser $actor, Household $household): void
    {
        if ((int) $household->owner_public_user_id !== (int) $actor->id) {
            throw ValidationException::withMessages([
                'household' => ['Seul le titulaire du Gbonhi peut le supprimer.'],
            ]);
        }

        DB::transaction(function () use ($household): void {
            $acceptedSharedMeters = HouseholdInvitation::query()
                ->where('household_id', $household->id)
                ->whereNotNull('accepted_at')
                ->whereNotNull('meter_id')
                ->get(['phone', 'meter_id']);

            foreach ($acceptedSharedMeters as $invitation) {
                $publicUserId = PublicUser::query()
                    ->where('phone', $invitation->phone)
                    ->value('id');

                if ($publicUserId === null || $this->hasAnotherAcceptedSharedMeter((int) $household->id, $invitation->phone, (int) $invitation->meter_id)) {
                    continue;
                }

                MeterAssignment::query()
                    ->where('public_user_id', $publicUserId)
                    ->where('meter_id', $invitation->meter_id)
                    ->where('assignment_source', 'gbonhi')
                    ->delete();
            }

            $household->delete();
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
