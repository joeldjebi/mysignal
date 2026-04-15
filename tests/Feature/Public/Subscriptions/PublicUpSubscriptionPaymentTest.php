<?php

namespace Tests\Feature\Public\Subscriptions;

use App\Models\PublicUser;
use App\Models\SubscriptionPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class PublicUpSubscriptionPaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_user_can_pay_and_activate_subscription(): void
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
            'phone' => '0700000700',
            'commune' => 'Cocody',
            'password' => 'secret123',
            'status' => 'active',
            'phone_verified_at' => now(),
        ]);

        $token = JWTAuth::fromUser($user);

        $paymentId = $this->withToken($token)
            ->postJson('/api/v1/public/subscription/payments')
            ->assertCreated()
            ->assertJsonPath('data.payment.status', 'pending')
            ->assertJsonPath('data.payment.amount', 5000)
            ->json('data.payment.id');

        $this->withToken($token)
            ->postJson("/api/v1/public/subscription/payments/{$paymentId}/confirm")
            ->assertOk()
            ->assertJsonPath('data.payment.status', 'paid')
            ->assertJsonPath('data.subscription.status', 'active');

        $this->assertDatabaseHas('subscription_payments', [
            'id' => $paymentId,
            'public_user_id' => $user->id,
            'status' => 'paid',
            'amount' => 5000,
        ]);

        $this->assertDatabaseHas('up_subscriptions', [
            'public_user_id' => $user->id,
            'status' => 'active',
            'amount' => 5000,
        ]);
    }

    public function test_subscription_payment_initialization_returns_existing_pending_payment(): void
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
            'phone' => '0700000701',
            'commune' => 'Cocody',
            'password' => 'secret123',
            'status' => 'active',
            'phone_verified_at' => now(),
        ]);

        $token = JWTAuth::fromUser($user);

        $firstPaymentId = $this->withToken($token)
            ->postJson('/api/v1/public/subscription/payments')
            ->assertCreated()
            ->json('data.payment.id');

        $secondPaymentId = $this->withToken($token)
            ->postJson('/api/v1/public/subscription/payments')
            ->assertCreated()
            ->json('data.payment.id');

        $this->assertSame($firstPaymentId, $secondPaymentId);
        $this->assertDatabaseCount('subscription_payments', 1);
        $this->assertDatabaseCount('up_subscriptions', 1);
    }
}
