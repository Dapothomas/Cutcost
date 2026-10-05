<?php

namespace Tests\Feature\Auth;

use App\Enums\Role;
use App\Enums\SubscriptionStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
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
        $this->assertSame('stripe', $user->ownedBusiness->payment_provider);
    }

    public function test_a_uk_city_stays_on_stripe_even_from_nigeria(): void
    {
        $this->withHeaders(['CF-IPCountry' => 'NG'])->post('/register', [
            'name' => 'UK Owner',
            'email' => 'mk@example.com',
            'phone' => '07700900112',
            'business_name' => 'MK Cuts',
            'city' => 'Milton Keynes',
            'plan' => 'starter',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $user = User::where('email', 'mk@example.com')->first();
        $this->assertSame('stripe', $user->ownedBusiness->payment_provider);
    }

    public function test_nigerian_shops_are_assigned_bachs(): void
    {
        $response = $this->withHeaders(['CF-IPCountry' => 'NG'])->post('/register', [
            'name' => 'Lagos Owner',
            'email' => 'lagos@example.com',
            'phone' => '08030000000',
            'business_name' => 'Lagos Cuts',
            'city' => 'Lagos',
            'plan' => 'shop',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('business.dashboard', absolute: false));

        $user = User::where('email', 'lagos@example.com')->first();
        $this->assertSame('bachs', $user->ownedBusiness->payment_provider);
    }

    public function test_nigerian_signup_skips_bachs_when_subscription_bypass_is_on(): void
    {
        config([
            'bachs.secret' => 'sk_live_test',
            'bachs.bypass_subscription' => true,
        ]);

        Http::fake();

        $response = $this->withHeaders(['CF-IPCountry' => 'NG'])->post('/register', [
            'name' => 'Lagos Owner',
            'email' => 'lagos-bypass@example.com',
            'phone' => '08030000001',
            'business_name' => 'Lagos Bypass',
            'city' => 'Lagos',
            'plan' => 'shop',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('business.dashboard', absolute: false));

        $user = User::where('email', 'lagos-bypass@example.com')->first();
        $this->assertSame(SubscriptionStatus::Active, $user->subscription_status);
        Http::assertNothingSent();
    }
}
