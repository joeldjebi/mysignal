<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'google_maps' => [
        'key' => env('GOOGLE_MAPS_API_KEY'),
    ],

    'firebase' => [
        'enabled' => (bool) env('FIREBASE_PUSH_ENABLED', true),
        'project_id' => env('FIREBASE_PROJECT_ID'),
        'credentials' => env('FIREBASE_CREDENTIALS'),
        'web' => [
            'vapid_key' => env('FIREBASE_WEB_VAPID_KEY'),
            'config' => [
                'apiKey' => env('FIREBASE_WEB_API_KEY'),
                'authDomain' => env('FIREBASE_WEB_AUTH_DOMAIN'),
                'projectId' => env('FIREBASE_PROJECT_ID'),
                'storageBucket' => env('FIREBASE_WEB_STORAGE_BUCKET'),
                'messagingSenderId' => env('FIREBASE_WEB_MESSAGING_SENDER_ID'),
                'appId' => env('FIREBASE_WEB_APP_ID'),
            ],
        ],
    ],

    'public_auth' => [
        'otp_digits' => (int) env('PUBLIC_AUTH_OTP_DIGITS', 4),
        'default_otp' => env('PUBLIC_AUTH_DEFAULT_OTP', '2604'),
    ],

    'partner_auth' => [
        'otp_digits' => (int) env('PARTNER_AUTH_OTP_DIGITS', 4),
        'default_otp' => env('PARTNER_AUTH_DEFAULT_OTP', '2604'),
    ],

];
