<?php

return [

    'secret' => env('BACHS_SECRET'),

    'webhook_secret' => env('BACHS_WEBHOOK_SECRET'),

    // Second endpoint: connected-account events use their own signing secret.
    'connect_webhook_secret' => env('BACHS_CONNECT_WEBHOOK_SECRET'),

    /*
    | Sandbox until live keys are ready. Production is https://api.bachs.io
    | with an sk_live_ key — a URL and key swap, not a code change.
    */
    'base_url' => env('BACHS_BASE_URL', 'https://sandbox-api.bachs.io'),

    'products' => [
        'starter' => env('BACHS_PRODUCT_STARTER'),
        'shop' => env('BACHS_PRODUCT_SHOP'),
        'studio' => env('BACHS_PRODUCT_STUDIO'),
    ],

    'connect' => [
        'country' => env('BACHS_CONNECT_COUNTRY', env('STRIPE_CONNECT_COUNTRY', 'GB')),
        'platform_fee_percent' => (float) env('BACHS_PLATFORM_FEE_PERCENT', env('STRIPE_PLATFORM_FEE_PERCENT', 0)),
    ],

];
