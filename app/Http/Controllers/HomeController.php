<?php

namespace App\Http\Controllers;

use App\Enums\SubscriptionPlan;
use App\Support\VisitorMarket;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(Request $request): View
    {
        $currency = VisitorMarket::currency($request);

        return view('home', [
            'currency' => $currency,
            'fromPrice' => SubscriptionPlan::Starter->priceLabel($currency),
            'zeroPrice' => $currency === 'NGN' ? '₦0' : '£0',
        ]);
    }
}
