<?php

namespace App\Http\Controllers\Barber;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        $todaysBookings = $user->bookings()
            ->with(['client', 'service'])
            ->whereDate('starts_at', today())
            ->where('status', '!=', BookingStatus::Cancelled)
            ->orderBy('starts_at')
            ->get();

        $upcomingCount = $user->bookings()
            ->where('starts_at', '>', now())
            ->where('status', BookingStatus::Scheduled)
            ->count();

        $clientsSeen = $user->bookings()
            ->where('status', BookingStatus::Completed)
            ->distinct('client_id')
            ->count('client_id');

        return view('barber.dashboard', [
            'business' => $user->business,
            'todaysBookings' => $todaysBookings,
            'upcomingCount' => $upcomingCount,
            'clientsSeen' => $clientsSeen,
        ]);
    }
}
