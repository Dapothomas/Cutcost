<?php

namespace App\Services;

use App\Models\Business;
use Stripe\Account;
use Stripe\Exception\ApiErrorException;
use Stripe\Stripe;
use Stripe\StripeClient;

class StripeConnectService
{
    public static function shouldBypass(): bool
    {
        return StripeCheckoutService::shouldBypass();
    }

    public function canAcceptPayments(Business $business): bool
    {
        if (static::shouldBypass()) {
            return true;
        }

        return filled($business->stripe_account_id) && $business->stripe_charges_enabled;
    }

    public function ensureExpressAccount(Business $business): Business
    {
        if (filled($business->stripe_account_id)) {
            return $business;
        }

        $business->loadMissing('owner');

        $account = $this->client()->request('post', '/v2/core/accounts', [
            'contact_email' => $business->owner->email,
            'display_name' => $business->name,
            'dashboard' => 'express',
            'identity' => [
                'country' => strtolower(config('stripe.connect.country', 'GB')),
                'entity_type' => 'individual',
            ],
            'configuration' => [
                'merchant' => [
                    'capabilities' => [
                        'card_payments' => ['requested' => true],
                    ],
                ],
                'recipient' => [
                    'capabilities' => [
                        'stripe_balance' => [
                            'stripe_transfers' => ['requested' => true],
                        ],
                    ],
                ],
            ],
            'defaults' => [
                'responsibilities' => [
                    'fees_collector' => 'application',
                    'losses_collector' => 'application',
                ],
            ],
            'include' => [
                'configuration.merchant',
                'configuration.recipient',
                'identity',
                'defaults',
            ],
            'metadata' => [
                'business_id' => (string) $business->id,
                'business_slug' => $business->slug,
            ],
        ], []);

        $business->update([
            'stripe_account_id' => $account->id,
        ]);

        return $business->fresh();
    }

    public function createOnboardingUrl(Business $business): string
    {
        $business = $this->ensureExpressAccount($business);

        $link = $this->client()->request('post', '/v2/core/account_links', [
            'account' => $business->stripe_account_id,
            'use_case' => [
                'type' => 'account_onboarding',
                'account_onboarding' => [
                    'configurations' => ['merchant', 'recipient'],
                    'return_url' => route('business.payments.return'),
                    'refresh_url' => route('business.payments.refresh'),
                ],
            ],
        ], []);

        return $link->url;
    }

    public function syncAccount(Business $business): Business
    {
        if (blank($business->stripe_account_id)) {
            return $business;
        }

        [$chargesEnabled, $payoutsEnabled] = $this->resolveCapabilities($business->stripe_account_id);

        $business->update([
            'stripe_charges_enabled' => $chargesEnabled,
            'stripe_payouts_enabled' => $payoutsEnabled,
            'stripe_onboarding_completed_at' => $chargesEnabled && $payoutsEnabled
                ? ($business->stripe_onboarding_completed_at ?? now())
                : null,
        ]);

        return $business->fresh();
    }

    public function handleAccountUpdated(object $account): void
    {
        $businessId = $account->metadata['business_id'] ?? null;

        $business = $businessId
            ? Business::query()->find($businessId)
            : Business::query()->where('stripe_account_id', $account->id)->first();

        if (! $business) {
            return;
        }

        [$chargesEnabled, $payoutsEnabled] = $this->resolveCapabilities($account->id);

        $business->update([
            'stripe_charges_enabled' => $chargesEnabled,
            'stripe_payouts_enabled' => $payoutsEnabled,
            'stripe_onboarding_completed_at' => $chargesEnabled && $payoutsEnabled
                ? ($business->stripe_onboarding_completed_at ?? now())
                : null,
        ]);
    }

    /**
     * @return array{label: string, tone: string, ready: bool}
     */
    public function statusFor(Business $business): array
    {
        if (static::shouldBypass()) {
            return [
                'label' => 'Payments bypassed in this environment',
                'tone' => 'muted',
                'ready' => true,
            ];
        }

        if (blank($business->stripe_account_id)) {
            return [
                'label' => 'Not connected',
                'tone' => 'warning',
                'ready' => false,
            ];
        }

        if ($business->stripe_charges_enabled) {
            return [
                'label' => 'Ready to accept payments',
                'tone' => 'success',
                'ready' => true,
            ];
        }

        return [
            'label' => 'Setup incomplete',
            'tone' => 'warning',
            'ready' => false,
        ];
    }

    public function connectErrorMessage(\Throwable $e): string
    {
        $message = strtolower($e->getMessage());

        if (str_contains($message, 'signed up for connect')) {
            return 'Stripe Connect is not enabled on your Cutcost Stripe account yet. In the Stripe Dashboard go to Connect → Get started, then try again.';
        }

        if (str_contains($message, 'accounts_v2_access_blocked')) {
            return 'Stripe Accounts v2 is not enabled on your platform yet. Enable it in the Stripe Dashboard or contact Stripe support.';
        }

        if (str_contains($message, 'invalid api key') || str_contains($message, 'no api key')) {
            return 'Stripe secret key is missing or invalid. Check STRIPE_SECRET in your .env file.';
        }

        if ($e instanceof ApiErrorException && filled($e->getMessage())) {
            return 'Stripe setup failed: '.$e->getMessage();
        }

        return 'We could not start Stripe setup. Check your Stripe keys and try again.';
    }

    /**
     * @return array{0: bool, 1: bool}
     */
    private function resolveCapabilities(string $accountId): array
    {
        Stripe::setApiKey(config('stripe.secret'));

        try {
            $account = Account::retrieve($accountId);

            if ($account->charges_enabled || $account->payouts_enabled) {
                return [(bool) $account->charges_enabled, (bool) $account->payouts_enabled];
            }
        } catch (\Throwable) {
            // Fall through to v2 lookup below.
        }

        try {
            $account = $this->client()->request('get', '/v2/core/accounts/'.$accountId, [
                'include' => ['configuration.merchant', 'configuration.recipient'],
            ], []);

            $merchant = $account->configuration->merchant ?? null;
            $recipient = $account->configuration->recipient ?? null;

            $chargesEnabled = ($merchant->capabilities->card_payments->status ?? null) === 'active';

            $transferStatus = $recipient->capabilities->stripe_balance->stripe_transfers->status ?? null;
            $payoutStatus = $merchant->capabilities->stripe_balance->payouts->status
                ?? $recipient->capabilities->stripe_balance->payouts->status
                ?? null;

            $payoutsEnabled = $payoutStatus === 'active' && $transferStatus === 'active';

            return [$chargesEnabled, $payoutsEnabled];
        } catch (\Throwable) {
            $account = Account::retrieve($accountId);

            return [(bool) $account->charges_enabled, (bool) $account->payouts_enabled];
        }
    }

    private function client(): StripeClient
    {
        return new StripeClient([
            'api_key' => config('stripe.secret'),
            'stripe_version' => config('stripe.api_version'),
        ]);
    }
}
