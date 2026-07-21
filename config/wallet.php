<?php

return [
    'apple' => [
        'pass_type_identifier' => env('APPLE_PASS_TYPE_IDENTIFIER'),
        'team_identifier' => env('APPLE_TEAM_IDENTIFIER'),
        'organization_name' => env('APPLE_WALLET_ORGANIZATION_NAME', 'My Signal'),
        'description' => env('APPLE_WALLET_DESCRIPTION', 'Carte Privilège My Signal'),
        'logo_text' => env('APPLE_WALLET_LOGO_TEXT', 'My Signal'),
        'foreground_color' => env('APPLE_WALLET_FOREGROUND_COLOR', 'rgb(255, 255, 255)'),
        'background_color' => env('APPLE_WALLET_BACKGROUND_COLOR', 'rgb(201, 162, 39)'),
        'label_color' => env('APPLE_WALLET_LABEL_COLOR', 'rgb(255, 255, 255)'),
        'cert_path' => env('APPLE_PASS_CERT_PATH'),
        'key_path' => env('APPLE_PASS_KEY_PATH'),
        'wwdr_path' => env('APPLE_WWDR_CERT_PATH'),
        'cert_base64' => env('APPLE_PASS_CERT_BASE64'),
        'key_base64' => env('APPLE_PASS_KEY_BASE64'),
        'wwdr_base64' => env('APPLE_WWDR_CERT_BASE64'),
        'key_passphrase' => env('APPLE_PASS_KEY_PASSPHRASE'),
        'asset_path' => env('APPLE_PASS_ASSET_PATH', 'resources/wallet/apple'),
    ],

    'google' => [
        'issuer_id' => env('GOOGLE_WALLET_ISSUER_ID'),
        'class_id' => env('GOOGLE_WALLET_CLASS_ID'),
        'service_account_path' => env('GOOGLE_WALLET_SERVICE_ACCOUNT_PATH'),
        'service_account_json' => env('GOOGLE_WALLET_SERVICE_ACCOUNT_JSON'),
        'service_account_base64' => env('GOOGLE_WALLET_SERVICE_ACCOUNT_BASE64'),
        'background_color' => env('GOOGLE_WALLET_BACKGROUND_COLOR', '#C9A227'),
    ],
];
