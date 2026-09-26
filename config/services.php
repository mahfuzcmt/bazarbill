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
        'token' => env('POSTMARK_TOKEN'),
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

    /*
    | Platform SMS gateway (bulksmsbd.net). Markets without their own API key
    | send through this account and pay with prepaid SMS credits assigned by
    | the super admin.
    */
    'sms' => [
        'api_key' => env('SMS_API_KEY'),
        'sender_id' => env('SMS_SENDER_ID', '8809617642636'),
        'url' => env('SMS_API_URL', 'http://bulksmsbd.net/api/smsapi'),
        'balance_url' => env('SMS_BALANCE_URL', 'http://bulksmsbd.net/api/getBalanceApi'),
        // Market owners see a warning when their balance drops to this level.
        'low_credit_threshold' => (int) env('SMS_LOW_CREDIT_THRESHOLD', 20),
    ],

    // Shown to market owners on expiry / renewal screens.
    'support' => [
        'phone' => env('SUPPORT_PHONE'),
        'email' => env('SUPPORT_EMAIL'),
        // Number shown on the public landing page (call + WhatsApp)
        'sales_phone' => env('SALES_PHONE', '01805995662'),
        'facebook' => env('FACEBOOK_PAGE_URL', 'https://www.facebook.com/duetapapp'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];
