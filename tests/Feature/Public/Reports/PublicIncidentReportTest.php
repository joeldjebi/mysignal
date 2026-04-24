<?php

namespace Tests\Feature\Public\Reports;

use App\Domain\Reports\Enums\IncidentReportStatus;
use App\Models\City;
use App\Models\Commune;
use App\Models\Country;
use App\Models\IncidentReport;
use App\Models\PublicUser;
use Database\Seeders\Reference\LocationReferenceSeeder;
use Database\Seeders\Reference\PricingRuleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tests\TestCase;

class PublicIncidentReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_user_can_create_and_list_incident_reports(): void
    {
        $this->seed([
            LocationReferenceSeeder::class,
            PricingRuleSeeder::class,
        ]);

        $user = PublicUser::query()->create([
            'first_name' => 'Awa',
            'last_name' => 'Kone',
            'phone' => '0700000400',
            'commune' => 'Cocody',
            'password' => 'secret123',
            'status' => 'active',
            'phone_verified_at' => now(),
        ]);

        $token = JWTAuth::fromUser($user);

        $meterId = $this->withToken($token)->postJson('/api/v1/public/meters', [
            'network_type' => 'CIE',
            'meter_number' => 'CIE-40000',
            'label' => 'Appartement',
            'is_primary' => true,
        ])->json('data.meter.id');

        $country = Country::query()->where('code', 'CI')->firstOrFail();
        $city = City::query()->where('code', 'ABJ')->firstOrFail();
        $commune = Commune::query()->where('code', 'ABJ-COCODY')->firstOrFail();

        $createResponse = $this->withToken($token)->postJson('/api/v1/public/reports', [
            'meter_id' => $meterId,
            'country_id' => $country->id,
            'city_id' => $city->id,
            'commune_id' => $commune->id,
            'signal_code' => 'EL-01',
            'description' => 'Coupure constatee depuis 18h00.',
            'signal_payload' => [],
        ]);

        $createResponse->assertCreated()
            ->assertJsonPath('data.report.network_type', 'CIE')
            ->assertJsonPath('data.report.location.commune', 'Cocody')
            ->assertJsonPath('data.report.signal_code', 'EL-01');

        $this->withToken($token)->getJson('/api/v1/public/reports')
            ->assertOk()
            ->assertJsonCount(1, 'data.reports');
    }

    public function test_public_user_gets_validation_error_when_country_is_invalid(): void
    {
        $this->seed(LocationReferenceSeeder::class);

        $user = PublicUser::query()->create([
            'first_name' => 'Awa',
            'last_name' => 'Kone',
            'phone' => '0700000409',
            'commune' => 'Cocody',
            'password' => 'secret123',
            'status' => 'active',
            'phone_verified_at' => now(),
        ]);

        $token = JWTAuth::fromUser($user);

        $meterId = $this->withToken($token)->postJson('/api/v1/public/meters', [
            'network_type' => 'CIE',
            'meter_number' => 'CIE-40009',
            'label' => 'Appartement',
            'is_primary' => true,
        ])->json('data.meter.id');

        $city = City::query()->where('code', 'ABJ')->firstOrFail();
        $commune = Commune::query()->where('code', 'ABJ-COCODY')->firstOrFail();

        $this->withToken($token)->postJson('/api/v1/public/reports', [
            'meter_id' => $meterId,
            'country_id' => 999999,
            'city_id' => $city->id,
            'commune_id' => $commune->id,
            'signal_code' => 'EL-01',
            'description' => 'Coupure constatee depuis 18h00.',
            'signal_payload' => [],
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['country_id']);
    }

    public function test_public_user_can_confirm_resolution_of_resolved_report(): void
    {
        $this->seed([
            LocationReferenceSeeder::class,
            PricingRuleSeeder::class,
        ]);

        $user = PublicUser::query()->create([
            'first_name' => 'Awa',
            'last_name' => 'Kone',
            'phone' => '0700000401',
            'commune' => 'Cocody',
            'password' => 'secret123',
            'status' => 'active',
            'phone_verified_at' => now(),
        ]);

        $token = JWTAuth::fromUser($user);

        $meterId = $this->withToken($token)->postJson('/api/v1/public/meters', [
            'network_type' => 'CIE',
            'meter_number' => 'CIE-40001',
            'label' => 'Maison',
            'is_primary' => true,
        ])->json('data.meter.id');

        $country = Country::query()->where('code', 'CI')->firstOrFail();
        $city = City::query()->where('code', 'ABJ')->firstOrFail();
        $commune = Commune::query()->where('code', 'ABJ-COCODY')->firstOrFail();

        $report = IncidentReport::query()->create([
            'public_user_id' => $user->id,
            'meter_id' => $meterId,
            'country_id' => $country->id,
            'city_id' => $city->id,
            'commune_id' => $commune->id,
            'network_type' => 'CIE',
            'signal_code' => 'EL-01',
            'signal_label' => 'Coupure totale de courant',
            'incident_type' => 'power_outage',
            'reference' => 'SIG-TEST-001',
            'description' => 'Incident resolu par l institution.',
            'signal_payload' => [],
            'target_sla_hours' => 4,
            'status' => IncidentReportStatus::Resolved->value,
            'payment_status' => 'paid',
            'paid_at' => now(),
            'resolved_at' => now(),
            'official_response' => 'Le service a ete retabli.',
            'resolution_confirmation_status' => 'pending',
        ]);

        $this->withToken($token)->postJson("/api/v1/public/reports/{$report->id}/confirm-resolution")
            ->assertOk()
            ->assertJsonPath('data.report.resolution_confirmation.status', 'confirmed');

        $this->assertDatabaseHas('incident_reports', [
            'id' => $report->id,
            'resolution_confirmation_status' => 'confirmed',
        ]);
    }
}
