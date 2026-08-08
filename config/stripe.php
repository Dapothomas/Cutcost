<?php

return [

    'key' => env('STRIPE_KEY'),

    'secret' => env('STRIPE_SECRET'),

    'api_version' => env('STRIPE_API_VERSION', '2026-01-28.clover'),

    'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),

    /*
    | When true (or when STRIPE_SECRET is empty in local/testing), registration
    | skips Stripe Checkout and activates the subscription immediately.
    */
    'bypass_checkout' => env('STRIPE_BYPASS_CHECKOUT', false),

    'prices' => [
        'starter' => env('STRIPE_PRICE_STARTER'),
        'shop' => env('STRIPE_PRICE_SHOP'),
        'studio' => env('STRIPE_PRICE_STUDIO'),
    ],

    'connect' => [
        'country' => env('STRIPE_CONNECT_COUNTRY', 'GB'),
        'platform_fee_percent' => (float) env('STRIPE_PLATFORM_FEE_PERCENT', 0),
    ],

];
