<?php

namespace App\Services\Payments;

use App\Models\Business;
use RuntimeException;

class BachsConnectService
{
    public function __construct(private BachsClient $bachs) {}

    public static function shouldBypass(): bool
    {
        return BachsCheckoutService::shouldBypass();
    }

    public function canAcceptPayments(Business $business): bool
    {
        if (static::shouldBypass()) {
            return true;
        }

        return filled($business->bachs_account_id) && $business->bachs_charges_enabled;
    }

    public function ensureAccount(Business $business): Business
    {
        if (filled($business->bachs_account_id)) {
            return $business;
        }

        $business->loadMissing('owner');

        $account = $this->bachs->post('/v1/accounts', [
            'contact_email' => $business->owner->email,
            'display_name' => $business->name,
            'country' => strtoupper((string) config('bachs.connect.country', 'GB')),
            'entity_type' => 'individual',
            'configuration' => [
                'recipient' => [
                    'capabilities' => [
                        'transfers' => ['requested' => true],
                        'payouts' => ['requested' => true],
                    ],
                ],
            ],
        ], 'account-business-'.$business->id);

        $business->update([
            'bachs_account_id' => $account['id'] ?? null,
            'bachs_charges_enabled' => $this->capabilityActive($account, 'transfers'),
            'bachs_payouts_enabled' => $this->capabilityActive($account, 'payouts'),
        ]);

        return $business->fresh();
    }

    public function createOnboardingUrl(Business $business): string
    {
        $business = $this->ensureAccount($business);

        $link = $this->bachs->post('/v1/accounts/'.$business->bachs_account_id.'/account-links', [
            'type' => 'onboarding',
            'return_url' => route('business.payments.return'),
            'refresh_url' => route('business.payments.refresh'),
        ]);

        $url = $link['url'] ?? null;

        if (blank($url)) {
            throw new RuntimeException('Bachs did not return an onboarding link.');
        }

        return $url;
    }

    public function syncAccount(Business $business): Business
    {
        if (blank($business->bachs_account_id)) {
            return $business;
        }

        $account = $this->bachs->get('/v1/accounts/'.$business->bachs_account_id);

        return $this->applyAccount($business, $account);
    }

    /**
     * @param  array<string, mixed>  $event
     */
    public function handleAccountEvent(array $event): void
    {
        $accountId = $event['account'] ?? $event['organization_id'] ?? null;
        $data = is_array($event['data'] ?? null) ? $event['data'] : [];
        $accountId = is_string($accountId) ? $accountId : ($data['account'] ?? null);

        if (! is_string($accountId) || $accountId === '') {
            return;
        }

        $business = Business::query()->where('bachs_account_id', $accountId)->first();

        if (! $business) {
            return;
        }

        if (($event['type'] ?? null) === 'capability.updated') {
            $capability = (string) ($data['capability'] ?? $data['id'] ?? '');
            $active = ($data['status'] ?? null) === 'active';
            $changes = match ($capability) {
                'transfers', 'card_collection' => ['bachs_charges_enabled' => $active || $business->bachs_charges_enabled],
                'payouts' => ['bachs_payouts_enabled' => $active || $business->bachs_payouts_enabled],
                default => null,
            };

            if ($changes) {
                $business->update($changes);
                $business->refresh();
            }
        }

        $this->syncAccount($business);
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

        if (blank($business->bachs_account_id)) {
            return [
                'label' => 'Not connected',
                'tone' => 'warning',
                'ready' => false,
            ];
        }

        if ($business->bachs_charges_enabled) {
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

        if (str_contains($message, 'connect') && str_contains($message, 'not')) {
            return 'Bachs Connect is not enabled on the Cutcost Bachs account yet. Turn on the connect capability in the Bachs dashboard, then try again.';
        }

        if (str_contains($message, 'secret key is missing') || str_contains($message, 'unauthorized')) {
            return 'Bachs secret key is missing or invalid. Check BACHS_SECRET in your .env file.';
        }

        if (filled($e->getMessage())) {
            return 'Bachs setup failed: '.$e->getMessage();
        }

        return 'We could not start Bachs setup. Check your Bachs keys and try again.';
    }

    /**
     * @param  array<string, mixed>  $account
     */
    private function applyAccount(Business $business, array $account): Business
    {
        $charges = $this->capabilityActive($account, 'transfers') || $this->capabilityActive($account, 'card_collection');
        $payouts = $this->capabilityActive($account, 'payouts');

        $business->update([
            'bachs_charges_enabled' => $charges,
            'bachs_payouts_enabled' => $payouts,
            'bachs_onboarding_completed_at' => $charges && $payouts
                ? ($business->bachs_onboarding_completed_at ?? now())
                : null,
        ]);

        return $business->fresh();
    }

    /**
     * @param  array<string, mixed>  $account
     */
    private function capabilityActive(array $account, string $name): bool
    {
        $capabilities = $account['capabilities'] ?? [];

        if (! is_array($capabilities)) {
            return false;
        }

        $capability = $capabilities[$name] ?? null;

        return is_array($capability) && ($capability['status'] ?? null) === 'active';
    }
}
