<?php

return [
    'payment_ttl_minutes' => (int) env('GATEWAY_PAYMENT_TTL_MINUTES', 30),
    'webhook_max_attempts' => (int) env('GATEWAY_WEBHOOK_MAX_ATTEMPTS', 10),
    'blockchain_driver' => env('GATEWAY_BLOCKCHAIN_DRIVER', 'mock'),
    'trongrid' => [
        'base_url' => env('TRONGRID_BASE_URL', 'https://nile.trongrid.io'),
        'api_key' => env('TRONGRID_API_KEY'),
    ],
];
