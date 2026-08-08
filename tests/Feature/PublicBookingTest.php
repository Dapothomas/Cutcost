<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Enums\Role;
use App\Models\Business;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicBookingTest extends TestCase
{
    use RefreshDatabase;

    public function test_clients_can_view_shop_booking_page(): void
    {
        [$business] = $this->shopWithService();

        $this->get(route('public.booking.show', $business))
            ->assertOk()
            ->assertSee($business->name)
            ->assertSee('Skin fade');
    }

    public function test_clients_can_book_themselves(): void
    {
        [$business, $service, $barber] = $this->shopWithService();

        $date = now()->addDay()->toDateString();

        $response = $this->from(route('public.booking.show', [
            'business' => $business,
            'service_id' => $service->id,
            'barber_id' => $barber->id,
            'date' => $date,
        ]))->post(route('public.booking.store', $business), [
            'service_id' => $service->id,
            'barber_id' => $barber->id,
            'date' => $date,
            'time' => '10:00',
            'name' => 'Self Booker',
            'phone' => '07700901111',
            'email' => 'self@example.com',
            'notes' => 'Please keep it short',
        ]);

        $booking = $business->bookings()->first();
        $this->assertNotNull($booking);

        $response->assertRedirect(route('public.booking.confirmation', [$business, $booking]));

        $this->assertDatabaseHas('clients', [
            'business_id' => $business->id,
            'name' => 'Self Booker',
            'phone' => '07700901111',
        ]);

        $this->assertDatabaseHas('bookings', [
            'business_id' => $business->id,
            'service_id' => $service->id,
            'barber_id' => $barber->id,
            'status' => BookingStatus::Scheduled->value,
            'payment_status' => PaymentStatus::Waived->value,
            'amount_cents' => $service->price_cents,
        ]);
    }

    public function test_disabled_public_booking_is_not_available(): void
    {
        [$business] = $this->shopWithService();
        $business->update(['public_booking_enabled' => false]);

        $this->get(route('public.booking.show', $business))->assertNotFound();
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
