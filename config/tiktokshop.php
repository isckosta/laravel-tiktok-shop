<?php

return [
    'http' => [
        'base_uri' => env('TTSHOP_BASE_URI', 'https://open-api.tiktokglobalshop.com'),
        'timeout'  => (int) env('TTSHOP_HTTP_TIMEOUT', 30),
    ],

    'default_connection' => env('TTSHOP_DEFAULT_CONNECTION', 'default'),

    'webhooks' => [
        'route' => '/webhooks/tiktok-shop',
        'signature_header' => 'X-Tt-Signature',
        'secret' => env('TTSHOP_WEBHOOK_SECRET', ''),
    ],

    'auth' => [
        'base_uri'   => env('TTSHOP_AUTH_BASE_URI', 'https://auth.tiktokglobalshop.com'),
        'service_id' => env('TTSHOP_SERVICE_ID'),
        'app_key'    => env('TTSHOP_APP_KEY'),
        'app_secret' => env('TTSHOP_APP_SECRET'),
        'redirect' => env('TTSHOP_REDIRECT_URI', 'https://your-app.com/tiktok/callback'),
        'refresh_skew' => (int) env('TTSHOP_REFRESH_SKEW', 120),
    ],
];
