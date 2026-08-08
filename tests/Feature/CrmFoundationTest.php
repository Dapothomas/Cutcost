<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrmFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_open_business_dashboard(): void
    {
        $owner = User::factory()->owner()->create();
        $business = Business::create([
            'owner_id' => $owner->id,
            'name' => 'Corner Cuts',
            'city' => 'Leeds',
        ]);
        $owner->update(['business_id' => $business->id]);

        $this->actingAs($owner)
            ->get(route('business.dashboard'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Business/Dashboard')
                ->where('business.name', 'Corner Cuts')
            );
    }

    public function test_barber_can_open_schedule(): void
    {
        $owner = User::factory()->owner()->create();
        $business = Business::create([
            'owner_id' => $owner->id,
            'name' => 'Corner Cuts',
        ]);

        $barber = User::factory()->barber()->create([
            'business_id' => $business->id,
        ]);

        $this->actingAs($barber)
            ->get(route('barber.dashboard'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Barber/Dashboard')
                ->has('todaysBookings')
            );
    }

    public function test_barber_cannot_access_business_area(): void
    {
        $barber = User::factory()->barber()->create([
            'role' => Role::Barber,
        ]);

        $this->actingAs($barber)
            ->get(route('business.dashboard'))
            ->assertForbidden();
    }

    public function test_owner_can_add_a_client(): void
    {
        $owner = User::factory()->owner()->create();
        $business = Business::create([
            'owner_id' => $owner->id,
            'name' => 'Corner Cuts',
        ]);
        $owner->update(['business_id' => $business->id]);

        $this->actingAs($owner)
            ->post(route('business.clients.store'), [
                'name' => 'Pat Client',
                'email' => 'pat@example.com',
                'phone' => '07700900999',
                'notes' => 'Regular fade',
            ])
            ->assertRedirect(route('business.clients.index'));

        $this->assertDatabaseHas('clients', [
            'business_id' => $business->id,
            'name' => 'Pat Client',
        ]);
    }
}
