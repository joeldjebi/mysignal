<?php

return [
    'public' => [
        // Valeur temporairement codee en V1. Elle migrera ensuite vers un parametrage super admin.
        'max_meters_per_network' => env('PUBLIC_MAX_METERS_PER_NETWORK', 1),
    ],
    'households' => [
        // Valeur temporairement codee en V1. Elle migrera ensuite vers un parametrage super admin.
        'invitation_expiry_minutes' => env('HOUSEHOLD_INVITATION_EXPIRY_MINUTES', 30),
    ],
    'reports' => [
        // Valeurs temporaires en V1. Elles migreront ensuite vers le parametrage super admin.
        'signal_types' => [
            'EL-01' => [
                'network_type' => 'CIE',
                'label' => 'Coupure totale de courant',
                'description' => 'Heure debut, GPS, numero de compteur',
                'sla_target' => ['hours' => 4, 'label' => '4h'],
                'data_fields' => [],
            ],
            'EL-02' => [
                'network_type' => 'CIE',
                'label' => 'Coupure repetitive (< 24h)',
                'description' => 'Nombre d occurrences, amplitude, GPS',
                'sla_target' => ['hours' => 2, 'label' => '2h'],
                'data_fields' => [
                    ['key' => 'occurrences_count', 'label' => 'Nombre d occurrences', 'type' => 'number', 'required' => true],
                    ['key' => 'amplitude', 'label' => 'Amplitude', 'type' => 'text', 'required' => true],
                ],
            ],
            'EL-03' => [
                'network_type' => 'CIE',
                'label' => 'Fluctuation / surtension',
                'description' => 'Niveau de tension, materiel endommage',
                'sla_target' => ['hours' => 6, 'label' => '6h'],
                'data_fields' => [
                    ['key' => 'voltage_level', 'label' => 'Niveau de tension', 'type' => 'text', 'required' => true],
                    ['key' => 'damaged_equipment', 'label' => 'Materiel endommage', 'type' => 'textarea', 'required' => true],
                ],
            ],
            'EL-04' => [
                'network_type' => 'CIE',
                'label' => 'Compteur defaillant / illisible',
                'description' => 'Photo compteur, numero de serie',
                'sla_target' => ['hours' => 48, 'label' => '48h'],
                'data_fields' => [
                    ['key' => 'meter_photo_reference', 'label' => 'Reference photo compteur', 'type' => 'text', 'required' => true],
                    ['key' => 'meter_serial_number', 'label' => 'Numero de serie', 'type' => 'text', 'required' => true],
                ],
            ],
            'EL-05' => [
                'network_type' => 'CIE',
                'label' => 'Fil / cable denude',
                'description' => 'Photo, GPS precis',
                'sla_target' => ['hours' => 1, 'label' => '1h urgence'],
                'data_fields' => [
                    ['key' => 'photo_reference', 'label' => 'Reference photo', 'type' => 'text', 'required' => true],
                    ['key' => 'precise_gps', 'label' => 'GPS precis', 'type' => 'text', 'required' => true],
                ],
            ],
            'EL-06' => [
                'network_type' => 'CIE',
                'label' => 'Facturation anormale',
                'description' => 'Montant facture, consommation declaree',
                'sla_target' => ['hours' => 72, 'label' => '72h'],
                'data_fields' => [
                    ['key' => 'invoice_amount', 'label' => 'Montant facture', 'type' => 'number', 'required' => true],
                    ['key' => 'declared_consumption', 'label' => 'Consommation declaree', 'type' => 'text', 'required' => true],
                ],
            ],
            'EL-07' => [
                'network_type' => 'CIE',
                'label' => 'Poteau endommage / penche',
                'description' => 'Photo, localisation GPS',
                'sla_target' => ['hours' => 2, 'label' => '2h urgence'],
                'data_fields' => [
                    ['key' => 'photo_reference', 'label' => 'Reference photo', 'type' => 'text', 'required' => true],
                    ['key' => 'gps_location', 'label' => 'Localisation GPS', 'type' => 'text', 'required' => true],
                ],
            ],
            'EAU-01' => [
                'network_type' => 'SODECI',
                'label' => 'Coupure d eau totale',
                'description' => 'Heure debut, GPS, numero de compteur',
                'sla_target' => ['hours' => 6, 'label' => '6h'],
                'data_fields' => [],
            ],
            'EAU-02' => [
                'network_type' => 'SODECI',
                'label' => 'Pression insuffisante',
                'description' => 'Niveau de pression declare, duree',
                'sla_target' => ['hours' => 8, 'label' => '8h'],
                'data_fields' => [
                    ['key' => 'declared_pressure_level', 'label' => 'Niveau de pression declare', 'type' => 'text', 'required' => true],
                    ['key' => 'duration', 'label' => 'Duree', 'type' => 'text', 'required' => true],
                ],
            ],
            'EAU-03' => [
                'network_type' => 'SODECI',
                'label' => 'Eau trouble / malodorante',
                'description' => 'Photo, description, heure',
                'sla_target' => ['hours' => 4, 'label' => '4h'],
                'data_fields' => [
                    ['key' => 'photo_reference', 'label' => 'Reference photo', 'type' => 'text', 'required' => true],
                    ['key' => 'issue_details', 'label' => 'Details constates', 'type' => 'textarea', 'required' => true],
                ],
            ],
            'EAU-04' => [
                'network_type' => 'SODECI',
                'label' => 'Fuite canalisation (rue)',
                'description' => 'Photo, volume estime, GPS',
                'sla_target' => ['hours' => 2, 'label' => '2h urgence'],
                'data_fields' => [
                    ['key' => 'photo_reference', 'label' => 'Reference photo', 'type' => 'text', 'required' => true],
                    ['key' => 'estimated_volume', 'label' => 'Volume estime', 'type' => 'text', 'required' => true],
                    ['key' => 'gps_location', 'label' => 'GPS', 'type' => 'text', 'required' => true],
                ],
            ],
            'EAU-05' => [
                'network_type' => 'SODECI',
                'label' => 'Compteur defaillant / illisible',
                'description' => 'Photo compteur, numero de serie',
                'sla_target' => ['hours' => 48, 'label' => '48h'],
                'data_fields' => [
                    ['key' => 'meter_photo_reference', 'label' => 'Reference photo compteur', 'type' => 'text', 'required' => true],
                    ['key' => 'meter_serial_number', 'label' => 'Numero de serie', 'type' => 'text', 'required' => true],
                ],
            ],
            'EAU-06' => [
                'network_type' => 'SODECI',
                'label' => 'Facturation anormale',
                'description' => 'Montant facture, consommation declaree',
                'sla_target' => ['hours' => 72, 'label' => '72h'],
                'data_fields' => [
                    ['key' => 'invoice_amount', 'label' => 'Montant facture', 'type' => 'number', 'required' => true],
                    ['key' => 'declared_consumption', 'label' => 'Consommation declaree', 'type' => 'text', 'required' => true],
                ],
            ],
        ],
    ],
];
