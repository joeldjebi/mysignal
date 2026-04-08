<?php

namespace Tests\Feature\Public\Profile;

use App\Models\PublicUser;
use Database\Seeders\Reference\LocationReferenceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tests\TestCase;

class PublicProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_public_user_can_view_profile(): void
    {
        $this->seed(LocationReferenceSeeder::class);

        $user = PublicUser::query()->create([
            'first_name' => 'Aya',
            'last_name' => 'Nene',
            'phone' => '0700000100',
            'commune' => 'Abobo',
            'password' => 'secret123',
            'status' => 'active',
            'phone_verified_at' => now(),
        ]);

        $token = JWTAuth::fromUser($user);

        $response = $this->withToken($token)->getJson('/api/v1/public/profile');

        $response->assertOk()
            ->assertJsonPath('data.user.phone', '0700000100');
    }

    public function test_authenticated_public_user_can_update_profile(): void
    {
        $this->seed(LocationReferenceSeeder::class);

        $user = PublicUser::query()->create([
            'first_name' => 'Aya',
            'last_name' => 'Nene',
            'phone' => '0700000101',
            'commune' => 'Abobo',
            'password' => 'secret123',
            'status' => 'active',
            'phone_verified_at' => now(),
        ]);

        $token = JWTAuth::fromUser($user);

        $response = $this->withToken($token)->putJson('/api/v1/public/profile', [
            'first_name' => 'Awa',
            'email' => 'awa@example.test',
            'commune' => 'Cocody',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.user.first_name', 'Awa')
            ->assertJsonPath('data.user.email', 'awa@example.test')
            ->assertJsonPath('data.user.commune', 'Cocody');

        $this->assertDatabaseHas('public_users', [
            'id' => $user->id,
            'first_name' => 'Awa',
            'email' => 'awa@example.test',
            'commune' => 'Cocody',
        ]);
    }
}
