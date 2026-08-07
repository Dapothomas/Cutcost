<?php

namespace App\Http\Controllers\Barber;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function index(Request $request): View
    {
        $bookings = $request->user()->bookings()
            ->with(['client', 'service'])
            ->latest('starts_at')
            ->paginate(20);

        return view('barber.bookings.index', compact('bookings'));
    }

    public function updateStatus(Request $request, Booking $booking): RedirectResponse
    {
        abort_unless($booking->barber_id === $request->user()->id, 404);

        $data = $request->validate([
            'status' => ['required', Rule::enum(BookingStatus::class)],
        ]);

        $booking->update(['status' => $data['status']]);

        return back()->with('status', 'Appointment updated.');
    }
}
