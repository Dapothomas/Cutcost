<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Services\BookingRevenueService;
use App\Services\StripeConnectService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class PaymentsController extends Controller
{
    public function index(Request $request, StripeConnectService $connect, BookingRevenueService $revenue): Response
    {
        $business = $request->user()->ownedBusiness()->firstOrFail();

        if (filled($business->stripe_account_id)) {
            $business = $connect->syncAccount($business);
        }

        $status = $connect->statusFor($business);
        $period = $revenue->normalizePeriod($request->string('period')->value() ?: 'month');

        return Inertia::render('Business/Payments/Index', [
            'payments' => [
                'ready' => $status['ready'],
                'label' => $status['label'],
                'tone' => $status['tone'],
                'charges_enabled' => $business->stripe_charges_enabled,
                'payouts_enabled' => $business->stripe_payouts_enabled,
                'account_id' => $business->stripe_account_id,
                'onboarding_completed_at' => $business->stripe_onboarding_completed_at?->toIso8601String(),
                'platform_fee_percent' => config('stripe.connect.platform_fee_percent', 0),
                'bypass_enabled' => StripeConnectService::shouldBypass(),
            ],
            'earnings' => $revenue->forPeriod($business, $period),
            'earningsPeriods' => $revenue->periodOptions(),
            'recentPaidBookings' => $revenue->recentPaidBookings($business, $period),
        ]);
    }

    public function connect(Request $request, StripeConnectService $connect): RedirectResponse|HttpResponse
    {
        $business = $request->user()->ownedBusiness()->firstOrFail();

        try {
            $url = $connect->createOnboardingUrl($business);
        } catch (\Throwable $e) {
            report($e);

            return redirect()
                ->route('business.payments.index')
                ->with('status', $connect->connectErrorMessage($e));
        }

        return $this->redirectToStripe($request, $url);
    }

    public function return(Request $request, StripeConnectService $connect): RedirectResponse
    {
        $business = $request->user()->ownedBusiness()->firstOrFail();

        $business = $connect->syncAccount($business);

        $message = $business->stripe_charges_enabled
            ? 'Stripe is connected — client payments will go to your account.'
            : 'Stripe setup is still incomplete. Finish the remaining steps to accept payments.';

        return redirect()
            ->route('business.payments.index')
            ->with('status', $message);
    }

    public function refresh(Request $request, StripeConnectService $connect): RedirectResponse|HttpResponse
    {
        $business = $request->user()->ownedBusiness()->firstOrFail();

        try {
            $url = $connect->createOnboardingUrl($business);
        } catch (\Throwable $e) {
            report($e);

            return redirect()
                ->route('business.payments.index')
                ->with('status', $connect->connectErrorMessage($e));
        }

        return $this->redirectToStripe($request, $url);
    }

    private function redirectToStripe(Request $request, string $url): RedirectResponse|HttpResponse
    {
        if ($request->header('X-Inertia')) {
            return Inertia::location($url);
        }

        return redirect()->away($url);
    }
}
