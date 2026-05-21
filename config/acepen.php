<?php

return [
    'reports' => [
        // Valeurs temporaires en V1. Elles migreront ensuite vers le parametrage super admin.
        'signal_types' => [
            'EL-01' => [
                'network_type' => 'CIE',
                'label' => 'Coupure totale de courant',
                'description' => 'Heure debut, GPS, numero de compteur',
                'sla_target' => ['hours' => 4, 'label' => '4h'],
            ],
            'EL-02' => [
                'network_type' => 'CIE',
                'label' => 'Coupure repetitive (< 24h)',
                'description' => 'Nombre d occurrences, amplitude, GPS',
                'sla_target' => ['hours' => 2, 'label' => '2h'],
            ],
            'EL-03' => [
                'network_type' => 'CIE',
                'label' => 'Fluctuation / surtension',
                'description' => 'Niveau de tension, materiel endommage',
                'sla_target' => ['hours' => 6, 'label' => '6h'],
            ],
            'EL-04' => [
                'network_type' => 'CIE',
                'label' => 'Compteur defaillant / illisible',
                'description' => 'Photo compteur, numero de serie',
                'sla_target' => ['hours' => 48, 'label' => '48h'],
            ],
            'EL-05' => [
                'network_type' => 'CIE',
                'label' => 'Fil / cable denude',
                'description' => 'Photo, GPS precis',
                'sla_target' => ['hours' => 1, 'label' => '1h urgence'],
            ],
            'EL-06' => [
                'network_type' => 'CIE',
                'label' => 'Facturation anormale',
                'description' => 'Montant facture, consommation declaree',
                'sla_target' => ['hours' => 72, 'label' => '72h'],
            ],
            'EL-07' => [
                'network_type' => 'CIE',
                'label' => 'Poteau endommage / penche',
                'description' => 'Photo, localisation GPS',
                'sla_target' => ['hours' => 2, 'label' => '2h urgence'],
            ],
            'EAU-01' => [
                'network_type' => 'SODECI',
                'label' => 'Coupure d eau totale',
                'description' => 'Heure debut, GPS, numero de compteur',
                'sla_target' => ['hours' => 6, 'label' => '6h'],
            ],
            'EAU-02' => [
                'network_type' => 'SODECI',
                'label' => 'Pression insuffisante',
                'description' => 'Niveau de pression declare, duree',
                'sla_target' => ['hours' => 8, 'label' => '8h'],
            ],
            'EAU-03' => [
                'network_type' => 'SODECI',
                'label' => 'Eau trouble / malodorante',
                'description' => 'Photo, description, heure',
                'sla_target' => ['hours' => 4, 'label' => '4h'],
            ],
            'EAU-04' => [
                'network_type' => 'SODECI',
                'label' => 'Fuite canalisation (rue)',
                'description' => 'Photo, volume estime, GPS',
                'sla_target' => ['hours' => 2, 'label' => '2h urgence'],
            ],
            'EAU-05' => [
                'network_type' => 'SODECI',
                'label' => 'Compteur defaillant / illisible',
                'description' => 'Photo compteur, numero de serie',
                'sla_target' => ['hours' => 48, 'label' => '48h'],
            ],
            'EAU-06' => [
                'network_type' => 'SODECI',
                'label' => 'Facturation anormale',
                'description' => 'Montant facture, consommation declaree',
                'sla_target' => ['hours' => 72, 'label' => '72h'],
            ],
        ],
    ],
];
