<?php

namespace App\Domain\Auth\Actions;

use App\Domain\Auth\Data\OtpRequestResult;
use App\Domain\Auth\Enums\OtpPurpose;
use App\Models\PublicUserOtp;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RequestPublicOtpAction
{
    public function handle(string $phone, OtpPurpose $purpose = OtpPurpose::Registration): OtpRequestResult
    {
        $code = app()->environment('local', 'testing')
            ? '1234'
            : (string) random_int(100000, 999999);
        $expiresAt = CarbonImmutable::now()->addMinutes(5);

        DB::transaction(function () use ($phone, $purpose, $code, $expiresAt): void {
            PublicUserOtp::query()
                ->where('phone', $phone)
                ->where('purpose', $purpose->value)
                ->delete();

            PublicUserOtp::query()->create([
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
