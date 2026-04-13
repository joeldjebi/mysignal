<?php

namespace Database\Seeders\Reference;

use App\Models\PricingRule;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;

class PricingRuleSeeder extends Seeder
{
    public function run(): void
    {
        $startsAt = CarbonImmutable::now();

        foreach ([
            [
                'code' => 'public_signal_report',
                'label' => 'Paiement signalement public',
            ],
            [
                'code' => 'public_up_standard',
                'label' => 'Tarification usager public',
            ],
            [
                'code' => 'public_upe_standard',
                'label' => 'Tarification usager public entreprise',
            ],
            [
                'code' => 'public_upti_standard',
                'label' => 'Tarification travailleur independant',
            ],
        ] as $pricingRule) {
            PricingRule::query()->updateOrCreate(
                ['code' => $pricingRule['code']],
                [
                    'label' => $pricingRule['label'],
                    'amount' => 100,
                    'currency' => 'FCFA',
                    'status' => 'active',
                    'starts_at' => $startsAt,
                ],
            );
        }
    }
}
