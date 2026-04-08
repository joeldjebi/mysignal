<?php

namespace Tests\Feature\Public\Payments;

use App\Models\City;
use App\Models\Commune;
use App\Models\Country;
use App\Models\PublicUser;
use Database\Seeders\Reference\LocationReferenceSeeder;
use Database\Seeders\Reference\PricingRuleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tests\TestCase;

class PublicReportPaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_user_can_initialize_and_confirm_simulated_payment(): void
    {
        $this->seed([
            LocationReferenceSeeder::class,
            PricingRuleSeeder::class,
        ]);

        $user = PublicUser::query()->create([
            'first_name' => 'Awa',
            'last_name' => 'Kone',
            'phone' => '0700000500',
            'commune' => 'Cocody',
            'password' => 'secret123',
            'status' => 'active',
            'phone_verified_at' => now(),
        ]);

        $token = JWTAuth::fromUser($user);

        $meterId = $this->withToken($token)->postJson('/api/v1/public/meters', [
            'network_type' => 'CIE',
            'meter_number' => 'CIE-50000',
            'label' => 'Appartement',
            'commune' => 'Cocody',
            'is_primary' => true,
        ])->json('data.meter.id');

        $country = Country::query()->where('code', 'CI')->firstOrFail();
        $city = City::query()->where('code', 'ABJ')->firstOrFail();
        $commune = Commune::query()->where('code', 'ABJ-COCODY')->firstOrFail();

        $reportId = $this->withToken($token)->postJson('/api/v1/public/reports', [
            'meter_id' => $meterId,
            'country_id' => $country->id,
            'city_id' => $city->id,
            'commune_id' => $commune->id,
            'signal_code' => 'EL-01',
            'description' => 'Coupure constatee.',
            'signal_payload' => [],
        ])->json('data.report.id');

        $paymentId = $this->withToken($token)->postJson("/api/v1/public/reports/{$reportId}/payments")
            ->assertCreated()
            ->assertJsonPath('data.payment.amount', 100)
            ->json('data.payment.id');

        $this->withToken($token)->postJson("/api/v1/public/payments/{$paymentId}/confirm")
            ->assertOk()
            ->assertJsonPath('data.payment.status', 'paid');

        $this->assertDatabaseHas('incident_reports', [
            'id' => $reportId,
            'payment_status' => 'paid',
        ]);
    }
}
