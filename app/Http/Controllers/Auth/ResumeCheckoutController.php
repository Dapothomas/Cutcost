<?php

namespace App\Http\Controllers\Auth;

use App\Enums\SubscriptionPlan;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Payments\CheckoutGateway;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ResumeCheckoutController extends Controller
{
    public function __invoke(Request $request, CheckoutGateway $checkout): RedirectResponse|View
    {
        $user = $request->user();

        if (! $user?->isOwner() || $user->hasActiveSubscription()) {
            return redirect()->route('business.dashboard');
        }

        $plan = $user->subscription_plan ?? SubscriptionPlan::Starter;
        $user->loadMissing('business');

        if (CheckoutGateway::shouldBypass($user->business)) {
            $checkout->activateWithoutCheckout($user, $plan);

            return redirect()->route('business.dashboard')
                ->with('status', 'Your subscription is active.');
        }

        try {
            $session = $checkout->createCheckoutSession($user, $plan);
        } catch (\Throwable) {
            return redirect()->route(config('app.waitlist_only') ? 'waitlist' : 'register')
                ->with('status', 'Unable to start checkout. Please contact support.');
        }

        return redirect()->away($session->url);
    }
}
