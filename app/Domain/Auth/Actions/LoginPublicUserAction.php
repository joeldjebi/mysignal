<?php

namespace App\Domain\Auth\Actions;

use App\Models\PublicUser;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginPublicUserAction
{
    public function handle(string $phone, string $password): array
    {
        $token = Auth::guard('public_api')->attempt([
            'phone' => $phone,
            'password' => $password,
        ]);

        if ($token === false) {
            throw ValidationException::withMessages([
                'phone' => ['Les identifiants fournis sont invalides.'],
            ]);
        }

        /** @var PublicUser $user */
        $user = Auth::guard('public_api')->user();
        $user->forceFill([
            'last_login_at' => CarbonImmutable::now(),
        ])->save();

        return [
            'token' => $token,
            'user' => $user->fresh(),
        ];
    }
}
