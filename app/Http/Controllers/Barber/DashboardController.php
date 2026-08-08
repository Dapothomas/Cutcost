<?php

namespace App\Http\Controllers\Barber;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();

        $todaysBookings = $user->bookings()
            ->with(['client', 'service'])
            ->whereDate('starts_at', today())
            ->where('status', '!=', BookingStatus::Cancelled)
            ->orderBy('starts_at')
            ->get()
            ->map(fn ($booking) => [
                'id' => $booking->id,
                'time' => $booking->starts_at->format('H:i'),
                'client_name' => $booking->client->name,
                'service_name' => $booking->service->name,
                'status' => $booking->status->value,
                'status_label' => $booking->status->label(),
            ]);

        $upcomingCount = $user->bookings()
            ->where('starts_at', '>', now())
            ->where('status', BookingStatus::Scheduled)
            ->count();

        $clientsSeen = $user->bookings()
            ->where('status', BookingStatus::Completed)
            ->distinct('client_id')
            ->count('client_id');

        return Inertia::render('Barber/Dashboard', [
            'businessName' => $user->business?->name ?? 'Your shop',
            'todayLabel' => now()->format('l, j F'),
            'todaysBookings' => $todaysBookings,
            'upcomingCount' => $upcomingCount,
            'clientsSeen' => $clientsSeen,
        ]);
    }
}
