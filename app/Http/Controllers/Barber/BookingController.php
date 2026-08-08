<?php

namespace App\Http\Controllers\Barber;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class BookingController extends Controller
{
    public function index(Request $request): Response
    {
        $bookings = $request->user()->bookings()
            ->with(['client', 'service'])
            ->latest('starts_at')
            ->paginate(20)
            ->through(fn (Booking $booking) => [
                'id' => $booking->id,
                'starts_at_label' => $booking->starts_at->format('D j M · H:i'),
                'client_name' => $booking->client->name,
                'service_name' => $booking->service->name,
                'status' => $booking->status->value,
            ]);

        return Inertia::render('Barber/Bookings/Index', [
            'bookings' => $bookings,
        ]);
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
