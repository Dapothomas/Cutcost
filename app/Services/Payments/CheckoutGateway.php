<?php

namespace App\Services\Payments;

use App\Enums\SubscriptionPlan;
use App\Models\Booking;
use App\Models\Business;
use App\Models\Service;
use App\Models\User;
use App\Services\StripeCheckoutService;

class CheckoutGateway
{
    public function __construct(
        private StripeCheckoutService $stripe,
        private BachsCheckoutService $bachs,
    ) {}

    public static function shouldBypass(?Business $business = null): bool
    {
        $provider = $business
            ? PaymentProvider::forBusiness($business)
            : PaymentProvider::forRequest();

        return $provider === PaymentProvider::BACHS
            ? BachsCheckoutService::shouldBypass()
            : StripeCheckoutService::shouldBypass();
    }

    public function activateWithoutCheckout(User $user, SubscriptionPlan $plan): void
    {
        $this->driver($user)->activateWithoutCheckout($user, $plan);
    }

    public function createCheckoutSession(User $user, SubscriptionPlan $plan): CheckoutSession
    {
        if ($this->usesBachs($user)) {
            return $this->bachs->createCheckoutSession($user, $plan);
        }

        $session = $this->stripe->createCheckoutSession($user, $plan);

        return new CheckoutSession($session->id, $session->url);
    }

    public function completeCheckout(string $sessionId, string $provider): User
    {
        return $provider === PaymentProvider::BACHS
            ? $this->bachs->completeCheckout($sessionId)
            : $this->stripe->completeCheckout($sessionId);
    }

    public function createBookingCheckoutSession(Booking $booking, Business $business, Service $service): CheckoutSession
    {
        if (PaymentProvider::forBusiness($business) === PaymentProvider::BACHS) {
            return $this->bachs->createBookingCheckoutSession($booking, $business, $service);
        }

        $session = $this->stripe->createBookingCheckoutSession($booking, $business, $service);

        return new CheckoutSession($session->id, $session->url);
    }

    public function completeBookingCheckout(string $sessionId, Business $business): Booking
    {
        return PaymentProvider::forBusiness($business) === PaymentProvider::BACHS
            ? $this->bachs->completeBookingCheckout($sessionId)
            : $this->stripe->completeBookingCheckout($sessionId);
    }

    public function cancelPendingBooking(Booking $booking): void
    {
        $booking->loadMissing('business');

        $this->driverFor($booking->business)->cancelPendingBooking($booking);
    }

    public function cancelSubscription(User $user): User
    {
        return $this->usesBachs($user)
            ? $this->bachs->cancelSubscription($user)
            : $this->stripe->cancelSubscription($user);
    }

    private function usesBachs(User $user): bool
    {
        $user->loadMissing('business');

        return $user->business
            && PaymentProvider::forBusiness($user->business) === PaymentProvider::BACHS;
    }

    private function driver(User $user): StripeCheckoutService|BachsCheckoutService
    {
        return $this->usesBachs($user) ? $this->bachs : $this->stripe;
    }

    private function driverFor(?Business $business): StripeCheckoutService|BachsCheckoutService
    {
        return $business && PaymentProvider::forBusiness($business) === PaymentProvider::BACHS
            ? $this->bachs
            : $this->stripe;
    }
}
