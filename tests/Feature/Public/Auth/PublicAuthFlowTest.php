<?php

namespace Tests\Feature\Public\Auth;

use App\Models\PublicUser;
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

        $registerResponse = $this->postJson('/api/v1/public/auth/register', [
            'first_name' => 'Ahou',
            'last_name' => 'Kouassi',
            'phone' => '0700000001',
            'email' => 'ahou@example.test',
            'commune' => 'Cocody',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'verification_token' => $verificationToken,
        ]);

        $registerResponse->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.phone', '0700000001')
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
            'commune' => 'Cocody',
        ]);
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
            ->assertJsonPath('data.user.id', $user->id);

        $token = $loginResponse->json('data.access_token');

        $meResponse = $this->withToken($token)
            ->getJson('/api/v1/public/me');

        $meResponse->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.phone', '0700000002');
    }
}
