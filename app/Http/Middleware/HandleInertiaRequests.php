<?php

namespace App\Http\Middleware;

use App\Enums\BookingStatus;
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

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role->value,
                    'shop_name' => $user->shop()?->name,
                    'booking_url' => $user->isOwner()
                        ? $user->ownedBusiness?->publicBookingUrl()
                        : null,
                    'payments_ready' => $user->isOwner()
                        ? (bool) $user->ownedBusiness?->canAcceptPayments()
                        : null,
                ] : null,
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
}
