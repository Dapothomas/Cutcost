<?php

namespace Database\Factories;

use App\Enums\Role;
use App\Enums\SubscriptionPlan;
use App\Enums\SubscriptionStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'role' => Role::Owner,
            'phone' => null,
            'business_id' => null,
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function owner(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => Role::Owner,
            'subscription_plan' => SubscriptionPlan::Shop,
            'subscription_status' => SubscriptionStatus::Active,
        ]);
    }

    public function barber(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => Role::Barber,
        ]);
    }
}
