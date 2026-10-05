<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Enums\Role;
use App\Enums\SubscriptionPlan;
use App\Enums\SubscriptionStatus;
use App\Models\Booking;
use App\Models\Business;
use App\Models\Client;
use App\Models\Service;
use App\Models\User;
use App\Services\Payments\BachsCheckoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BachsCheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_subscription_checkout_uses_bachs(): void
    {
        config([
            'bachs.secret' => 'sk_sandbox_test',
            'bachs.base_url' => 'https://sandbox-api.bachs.io',
            'bachs.products.shop' => 'prod_shop',
        ]);

        Http::fake([
            'https://sandbox-api.bachs.io/v1/checkout-sessions' => Http::response([
                'checkout_id' => 'chk_test',
                'checkout_url' => 'https://checkout.bachs.io/c/test',
                'status' => 'open',
            ], 201),
        ]);

        $user = User::factory()->create([
            'subscription_plan' => SubscriptionPlan::Shop,
            'subscription_status' => SubscriptionStatus::Pending,
        ]);

        $session = app(BachsCheckoutService::class)->createCheckoutSession($user, SubscriptionPlan::Shop);

        $this->assertSame('chk_test', $session->id);
        $this->assertSame('https://checkout.bachs.io/c/test', $session->url);

        Http::assertSent(function ($request) use ($user) {
            $body = $request->data();

            return $request->url() === 'https://sandbox-api.bachs.io/v1/checkout-sessions'
                && ($body['product_cart'][0]['product_id'] ?? null) === 'prod_shop'
                && ($body['billing_currency'] ?? null) === 'USD'
                && ($body['metadata']['user_id'] ?? null) === (string) $user->id
                && ($body['customer']['email'] ?? null) === $user->email;
        });
    }

    public function test_webhook_activates_the_subscription(): void
    {
        config(['bachs.webhook_secret' => 'whsec_test']);

        $user = User::factory()->create([
            'subscription_plan' => SubscriptionPlan::Shop,
            'subscription_status' => SubscriptionStatus::Pending,
        ]);

        $body = json_encode([
            'id' => 'evt_1',
            'type' => 'checkout.completed',
            'data' => [
                'checkout_id' => 'chk_1',
                'status' => 'completed',
                'metadata' => [
                    'type' => 'subscription',
                    'user_id' => (string) $user->id,
                    'plan' => 'shop',
                ],
                'customer' => ['id' => 'cust_1'],
                'subscription_id' => 'sub_1',
            ],
        ], JSON_THROW_ON_ERROR);

        $timestamp = (string) time();
        $signature = hash_hmac('sha256', $timestamp.'.'.$body, 'whsec_test');

        $this->call('POST', '/bachs/webhook', [], [], [], [
            'HTTP_X_BACHS_TIMESTAMP' => $timestamp,
            'HTTP_X_BACHS_SIGNATURE' => $signature,
            'CONTENT_TYPE' => 'application/json',
        ], $body)->assertOk();

        $user->refresh();

        $this->assertSame(SubscriptionStatus::Active, $user->subscription_status);
        $this->assertSame('sub_1', $user->bachs_subscription_id);
        $this->assertSame('cust_1', $user->bachs_customer_id);
    }

    public function test_booking_checkout_keeps_the_client_email_after_a_partial_load(): void
    {
        config([
            'bachs.secret' => 'sk_sandbox_test',
            'bachs.base_url' => 'https://sandbox-api.bachs.io',
        ]);

        Http::fake([
            'https://sandbox-api.bachs.io/v1/checkout-sessions' => Http::response([
                'checkout_id' => 'chk_book',
                'checkout_url' => 'https://checkout.bachs.io/c/book',
                'status' => 'open',
            ], 201),
        ]);

        $owner = User::factory()->owner()->create();
        $business = Business::create([
            'owner_id' => $owner->id,
            'payment_provider' => 'bachs',
            'name' => 'Lagos Shop',
            'slug' => 'lagos-shop',
        ]);
        $barber = User::factory()->barber()->create([
            'role' => Role::Barber,
            'business_id' => $business->id,
        ]);
        $service = Service::create([
            'business_id' => $business->id,
            'name' => 'Braids',
            'duration_minutes' => 30,
            'price_cents' => 3000,
            'is_active' => true,
        ]);
        $client = Client::create([
            'business_id' => $business->id,
            'name' => 'Ada',
            'phone' => '08030000000',
            'email' => 'ada@example.com',
        ]);
        $booking = Booking::create([
            'business_id' => $business->id,
            'client_id' => $client->id,
            'barber_id' => $barber->id,
            'service_id' => $service->id,
            'starts_at' => now()->addDay(),
            'ends_at' => now()->addDay()->addMinutes(30),
            'status' => BookingStatus::PendingPayment,
            'payment_status' => PaymentStatus::Pending,
            'amount_cents' => 3000,
        ]);
        $booking->setRelation('client', new Client([
            'id' => $client->id,
            'name' => $client->name,
        ]));

        $session = app(BachsCheckoutService::class)->createBookingCheckoutSession($booking, $business, $service);

        $this->assertSame('chk_book', $session->id);
        Http::assertSent(function ($request) {
            return ($request->data()['customer']['email'] ?? null) === 'ada@example.com';
        });
    }

    public function test_webhook_rejects_a_bad_signature(): void
    {
        config(['bachs.webhook_secret' => 'whsec_test']);

        $this->call('POST', '/bachs/webhook', [], [], [], [
            'HTTP_X_BACHS_TIMESTAMP' => (string) time(),
            'HTTP_X_BACHS_SIGNATURE' => 'nope',
            'CONTENT_TYPE' => 'application/json',
        ], '{"id":"evt_2","type":"checkout.completed","data":{}}')->assertStatus(400);
    }
}
