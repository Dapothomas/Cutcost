<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Payments\CheckoutGateway;
use App\Services\Payments\PaymentProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CheckoutSuccessController extends Controller
{
    public function __invoke(Request $request, CheckoutGateway $checkout): RedirectResponse
    {
        if ($request->filled('checkout_id')) {
            $provider = PaymentProvider::BACHS;
            $sessionId = $request->string('checkout_id');
        } elseif ($request->filled('session_id')) {
            $provider = PaymentProvider::STRIPE;
            $sessionId = $request->string('session_id');
        } else {
            return redirect()->route($this->signupRoute())
                ->with('status', 'Missing checkout session. Please try again.');
        }

        try {
            $user = $checkout->completeCheckout($sessionId->value(), $provider);
        } catch (\Throwable) {
            return redirect()->route($this->signupRoute())
                ->with('status', 'We could not confirm your payment. Please contact support.');
        }

        event(new \Illuminate\Auth\Events\Registered($user));

        Auth::login($user);

        return redirect()->route('business.dashboard')
            ->with('status', 'Welcome to Cutcost — your subscription is active.');
    }

    public function cancel(): RedirectResponse
    {
        return redirect()->route($this->signupRoute())
            ->with('status', 'Checkout was cancelled. You can sign up again when ready.');
    }

    private function signupRoute(): string
    {
        return config('app.waitlist_only') ? 'waitlist' : 'register';
    }
}
