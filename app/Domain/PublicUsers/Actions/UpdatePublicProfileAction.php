<?php

namespace App\Domain\PublicUsers\Actions;

use App\Models\Commune;
use App\Models\PublicUser;

class UpdatePublicProfileAction
{
    public function handle(PublicUser $user, array $payload): PublicUser
    {
        unset($payload['public_user_type_id']);

        if (isset($payload['commune_id'])) {
            $commune = Commune::query()
                ->with('city.country')
                ->findOrFail($payload['commune_id']);

            $payload['country_id'] = $commune->city->country->id;
            $payload['city_id'] = $commune->city->id;
            $payload['commune_id'] = $commune->id;
            $payload['country'] = $commune->city->country->name;
            $payload['city'] = $commune->city->name;
            $payload['commune'] = $commune->name;
        }

        $user->fill($payload);
        $user->save();

        return $user->fresh();
    }
}
