<?php

namespace App\Http\Controllers;

use App\Services\Payments\BachsCheckoutService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BachsWebhookController extends Controller
{
    public function __invoke(Request $request, BachsCheckoutService $checkout): Response
    {
        try {
            $checkout->handleWebhook(
                $request->getContent(),
                $request->header('X-Bachs-Timestamp'),
                $request->header('X-Bachs-Signature'),
            );
        } catch (\Throwable) {
            return response('Webhook error', 400);
        }

        return response('OK', 200);
    }
}
