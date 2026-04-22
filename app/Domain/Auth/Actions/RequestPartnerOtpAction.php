<?php

namespace App\Domain\Auth\Actions;

use App\Domain\Auth\Data\OtpRequestResult;
use App\Domain\Auth\Enums\PartnerOtpPurpose;
use App\Models\PartnerUserOtp;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class RequestPartnerOtpAction
{
    public function handle(string $phone, PartnerOtpPurpose $purpose = PartnerOtpPurpose::PasswordReset): OtpRequestResult
    {
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

        $digits = max(4, (int) config('services.partner_auth.otp_digits', 4));
        $min = 10 ** ($digits - 1);
        $max = (10 ** $digits) - 1;
        $code = (string) random_int($min, $max);
        $expiresAt = CarbonImmutable::now()->addMinutes(5);

        DB::transaction(function () use ($phone, $purpose, $code, $expiresAt): void {
            PartnerUserOtp::query()
                ->where('phone', $phone)
                ->where('purpose', $purpose->value)
                ->delete();

            PartnerUserOtp::query()->create([
                'phone' => $phone,
                'code' => Hash::make($code),
                'purpose' => $purpose->value,
                'expires_at' => $expiresAt,
                'attempts' => 0,
                'max_attempts' => 5,
            ]);
        });

        return new OtpRequestResult(
            phone: $phone,
            code: $code,
            expiresAt: $expiresAt->toIso8601String(),
        );
    }
}
