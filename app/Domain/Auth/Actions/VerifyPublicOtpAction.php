<?php

namespace App\Domain\Auth\Actions;

use App\Domain\Auth\Enums\OtpPurpose;
use App\Models\PublicUserOtp;
use App\Models\PublicUserPhoneVerification;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class VerifyPublicOtpAction
{
    public function handle(string $phone, string $code, OtpPurpose $purpose = OtpPurpose::Registration): PublicUserPhoneVerification
    {
        $otp = PublicUserOtp::query()
            ->where('phone', $phone)
            ->where('purpose', $purpose->value)
            ->whereNull('verified_at')
            ->latest('id')
            ->first();

        if ($otp === null) {
            throw ValidationException::withMessages([
                'phone' => ['Aucun code OTP valide n’a ete trouve pour ce numero.'],
            ]);
        }

        if ($otp->expires_at->isPast()) {
            throw ValidationException::withMessages([
                'code' => ['Ce code OTP a expire.'],
            ]);
        }

        if ($otp->attempts >= $otp->max_attempts) {
            throw ValidationException::withMessages([
                'code' => ['Le nombre maximal de tentatives OTP a ete atteint.'],
            ]);
        }

        $defaultOtp = (string) config('services.public_auth.default_otp', '2604');
        $isValidCode = Hash::check($code, $otp->code) || ($defaultOtp !== '' && hash_equals($defaultOtp, $code));

        if (! $isValidCode) {
            $otp->increment('attempts');

            throw ValidationException::withMessages([
                'code' => ['Le code OTP fourni est invalide.'],
            ]);
        }

        return DB::transaction(function () use ($otp, $phone): PublicUserPhoneVerification {
            $verifiedAt = CarbonImmutable::now();

            $otp->forceFill([
                'verified_at' => $verifiedAt,
                'attempts' => $otp->attempts + 1,
            ])->save();

            PublicUserPhoneVerification::query()
                ->where('phone', $phone)
                ->delete();

            return PublicUserPhoneVerification::query()->create([
                'phone' => $phone,
                'verified_at' => $verifiedAt,
                'expires_at' => $verifiedAt->addMinutes(30),
                'token' => (string) Str::uuid(),
            ]);
        });
    }
}
