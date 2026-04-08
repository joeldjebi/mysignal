<?php

namespace Database\Seeders\Reference;

use App\Models\PricingRule;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;

class PricingRuleSeeder extends Seeder
{
    public function run(): void
    {
        PricingRule::query()->updateOrCreate(
            ['code' => 'public_signal_report'],
            [
                'label' => 'Paiement signalement public',
                'amount' => 100,
                'currency' => 'FCFA',
                'status' => 'active',
                'starts_at' => CarbonImmutable::now(),
            ],
        );
    }
}
