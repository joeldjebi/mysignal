<?php

namespace Tests\Feature\SuperAdmin;

use App\Models\User;
use Database\Seeders\Admin\SuperAdminSeeder;
use Database\Seeders\Reference\LocationReferenceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminCrudPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_create_country_and_permission_from_web_crud(): void
    {
        $this->seed([
            SuperAdminSeeder::class,
            LocationReferenceSeeder::class,
        ]);

        $superAdmin = User::query()->where('email', 'jo.djebi@gmail.com')->firstOrFail();

        $this->actingAs($superAdmin)
            ->post('/sa/countries', [
                'name' => 'Ghana',
                'code' => 'GH',
            ])
            ->assertRedirect('/sa/countries');

        $this->actingAs($superAdmin)
            ->post('/sa/permissions', [
                'code' => 'MANAGE_LOCATIONS',
                'name' => 'Gerer les localites',
                'description' => 'Autorise la gestion des pays, villes et communes.',
            ])
            ->assertRedirect('/sa/permissions');

        $this->assertDatabaseHas('countries', [
            'code' => 'GH',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('permissions', [
            'code' => 'MANAGE_LOCATIONS',
            'status' => 'active',
        ]);
    }
}
