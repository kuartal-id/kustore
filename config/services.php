<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
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

    'kuartal_id' => [
        'issuer' => rtrim((string) env('KUARTAL_ID_ISSUER', 'https://id.kuartal.id'), '/'),
        'client_id' => env('KUARTAL_ID_CLIENT_ID'),
        'client_secret' => env('KUARTAL_ID_CLIENT_SECRET'),
        'redirect' => env('KUARTAL_ID_REDIRECT_URI', 'https://kustore.id/auth/kuartal/callback'),
        // Only what Kustore needs. "entitlements" is intentionally NOT requested.
        'scopes' => ['openid', 'profile', 'email'],
        'cache_ttl' => 3600,
        'leeway' => 60,
    ],

];
