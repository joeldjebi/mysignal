<?php

namespace App\Domain\Auth\Actions;

use App\Domain\Auth\Enums\PublicUserStatus;
use App\Models\PublicUser;
use App\Models\PublicUserPhoneVerification;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RegisterPublicUserAction
{
    public function handle(array $payload): array
    {
        $verification = PublicUserPhoneVerification::query()
            ->where('phone', $payload['phone'])
            ->where('token', $payload['verification_token'])
            ->whereNull('used_at')
            ->latest('id')
            ->first();

        if ($verification === null || $verification->expires_at->isPast()) {
            throw ValidationException::withMessages([
                'verification_token' => ['La verification du numero a expire ou est invalide.'],
            ]);
        }

        $publicUser = DB::transaction(function () use ($payload, $verification): PublicUser {
            $user = PublicUser::query()->create([
                'public_user_type_id' => $payload['public_user_type_id'],
                'first_name' => $payload['first_name'],
                'last_name' => $payload['last_name'],
                'phone' => $payload['phone'],
                'is_whatsapp_number' => (bool) ($payload['is_whatsapp_number'] ?? false),
                'email' => $payload['email'] ?? null,
                'company_name' => $payload['company_name'] ?? null,
                'company_registration_number' => $payload['company_registration_number'] ?? null,
                'tax_identifier' => $payload['tax_identifier'] ?? null,
                'business_sector' => $payload['business_sector'] ?? null,
                'company_address' => $payload['company_address'] ?? null,
                'commune' => $payload['commune'],
                'password' => $payload['password'],
                'phone_verified_at' => $verification->verified_at,
                'status' => PublicUserStatus::Active->value,
            ]);

            $verification->forceFill([
                'used_at' => CarbonImmutable::now(),
            ])->save();

            return $user;
        });

        $token = Auth::guard('public_api')->login($publicUser);

        return [
            'token' => $token,
            'user' => $publicUser->fresh('publicUserType.pricingRule'),
        ];
    }
}
