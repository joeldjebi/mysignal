<?php

namespace Tests\Feature\Public\Meters;

use App\Models\PublicUser;
use Database\Seeders\Reference\LocationReferenceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tests\TestCase;

class PublicMeterTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_public_user_can_create_and_list_meters(): void
    {
        $this->seed(LocationReferenceSeeder::class);

        $user = PublicUser::query()->create([
            'first_name' => 'Mariam',
            'last_name' => 'Kone',
            'phone' => '0700000200',
            'commune' => 'Yopougon',
            'password' => 'secret123',
            'status' => 'active',
            'phone_verified_at' => now(),
        ]);

        $token = JWTAuth::fromUser($user);

        $createResponse = $this->withToken($token)->postJson('/api/v1/public/meters', [
            'network_type' => 'CIE',
            'meter_number' => 'CIE-12345',
            'label' => 'Maison principale',
            'commune' => 'Yopougon',
            'neighborhood' => 'Niangon',
            'sub_neighborhood' => 'Niangon Nord',
            'address' => 'Niangon',
            'is_primary' => true,
        ]);

        $createResponse->assertCreated()
            ->assertJsonPath('data.meter.network_type', 'CIE')
            ->assertJsonPath('data.meter.neighborhood', 'Niangon')
            ->assertJsonPath('data.meter.sub_neighborhood', 'Niangon Nord')
            ->assertJsonPath('data.meter.is_primary', true);

        $listResponse = $this->withToken($token)->getJson('/api/v1/public/meters');

        $listResponse->assertOk()
            ->assertJsonCount(1, 'data.meters')
            ->assertJsonPath('data.meters.0.meter_number', 'CIE-12345');
    }

    public function test_authenticated_public_user_can_update_meter_and_set_primary_flag(): void
    {
        $this->seed(LocationReferenceSeeder::class);

        $user = PublicUser::query()->create([
            'first_name' => 'Mariam',
            'last_name' => 'Kone',
            'phone' => '0700000201',
            'commune' => 'Yopougon',
            'password' => 'secret123',
            'status' => 'active',
            'phone_verified_at' => now(),
        ]);

        $token = JWTAuth::fromUser($user);

        $firstMeter = $this->withToken($token)->postJson('/api/v1/public/meters', [
            'network_type' => 'CIE',
            'meter_number' => 'CIE-20000',
            'label' => 'Compteur 1',
            'is_primary' => true,
        ])->json('data.meter.id');

        $secondMeter = $this->withToken($token)->postJson('/api/v1/public/meters', [
            'network_type' => 'CIE',
            'meter_number' => 'CIE-20001',
            'label' => 'Compteur 2',
            'is_primary' => false,
        ])->json('data.meter.id');

        $updateResponse = $this->withToken($token)->patchJson("/api/v1/public/meters/{$secondMeter}", [
            'label' => 'Compteur principal',
            'is_primary' => true,
        ]);

        $updateResponse->assertOk()
            ->assertJsonPath('data.meter.label', 'Compteur principal')
            ->assertJsonPath('data.meter.is_primary', true);

        $listResponse = $this->withToken($token)->getJson('/api/v1/public/meters');

        $listResponse->assertOk()
            ->assertJsonPath('data.meters.0.id', $secondMeter);

        $this->assertDatabaseHas('meter_assignments', [
            'public_user_id' => $user->id,
            'meter_id' => $firstMeter,
            'is_primary' => false,
        ]);
    }

    public function test_public_user_can_create_multiple_meters_for_same_network(): void
    {
        $this->seed(LocationReferenceSeeder::class);

        $user = PublicUser::query()->create([
            'first_name' => 'Mariam',
            'last_name' => 'Kone',
            'phone' => '0700000202',
            'commune' => 'Yopougon',
            'password' => 'secret123',
            'status' => 'active',
            'phone_verified_at' => now(),
        ]);

        $token = JWTAuth::fromUser($user);

        $this->withToken($token)->postJson('/api/v1/public/meters', [
            'network_type' => 'SODECI',
            'meter_number' => 'SOD-1000',
        ])->assertCreated();

        $response = $this->withToken($token)->postJson('/api/v1/public/meters', [
            'network_type' => 'SODECI',
            'meter_number' => 'SOD-1001',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.meter.network_type', 'SODECI')
            ->assertJsonPath('data.meter.meter_number', 'SOD-1001');

        $this->withToken($token)->getJson('/api/v1/public/meters')
            ->assertOk()
            ->assertJsonCount(2, 'data.meters');
    }
}
