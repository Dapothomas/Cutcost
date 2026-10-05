<?php

return [

    /*
    | Fallback only. Live checkout follows the shop: Nigeria uses Bachs,
    | the UK and every other market use Stripe. Stored on the business
    | at signup so later booking payments stay on the same processor.
    */
    'provider' => env('PAYMENT_PROVIDER', 'stripe'),

];
