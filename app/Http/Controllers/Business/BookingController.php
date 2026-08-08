<?php

namespace App\Http\Controllers\Business;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class BookingController extends Controller
{
    public function index(Request $request): Response
    {
        $business = $request->user()->ownedBusiness;

        $bookings = $business->bookings()
            ->with(['client', 'service', 'barber'])
            ->latest('starts_at')
            ->paginate(20)
            ->through(fn (Booking $booking) => [
                'id' => $booking->id,
                'starts_at_label' => $booking->starts_at->format('D j M · H:i'),
                'client_name' => $booking->client->name,
                'service_name' => $booking->service->name,
                'barber_name' => $booking->barber->name,
                'status' => $booking->status->value,
            ]);

        return Inertia::render('Business/Bookings/Index', [
            'bookings' => $bookings,
        ]);
    }

    public function create(Request $request): Response
    {
        $business = $request->user()->ownedBusiness()->with([
            'clients' => fn ($q) => $q->orderBy('name'),
            'services' => fn ($q) => $q->where('is_active', true)->orderBy('name'),
            'barbers' => fn ($q) => $q->orderBy('name'),
        ])->firstOrFail();

        $assignableBarbers = $business->barbers;
        if ($assignableBarbers->isEmpty()) {
            $assignableBarbers = collect([$request->user()]);
        }

        return Inertia::render('Business/Bookings/Create', [
            'clients' => $business->clients->map(fn ($client) => [
                'id' => $client->id,
                'name' => $client->name,
            ]),
            'services' => $business->services->map(fn ($service) => [
                'id' => $service->id,
                'name' => $service->name,
                'duration_minutes' => $service->duration_minutes,
                'price_label' => '£'.number_format($service->price_cents / 100, 2),
            ]),
            'barbers' => $assignableBarbers->map(fn ($barber) => [
                'id' => $barber->id,
                'name' => $barber->name,
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $business = $request->user()->ownedBusiness;

        $data = $request->validate([
            'client_id' => ['required', Rule::exists('clients', 'id')->where('business_id', $business->id)],
            'service_id' => ['required', Rule::exists('services', 'id')->where('business_id', $business->id)],
            'barber_id' => [
                'required',
                Rule::exists('users', 'id')->where(function ($query) use ($business, $request) {
                    $query->where(function ($inner) use ($business) {
                        $inner->where('business_id', $business->id)
                            ->where('role', Role::Barber->value);
                    })->orWhere('id', $request->user()->id);
                }),
            ],
            'starts_at' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $service = Service::findOrFail($data['service_id']);
        $startsAt = Carbon::parse($data['starts_at']);

        $business->bookings()->create([
            'client_id' => $data['client_id'],
            'service_id' => $data['service_id'],
            'barber_id' => $data['barber_id'],
            'starts_at' => $startsAt,
            'ends_at' => $startsAt->copy()->addMinutes($service->duration_minutes),
            'status' => BookingStatus::Scheduled,
            'payment_status' => PaymentStatus::Waived,
            'amount_cents' => $service->price_cents,
            'notes' => $data['notes'] ?? null,
        ]);

        return redirect()->route('business.bookings.index')
            ->with('status', 'Appointment booked.');
    }

    public function updateStatus(Request $request, Booking $booking): RedirectResponse
    {
        $this->authorizeBooking($request, $booking);

        $data = $request->validate([
            'status' => ['required', Rule::enum(BookingStatus::class)],
        ]);

        $booking->update(['status' => $data['status']]);

        return back()->with('status', 'Appointment updated.');
    }

    public function destroy(Request $request, Booking $booking): RedirectResponse
    {
        $this->authorizeBooking($request, $booking);

        $booking->delete();

        return redirect()->route('business.bookings.index')
            ->with('status', 'Appointment removed.');
    }

    private function authorizeBooking(Request $request, Booking $booking): void
    {
        abort_unless($booking->business_id === $request->user()->ownedBusiness?->id, 404);
    }
}
