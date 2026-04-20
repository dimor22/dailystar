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

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'marketing' => [
        'contact_email' => env('CONTACT_EMAIL', env('MAIL_FROM_ADDRESS', 'hello@dailystars.org')),
        'donation_url' => env('DONATION_URL', 'https://buymeacoffee.com/dailystarsapp'),
        'tiktok_url' => env('TIKTOK_URL', 'https://www.tiktok.com/@daily_stars_12'),
        'youtube_url' => env('YOUTUBE_URL', 'https://www.youtube.com/@DailyStars-12'),
    ],

];
