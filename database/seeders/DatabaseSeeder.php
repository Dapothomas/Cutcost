<?php

namespace Database\Seeders;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Enums\Role;
use App\Models\Booking;
use App\Models\Business;
use App\Models\Client;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::create([
            'name' => 'Shop Owner',
            'email' => 'owner@cutcost.test',
            'password' => Hash::make('password'),
            'role' => Role::Owner,
            'phone' => '07700900123',
            'email_verified_at' => now(),
            'subscription_plan' => \App\Enums\SubscriptionPlan::Shop,
            'subscription_status' => \App\Enums\SubscriptionStatus::Active,
        ]);

        $business = Business::create([
            'owner_id' => $owner->id,
            'name' => 'Cutcost Demo Barbers',
            'slug' => 'cutcost-demo-barbers',
            'phone' => '07700900123',
            'city' => 'London',
            'address' => '12 High Street',
            'public_booking_enabled' => true,
        ]);


        $owner->update(['business_id' => $business->id]);

        $barber = User::create([
            'name' => 'Alex Barber',
            'email' => 'barber@cutcost.test',
            'password' => Hash::make('password'),
            'role' => Role::Barber,
            'phone' => '07700900456',
            'business_id' => $business->id,
            'email_verified_at' => now(),
        ]);

        $fade = Service::create([
            'business_id' => $business->id,
            'name' => 'Skin fade',
            'duration_minutes' => 45,
            'price_cents' => 2500,
            'is_active' => true,
        ]);

        Service::create([
            'business_id' => $business->id,
            'name' => 'Beard trim',
            'duration_minutes' => 20,
            'price_cents' => 1200,
            'is_active' => true,
        ]);

        $client = Client::create([
            'business_id' => $business->id,
            'name' => 'Jordan Lee',
            'email' => 'jordan@example.com',
            'phone' => '07700900789',
            'notes' => 'Prefers mid fade, low skin.',
        ]);

        Client::create([
            'business_id' => $business->id,
            'name' => 'Sam Rivera',
            'phone' => '07700900000',
        ]);

        $startsAt = now()->setTime(11, 0);

        Booking::create([
            'business_id' => $business->id,
            'client_id' => $client->id,
            'barber_id' => $barber->id,
            'service_id' => $fade->id,
            'starts_at' => $startsAt,
            'ends_at' => $startsAt->copy()->addMinutes(45),
            'status' => BookingStatus::Scheduled,
            'payment_status' => PaymentStatus::Paid,
            'amount_cents' => $fade->price_cents,
        ]);
    }
}
