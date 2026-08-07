<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Models\Business;
use App\Models\Client;
use App\Models\Service;
use App\Support\BookingSlots;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PublicBookingController extends Controller
{
    public function show(Request $request, Business $business, BookingSlots $slots): View
    {
        abort_unless($business->public_booking_enabled, 404);

        $services = $business->services()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $barbers = $business->bookableStaff();

        $selectedService = $services->firstWhere('id', (int) $request->query('service_id'))
            ?? $services->first();

        $selectedBarberId = $request->query('barber_id', 'any');
        $selectedDate = $request->query('date', now()->addDay()->toDateString());

        try {
            $date = Carbon::parse($selectedDate)->startOfDay();
        } catch (\Throwable) {
            $date = now()->addDay()->startOfDay();
            $selectedDate = $date->toDateString();
        }

        $availableSlots = [];

        if ($selectedService) {
            if ($selectedBarberId === 'any') {
                $union = [];
                foreach ($barbers as $barber) {
                    $union = array_merge($union, $slots->for($business, $selectedService, $barber, $date));
                }
                $availableSlots = collect($union)->unique()->sort()->values()->all();
            } else {
                $barber = $barbers->firstWhere('id', (int) $selectedBarberId);
                if ($barber) {
                    $availableSlots = $slots->for($business, $selectedService, $barber, $date);
                }
            }
        }

        return view('public.booking', [
            'business' => $business,
            'services' => $services,
            'barbers' => $barbers,
            'selectedService' => $selectedService,
            'selectedBarberId' => $selectedBarberId,
            'selectedDate' => $selectedDate,
            'availableSlots' => $availableSlots,
        ]);
    }

    public function store(Request $request, Business $business, BookingSlots $slots): RedirectResponse
    {
        abort_unless($business->public_booking_enabled, 404);

        $barbers = $business->bookableStaff();
        $barberIds = $barbers->pluck('id')->all();

        $data = $request->validate([
            'service_id' => ['required', Rule::exists('services', 'id')->where(fn ($q) => $q->where('business_id', $business->id)->where('is_active', true))],
            'barber_id' => ['required'],
            'date' => ['required', 'date', 'after_or_equal:today'],
            'time' => ['required', 'date_format:H:i'],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $service = Service::query()
            ->where('business_id', $business->id)
            ->where('is_active', true)
            ->findOrFail($data['service_id']);

        $date = Carbon::parse($data['date'])->startOfDay();
        $startsAt = Carbon::parse($data['date'].' '.$data['time']);

        if ($startsAt->lessThanOrEqualTo(now())) {
            throw ValidationException::withMessages([
                'time' => 'Please choose a future time.',
            ]);
        }

        if ($data['barber_id'] === 'any') {
            $barber = $slots->firstAvailableBarber($business, $service, $barbers, $date, $data['time']);
        } else {
            if (! in_array((int) $data['barber_id'], $barberIds, true)) {
                throw ValidationException::withMessages([
                    'barber_id' => 'That barber is not available.',
                ]);
            }

            $barber = $barbers->firstWhere('id', (int) $data['barber_id']);

            if (! in_array($data['time'], $slots->for($business, $service, $barber, $date), true)) {
                $barber = null;
            }
        }

        if (! $barber) {
            throw ValidationException::withMessages([
                'time' => 'That slot is no longer available. Pick another time.',
            ]);
        }

        $booking = DB::transaction(function () use ($business, $data, $service, $barber, $startsAt) {
            $client = $this->findOrCreateClient($business, $data);

            return $business->bookings()->create([
                'client_id' => $client->id,
                'service_id' => $service->id,
                'barber_id' => $barber->id,
                'starts_at' => $startsAt,
                'ends_at' => $startsAt->copy()->addMinutes($service->duration_minutes),
                'status' => BookingStatus::Scheduled,
                'notes' => $data['notes'] ?? null,
            ]);
        });

        return redirect()
            ->route('public.booking.confirmation', [$business, $booking])
            ->with('status', 'Your appointment is booked.');
    }

    public function confirmation(Business $business, \App\Models\Booking $booking): View
    {
        abort_unless($business->public_booking_enabled, 404);
        abort_unless($booking->business_id === $business->id, 404);

        $booking->load(['client', 'service', 'barber']);

        return view('public.booking-confirmation', [
            'business' => $business,
            'booking' => $booking,
        ]);
    }

    /**
     * @param  array{name: string, phone: string, email?: string|null, notes?: string|null}  $data
     */
    private function findOrCreateClient(Business $business, array $data): Client
    {
        if (! empty($data['email'])) {
            $existing = $business->clients()->where('email', $data['email'])->first();

            if ($existing) {
                $existing->update([
                    'name' => $data['name'],
                    'phone' => $data['phone'],
                ]);

                return $existing;
            }
        }

        $existing = $business->clients()->where('phone', $data['phone'])->first();

        if ($existing) {
            $existing->update([
                'name' => $data['name'],
                'email' => $data['email'] ?? $existing->email,
            ]);

            return $existing;
        }

        return $business->clients()->create([
            'name' => $data['name'],
            'phone' => $data['phone'],
            'email' => $data['email'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);
    }
}

