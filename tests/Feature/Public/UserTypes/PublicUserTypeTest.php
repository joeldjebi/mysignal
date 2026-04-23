<?php

namespace Tests\Feature\Public\UserTypes;

use Database\Seeders\Reference\PricingRuleSeeder;
use Database\Seeders\Reference\PublicUserTypeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicUserTypeTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_user_types_endpoint_returns_active_types_for_mobile_registration(): void
    {
        $this->seed(PricingRuleSeeder::class);
        $this->seed(PublicUserTypeSeeder::class);

        $response = $this->getJson('/api/v1/public/user-types');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user_types.0.code', 'UP')
            ->assertJsonPath('data.user_types.0.profile_kind', 'individual')
            ->assertJsonPath('data.user_types.0.pricing_rule.currency', 'FCFA')
            ->assertJsonStructure([
                'data' => [
                    'user_types' => [
                        '*' => [
                            'id',
                            'code',
                            'name',
                            'description',
                            'profile_kind',
                            'sort_order',
                            'pricing_rule' => [
                                'id',
                                'code',
                                'label',
                                'amount',
                                'currency',
                            ],
                        ],
                    ],
                ],
            ]);
    }
}
