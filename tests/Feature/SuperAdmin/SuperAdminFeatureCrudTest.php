<?php

namespace Tests\Feature\SuperAdmin;

use App\Models\User;
use Database\Seeders\Admin\SuperAdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminFeatureCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_create_feature_from_web_crud(): void
    {
        $this->seed(SuperAdminSeeder::class);

        $superAdmin = User::query()->where('email', 'jo.djebi@gmail.com')->firstOrFail();

        $this->actingAs($superAdmin)
            ->post('/sa/features', [
                'code' => 'PUBLIC_ALERT_STATS',
                'name' => 'Statistiques d alertes',
                'description' => 'Permet de consulter les statistiques des signalements.',
            ])
            ->assertRedirect('/sa/features');

        $this->assertDatabaseHas('features', [
            'code' => 'PUBLIC_ALERT_STATS',
            'status' => 'active',
        ]);
    }
}
