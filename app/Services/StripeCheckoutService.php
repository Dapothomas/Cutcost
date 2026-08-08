<?php

namespace App\Services;

use App\Enums\SubscriptionPlan;
use App\Enums\SubscriptionStatus;
use App\Models\User;
use Illuminate\Support\Facades\App;
use Stripe\Checkout\Session;
use Stripe\Stripe;
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
            'customer.subscription.updated',
            'customer.subscription.deleted' => $this->handleSubscriptionChange($event->data->object),
            default => null,
        };
    }

    private function handleCheckoutCompleted(object $session): void
    {
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

        $user->update([
            'subscription_status' => $status->value,
            'subscription_plan' => $subscription->metadata['plan'] ?? $user->subscription_plan,
        ]);
    }
}
