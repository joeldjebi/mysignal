<?php

return [
    'access_key' => env('WASABI_ACCESS_KEY', env('WAS_ACCESS_KEY')),
    'secret_key' => env('WASABI_SECRET_KEY', env('WAS_SECRET_KEY')),
    'region' => env('WASABI_REGION', 'us-east-1'),
    'bucket' => env('WASABI_BUCKET'),
    'endpoint' => env('WASABI_ENDPOINT'),
    'url' => env('WASABI_URL'),
    'avatar_directory' => env('WASABI_AVATAR_DIRECTORY', 'images/avatar'),
    'application_logo_directory' => env('WASABI_APPLICATION_LOGO_DIRECTORY', 'applications/logos'),
    'application_icon_directory' => env('WASABI_APPLICATION_ICON_DIRECTORY', 'applications/icons'),
    'organization_logo_directory' => env('WASABI_ORGANIZATION_LOGO_DIRECTORY', 'organizations/logos'),
    'application_hero_directory' => env('WASABI_APPLICATION_HERO_DIRECTORY', 'applications/heroes'),
    'landing_partner_logo_directory' => env('WASABI_LANDING_PARTNER_LOGO_DIRECTORY', 'landing/partners'),
    'report_signal_directory' => env('WASABI_REPORT_SIGNAL_DIRECTORY', 'reports/signals'),
    'report_damage_directory' => env('WASABI_REPORT_DAMAGE_DIRECTORY', 'reports/damages'),
    'purchase_receipt_directory' => env('WASABI_PURCHASE_RECEIPT_DIRECTORY', 'purchase-receipts'),
    'connect_timeout' => (int) env('WASABI_CONNECT_TIMEOUT', 10),
    'timeout' => (int) env('WASABI_TIMEOUT', 180),
    'retries' => (int) env('WASABI_RETRIES', 2),
];
