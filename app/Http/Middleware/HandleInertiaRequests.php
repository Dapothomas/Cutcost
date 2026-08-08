<?php

namespace App\Http\Middleware;

use App\Enums\BookingStatus;
use App\Models\Booking;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $shop = $user?->shop();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'initials' => mb_strtoupper(mb_substr($user->name, 0, 1)),
                    'role' => $user->role->value,
                    'shop_name' => $shop?->name,
                    'booking_url' => $user->isOwner()
                        ? $user->ownedBusiness?->publicBookingUrl()
                        : null,
                    'payments_ready' => $user->isOwner()
                        ? (bool) $user->ownedBusiness?->canAcceptPayments()
                        : null,
                ] : null,
            ],
            'notifications' => fn () => $this->notificationsFor($user, $shop?->id),
            'theme' => [
                'primary_color' => $shop?->primary_color,
                'tokens' => $shop?->brandTheme(),
            ],
            'flash' => [
                'status' => fn () => $request->session()->get('status'),
            ],
            'bookingStatuses' => fn () => collect(BookingStatus::cases())
                ->map(fn (BookingStatus $status) => [
                    'value' => $status->value,
                    'label' => $status->label(),
                ])->values(),
        ];
    }

    /**
     * @return array{items: list<array<string, mixed>>, unread_count: int}
     */
    private function notificationsFor(mixed $user, ?int $businessId): array
    {
        if (! $user || ! $businessId) {
            return ['items' => [], 'unread_count' => 0];
        }

        $query = Booking::query()
            ->with(['client:id,name', 'service:id,name'])
            ->where('business_id', $businessId)
            ->where('starts_at', '>=', now())
            ->where('starts_at', '<=', now()->endOfDay())
            ->where('status', '!=', BookingStatus::Cancelled)
            ->orderBy('starts_at')
            ->limit(8);

        if ($user->isBarber()) {
            $query->where('barber_id', $user->id);
        }

        $items = $query->get()->map(fn (Booking $booking) => [
            'id' => $booking->id,
            'title' => $booking->client?->name
                ? "{$booking->client->name} · ".($booking->service?->name ?? 'Booking')
                : ($booking->service?->name ?? 'Upcoming booking'),
            'body' => $booking->starts_at?->timezone(config('app.timezone'))->format('g:i A'),
            'href' => $user->isOwner() ? '/business/bookings' : '/barber/bookings',
        ])->values()->all();

        return [
            'items' => $items,
            'unread_count' => count($items),
        ];
    }
}
