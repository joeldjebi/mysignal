<?php

namespace Tests\Feature\SuperAdmin;

use App\Models\Commune;
use App\Models\Neighborhood;
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

    public function test_super_admin_can_manage_neighborhoods_and_sub_neighborhoods(): void
    {
        $this->seed([
            SuperAdminSeeder::class,
            LocationReferenceSeeder::class,
        ]);

        $superAdmin = User::query()->where('email', 'jo.djebi@gmail.com')->firstOrFail();
        $commune = Commune::query()->where('code', 'ABJ-COCODY')->firstOrFail();

        $this->actingAs($superAdmin)
            ->post('/sa/neighborhoods', [
                'commune_id' => $commune->id,
                'name' => 'Deux Plateaux',
                'code' => 'ABJ-COCODY-DEUX-PLATEAUX',
            ])
            ->assertRedirect('/sa/neighborhoods');

        $neighborhood = Neighborhood::query()
            ->where('code', 'ABJ-COCODY-DEUX-PLATEAUX')
            ->firstOrFail();

        $this->actingAs($superAdmin)
            ->post('/sa/sub-neighborhoods', [
                'neighborhood_id' => $neighborhood->id,
                'name' => 'Vallon',
                'code' => 'ABJ-COCODY-DEUX-PLATEAUX-VALLON',
            ])
            ->assertRedirect('/sa/sub-neighborhoods');

        $this->assertDatabaseHas('neighborhoods', [
            'commune_id' => $commune->id,
            'code' => 'ABJ-COCODY-DEUX-PLATEAUX',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('sub_neighborhoods', [
            'neighborhood_id' => $neighborhood->id,
            'code' => 'ABJ-COCODY-DEUX-PLATEAUX-VALLON',
            'status' => 'active',
        ]);
    }
}
