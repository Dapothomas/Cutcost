<?php

namespace App\Support;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Business;
use App\Models\Client;
use App\Models\User;
use App\Notifications\ShopAlert;
use Illuminate\Support\Facades\Notification;

class ShopNotifier
{
    public static function owner(Business $business, string $title, string $body, ?string $href = null, string $type = 'info'): void
    {
        $owner = $business->owner_id
            ? User::query()->find($business->owner_id)
            : $business->owner()->first();

        if (! $owner) {
            return;
        }

        Notification::send($owner, new ShopAlert($title, $body, $href, $type));
    }

    public static function bookingCreated(Booking $booking, string $source = 'shop'): void
    {
        $booking->loadMissing(['client:id,name', 'service:id,name', 'barber:id,name', 'business']);

        $business = $booking->business;
        if (! $business) {
            return;
        }

        $client = $booking->client?->name ?? 'A client';
        $service = $booking->service?->name ?? 'appointment';
        $when = $booking->starts_at?->timezone(config('app.timezone'))->format('D j M · g:i A') ?? '';

        $prefix = match (true) {
            $booking->status === BookingStatus::PendingPayment => 'Booking awaiting payment',
            $source === 'public' => 'New online booking',
            default => 'New booking',
        };

        self::owner(
            $business,
            "{$prefix}: {$client}",
            trim("{$service} with ".($booking->barber?->name ?? 'stylist').($when ? " · {$when}" : '')),
            '/business/bookings',
            'booking_created',
        );
    }

    public static function bookingPaid(Booking $booking): void
    {
        $booking->loadMissing(['client:id,name', 'service:id,name', 'business']);

        $business = $booking->business;
        if (! $business) {
            return;
        }

        $amount = $booking->amount_cents
            ? '£'.number_format($booking->amount_cents / 100, 2)
            : 'Payment';

        self::owner(
            $business,
            "Payment received · {$amount}",
            ($booking->client?->name ?? 'Client').' paid for '.($booking->service?->name ?? 'their booking'),
            '/business/payments',
            'booking_paid',
        );
    }

    public static function bookingCancelled(Booking $booking, string $reason = 'cancelled'): void
    {
        $booking->loadMissing(['client:id,name', 'service:id,name', 'business']);

        $business = $booking->business;
        if (! $business) {
            return;
        }

        $when = $booking->starts_at?->timezone(config('app.timezone'))->format('D j M · g:i A') ?? '';

        self::owner(
            $business,
            'Booking '.$reason,
            ($booking->client?->name ?? 'Client').' · '.($booking->service?->name ?? 'appointment').($when ? " · {$when}" : ''),
            '/business/bookings',
            'booking_cancelled',
        );
    }

    public static function bookingStatusChanged(Booking $booking, string $statusLabel): void
    {
        $booking->loadMissing(['client:id,name', 'service:id,name', 'business']);

        $business = $booking->business;
        if (! $business) {
            return;
        }

        self::owner(
            $business,
            "Booking marked {$statusLabel}",
            ($booking->client?->name ?? 'Client').' · '.($booking->service?->name ?? 'appointment'),
            '/business/bookings',
            'booking_status',
        );
    }

    public static function clientAdded(Business $business, Client $client): void
    {
        self::owner(
            $business,
            'New client added',
            $client->name.($client->phone ? " · {$client->phone}" : ''),
            '/business/clients',
            'client_created',
        );
    }

    public static function stylistAdded(Business $business, User $stylist): void
    {
        self::owner(
            $business,
            'Stylist added to your team',
            $stylist->name,
            '/business/staff',
            'stylist_created',
        );
    }
}
