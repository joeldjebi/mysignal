<?php

namespace Tests\Feature\Public\Subscriptions;

use App\Models\PublicUser;
use App\Models\SubscriptionPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class PublicUpSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_user_can_initialize_pending_subscription_from_active_plan(): void
    {
        $plan = SubscriptionPlan::query()->create([
            'code' => 'UP_ANNUAL',
            'name' => 'Abonnement annuel UP',
            'duration_months' => 12,
            'price' => 5000,
            'currency' => 'FCFA',
            'is_active' => true,
        ]);

        $user = PublicUser::query()->create([
            'first_name' => 'Awa',
            'last_name' => 'Kone',
            'phone' => '0700000600',
            'commune' => 'Cocody',
            'password' => 'secret123',
            'status' => 'active',
            'phone_verified_at' => now(),
        ]);

        $token = JWTAuth::fromUser($user);

        $subscriptionId = $this->withToken($token)
            ->postJson('/api/v1/public/subscription')
            ->assertCreated()
            ->assertJsonPath('data.subscription.status', 'pending')
            ->assertJsonPath('data.subscription.amount', 5000)
            ->assertJsonPath('data.subscription.currency', 'FCFA')
            ->assertJsonPath('data.subscription.plan.code', 'UP_ANNUAL')
            ->json('data.subscription.id');

        $this->assertDatabaseHas('up_subscriptions', [
            'id' => $subscriptionId,
            'public_user_id' => $user->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'pending',
            'amount' => 5000,
            'currency' => 'FCFA',
            'grace_period_days' => 1,
        ]);

        $this->withToken($token)
            ->getJson('/api/v1/public/subscription')
            ->assertOk()
            ->assertJsonPath('data.subscription.id', $subscriptionId);
    }

    public function test_subscription_initialization_returns_existing_pending_subscription(): void
    {
        SubscriptionPlan::query()->create([
            'code' => 'UP_ANNUAL',
            'name' => 'Abonnement annuel UP',
            'duration_months' => 12,
            'price' => 5000,
            'currency' => 'FCFA',
            'is_active' => true,
        ]);

        $user = PublicUser::query()->create([
            'first_name' => 'Awa',
            'last_name' => 'Kone',
            'phone' => '0700000601',
            'commune' => 'Cocody',
            'password' => 'secret123',
            'status' => 'active',
            'phone_verified_at' => now(),
        ]);

        $token = JWTAuth::fromUser($user);

        $firstSubscriptionId = $this->withToken($token)
            ->postJson('/api/v1/public/subscription')
            ->assertCreated()
            ->json('data.subscription.id');

        $secondSubscriptionId = $this->withToken($token)
            ->postJson('/api/v1/public/subscription')
            ->assertCreated()
            ->json('data.subscription.id');

        $this->assertSame($firstSubscriptionId, $secondSubscriptionId);
        $this->assertDatabaseCount('up_subscriptions', 1);
    }
}
