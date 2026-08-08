<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Enums\SubscriptionPlan;
use App\Enums\SubscriptionStatus;
use App\Models\Booking;
use App\Models\Business;
use App\Models\Service;
use App\Models\User;
use App\Support\ShopNotifier;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Stripe\Subscription;
use Stripe\Webhook;

class StripeCheckoutService
{
    public static function shouldBypass(): bool
    {
        if (config('stripe.bypass_checkout')) {
            return true;
        }

        if (blank(config('stripe.secret'))) {
            return App::environment(['local', 'testing']);
        }

        return false;
    }

    public function activateWithoutCheckout(User $user, SubscriptionPlan $plan): void
    {
        $user->update([
            'subscription_plan' => $plan->value,
            'subscription_status' => SubscriptionStatus::Active->value,
        ]);
    }

    public function createCheckoutSession(User $user, SubscriptionPlan $plan): Session
    {
        $priceId = $plan->stripePriceId();

        if (blank($priceId)) {
            throw new \RuntimeException("Stripe price ID is not configured for the {$plan->value} plan.");
        }

        Stripe::setApiKey(config('stripe.secret'));

        return Session::create([
            'mode' => 'subscription',
            'customer_email' => $user->email,
            'client_reference_id' => (string) $user->id,
            'line_items' => [[
                'price' => $priceId,
                'quantity' => 1,
            ]],
            'success_url' => route('register.checkout.success').'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('register.checkout.cancel'),
            'managed_payments' => [
                'enabled' => false,
            ],
            'metadata' => [
                'user_id' => (string) $user->id,
                'plan' => $plan->value,
            ],
            'subscription_data' => [
                'metadata' => [
                    'user_id' => (string) $user->id,
                    'plan' => $plan->value,
                ],
            ],
        ]);
    }

    public function completeCheckout(string $sessionId): User
    {
        Stripe::setApiKey(config('stripe.secret'));

        $session = Session::retrieve($sessionId, [
            'expand' => ['subscription'],
        ]);

        if ($session->payment_status !== 'paid' && $session->status !== 'complete') {
            throw new \RuntimeException('Checkout session is not complete.');
        }

        $user = User::query()->findOrFail($session->client_reference_id ?? $session->metadata['user_id'] ?? null);

        $plan = SubscriptionPlan::from($session->metadata['plan'] ?? $user->subscription_plan);

        $user->update([
            'subscription_plan' => $plan->value,
            'subscription_status' => SubscriptionStatus::Active->value,
            'stripe_customer_id' => $session->customer,
            'stripe_subscription_id' => is_string($session->subscription)
                ? $session->subscription
                : $session->subscription?->id,
        ]);

        return $user->fresh();
    }

    public function createBookingCheckoutSession(Booking $booking, Business $business, Service $service): Session
    {
        if (! $business->canAcceptPayments()) {
            throw new \RuntimeException('This shop is not ready to accept payments yet.');
        }

        if (blank($business->stripe_account_id)) {
            throw new \RuntimeException('This shop has not connected Stripe yet.');
        }

        Stripe::setApiKey(config('stripe.secret'));

        $booking->loadMissing(['client', 'barber']);

        $sessionPayload = [
            'mode' => 'payment',
            'customer_email' => $booking->client->email ?: null,
            'client_reference_id' => (string) $booking->id,
            'line_items' => [[
                'price_data' => [
                    'currency' => 'gbp',
                    'unit_amount' => $booking->amount_cents,
                    'product_data' => [
                        'name' => $service->name,
                        'description' => sprintf(
                            '%s · %s with %s',
                            $business->name,
                            $booking->starts_at->format('D j M · H:i'),
                            $booking->barber->name,
                        ),
                    ],
                ],
                'quantity' => 1,
            ]],
            'success_url' => route('public.booking.checkout.success', $business).'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('public.booking.checkout.cancel', [$business, $booking]),
            'managed_payments' => [
                'enabled' => false,
            ],
            'metadata' => [
                'type' => 'booking',
                'booking_id' => (string) $booking->id,
                'business_id' => (string) $business->id,
            ],
        ];

        $applicationFee = $this->bookingApplicationFeeCents($booking->amount_cents);

        if ($applicationFee > 0) {
            $sessionPayload['payment_intent_data'] = [
                'application_fee_amount' => $applicationFee,
                'transfer_data' => [
                    'destination' => $business->stripe_account_id,
                ],
            ];
        } else {
            $sessionPayload['payment_intent_data'] = [
                'transfer_data' => [
                    'destination' => $business->stripe_account_id,
                ],
            ];
        }

        return Session::create($sessionPayload);
    }

    private function bookingApplicationFeeCents(int $amountCents): int
    {
        $percent = config('stripe.connect.platform_fee_percent', 0);

        if ($percent <= 0) {
            return 0;
        }

        return (int) round($amountCents * ($percent / 100));
    }

    public function confirmBookingWithoutCheckout(Booking $booking): Booking
    {
        $booking->update([
            'status' => BookingStatus::Scheduled,
            'payment_status' => PaymentStatus::Waived,
        ]);

        return $booking->fresh();
    }

    public function completeBookingCheckout(string $sessionId): Booking
    {
        Stripe::setApiKey(config('stripe.secret'));

        $session = Session::retrieve($sessionId);

        if ($session->payment_status !== 'paid' && $session->status !== 'complete') {
            throw new \RuntimeException('Checkout session is not complete.');
        }

        $booking = Booking::query()->findOrFail(
            $session->client_reference_id ?? $session->metadata['booking_id'] ?? null
        );

        $alreadyPaid = $booking->payment_status === PaymentStatus::Paid;

        $booking->update([
            'status' => BookingStatus::Scheduled,
            'payment_status' => PaymentStatus::Paid,
            'stripe_checkout_session_id' => $session->id,
        ]);

        $booking = $booking->fresh();

        if (! $alreadyPaid) {
            ShopNotifier::bookingPaid($booking);
        }

        return $booking;
    }

    public function cancelPendingBooking(Booking $booking): void
    {
        if ($booking->status !== BookingStatus::PendingPayment) {
            return;
        }

        $booking->update([
            'status' => BookingStatus::Cancelled,
            'payment_status' => PaymentStatus::Failed,
        ]);

        ShopNotifier::bookingCancelled($booking->fresh(), 'cancelled');
    }

    public function handleWebhook(string $payload, ?string $signature): void
    {
        $secret = config('stripe.webhook_secret');

        if (blank($secret)) {
            return;
        }

        $event = Webhook::constructEvent(
            $payload,
            $signature ?? '',
            $secret,
        );

        match ($event->type) {
            'checkout.session.completed' => $this->handleCheckoutCompleted($event->data->object),
            'checkout.session.expired' => $this->handleCheckoutExpired($event->data->object),
            'account.updated' => app(StripeConnectService::class)->handleAccountUpdated($event->data->object),
            'customer.subscription.updated',
            'customer.subscription.deleted' => $this->handleSubscriptionChange($event->data->object),
            default => null,
        };
    }

    private function handleCheckoutCompleted(object $session): void
    {
        if (($session->metadata['type'] ?? null) === 'booking') {
            $this->confirmBookingFromSession($session);

            return;
        }

        if ($session->mode !== 'subscription') {
            return;
        }

        $userId = $session->client_reference_id ?? $session->metadata['user_id'] ?? null;

        if (! $userId) {
            return;
        }

        User::query()->whereKey($userId)->update([
            'subscription_plan' => $session->metadata['plan'] ?? null,
            'subscription_status' => SubscriptionStatus::Active->value,
            'stripe_customer_id' => $session->customer,
            'stripe_subscription_id' => $session->subscription,
        ]);
    }

    private function handleCheckoutExpired(object $session): void
    {
        if (($session->metadata['type'] ?? null) !== 'booking') {
            return;
        }

        $booking = Booking::query()->find($session->metadata['booking_id'] ?? $session->client_reference_id);

        if ($booking) {
            $this->cancelPendingBooking($booking);
        }
    }

    private function confirmBookingFromSession(object $session): void
    {
        $booking = Booking::query()->find($session->metadata['booking_id'] ?? $session->client_reference_id);

        if (! $booking) {
            return;
        }

        $alreadyPaid = $booking->payment_status === PaymentStatus::Paid;

        $booking->update([
            'status' => BookingStatus::Scheduled,
            'payment_status' => PaymentStatus::Paid,
            'stripe_checkout_session_id' => $session->id,
        ]);

        if (! $alreadyPaid) {
            ShopNotifier::bookingPaid($booking->fresh());
        }
    }

    public function cancelSubscription(User $user): User
    {
        if (! $user->isOwner() || $user->subscription_status !== SubscriptionStatus::Active) {
            throw new \RuntimeException('No active subscription to cancel.');
        }

        if (self::shouldBypass() || blank($user->stripe_subscription_id)) {
            $user->update([
                'subscription_status' => SubscriptionStatus::Canceled->value,
                'subscription_cancel_at' => now(),
            ]);

            return $user->fresh();
        }

        Stripe::setApiKey(config('stripe.secret'));

        $subscription = Subscription::update($user->stripe_subscription_id, [
            'cancel_at_period_end' => true,
        ]);

        $endsAt = isset($subscription->current_period_end)
            ? Carbon::createFromTimestamp($subscription->current_period_end)
            : now()->addMonth();

        $user->update([
            'subscription_cancel_at' => $endsAt,
        ]);

        return $user->fresh();
    }

    private function handleSubscriptionChange(object $subscription): void
    {
        $user = User::query()
            ->where('stripe_subscription_id', $subscription->id)
            ->first();

        if (! $user) {
            return;
        }

        $status = match ($subscription->status) {
            'active', 'trialing' => SubscriptionStatus::Active,
            'past_due', 'unpaid' => SubscriptionStatus::PastDue,
            'canceled', 'incomplete_expired' => SubscriptionStatus::Canceled,
            default => SubscriptionStatus::Pending,
        };

        $cancelAt = null;
        if (! empty($subscription->cancel_at_period_end) && ! empty($subscription->current_period_end)) {
            $cancelAt = Carbon::createFromTimestamp($subscription->current_period_end);
        } elseif ($status === SubscriptionStatus::Canceled) {
            $cancelAt = now();
        }

        $user->update([
            'subscription_status' => $status->value,
            'subscription_plan' => $subscription->metadata['plan'] ?? $user->subscription_plan,
            'subscription_cancel_at' => $cancelAt,
        ]);
    }
}
