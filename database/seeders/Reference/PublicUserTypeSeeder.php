<?php

namespace Database\Seeders\Reference;

use App\Models\PricingRule;
use App\Models\PublicUserType;
use Illuminate\Database\Seeder;

class PublicUserTypeSeeder extends Seeder
{
    public function run(): void
    {
        $pricingRuleIds = PricingRule::query()->pluck('id', 'code');

        $types = [
            [
                'code' => 'UP',
                'pricing_rule_code' => 'public_up_standard',
                'name' => 'Usager public',
                'description' => 'Compte public classique pour les particuliers.',
                'profile_kind' => 'individual',
                'sort_order' => 1,
            ],
            [
                'code' => 'UPE',
                'pricing_rule_code' => 'public_upe_standard',
                'name' => 'Usager public entreprise',
                'description' => 'Compte public dedie aux entreprises et structures professionnelles.',
                'profile_kind' => 'business',
                'sort_order' => 2,
            ],
            [
                'code' => 'UPTI',
                'pricing_rule_code' => 'public_upti_standard',
                'name' => 'Travailleur independant',
                'description' => 'Compte public dedie aux travailleurs independants avec secteur d activite requis.',
                'profile_kind' => 'individual',
                'sort_order' => 3,
            ],
        ];

        foreach ($types as $type) {
            $pricingRuleId = $pricingRuleIds[$type['pricing_rule_code']] ?? null;

            if ($pricingRuleId === null) {
                continue;
            }

            PublicUserType::query()->updateOrCreate(
                ['code' => $type['code']],
                [
                    'pricing_rule_id' => $pricingRuleId,
                    'name' => $type['name'],
                    'description' => $type['description'],
                    'profile_kind' => $type['profile_kind'],
                    'status' => 'active',
                    'sort_order' => $type['sort_order'],
                ],
            );
        }
    }
}
