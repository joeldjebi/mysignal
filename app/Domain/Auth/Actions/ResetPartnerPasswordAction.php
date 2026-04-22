<?php

namespace App\Domain\Auth\Actions;

use App\Models\PartnerUserPhoneVerification;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ResetPartnerPasswordAction
{
    public function handle(string $phone, string $verificationToken, string $password): User
    {
        $verification = PartnerUserPhoneVerification::query()
            ->where('phone', $phone)
            ->where('token', $verificationToken)
            ->whereNull('used_at')
            ->latest('id')
            ->first();

        if ($verification === null || $verification->expires_at->isPast()) {
            throw ValidationException::withMessages([
                'verification_token' => ['La verification du numero a expire ou est invalide.'],
            ]);
        }

        $user = User::query()
            ->with('organization.organizationType')
            ->where('phone', $phone)
            ->where('status', 'active')
            ->where('is_super_admin', false)
            ->first();

        if (
            $user === null ||
            $user->organization_id === null ||
            $user->organization?->organizationType?->code !== 'PARTNER_ESTABLISHMENT'
        ) {
            throw ValidationException::withMessages([
                'phone' => ['Aucun compte partenaire actif n a ete trouve pour ce numero.'],
            ]);
        }

        return DB::transaction(function () use ($user, $verification, $password): User {
            $user->update([
                'password' => Hash::make($password),
            ]);

            $verification->update([
                'used_at' => CarbonImmutable::now(),
            ]);

            return $user->fresh(['organization.organizationType', 'roles.permissions']);
        });
    }
}
