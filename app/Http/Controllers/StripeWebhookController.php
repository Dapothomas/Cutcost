<?php

namespace App\Http\Controllers;

use App\Services\StripeCheckoutService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StripeWebhookController extends Controller
{
    public function __invoke(Request $request, StripeCheckoutService $checkout): Response
    {
        try {
            $checkout->handleWebhook(
                $request->getContent(),
                $request->header('Stripe-Signature'),
            );
        } catch (\Throwable) {
            return response('Webhook error', 400);
        }

        return response('OK', 200);
    }
}
