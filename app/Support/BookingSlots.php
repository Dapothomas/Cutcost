<?php

namespace App\Support;

use App\Enums\BookingStatus;
use App\Models\Business;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class BookingSlots
{
    public const OPEN_HOUR = 9;

    public const CLOSE_HOUR = 18;

    public const STEP_MINUTES = 15;

    /**
     * @return list<string> Times in H:i format
     */
    public function for(Business $business, Service $service, User $barber, CarbonInterface $date): array
    {
        if ($date->isBefore(today())) {
            return [];
        }

        $horizon = max(1, (int) ($business->booking_horizon_days ?: 60));
        if ($date->greaterThan(today()->addDays($horizon))) {
            return [];
        }

        $ranges = $business->openingRangesFor($date);
        if ($ranges === []) {
            return [];
        }

        $duration = $service->duration_minutes;
        $step = max(5, (int) ($business->slot_interval_minutes ?: self::STEP_MINUTES));
        $leadMinutes = max(0, (int) ($business->booking_lead_minutes ?: 0));
        $earliest = now()->addMinutes($leadMinutes);

        $existing = $business->bookings()
            ->where('barber_id', $barber->id)
            ->whereDate('starts_at', $date)
            ->where('status', '!=', BookingStatus::Cancelled)
            ->get(['starts_at', 'ends_at']);

        $slots = [];

        foreach ($ranges as $range) {
            [$openHour, $openMinute] = array_map('intval', explode(':', $range['open']));
            [$closeHour, $closeMinute] = array_map('intval', explode(':', $range['close']));

            $dayStart = $date->copy()->setTime($openHour, $openMinute);
            $dayEnd = $date->copy()->setTime($closeHour, $closeMinute);
            $cursor = $dayStart->copy();

            while ($cursor->copy()->addMinutes($duration)->lte($dayEnd)) {
                $slotStart = $cursor->copy();
                $slotEnd = $cursor->copy()->addMinutes($duration);

                if ($slotStart->greaterThanOrEqualTo($earliest) && ! $this->overlaps($slotStart, $slotEnd, $existing)) {
                    $slots[] = $slotStart->format('H:i');
                }

                $cursor->addMinutes($step);
            }
        }

        return array_values(array_unique($slots));
    }

    /**
     * @param  Collection<int, User>  $barbers
     */
    public function firstAvailableBarber(
        Business $business,
        Service $service,
        Collection $barbers,
        CarbonInterface $date,
        string $time,
    ): ?User {
        foreach ($barbers as $barber) {
            if (in_array($time, $this->for($business, $service, $barber, $date), true)) {
                return $barber;
            }
        }

        return null;
    }

    /**
     * @param  Collection<int, object>  $existing
     */
    private function overlaps(Carbon $start, Carbon $end, Collection $existing): bool
    {
        foreach ($existing as $booking) {
            $bookingStart = Carbon::parse($booking->starts_at);
            $bookingEnd = Carbon::parse($booking->ends_at);

            if ($start->lt($bookingEnd) && $end->gt($bookingStart)) {
                return true;
            }
        }

        return false;
    }
}
