<?php

namespace Tests\Feature\SuperAdmin;

use App\Models\SubscriptionPlan;
use App\Models\User;
use Database\Seeders\Admin\SuperAdminSeeder;
use Database\Seeders\Reference\SuperAdminPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminSubscriptionPlanTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_create_subscription_plan(): void
    {
        $this->seed([
            SuperAdminSeeder::class,
            SuperAdminPermissionSeeder::class,
        ]);

        $superAdmin = User::query()->where('email', 'jo.djebi@gmail.com')->firstOrFail();

        $this->actingAs($superAdmin)
            ->post('/sa/subscription-plans', [
                'code' => 'up_annual',
                'name' => 'Abonnement annuel UP',
                'description' => 'Plan annuel permettant aux UP de faire des signalements.',
                'duration_months' => 12,
                'price' => 5000,
                'currency' => 'fcfa',
                'is_active' => '1',
            ])
            ->assertRedirect('/sa/subscription-plans');

        $this->assertDatabaseHas('subscription_plans', [
            'code' => 'UP_ANNUAL',
            'name' => 'Abonnement annuel UP',
            'duration_months' => 12,
            'price' => 5000,
            'currency' => 'FCFA',
            'is_active' => true,
            'created_by' => $superAdmin->id,
        ]);
    }

    public function test_super_admin_cannot_create_second_active_subscription_plan(): void
    {
        $this->seed([
            SuperAdminSeeder::class,
            SuperAdminPermissionSeeder::class,
        ]);

        SubscriptionPlan::query()->create([
            'code' => 'UP_ANNUAL',
            'name' => 'Abonnement annuel UP',
            'duration_months' => 12,
            'price' => 5000,
            'currency' => 'FCFA',
            'is_active' => true,
        ]);

        $superAdmin = User::query()->where('email', 'jo.djebi@gmail.com')->firstOrFail();

        $this->actingAs($superAdmin)
            ->from('/sa/subscription-plans')
            ->post('/sa/subscription-plans', [
                'code' => 'UP_ANNUAL_PLUS',
                'name' => 'Abonnement annuel UP Plus',
                'duration_months' => 12,
                'price' => 10000,
                'currency' => 'FCFA',
                'is_active' => '1',
            ])
            ->assertRedirect('/sa/subscription-plans')
            ->assertSessionHasErrors('is_active');

        $this->assertDatabaseMissing('subscription_plans', [
            'code' => 'UP_ANNUAL_PLUS',
        ]);
    }
}
