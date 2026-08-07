<?php

namespace App\Http\Controllers\Business;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $business = $request->user()->ownedBusiness()->withCount([
            'services',
            'barbers',
            'clients',
            'bookings',
        ])->firstOrFail();

        $todaysBookings = $business->bookings()
            ->with(['client', 'service', 'barber'])
            ->whereDate('starts_at', today())
            ->where('status', '!=', BookingStatus::Cancelled)
            ->orderBy('starts_at')
            ->get();

        return view('business.dashboard', [
            'business' => $business,
            'todaysBookings' => $todaysBookings,
        ]);
    }
}
