<?php

namespace App\Domain\PublicUsers\Actions;

use App\Models\PublicUser;

class UpdatePublicProfileAction
{
    public function handle(PublicUser $user, array $payload): PublicUser
    {
        unset($payload['public_user_type_id']);

        $user->fill($payload);
        $user->save();

        return $user->fresh();
    }
}
