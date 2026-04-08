<?php

namespace Tests\Feature\SuperAdmin;

use Database\Seeders\Admin\SuperAdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeded_super_admin_can_login_and_access_dashboard(): void
    {
        $this->seed(SuperAdminSeeder::class);

        $response = $this->post('/sa/login', [
            'email' => 'jo.djebi@gmail.com',
            'password' => '12345678',
        ]);

        $response->assertRedirect('/sa/dashboard');

        $this->get('/sa/dashboard')
            ->assertOk()
            ->assertSee('Super Admin');
    }
}
