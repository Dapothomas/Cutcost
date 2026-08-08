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
     * @return array{items: list<array<string, mixed>>, unread_count: int, see_all_href: ?string}
     */
    private function notificationsFor(mixed $user, ?int $businessId): array
    {
        if (! $user || ! $businessId) {
            return ['items' => [], 'unread_count' => 0, 'see_all_href' => null];
        }

        if ($user->isOwner()) {
            return $this->ownerNotifications($user);
        }

        return $this->barberScheduleNotifications($user, $businessId);
    }

    /**
     * @return array{items: list<array<string, mixed>>, unread_count: int, see_all_href: string}
     */
    private function ownerNotifications(mixed $user): array
    {
        $items = $user->notifications()
            ->latest()
            ->limit(8)
            ->get()
            ->map(function ($notification) {
                $data = $notification->data;

                return [
                    'id' => $notification->id,
                    'title' => $data['title'] ?? 'Notification',
                    'body' => $data['body'] ?? '',
                    'href' => '/business/notifications/'.$notification->id.'/read',
                    'read' => $notification->read_at !== null,
                    'created_at_label' => $notification->created_at?->timezone(config('app.timezone'))->diffForHumans(),
                ];
            })
            ->values()
            ->all();

        return [
            'items' => $items,
            'unread_count' => $user->unreadNotifications()->count(),
            'see_all_href' => '/business/notifications',
        ];
    }

    /**
     * @return array{items: list<array<string, mixed>>, unread_count: int, see_all_href: null}
     */
    private function barberScheduleNotifications(mixed $user, int $businessId): array
    {
        $items = Booking::query()
            ->with(['client:id,name', 'service:id,name'])
            ->where('business_id', $businessId)
            ->where('barber_id', $user->id)
            ->where('starts_at', '>=', now())
            ->where('starts_at', '<=', now()->endOfDay())
            ->where('status', '!=', BookingStatus::Cancelled)
            ->orderBy('starts_at')
            ->limit(8)
            ->get()
            ->map(fn (Booking $booking) => [
                'id' => $booking->id,
                'title' => $booking->client?->name
                    ? "{$booking->client->name} · ".($booking->service?->name ?? 'Booking')
                    : ($booking->service?->name ?? 'Upcoming booking'),
                'body' => $booking->starts_at?->timezone(config('app.timezone'))->format('g:i A'),
                'href' => '/barber/bookings',
                'read' => true,
                'created_at_label' => null,
            ])
            ->values()
            ->all();

        return [
            'items' => $items,
            'unread_count' => count($items),
            'see_all_href' => null,
        ];
    }
}
