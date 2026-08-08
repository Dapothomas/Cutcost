<?php

namespace Tests\Unit;

use App\Models\Business;
use App\Models\Service;
use App\Models\User;
use App\Support\BookingSlots;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingSlotsSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_closed_day_returns_no_slots(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-08-10 08:00:00')); // Monday

        [$business, $service, $barber] = $this->fixtures();
        $business->update([
            'opening_hours' => array_merge(Business::defaultOpeningHours(), [
                'mon' => [],
            ]),
        ]);

        $slots = app(BookingSlots::class)->for(
            $business->fresh(),
            $service,
            $barber,
            Carbon::parse('2026-08-10'),
        );

        $this->assertSame([], $slots);
    }

    public function test_custom_hours_and_interval_are_respected(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-08-10 08:00:00'));

        [$business, $service, $barber] = $this->fixtures();
        $business->update([
            'slot_interval_minutes' => 60,
            'opening_hours' => array_merge(Business::defaultOpeningHours(), [
                'mon' => [['open' => '10:00', 'close' => '12:00']],
            ]),
        ]);

        $slots = app(BookingSlots::class)->for(
            $business->fresh(),
            $service,
            $barber,
            Carbon::parse('2026-08-10'),
        );

        $this->assertSame(['10:00', '11:00'], $slots);
    }

    /**
     * @return array{0: Business, 1: Service, 2: User}
     */
    private function fixtures(): array
    {
        $owner = User::factory()->owner()->create();
        $business = Business::create([
            'owner_id' => $owner->id,
            'name' => 'Slots Shop',
            'slug' => 'slots-shop',
            'public_booking_enabled' => true,
        ]);
        $owner->update(['business_id' => $business->id]);

        $barber = User::factory()->barber()->create([
            'business_id' => $business->id,
        ]);

        $service = Service::create([
            'business_id' => $business->id,
            'name' => 'Cut',
            'duration_minutes' => 60,
            'price_cents' => 2000,
            'is_active' => true,
        ]);

        return [$business, $service, $barber];
    }
}
