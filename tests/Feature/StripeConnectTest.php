<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Enums\SubscriptionPlan;
use App\Enums\SubscriptionStatus;
use App\Models\Business;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StripeConnectTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_view_payments_page(): void
    {
        $owner = $this->ownerWithBusiness();

        $this->actingAs($owner)
            ->get(route('business.payments.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Business/Payments/Index')
                ->where('payments.ready', true)
            );
    }

    public function test_paid_public_booking_is_blocked_without_stripe_connect(): void
    {
        config(['stripe.bypass_checkout' => false]);

        [$business, $service, $barber] = $this->shopWithService();

        $date = now()->addDay()->toDateString();

        $this->from(route('public.booking.show', $business))
            ->post(route('public.booking.store', $business), [
                'service_id' => $service->id,
                'barber_id' => $barber->id,
                'date' => $date,
                'time' => '10:00',
                'name' => 'Blocked Booker',
                'phone' => '07700901112',
            ])
            ->assertSessionHasErrors('service_id');

        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_public_booking_page_shows_payment_blocked_message(): void
    {
        config(['stripe.bypass_checkout' => false]);

        [$business, $service] = $this->shopWithService();

        $this->get(route('public.booking.show', [
            'business' => $business,
            'service_id' => $service->id,
        ]))
            ->assertOk()
            ->assertSee('Online payment is not available yet');
    }

    private function ownerWithBusiness(): User
    {
        $owner = User::factory()->owner()->create([
            'subscription_plan' => SubscriptionPlan::Shop,
            'subscription_status' => SubscriptionStatus::Active,
        ]);

        $business = Business::create([
            'owner_id' => $owner->id,
            'name' => 'Owner Shop',
            'slug' => 'owner-shop',
            'public_booking_enabled' => true,
        ]);

        $owner->update(['business_id' => $business->id]);

        return $owner->fresh();
    }

    /**
     * @return array{0: Business, 1: Service, 2: User}
     */
    private function shopWithService(): array
    {
        $owner = User::factory()->owner()->create();

        $business = Business::create([
            'owner_id' => $owner->id,
            'name' => 'Corner Cuts',
            'slug' => 'corner-cuts',
            'city' => 'Leeds',
            'public_booking_enabled' => true,
        ]);

        $owner->update(['business_id' => $business->id]);

        $barber = User::factory()->barber()->create([
            'role' => Role::Barber,
            'business_id' => $business->id,
        ]);

        $service = Service::create([
            'business_id' => $business->id,
            'name' => 'Skin fade',
            'duration_minutes' => 45,
            'price_cents' => 2500,
            'is_active' => true,
        ]);

        return [$business, $service, $barber];
    }
}
