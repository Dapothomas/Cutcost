<?php

namespace App\Services\Payments;

use App\Models\Business;
use App\Support\VisitorMarket;
use Illuminate\Http\Request;

class PaymentProvider
{
    public const BACHS = 'bachs';

    public const STRIPE = 'stripe';

    /**
     * Nigeria uses Bachs. The UK and every other market use Stripe.
     */
    public static function forRequest(?Request $request = null): string
    {
        return VisitorMarket::isNigeria($request) ? self::BACHS : self::STRIPE;
    }

    public static function forBusiness(Business $business): string
    {
        return $business->payment_provider === self::BACHS ? self::BACHS : self::STRIPE;
    }

    public static function labelFor(string $provider): string
    {
        return $provider === self::BACHS ? 'Bachs' : 'Stripe';
    }

    public static function labelForRequest(?Request $request = null): string
    {
        return self::labelFor(self::forRequest($request));
    }

    public static function labelForBusiness(Business $business): string
    {
        return self::labelFor(self::forBusiness($business));
    }
}
