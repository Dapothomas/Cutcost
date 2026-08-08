<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\StripeCheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CheckoutSuccessController extends Controller
{
    public function __invoke(Request $request, StripeCheckoutService $checkout): RedirectResponse
    {
        $sessionId = $request->string('session_id');

        if ($sessionId->isEmpty()) {
            return redirect()->route('register')
                ->with('status', 'Missing checkout session. Please try again.');
        }

        try {
            $user = $checkout->completeCheckout($sessionId->value());
        } catch (\Throwable) {
            return redirect()->route('register')
                ->with('status', 'We could not confirm your payment. Please contact support.');
        }

        event(new \Illuminate\Auth\Events\Registered($user));

        Auth::login($user);

        return redirect()->route('business.dashboard')
            ->with('status', 'Welcome to Cutcost — your subscription is active.');
    }

    public function cancel(): RedirectResponse
    {
        return redirect()->route('register')
            ->with('status', 'Checkout was cancelled. You can sign up again when ready.');
    }
}
