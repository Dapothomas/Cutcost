<?php

namespace App\Services\Payments;

use App\Models\Business;
use App\Services\StripeConnectService;

class ConnectGateway
{
    public function __construct(
        private StripeConnectService $stripe,
        private BachsConnectService $bachs,
    ) {}

    public static function shouldBypass(Business $business): bool
    {
        return self::usesBachs($business)
            ? BachsConnectService::shouldBypass()
            : StripeConnectService::shouldBypass();
    }

    public function canAcceptPayments(Business $business): bool
    {
        return self::usesBachs($business)
            ? $this->bachs->canAcceptPayments($business)
            : $this->stripe->canAcceptPayments($business);
    }

    public function hasLinkedAccount(Business $business): bool
    {
        return self::usesBachs($business)
            ? filled($business->bachs_account_id)
            : filled($business->stripe_account_id);
    }

    public function needsSync(Business $business): bool
    {
        if (self::usesBachs($business)) {
            return filled($business->bachs_account_id) && ! $business->bachs_charges_enabled;
        }

        return filled($business->stripe_account_id) && ! $business->stripe_charges_enabled;
    }

    public function createOnboardingUrl(Business $business): string
    {
        return self::usesBachs($business)
            ? $this->bachs->createOnboardingUrl($business)
            : $this->stripe->createOnboardingUrl($business);
    }

    public function syncAccount(Business $business): Business
    {
        return self::usesBachs($business)
            ? $this->bachs->syncAccount($business)
            : $this->stripe->syncAccount($business);
    }

    /**
     * @return array{label: string, tone: string, ready: bool}
     */
    public function statusFor(Business $business): array
    {
        return self::usesBachs($business)
            ? $this->bachs->statusFor($business)
            : $this->stripe->statusFor($business);
    }

    public function connectErrorMessage(\Throwable $e, Business $business): string
    {
        return self::usesBachs($business)
            ? $this->bachs->connectErrorMessage($e)
            : $this->stripe->connectErrorMessage($e);
    }

    /**
     * @return array{
     *     provider: string,
     *     provider_label: string,
     *     charges_enabled: bool,
     *     payouts_enabled: bool,
     *     account_id: ?string,
     *     onboarding_completed_at: ?string,
     *     platform_fee_percent: float
     * }
     */
    public function present(Business $business): array
    {
        $bachs = self::usesBachs($business);

        return [
            'provider' => $bachs ? PaymentProvider::BACHS : PaymentProvider::STRIPE,
            'provider_label' => PaymentProvider::labelForBusiness($business),
            'charges_enabled' => (bool) ($bachs ? $business->bachs_charges_enabled : $business->stripe_charges_enabled),
            'payouts_enabled' => (bool) ($bachs ? $business->bachs_payouts_enabled : $business->stripe_payouts_enabled),
            'account_id' => $bachs ? $business->bachs_account_id : $business->stripe_account_id,
            'onboarding_completed_at' => ($bachs ? $business->bachs_onboarding_completed_at : $business->stripe_onboarding_completed_at)?->toIso8601String(),
            'platform_fee_percent' => (float) ($bachs
                ? config('bachs.connect.platform_fee_percent', 0)
                : config('stripe.connect.platform_fee_percent', 0)),
        ];
    }

    private static function usesBachs(Business $business): bool
    {
        return PaymentProvider::forBusiness($business) === PaymentProvider::BACHS;
    }
}
