<?php

namespace App\Services\Payments;

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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use RuntimeException;

class BachsCheckoutService
{
    public function __construct(private BachsClient $bachs) {}

    public static function shouldBypass(): bool
    {
        if (blank(config('bachs.secret'))) {
            return App::environment(['local', 'testing']);
        }

        return false;
    }

    public static function shouldBypassSubscription(): bool
    {
        return (bool) config('bachs.bypass_subscription') || self::shouldBypass();
    }

    public function activateWithoutCheckout(User $user, SubscriptionPlan $plan): void
    {
        $user->update([
            'subscription_plan' => $plan->value,
            'subscription_status' => SubscriptionStatus::Active->value,
        ]);
    }

    public function createCheckoutSession(User $user, SubscriptionPlan $plan): CheckoutSession
    {
        $productId = config("bachs.products.{$plan->value}");

        if (blank($productId)) {
            throw new RuntimeException("Bachs product ID is not configured for the {$plan->value} plan.");
        }

        $session = $this->bachs->post('/v1/checkout-sessions', [
            'customer' => [
                'email' => $user->email,
                'name' => $user->name,
            ],
            'product_cart' => [[
                'product_id' => $productId,
                'quantity' => 1,
            ]],
            // Bachs will not start a subscription in naira. Plans bill in dollars.
            'billing_currency' => 'USD',
            'metadata' => [
                'type' => 'subscription',
                'user_id' => (string) $user->id,
                'plan' => $plan->value,
            ],
            'reference' => 'user_'.$user->id.'_'.$plan->value.'_'.Str::lower(Str::random(8)),
            'success_url' => route('register.checkout.success'),
            'cancel_url' => route('register.checkout.cancel'),
        ], 'checkout-user-'.$user->id.'-'.Str::lower(Str::random(8)));

        return new CheckoutSession(
            (string) ($session['checkout_id'] ?? ''),
            (string) ($session['checkout_url'] ?? ''),
        );
    }

    public function completeCheckout(string $checkoutId): User
    {
        $session = $this->bachs->get('/v1/checkout-sessions/'.$checkoutId);

        if (($session['status'] ?? null) !== 'completed') {
            throw new RuntimeException('Checkout session is not complete.');
        }

        return $this->activateSubscriptionFromCheckout($session);
    }

    public function createBookingCheckoutSession(Booking $booking, Business $business, Service $service): CheckoutSession
    {
        if (! $business->canAcceptPayments()) {
            throw new RuntimeException('This shop is not ready to accept payments yet.');
        }

        if (blank($business->bachs_account_id)) {
            throw new RuntimeException('This shop has not connected Bachs yet.');
        }

        $booking->loadMissing(['client', 'barber']);

        $email = $booking->client->email;

        if (blank($email)) {
            throw new RuntimeException('A client email is required before Bachs can take payment.');
        }

        $amount = $this->money((int) $booking->amount_cents);
        $feePercent = (float) config('bachs.connect.platform_fee_percent', 0);
        $fee = $feePercent > 0
            ? $this->money((int) round($booking->amount_cents * ($feePercent / 100)))
            : null;

        $payload = [
            'customer' => [
                'email' => $email,
                'name' => $booking->client->name,
            ],
            'pricing' => [
                'currency' => 'GBP',
                'amount' => $amount,
            ],
            'metadata' => [
                'type' => 'booking',
                'booking_id' => (string) $booking->id,
                'business_id' => (string) $business->id,
            ],
            'reference' => 'booking_'.$booking->id.'_'.Str::lower(Str::random(6)),
            'success_url' => route('public.booking.checkout.success', $business),
            'cancel_url' => route('public.booking.checkout.cancel', [$business, $booking]),
            'transfer_data' => [
                'destination' => $business->bachs_account_id,
            ],
        ];

        if ($fee !== null && $fee !== '0.00') {
            $payload['platform_fee'] = $fee;
        } else {
            $payload['transfer_data']['amount'] = $amount;
        }

        $session = $this->bachs->post('/v1/checkout-sessions', $payload, 'checkout-booking-'.$booking->id.'-'.Str::lower(Str::random(6)));

        return new CheckoutSession(
            (string) ($session['checkout_id'] ?? ''),
            (string) ($session['checkout_url'] ?? ''),
        );
    }

    public function confirmBookingWithoutCheckout(Booking $booking): Booking
    {
        $booking->update([
            'status' => BookingStatus::Scheduled,
            'payment_status' => PaymentStatus::Waived,
        ]);

        return $booking->fresh();
    }

    public function completeBookingCheckout(string $checkoutId): Booking
    {
        $session = $this->bachs->get('/v1/checkout-sessions/'.$checkoutId);

        if (($session['status'] ?? null) !== 'completed') {
            throw new RuntimeException('Checkout session is not complete.');
        }

        return $this->confirmBookingFromCheckout($session, $checkoutId);
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

    public function cancelSubscription(User $user): User
    {
        if (! $user->isOwner() || $user->subscription_status !== SubscriptionStatus::Active) {
            throw new RuntimeException('No active subscription to cancel.');
        }

        if (self::shouldBypass() || blank($user->bachs_subscription_id)) {
            $user->update([
                'subscription_status' => SubscriptionStatus::Canceled->value,
                'subscription_cancel_at' => now(),
            ]);

            return $user->fresh();
        }

        $subscription = $this->bachs->delete('/v1/subscriptions/'.$user->bachs_subscription_id, [
            'cancel_at_period_end' => true,
            'reason' => 'Customer requested',
        ]);

        $user->update([
            'subscription_cancel_at' => $this->periodEnd($subscription['current_period_end'] ?? null) ?? now()->addMonth(),
        ]);

        return $user->fresh();
    }

    public function handleWebhook(string $payload, ?string $timestamp, ?string $signature): void
    {
        $this->assertSignature($payload, $timestamp, $signature);

        $event = json_decode($payload, true);

        if (! is_array($event)) {
            throw new RuntimeException('Bachs webhook payload is not JSON.');
        }

        $eventId = $event['id'] ?? null;

        if (is_string($eventId) && $eventId !== '' && ! Cache::add('bachs-event:'.$eventId, true, now()->addDay())) {
            return;
        }

        $type = (string) ($event['type'] ?? '');
        $data = is_array($event['data'] ?? null) ? $event['data'] : [];

        match ($type) {
            'collection.succeeded', 'checkout.completed' => $this->handleCheckoutPaid($data),
            'checkout.expired', 'collection.abandoned' => $this->handleCheckoutAbandoned($data),
            'customer.subscription.created',
            'customer.subscription.updated',
            'customer.subscription.deleted' => $this->handleSubscriptionChange($data, $type),
            'account.updated', 'capability.updated' => app(BachsConnectService::class)->handleAccountEvent($event),
            default => null,
        };
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function handleCheckoutPaid(array $data): void
    {
        $checkoutId = (string) ($data['checkout_id'] ?? $data['id'] ?? '');

        if ($checkoutId === '') {
            return;
        }

        $session = isset($data['metadata']) ? $data : $this->bachs->get('/v1/checkout-sessions/'.$checkoutId);
        $metadata = is_array($session['metadata'] ?? null) ? $session['metadata'] : [];

        if (($metadata['type'] ?? null) === 'booking') {
            $this->confirmBookingFromCheckout($session, $checkoutId);

            return;
        }

        if (($metadata['type'] ?? null) === 'subscription') {
            $this->activateSubscriptionFromCheckout($session);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function handleCheckoutAbandoned(array $data): void
    {
        $metadata = is_array($data['metadata'] ?? null) ? $data['metadata'] : [];

        if (($metadata['type'] ?? null) !== 'booking') {
            $checkoutId = (string) ($data['checkout_id'] ?? '');

            if ($checkoutId === '') {
                return;
            }

            $session = $this->bachs->get('/v1/checkout-sessions/'.$checkoutId);
            $metadata = is_array($session['metadata'] ?? null) ? $session['metadata'] : [];
        }

        if (($metadata['type'] ?? null) !== 'booking') {
            return;
        }

        $booking = Booking::query()->find($metadata['booking_id'] ?? null);

        if ($booking) {
            $this->cancelPendingBooking($booking);
        }
    }

    /**
     * @param  array<string, mixed>  $session
     */
    private function activateSubscriptionFromCheckout(array $session): User
    {
        $metadata = is_array($session['metadata'] ?? null) ? $session['metadata'] : [];
        $userId = $metadata['user_id'] ?? null;
        $user = User::query()->findOrFail($userId);
        $plan = SubscriptionPlan::from($metadata['plan'] ?? $user->subscription_plan);
        $customer = $session['customer'] ?? null;
        $customerId = is_array($customer) ? ($customer['id'] ?? null) : $customer;

        $user->update([
            'subscription_plan' => $plan->value,
            'subscription_status' => SubscriptionStatus::Active->value,
            'bachs_customer_id' => $customerId ?: $user->bachs_customer_id,
            'bachs_subscription_id' => $session['subscription_id']
                ?? (is_array($session['subscription'] ?? null) ? ($session['subscription']['id'] ?? null) : ($session['subscription'] ?? null))
                ?? $user->bachs_subscription_id,
        ]);

        return $user->fresh();
    }

    /**
     * @param  array<string, mixed>  $session
     */
    private function confirmBookingFromCheckout(array $session, string $checkoutId): Booking
    {
        $metadata = is_array($session['metadata'] ?? null) ? $session['metadata'] : [];
        $booking = Booking::query()->findOrFail($metadata['booking_id'] ?? null);
        $alreadyPaid = $booking->payment_status === PaymentStatus::Paid;

        $booking->update([
            'status' => BookingStatus::Scheduled,
            'payment_status' => PaymentStatus::Paid,
            'bachs_checkout_id' => $session['checkout_id'] ?? $checkoutId,
        ]);

        $booking = $booking->fresh();

        if (! $alreadyPaid) {
            ShopNotifier::bookingPaid($booking);
        }

        return $booking;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function handleSubscriptionChange(array $data, string $type): void
    {
        $subscription = is_array($data['object'] ?? null) ? $data['object'] : $data;
        $subscriptionId = $subscription['id'] ?? $subscription['subscription_id'] ?? null;

        if (! is_string($subscriptionId) || $subscriptionId === '') {
            return;
        }

        $metadata = is_array($subscription['metadata'] ?? null) ? $subscription['metadata'] : [];
        $user = User::query()->where('bachs_subscription_id', $subscriptionId)->first();

        if (! $user && filled($metadata['user_id'] ?? null)) {
            $user = User::query()->find($metadata['user_id']);
        }

        if (! $user) {
            return;
        }

        $statusName = $type === 'customer.subscription.deleted'
            ? 'canceled'
            : (string) ($subscription['status'] ?? '');

        $status = match ($statusName) {
            'active', 'trialing' => SubscriptionStatus::Active,
            'past_due', 'unpaid' => SubscriptionStatus::PastDue,
            'canceled', 'cancelled' => SubscriptionStatus::Canceled,
            default => SubscriptionStatus::Pending,
        };

        $cancelAt = null;

        if (! empty($subscription['cancel_at_period_end'])) {
            $cancelAt = $this->periodEnd($subscription['current_period_end'] ?? null);
        } elseif ($status === SubscriptionStatus::Canceled) {
            $cancelAt = now();
        }

        $user->update([
            'bachs_subscription_id' => $subscriptionId,
            'bachs_customer_id' => $this->customerId($subscription['customer'] ?? null) ?: $user->bachs_customer_id,
            'subscription_status' => $status->value,
            'subscription_plan' => $metadata['plan'] ?? $user->subscription_plan,
            'subscription_cancel_at' => $cancelAt,
        ]);
    }

    private function assertSignature(string $payload, ?string $timestamp, ?string $signature): void
    {
        $secrets = array_values(array_filter([
            config('bachs.webhook_secret'),
            config('bachs.connect_webhook_secret'),
        ]));

        if ($secrets === [] || blank($timestamp) || blank($signature)) {
            throw new RuntimeException('Bachs webhook signature is missing.');
        }

        if (abs(time() - (int) $timestamp) > 300) {
            throw new RuntimeException('Bachs webhook timestamp is outside the allowed window.');
        }

        foreach ($secrets as $secret) {
            $expected = hash_hmac('sha256', $timestamp.'.'.$payload, $secret);

            if (hash_equals($expected, $signature)) {
                return;
            }
        }

        throw new RuntimeException('Bachs webhook signature is invalid.');
    }

    private function money(int $cents): string
    {
        return number_format($cents / 100, 2, '.', '');
    }

    private function periodEnd(mixed $value): ?Carbon
    {
        if (blank($value)) {
            return null;
        }

        if (is_numeric($value)) {
            return Carbon::createFromTimestamp((int) $value);
        }

        return Carbon::parse((string) $value);
    }

    private function customerId(mixed $customer): ?string
    {
        if (is_string($customer) && $customer !== '') {
            return $customer;
        }

        if (is_array($customer) && filled($customer['id'] ?? null)) {
            return (string) $customer['id'];
        }

        return null;
    }
}
