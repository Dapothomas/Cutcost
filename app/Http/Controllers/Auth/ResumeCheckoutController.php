<?php

namespace App\Http\Controllers\Auth;

use App\Enums\SubscriptionPlan;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\StripeCheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ResumeCheckoutController extends Controller
{
    public function __invoke(Request $request, StripeCheckoutService $checkout): RedirectResponse|View
    {
        $user = $request->user();

        if (! $user?->isOwner() || $user->hasActiveSubscription()) {
            return redirect()->route('business.dashboard');
        }

        $plan = $user->subscription_plan ?? SubscriptionPlan::Starter;

        if (StripeCheckoutService::shouldBypass()) {
            $checkout->activateWithoutCheckout($user, $plan);

            return redirect()->route('business.dashboard')
                ->with('status', 'Your subscription is active.');
        }

        try {
            $session = $checkout->createCheckoutSession($user, $plan);
        } catch (\Throwable) {
            return redirect()->route('register')
                ->with('status', 'Unable to start checkout. Please contact support.');
        }

        return redirect()->away($session->url);
    }
}
