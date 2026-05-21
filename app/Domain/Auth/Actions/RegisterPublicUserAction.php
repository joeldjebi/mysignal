<?php

namespace App\Domain\Auth\Actions;

use App\Domain\Auth\Enums\PublicUserStatus;
use App\Models\Commune;
use App\Models\PublicUser;
use App\Models\PublicUserPhoneVerification;
use App\Models\PublicUserType;
use App\Support\Auth\PublicApiTokenTtl;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RegisterPublicUserAction
{
    public function handle(array $payload): array
    {
        $publicUserTypeId = $payload['public_user_type_id'] ?? PublicUserType::query()
            ->where('code', 'UP')
            ->where('status', 'active')
            ->value('id');

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

        $commune = Commune::query()
            ->with('city.country')
            ->findOrFail($payload['commune_id']);

        $publicUser = DB::transaction(function () use ($payload, $verification, $publicUserTypeId, $commune): PublicUser {
            $user = PublicUser::query()->firstOrNew([
                'phone' => $payload['phone'],
            ]);

            $user->fill([
                'public_user_type_id' => $publicUserTypeId,
                'first_name' => $payload['first_name'],
                'last_name' => $payload['last_name'],
                'is_whatsapp_number' => (bool) ($payload['is_whatsapp_number'] ?? false),
                'email' => $payload['email'] ?? $user->email,
                'country_id' => $commune->city->country->id,
                'city_id' => $commune->city->id,
                'commune_id' => $commune->id,
                'company_name' => $payload['company_name'] ?? $user->company_name,
                'company_registration_number' => $payload['company_registration_number'] ?? $user->company_registration_number,
                'tax_identifier' => $payload['tax_identifier'] ?? $user->tax_identifier,
                'business_sector' => $payload['business_sector'] ?? $user->business_sector,
                'company_address' => $payload['company_address'] ?? $user->company_address,
                'country' => $commune->city->country->name,
                'city' => $commune->city->name,
                'commune' => $commune->name,
                'password' => $payload['password'],
                'phone_verified_at' => $verification->verified_at,
                'status' => PublicUserStatus::Active->value,
            ]);
            $user->save();

            $verification->forceFill([
                'used_at' => CarbonImmutable::now(),
            ])->save();

            return $user;
        });

        $guard = Auth::guard('public_api');
        $guard->factory()->setTTL(PublicApiTokenTtl::minutes());

        $token = $guard->login($publicUser);

        return [
            'token' => $token,
            'user' => $publicUser->fresh(['publicUserType.pricingRule', 'countryReference', 'cityReference', 'communeReference']),
        ];
    }
}
