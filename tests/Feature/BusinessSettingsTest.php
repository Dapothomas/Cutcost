<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_update_primary_colour(): void
    {
        [$owner, $business] = $this->ownerWithShop();

        $this->actingAs($owner)
            ->patch(route('business.settings.update'), [
                'primary_color' => '#0f766e',
            ])
            ->assertRedirect();

        $this->assertSame('#0F766E', $business->fresh()->primary_color);
    }

    public function test_owner_can_reset_primary_colour(): void
    {
        [$owner, $business] = $this->ownerWithShop();
        $business->update(['primary_color' => '#BE123C']);

        $this->actingAs($owner)
            ->patch(route('business.settings.update'), [
                'primary_color' => null,
            ])
            ->assertRedirect();

        $this->assertNull($business->fresh()->primary_color);
    }

    public function test_settings_page_includes_current_colour(): void
    {
        [$owner, $business] = $this->ownerWithShop();
        $business->update(['primary_color' => '#7C3AED']);

        $this->actingAs($owner)
            ->get(route('business.settings.edit'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Business/Settings/Edit')
                ->where('business.primary_color', '#7C3AED')
                ->has('presets')
            );
    }

    public function test_theme_tokens_are_shared_with_inertia(): void
    {
        [$owner, $business] = $this->ownerWithShop();
        $business->update(['primary_color' => '#0F766E']);

        $this->actingAs($owner)
            ->get(route('business.dashboard'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('theme.primary_color', '#0F766E')
                ->has('theme.tokens.primary')
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
