<?php

namespace Database\Seeders\Reference;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            [
                'code' => 'HUISSIER',
                'name' => 'Huissier',
                'description' => 'Role dedie au constat des faits et a la production du rapport de constat.',
            ],
            [
                'code' => 'AVOCAT',
                'name' => 'Avocat',
                'description' => 'Role dedie au suivi de la procedure judiciaire et des actes contentieux.',
            ],
        ] as $role) {
            Role::query()->updateOrCreate(
                ['code' => $role['code']],
                [
                    'name' => $role['name'],
                    'description' => $role['description'],
                    'status' => 'active',
                ]
            );
        }
    }
}
