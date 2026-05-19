<?php

namespace App\Domain\Auth\Actions;

use App\Models\PublicUser;
use App\Support\Auth\PublicApiTokenTtl;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginPublicUserAction
{
    public function handle(string $phone, string $password): array
    {
        $guard = Auth::guard('public_api');
        $guard->factory()->setTTL(PublicApiTokenTtl::minutes());

        $token = $guard->attempt([
            'phone' => $phone,
            'password' => $password,
        ]);

        if ($token === false) {
            throw ValidationException::withMessages([
                'phone' => ['Les identifiants fournis sont invalides.'],
            ]);
        }

        /** @var PublicUser $user */
        $user = $guard->user();
        $user->forceFill([
            'last_login_at' => CarbonImmutable::now(),
        ])->save();

        return [
            'token' => $token,
            'user' => $user->fresh(),
        ];
    }
}
