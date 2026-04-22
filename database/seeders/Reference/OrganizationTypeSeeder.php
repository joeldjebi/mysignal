<?php

namespace Database\Seeders\Reference;

use App\Models\OrganizationType;
use Illuminate\Database\Seeder;

class OrganizationTypeSeeder extends Seeder
{
    public function run(): void
    {
        $organizationTypes = [
            [
                'code' => 'ELECTRICITE',
                'name' => 'Electricite',
                'description' => 'Institutions et portails lies a la distribution, a la qualite et a la continuite du service d energie.',
            ],
            [
                'code' => 'EAU_POTABLE',
                'name' => 'Eau potable',
                'description' => 'Institutions et portails lies a l acces a l eau potable, a la pression, aux fuites et a la qualite de service.',
            ],
            [
                'code' => 'BANQUE',
                'name' => 'Banque',
                'description' => 'Institutions bancaires traitant les griefs clients lies aux comptes, transactions, cartes et services financiers.',
            ],
            [
                'code' => 'MICROFINANCE',
                'name' => 'Microfinance',
                'description' => 'Institutions de microfinance traitant les griefs sur les services financiers de proximite.',
            ],
            [
                'code' => 'ASSURANCE',
                'name' => 'Assurance',
                'description' => 'Institutions d assurance traitant les sinistres, indemnisations, remboursements et litiges de contrats.',
            ],
            [
                'code' => 'TELECOMMUNICATION',
                'name' => 'Telecommunication',
                'description' => 'Institutions telecoms gerant la voix, les SMS, la data et la qualite de connectivite.',
            ],
            [
                'code' => 'ENVIRONNEMENT',
                'name' => 'Environnement',
                'description' => 'Institutions traitant les griefs lies a la pollution, aux nuisances et au cadre de vie.',
            ],
            [
                'code' => 'ENTREPRISE_GO',
                'name' => 'Entreprise GO',
                'description' => 'Type historique conserve pour compatibilite avec les premiers tests du systeme.',
            ],
            [
                'code' => 'PARTNER_ESTABLISHMENT',
                'name' => 'Etablissement partenaire',
                'description' => 'Boutiques, supermarches, pharmacies et enseignes partenaires habilites a verifier les cartes et appliquer des reductions.',
            ],
        ];

        foreach ($organizationTypes as $organizationType) {
            OrganizationType::query()->updateOrCreate(
                ['code' => $organizationType['code']],
                [
                    'name' => $organizationType['name'],
                    'description' => $organizationType['description'],
                    'status' => 'active',
                ],
            );
        }
    }
}
