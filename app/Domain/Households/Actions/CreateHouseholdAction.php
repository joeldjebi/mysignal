<?php

namespace App\Domain\Households\Actions;

use App\Domain\Households\Enums\HouseholdMemberStatus;
use App\Domain\Households\Enums\HouseholdStatus;
use App\Models\Household;
use App\Models\HouseholdMember;
use App\Models\PublicUser;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateHouseholdAction
{
    public function handle(PublicUser $user, array $payload): Household
    {
        if ($user->ownedHousehold()->exists()) {
            throw ValidationException::withMessages([
                'household' => ['Vous avez deja un foyer principal.'],
            ]);
        }

        return DB::transaction(function () use ($user, $payload): Household {
            $household = Household::query()->create([
                'owner_public_user_id' => $user->id,
                'name' => $payload['name'] ?? null,
                'commune' => $payload['commune'] ?? $user->commune,
                'address' => $payload['address'] ?? null,
                'status' => HouseholdStatus::Active->value,
            ]);

            HouseholdMember::query()->create([
                'household_id' => $household->id,
                'public_user_id' => $user->id,
                'relationship' => 'owner',
                'is_owner' => true,
                'status' => HouseholdMemberStatus::Active->value,
                'joined_at' => CarbonImmutable::now(),
            ]);

            return $household->fresh();
        });
    }
}
