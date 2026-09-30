<?php

namespace App\Support;

use Illuminate\Http\Request;

final class VisitorMarket
{
    public const COOKIE = 'cutcost_country';

    public static function currency(?Request $request = null): string
    {
        return self::isNigeria($request) ? 'NGN' : 'GBP';
    }

    public static function isNigeria(?Request $request = null): bool
    {
        $request ??= request();

        $cookie = strtoupper((string) $request->cookie(self::COOKIE));
        if ($cookie === 'NG') {
            return true;
        }
        if ($cookie === 'GB') {
            return false;
        }

        foreach (['CF-IPCountry', 'CloudFront-Viewer-Country', 'X-Country-Code', 'X-Geo-Country'] as $header) {
            $value = strtoupper((string) $request->header($header));
            if ($value === 'NG') {
                return true;
            }
            if (preg_match('/^[A-Z]{2}$/', $value) === 1 && ! in_array($value, ['XX', 'T1'], true)) {
                return false;
            }
        }

        $accept = strtolower((string) $request->header('Accept-Language', ''));

        return str_contains($accept, '-ng');
    }
}
