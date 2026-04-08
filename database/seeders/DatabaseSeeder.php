<?php

namespace Database\Seeders;

use Database\Seeders\Admin\SuperAdminSeeder;
use Database\Seeders\Reference\ApplicationSeeder;
use Database\Seeders\Reference\FeatureSeeder;
use Database\Seeders\Reference\LocationReferenceSeeder;
use Database\Seeders\Reference\OrganizationTypeSeeder;
use Database\Seeders\Reference\PricingRuleSeeder;
use Database\Seeders\Reference\SlaPolicySeeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            SuperAdminSeeder::class,
            ApplicationSeeder::class,
            FeatureSeeder::class,
            LocationReferenceSeeder::class,
            OrganizationTypeSeeder::class,
            PricingRuleSeeder::class,
            SlaPolicySeeder::class,
        ]);
    }
}
