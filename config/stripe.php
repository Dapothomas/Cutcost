<?php

return [

    'key' => env('STRIPE_KEY'),

    'secret' => env('STRIPE_SECRET'),

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

];
