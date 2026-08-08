<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Enums\Role;
use App\Models\Booking;
use App\Models\Business;
use App\Models\Client;
use App\Models\Service;
use App\Models\User;
use App\Services\BookingRevenueService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingRevenueTest extends TestCase
{
    use RefreshDatabase;

    public function test_revenue_counts_only_paid_bookings(): void
    {
        [$business, $service, $client, $barber] = $this->shopFixtures();

        Booking::create([
            'business_id' => $business->id,
            'client_id' => $client->id,
            'barber_id' => $barber->id,
            'service_id' => $service->id,
            'starts_at' => now()->addDay(),
            'ends_at' => now()->addDay()->addMinutes(45),
            'status' => BookingStatus::Scheduled,
            'payment_status' => PaymentStatus::Paid,
            'amount_cents' => 2500,
        ]);

        Booking::create([
            'business_id' => $business->id,
            'client_id' => $client->id,
            'barber_id' => $barber->id,
            'service_id' => $service->id,
            'starts_at' => now()->addDays(2),
            'ends_at' => now()->addDays(2)->addMinutes(45),
            'status' => BookingStatus::Scheduled,
            'payment_status' => PaymentStatus::Waived,
            'amount_cents' => 2500,
        ]);

        $summary = app(BookingRevenueService::class)->summary($business);

        $this->assertSame('£25.00', $summary['all_time']['amount_label']);
        $this->assertSame(1, $summary['all_time']['paid_bookings_count']);
    }

    public function test_owner_dashboard_includes_earnings(): void
    {
        [$business, $service, $client, $barber, $owner] = $this->shopFixtures(withOwner: true);

        Booking::create([
            'business_id' => $business->id,
            'client_id' => $client->id,
            'barber_id' => $barber->id,
            'service_id' => $service->id,
            'starts_at' => now()->addDay(),
            'ends_at' => now()->addDay()->addMinutes(45),
            'status' => BookingStatus::Scheduled,
            'payment_status' => PaymentStatus::Paid,
            'amount_cents' => 1200,
        ]);

        $this->actingAs($owner)
            ->get(route('business.dashboard'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Business/Dashboard')
                ->where('earnings.today.amount_label', '£12.00')
            );
    }

    public function test_payments_page_supports_period_filter(): void
    {
        [$business, $service, $client, $barber, $owner] = $this->shopFixtures(withOwner: true);

        $booking = Booking::create([
            'business_id' => $business->id,
            'client_id' => $client->id,
            'barber_id' => $barber->id,
            'service_id' => $service->id,
            'starts_at' => now()->subMonth(),
            'ends_at' => now()->subMonth()->addMinutes(45),
            'status' => BookingStatus::Scheduled,
            'payment_status' => PaymentStatus::Paid,
            'amount_cents' => 5000,
        ]);

        $booking->forceFill([
            'updated_at' => now()->subMonth(),
            'created_at' => now()->subMonth(),
        ])->saveQuietly();

        $this->actingAs($owner)
            ->get(route('business.payments.index', ['period' => 'month']))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Business/Payments/Index')
                ->where('earnings.amount_label', '£0.00')
                ->where('earnings.period', 'month')
            );

        $this->actingAs($owner)
            ->get(route('business.payments.index', ['period' => 'all']))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('earnings.amount_label', '£50.00')
            );
    }

    /**
     * @return array{0: Business, 1: Service, 2: Client, 3: User, 4?: User}
     */
    private function shopFixtures(bool $withOwner = false): array
    {
        $owner = User::factory()->owner()->create();

        $business = Business::create([
            'owner_id' => $owner->id,
            'name' => 'Revenue Shop',
            'slug' => 'revenue-shop',
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

        $client = Client::create([
            'business_id' => $business->id,
            'name' => 'Paying Client',
            'phone' => '07700901111',
        ]);

        return $withOwner
            ? [$business, $service, $client, $barber, $owner->fresh()]
            : [$business, $service, $client, $barber];
    }
}
