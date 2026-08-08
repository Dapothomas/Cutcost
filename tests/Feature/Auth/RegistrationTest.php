<?php

namespace Tests\Feature\Auth;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_shop_owners_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test Owner',
            'email' => 'owner@example.com',
            'phone' => '07700900111',
            'business_name' => 'Test Cuts',
            'city' => 'Manchester',
            'plan' => 'starter',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('business.dashboard', absolute: false));

        $user = User::where('email', 'owner@example.com')->first();
        $this->assertNotNull($user);
        $this->assertSame(Role::Owner, $user->role);
        $this->assertNotNull($user->ownedBusiness);
        $this->assertSame('Test Cuts', $user->ownedBusiness->name);
    }
}
