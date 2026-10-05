<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Services\BookingRevenueService;
use App\Services\Payments\ConnectGateway;
use App\Services\Payments\PaymentProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class PaymentsController extends Controller
{
    public function index(Request $request, ConnectGateway $connect, BookingRevenueService $revenue): Response
    {
        $business = $request->user()->ownedBusiness()->firstOrFail();

        if ($connect->hasLinkedAccount($business)) {
            $business = $connect->syncAccount($business);
        }

        $status = $connect->statusFor($business);
        $period = $revenue->normalizePeriod($request->string('period')->value() ?: 'month');
        $account = $connect->present($business);

        return Inertia::render('Business/Payments/Index', [
            'payments' => [
                'ready' => $status['ready'],
                'label' => $status['label'],
                'tone' => $status['tone'],
                'charges_enabled' => $account['charges_enabled'],
                'payouts_enabled' => $account['payouts_enabled'],
                'account_id' => $account['account_id'],
                'onboarding_completed_at' => $account['onboarding_completed_at'],
                'platform_fee_percent' => $account['platform_fee_percent'],
                'bypass_enabled' => ConnectGateway::shouldBypass($business),
                'provider' => $account['provider'],
                'provider_label' => $account['provider_label'],
            ],
            'earnings' => $revenue->forPeriod($business, $period),
            'earningsPeriods' => $revenue->periodOptions(),
            'recentPaidBookings' => $revenue->recentPaidBookings($business, $period),
        ]);
    }

    public function connect(Request $request, ConnectGateway $connect): RedirectResponse|HttpResponse
    {
        $business = $request->user()->ownedBusiness()->firstOrFail();

        try {
            $url = $connect->createOnboardingUrl($business);
        } catch (\Throwable $e) {
            report($e);

            return redirect()
                ->route('business.payments.index')
                ->with('status', $connect->connectErrorMessage($e, $business));
        }

        return $this->redirectToProvider($request, $url);
    }

    public function return(Request $request, ConnectGateway $connect): RedirectResponse
    {
        $business = $request->user()->ownedBusiness()->firstOrFail();

        $business = $connect->syncAccount($business);
        $ready = PaymentProvider::forBusiness($business) === PaymentProvider::BACHS
            ? $business->bachs_charges_enabled
            : $business->stripe_charges_enabled;
        $label = PaymentProvider::labelForBusiness($business);

        $message = $ready
            ? $label.' is connected — client payments will go to your account.'
            : $label.' setup is still incomplete. Finish the remaining steps to accept payments.';

        return redirect()
            ->route('business.payments.index')
            ->with('status', $message);
    }

    public function refresh(Request $request, ConnectGateway $connect): RedirectResponse|HttpResponse
    {
        $business = $request->user()->ownedBusiness()->firstOrFail();

        try {
            $url = $connect->createOnboardingUrl($business);
        } catch (\Throwable $e) {
            report($e);

            return redirect()
                ->route('business.payments.index')
                ->with('status', $connect->connectErrorMessage($e, $business));
        }

        return $this->redirectToProvider($request, $url);
    }

    private function redirectToProvider(Request $request, string $url): RedirectResponse|HttpResponse
    {
        if ($request->header('X-Inertia')) {
            return Inertia::location($url);
        }

        return redirect()->away($url);
    }
}
