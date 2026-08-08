<?php

namespace App\Http\Controllers\Business;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Services\BookingRevenueService;
use App\Services\StripeConnectService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request, BookingRevenueService $revenue): Response
    {
        $business = $request->user()->ownedBusiness()->withCount([
            'services',
            'barbers',
            'clients',
            'bookings',
        ])->firstOrFail();

        if (filled($business->stripe_account_id) && ! $business->stripe_charges_enabled) {
            $business = app(StripeConnectService::class)->syncAccount($business);
        }

        $todaysBookings = $business->bookings()
            ->with(['client', 'service', 'barber'])
            ->whereDate('starts_at', today())
            ->whereNotIn('status', [BookingStatus::Cancelled, BookingStatus::PendingPayment])
            ->orderBy('starts_at')
            ->get()
            ->map(fn ($booking) => [
                'time' => $booking->starts_at->format('H:i'),
                'client_name' => $booking->client->name,
                'service_name' => $booking->service->name,
                'barber_name' => $booking->barber->name,
                'status' => $booking->status->label(),
            ]);

        return Inertia::render('Business/Dashboard', [
            'business' => [
                'name' => $business->name,
                'city' => $business->city,
                'clients_count' => $business->clients_count,
                'services_count' => $business->services_count,
                'barbers_count' => $business->barbers_count,
                'bookings_count' => $business->bookings_count,
                'public_booking_url' => $business->publicBookingUrl(),
                'payments_ready' => $business->canAcceptPayments(),
                'payments_bypassed' => StripeConnectService::shouldBypass(),
            ],
            'todaysBookings' => $todaysBookings,
            'todayLabel' => now()->format('l, j F'),
            'earningsPeriods' => $revenue->periodOptions(),
            'earningsByPeriod' => $revenue->panelByPeriod($business),
        ]);
    }
}
