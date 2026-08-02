<?php

declare(strict_types=1);

return [
    'api_key' => env('BACHS_API_KEY'),
    'base_url' => env('BACHS_BASE_URL', 'https://api.bachs.io'),
    'timeout' => (int) env('BACHS_TIMEOUT', 30),

    'webhook' => [
        'secret' => env('BACHS_WEBHOOK_SECRET'),
        'tolerance' => (int) env('BACHS_WEBHOOK_TOLERANCE', 300),
        'path' => env('BACHS_WEBHOOK_PATH', 'bachs/webhook'),
        'route_enabled' => env('BACHS_WEBHOOK_ROUTE_ENABLED', true),
    ],
];
