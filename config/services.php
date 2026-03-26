<?php
return [
    'mailgun'   => ['domain' => env('MAILGUN_DOMAIN'), 'secret' => env('MAILGUN_SECRET'), 'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'), 'scheme' => 'https'],
    'postmark'  => ['token' => env('POSTMARK_TOKEN')],
    'ses'       => ['key' => env('AWS_ACCESS_KEY_ID'), 'secret' => env('AWS_SECRET_ACCESS_KEY'), 'region' => env('AWS_DEFAULT_REGION', 'ap-south-1')],

    'anthropic' => [
        'key'   => env('ANTHROPIC_API_KEY'),
        'model' => env('ANTHROPIC_MODEL', 'claude-sonnet-4-20250514'),
    ],

    'razorpay' => [
        'key_id'         => env('RAZORPAY_KEY_ID'),
        'key_secret'     => env('RAZORPAY_KEY_SECRET'),
        'webhook_secret' => env('RAZORPAY_WEBHOOK_SECRET'),
    ],

    'tao' => [
        'url'      => env('TAO_URL'),
        'username' => env('TAO_USERNAME', 'admin'),
        'password' => env('TAO_PASSWORD'),
    ],

    'youtube' => [
        'api_key' => env('YOUTUBE_API_KEY'),
    ],

    'telegram' => [
        'bot_token'  => env('TELEGRAM_BOT_TOKEN'),
        'channel_id' => env('TELEGRAM_CHANNEL_ID'),
    ],
];
