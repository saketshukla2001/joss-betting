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

    'open_cricket' => [
        'key' => env('CRICKET_API_KEY'),
        'base_url' => env('CRICKET_API_BASE_URL', 'https://opencricketapi.com/v1/'),
        'timeout' => (int) env('CRICKET_API_TIMEOUT', 10),
    ],

    'cricbuzz' => [
        'host' => env('RAPIDAPI_HOST', 'cricbuzz-cricket.p.rapidapi.com'),
        'key' => env('RAPIDAPI_KEY'),
        'base_url' => env('CRICBUZZ_API_BASE_URL', 'https://cricbuzz-cricket.p.rapidapi.com'),
        'timeout' => (int) env('CRICBUZZ_API_TIMEOUT', 15),
        'throttle_ms' => (int) env('CRICBUZZ_THROTTLE_MS', 1600),
        'retry_backoff_ms' => (int) env('CRICBUZZ_RETRY_BACKOFF_MS', 1500),
        'board_ttl' => (int) env('CRICBUZZ_BOARD_TTL', 60),
        'match_ids' => [
            165061 => 'UP vs Jharkhand',
            165080 => 'Odisha vs Goa',
            177178 => 'Pakistan A vs India A',
            175840 => 'Ireland A vs Zimbabwe A',
            175240 => 'Abu Dhabi Knight Riders vs Dubai Capitals',
            161285 => 'New Zealand vs India',
        ],
    ],

    'sportmonks' => [
        'cricket_key' => env('SPORTMONKS_CRICKET_KEY', env('CRICKET_API_KEY')),
        'cricket_base_url' => env('SPORTMONKS_CRICKET_BASE_URL', 'https://cricket.sportmonks.com/api/v2.0'),
        'cricket_timeout' => (int) env('SPORTMONKS_CRICKET_TIMEOUT', 10),
    ],

];
