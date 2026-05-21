<?php

namespace Tests\Feature\Public\Auth;

use App\Models\PublicUser;
use App\Models\Commune;
use App\Models\PublicUserType;
use Database\Seeders\Reference\LocationReferenceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicAuthFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_user_can_complete_registration_flow(): void
    {
        $this->seed(LocationReferenceSeeder::class);

        $otpResponse = $this->postJson('/api/v1/public/auth/request-otp', [
            'phone' => '0700000001',
        ]);

        $otpResponse->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.phone', '0700000001')
            ->assertJsonPath('data.otp_code_for_testing', '1234');

        $verifyResponse = $this->postJson('/api/v1/public/auth/verify-otp', [
            'phone' => '0700000001',
            'code' => '1234',
        ]);

        $verifyResponse->assertOk()
            ->assertJsonPath('success', true);

        $verificationToken = $verifyResponse->json('data.verification_token');
        $commune = Commune::query()->where('name', 'Cocody')->with('city.country')->firstOrFail();

        $registerResponse = $this->postJson('/api/v1/public/auth/register', [
            'first_name' => 'Ahou',
            'last_name' => 'Kouassi',
            'phone' => '0700000001',
            'email' => 'ahou@example.test',
            'country_id' => $commune->city->country->id,
            'city_id' => $commune->city->id,
            'commune_id' => $commune->id,
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'verification_token' => $verificationToken,
        ]);

        $registerResponse->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.phone', '0700000001')
            ->assertJsonPath('data.expires_in', 63072000)
            ->assertJsonStructure([
                'data' => [
                    'access_token',
                    'token_type',
                    'expires_in',
                    'user',
                ],
            ]);

        $this->assertDatabaseHas('public_users', [
            'phone' => '0700000001',
            'country' => "Cote d'Ivoire",
            'city' => 'Abidjan',
            'commune' => 'Cocody',
            'commune_id' => $commune->id,
        ]);
    }

    public function test_upe_registration_accepts_optional_business_sector_tax_identifier_and_company_address(): void
    {
        $this->seed(LocationReferenceSeeder::class);

        $otpResponse = $this->postJson('/api/v1/public/auth/request-otp', [
            'phone' => '0700000003',
        ]);

        $verificationToken = $this->postJson('/api/v1/public/auth/verify-otp', [
            'phone' => '0700000003',
            'code' => $otpResponse->json('data.otp_code_for_testing'),
        ])->json('data.verification_token');

        $commune = Commune::query()->where('name', 'Cocody')->with('city.country')->firstOrFail();
        $upeTypeId = PublicUserType::query()->where('code', 'UPE')->value('id');

        $registerResponse = $this->postJson('/api/v1/public/auth/register', [
            'public_user_type_id' => $upeTypeId,
            'first_name' => 'Awa',
            'last_name' => 'Kone',
            'phone' => '0700000003',
            'email' => 'awa@example.test',
            'country_id' => $commune->city->country->id,
            'city_id' => $commune->city->id,
            'commune_id' => $commune->id,
            'company_name' => 'Awa Services',
            'company_registration_number' => 'CI-ABJ-2026-B-001',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'verification_token' => $verificationToken,
        ]);

        $registerResponse->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.business_sector', null)
            ->assertJsonPath('data.user.tax_identifier', null)
            ->assertJsonPath('data.user.company_address', null);
    }

    public function test_public_user_can_login_and_fetch_profile(): void
    {
        $this->seed(LocationReferenceSeeder::class);

        $user = PublicUser::query()->create([
            'first_name' => 'Jean',
            'last_name' => 'Yao',
            'phone' => '0700000002',
            'commune' => 'Yopougon',
            'password' => 'secret123',
            'status' => 'active',
            'phone_verified_at' => now(),
        ]);

        $loginResponse = $this->postJson('/api/v1/public/auth/login', [
            'phone' => '0700000002',
            'password' => 'secret123',
        ]);

        $loginResponse->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.id', $user->id)
            ->assertJsonPath('data.expires_in', 63072000);

        $token = $loginResponse->json('data.access_token');

        $meResponse = $this->withToken($token)
            ->getJson('/api/v1/public/me');

        $meResponse->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.phone', '0700000002');
    }
}
