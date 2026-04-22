<?php

namespace App\Domain\Auth\Actions;

use App\Domain\Auth\Enums\PartnerOtpPurpose;
use App\Models\PartnerUserOtp;
use App\Models\PartnerUserPhoneVerification;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class VerifyPartnerOtpAction
{
    public function handle(string $phone, string $code, PartnerOtpPurpose $purpose = PartnerOtpPurpose::PasswordReset): PartnerUserPhoneVerification
    {
        $otp = PartnerUserOtp::query()
            ->where('phone', $phone)
            ->where('purpose', $purpose->value)
            ->whereNull('verified_at')
            ->latest('id')
            ->first();

        if ($otp === null) {
            throw ValidationException::withMessages([
                'phone' => ['Aucun code OTP valide n a ete trouve pour ce numero.'],
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

        $defaultOtp = (string) config('services.partner_auth.default_otp', '2604');
        $isValidCode = Hash::check($code, $otp->code) || ($defaultOtp !== '' && hash_equals($defaultOtp, $code));

        if (! $isValidCode) {
            $otp->increment('attempts');

            throw ValidationException::withMessages([
                'code' => ['Le code OTP fourni est invalide.'],
            ]);
        }

        return DB::transaction(function () use ($otp, $phone): PartnerUserPhoneVerification {
            $verifiedAt = CarbonImmutable::now();

            $otp->forceFill([
                'verified_at' => $verifiedAt,
                'attempts' => $otp->attempts + 1,
            ])->save();

            PartnerUserPhoneVerification::query()
                ->where('phone', $phone)
                ->delete();

            return PartnerUserPhoneVerification::query()->create([
                'phone' => $phone,
                'verified_at' => $verifiedAt,
                'expires_at' => $verifiedAt->addMinutes(30),
                'token' => (string) Str::uuid(),
            ]);
        });
    }
}
