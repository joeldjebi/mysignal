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

    'fineopay' => [
        'base_url' => env('FINEOPAY_BASE_URL', 'https://api.fineopay.com'),
        'checkout_path' => env('FINEOPAY_CHECKOUT_PATH', '/api/v1/business/dev/checkout-link'),
        'business_code' => env('FINEOPAY_BUSINESS_CODE'),
        'api_key' => env('FINEOPAY_API_KEY'),
        'callback_token' => env('FINEOPAY_CALLBACK_TOKEN'),
        'connect_timeout' => (int) env('FINEOPAY_CONNECT_TIMEOUT', 10),
        'timeout' => (int) env('FINEOPAY_TIMEOUT', 45),
    ],

    'public_auth' => [
        'otp_digits' => (int) env('PUBLIC_AUTH_OTP_DIGITS', 4),
        'default_otp' => env(
            'PUBLIC_AUTH_DEFAULT_OTP',
            in_array(env('APP_ENV', 'production'), ['local', 'testing'], true) ? '2604' : ''
        ),
        'token_ttl_minutes' => (int) env('PUBLIC_AUTH_TOKEN_TTL_MINUTES', 1051200),
        'send_sms' => (bool) env('PUBLIC_AUTH_SEND_SMS', ! in_array(env('APP_ENV', 'production'), ['local', 'testing'], true)),
        'sms_sender' => env('PUBLIC_AUTH_SMS_SENDER', 'MY-SIGNAL'),
        'sms_country_code' => env('PUBLIC_AUTH_SMS_COUNTRY_CODE', '225'),
    ],

    'public_reports' => [
        'request_timeout' => (int) env('PUBLIC_REPORT_REQUEST_TIMEOUT', 240),
        'video_conversion_timeout' => (int) env('PUBLIC_REPORT_VIDEO_CONVERSION_TIMEOUT', 120),
    ],

    'partner_auth' => [
        'otp_digits' => (int) env('PARTNER_AUTH_OTP_DIGITS', 4),
        'default_otp' => env('PARTNER_AUTH_DEFAULT_OTP', '2604'),
    ],

    'mtarget' => [
        'url' => env('MTARGET_URL', 'https://api-public-2.mtarget.fr/messages'),
        'username' => env('MTARGET_USERNAME', 'bwantech'),
        'password' => env('MTARGET_PASSWORD', 'x7jyKG0IJRNH'),
    ],

    'top_teaser' => [
        'email_url' => env('TOP_TEASER_EMAIL_URL', 'https://top-teaser.com/api/external/v1/emails/send'),
        'key' => env('TOP_TEASER_KEY'),
        'token' => env('TOP_TEASER_TOKEN'),
    ],

    'institution_activation' => [
        'base_url' => rtrim(env('INSTITUTION_ACTIVATION_BASE_URL', env('APP_URL', 'https://my-signal.pro')), '/'),
        'send_email' => (bool) env('INSTITUTION_ACTIVATION_SEND_EMAIL', false),
    ],

];
