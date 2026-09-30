<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UiSmokeTest extends TestCase
{
    use RefreshDatabase;

    private function shop(): array
    {
        $owner = User::factory()->owner()->create();
        $business = Business::create([
            'owner_id' => $owner->id,
            'name' => 'Corner Cuts',
            'city' => 'Leeds',
        ]);
        $owner->update(['business_id' => $business->id]);

        $client = $business->clients()->create([
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
            'phone' => '07700900123',
        ]);

        $service = $business->services()->create([
            'name' => 'Skin fade',
            'duration_minutes' => 30,
            'price_cents' => 2500,
            'is_active' => true,
        ]);

        $barber = User::factory()->barber()->create(['business_id' => $business->id]);

        $business->bookings()->create([
            'client_id' => $client->id,
            'service_id' => $service->id,
            'barber_id' => $barber->id,
            'starts_at' => now()->addDay(),
            'ends_at' => now()->addDay()->addMinutes(30),
            'status' => BookingStatus::Scheduled,
            'payment_status' => PaymentStatus::Waived,
            'amount_cents' => 2500,
        ]);

        return [$owner, $barber];
    }

    public function test_owner_screens_render(): void
    {
        [$owner] = $this->shop();

        $routes = [
            '/business',
            '/business/clients',
            '/business/clients/create',
            '/business/bookings',
            '/business/bookings/create',
            '/business/services',
            '/business/services/create',
            '/business/staff',
            '/business/staff/create',
            '/business/payments',
            '/business/settings',
            '/business/notifications',
            '/profile',
        ];

        foreach ($routes as $route) {
            $this->actingAs($owner)->get($route)->assertOk();
        }
    }

    public function test_list_search_and_filters_render(): void
    {
        [$owner] = $this->shop();

        $this->actingAs($owner)->get('/business/clients?search=ada')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('filters.search', 'ada')
                ->where('clients.total', 1));

        $this->actingAs($owner)->get('/business/clients?search=nobody')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->where('clients.total', 0));

        $this->actingAs($owner)->get('/business/services?search=fade')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->where('services.total', 1));

        $this->actingAs($owner)->get('/business/staff?search=' . urlencode('@'))
            ->assertOk();

        $this->actingAs($owner)->get('/business/bookings?search=Ada&status=scheduled')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('filters.status', 'scheduled')
                ->where('bookings.total', 1));

        // An unknown status must be discarded rather than filtering everything out.
        $this->actingAs($owner)->get('/business/bookings?status=bogus')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('filters.status', '')
                ->where('bookings.total', 1));
    }

    public function test_barber_screens_render(): void
    {
        [, $barber] = $this->shop();

        $this->actingAs($barber)->get('/barber')->assertOk();
        $this->actingAs($barber)->get('/barber/bookings')->assertOk();
    }
}
