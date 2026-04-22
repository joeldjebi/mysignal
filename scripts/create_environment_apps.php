<?php

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Application;
use App\Models\SignalType;

$nextSortOrder = ((int) Application::query()->max('sort_order')) + 1;

$catalog = [
    [
        'application' => [
            'code' => 'GEST_DECHETS',
            'name' => 'Gestion de dechets',
            'slug' => 'gestion-de-dechets',
            'tagline' => 'Le suivi des griefs lies a la gestion des dechets.',
            'short_description' => 'Pour les depotoirs sauvages, les retards de collecte et les situations de non-collecte des dechets.',
            'long_description' => 'GESTION DE DECHETS centralise les signalements lies a la collecte, au traitement et aux dysfonctionnements observes dans la gestion des dechets menagers et assimilables.',
            'logo_path' => 'image/logo/logo-my-signal.png',
            'primary_color' => '#174c3c',
            'secondary_color' => '#2f7d65',
            'accent_color' => '#d39b3f',
            'status' => 'active',
            'sort_order' => $nextSortOrder++,
        ],
        'signal_types' => [
            [
                'code' => 'GD_DEPOTOIRS_SAUVAGES',
                'label' => 'Depotoirs sauvages',
                'description' => 'Presence de dechets abandonnes ou de depotoirs anarchiques dans l espace public.',
            ],
            [
                'code' => 'GD_RETARD_COLLECTE',
                'label' => 'Retard de collecte des dechets menageres',
                'description' => 'Retard inhabituel ou repetition de retards dans la collecte des dechets menagers.',
            ],
            [
                'code' => 'GD_NON_COLLECTE',
                'label' => 'Non-collecte des dechets',
                'description' => 'Absence complete de collecte des dechets sur la periode attendue.',
            ],
        ],
    ],
    [
        'application' => [
            'code' => 'INDUSTR_ENV',
            'name' => 'Industrie',
            'slug' => 'industrie',
            'tagline' => 'Le suivi des infractions environnementales d origine industrielle.',
            'short_description' => 'Pour les pollutions de l air, de l eau ou des sols et les gestions illicites de dechets.',
            'long_description' => 'INDUSTRIE permet de centraliser les signalements lies aux atteintes environnementales d origine industrielle afin de faciliter le suivi et les actions correctives.',
            'logo_path' => 'image/logo/logo-my-signal.png',
            'primary_color' => '#2e3440',
            'secondary_color' => '#55606f',
            'accent_color' => '#c26a3d',
            'status' => 'active',
            'sort_order' => $nextSortOrder++,
        ],
        'signal_types' => [
            [
                'code' => 'IND_POLL_AIR_EAU_SOLS',
                'label' => 'Pollution de l air, de l eau ou des sols',
                'description' => 'Signalement d une pollution environnementale affectant l air, l eau ou les sols.',
            ],
            [
                'code' => 'IND_GEST_ILLIC_DECHETS',
                'label' => 'Gestion illicite des dechets',
                'description' => 'Pratiques illicites de traitement, stockage, transport ou elimination de dechets.',
            ],
        ],
    ],
    [
        'application' => [
            'code' => 'TRANSPORT',
            'name' => 'Transport',
            'slug' => 'transport',
            'tagline' => 'Le suivi des infractions environnementales liees au transport.',
            'short_description' => 'Pour les vehicules polluants et les transports de dechets, sable ou gravier non couverts.',
            'long_description' => 'TRANSPORT regroupe les signalements lies aux nuisances et infractions environnementales observees dans les activites de transport routier.',
            'logo_path' => 'image/logo/logo-my-signal.png',
            'primary_color' => '#103c5a',
            'secondary_color' => '#2b6d96',
            'accent_color' => '#d0a24d',
            'status' => 'active',
            'sort_order' => $nextSortOrder++,
        ],
        'signal_types' => [
            [
                'code' => 'TR_VEHICULE_POLLUANT',
                'label' => 'Vehicule polluant (gaz d echappement)',
                'description' => 'Vehicule emettant des gaz d echappement polluants ou excessifs.',
            ],
            [
                'code' => 'TR_ORDURES_NON_COUVERT',
                'label' => 'Vehicules de collecte d ordures non couvert',
                'description' => 'Vehicule de collecte d ordures circulant sans couverture adequate.',
            ],
            [
                'code' => 'TR_SABLE_GRAV_NON_COUVERT',
                'label' => 'Vehicules de transport de sable ou gravier non couverts',
                'description' => 'Vehicule transportant du sable, du gravier ou des agregats sans couverture conforme.',
            ],
        ],
    ],
];

$createdApplications = [];
$createdSignalTypes = [];

foreach ($catalog as $entry) {
    $applicationData = $entry['application'];

    $application = Application::query()->updateOrCreate(
        ['code' => $applicationData['code']],
        $applicationData,
    );

    $createdApplications[] = [
        'id' => $application->id,
        'code' => $application->code,
        'name' => $application->name,
    ];

    foreach ($entry['signal_types'] as $signalTypeData) {
        $signalType = SignalType::query()->updateOrCreate(
            [
                'application_id' => $application->id,
                'organization_id' => null,
                'code' => $signalTypeData['code'],
            ],
            [
                'network_type' => $application->code,
                'label' => $signalTypeData['label'],
                'default_sla_hours' => 24,
                'description' => $signalTypeData['description'],
                'data_fields' => [],
                'status' => 'active',
            ],
        );

        $createdSignalTypes[] = [
            'id' => $signalType->id,
            'application_code' => $application->code,
            'code' => $signalType->code,
            'label' => $signalType->label,
        ];
    }
}

echo json_encode([
    'applications' => $createdApplications,
    'signal_types' => $createdSignalTypes,
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL;
