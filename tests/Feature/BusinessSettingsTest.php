<?php

namespace Tests\Feature;

use App\Enums\SubscriptionStatus;
use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_update_shop_settings(): void
    {
        [$owner, $business] = $this->ownerWithShop();

        $hours = [];
        foreach (Business::WEEKDAYS as $day) {
            $hours[$day] = [
                'closed' => $day === 'sun',
                'open' => '10:00',
                'close' => '17:00',
            ];
        }

        $this->actingAs($owner)
            ->patch(route('business.settings.update'), [
                'name' => 'New Shop Name',
                'slug' => 'new-shop-name',
                'phone' => '07700900111',
                'city' => 'Manchester',
                'address' => '1 High Street',
                'public_booking_enabled' => true,
                'primary_color' => '#db2777',
                'slot_interval_minutes' => 30,
                'booking_lead_minutes' => 60,
                'booking_horizon_days' => 30,
                'opening_hours' => $hours,
            ])
            ->assertRedirect();

        $business->refresh();

        $this->assertSame('New Shop Name', $business->name);
        $this->assertSame('new-shop-name', $business->slug);
        $this->assertSame('#DB2777', $business->primary_color);
        $this->assertSame(30, $business->slot_interval_minutes);
        $this->assertSame([], $business->opening_hours['sun']);
        $this->assertSame('10:00', $business->opening_hours['mon'][0]['open']);
    }

    public function test_owner_can_cancel_subscription_when_bypassed(): void
    {
        config(['stripe.bypass_checkout' => true]);

        [$owner] = $this->ownerWithShop();

        $this->actingAs($owner)
            ->post(route('business.settings.subscription.cancel'), [
                'confirm' => true,
            ])
            ->assertRedirect();

        $owner->refresh();
        $this->assertSame(SubscriptionStatus::Canceled, $owner->subscription_status);
        $this->assertNotNull($owner->subscription_cancel_at);
    }

    public function test_settings_page_includes_sections(): void
    {
        [$owner, $business] = $this->ownerWithShop();
        $business->update(['primary_color' => '#7C3AED']);

        $this->actingAs($owner)
            ->get(route('business.settings.edit'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Business/Settings/Edit')
                ->where('business.primary_color', '#7C3AED')
                ->has('business.opening_hours')
                ->has('subscription')
                ->has('presets')
            );
    }

    public function test_theme_tokens_include_sidebar(): void
    {
        [$owner, $business] = $this->ownerWithShop();
        $business->update(['primary_color' => '#0F766E']);

        $this->actingAs($owner)
            ->get(route('business.dashboard'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('theme.primary_color', '#0F766E')
                ->has('theme.tokens.sidebar_background')
            );
    }

    /**
     * @return array{0: User, 1: Business}
     */
    private function ownerWithShop(): array
    {
        $owner = User::factory()->owner()->create();

        $business = Business::create([
            'owner_id' => $owner->id,
            'name' => 'Colour Shop',
            'slug' => 'colour-shop',
            'public_booking_enabled' => true,
        ]);

        $owner->update(['business_id' => $business->id]);

        return [$owner->fresh(), $business];
    }
}
