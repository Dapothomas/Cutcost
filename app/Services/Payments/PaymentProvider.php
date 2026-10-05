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
     * A shop in Nigeria uses Bachs. A shop in the UK uses Stripe.
     * The city on the shop decides when it is known. Otherwise the visitor's country is used.
     */
    public static function forPlace(?string $city, ?Request $request = null): string
    {
        $normalized = strtolower(trim((string) $city));

        if (self::cityIn($normalized, self::NIGERIA_CITIES)) {
            return self::BACHS;
        }

        if (self::cityIn($normalized, self::UK_CITIES)) {
            return self::STRIPE;
        }

        return self::forRequest($request);
    }

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

    /** @var list<string> */
    private const NIGERIA_CITIES = [
        'lagos', 'ikeja', 'ota', 'abuja', 'port harcourt', 'ibadan', 'kano', 'enugu',
        'benin', 'benin city', 'kaduna', 'warri', 'abeokuta', 'onitsha', 'uyo',
        'calabar', 'jos', 'ilorin', 'aba', 'owerri', 'akure', 'lekki', 'ajah',
        'surulere', 'yaba', 'ikoyi', 'victoria island',
    ];

    /** @var list<string> */
    private const UK_CITIES = [
        'milton keynes', 'london', 'manchester', 'birmingham', 'leeds', 'liverpool',
        'bristol', 'sheffield', 'nottingham', 'leicester', 'coventry', 'cardiff',
        'edinburgh', 'glasgow', 'belfast', 'newcastle', 'southampton', 'brighton',
        'oxford', 'cambridge', 'reading', 'luton', 'northampton',
    ];

    /**
     * @param  list<string>  $cities
     */
    private static function cityIn(string $city, array $cities): bool
    {
        return $city !== '' && in_array($city, $cities, true);
    }
}
