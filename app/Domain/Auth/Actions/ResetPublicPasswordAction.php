<?php

namespace App\Domain\Auth\Actions;

use App\Domain\Auth\Enums\PublicUserStatus;
use App\Models\PublicUser;
use App\Models\PublicUserPhoneVerification;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ResetPublicPasswordAction
{
    public function handle(string $phone, string $verificationToken, string $password): PublicUser
    {
        $verification = PublicUserPhoneVerification::query()
            ->where('phone', $phone)
            ->where('token', $verificationToken)
            ->whereNull('used_at')
            ->latest('id')
            ->first();

        if ($verification === null || $verification->expires_at->isPast()) {
            throw ValidationException::withMessages([
                'verification_token' => ['La vérification du numéro a expiré ou est invalide.'],
            ]);
        }

        $user = PublicUser::query()
            ->where('phone', $phone)
            ->where('status', PublicUserStatus::Active->value)
            ->first();

        if ($user === null) {
            throw ValidationException::withMessages([
                'phone' => ['Aucun compte UP actif n’a été trouvé pour ce numéro.'],
            ]);
        }

        return DB::transaction(function () use ($user, $verification, $password): PublicUser {
            $user->update([
                'password' => Hash::make($password),
            ]);

            $verification->update([
                'used_at' => CarbonImmutable::now(),
            ]);

            return $user->fresh(['publicUserType.pricingRule']);
        });
    }
}
