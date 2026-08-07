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

        $dayStart = $date->copy()->setTime(self::OPEN_HOUR, 0);
        $dayEnd = $date->copy()->setTime(self::CLOSE_HOUR, 0);
        $duration = $service->duration_minutes;

        $existing = $business->bookings()
            ->where('barber_id', $barber->id)
            ->whereDate('starts_at', $date)
            ->where('status', '!=', BookingStatus::Cancelled)
            ->get(['starts_at', 'ends_at']);

        $slots = [];
        $cursor = $dayStart->copy();

        while ($cursor->copy()->addMinutes($duration)->lte($dayEnd)) {
            $slotStart = $cursor->copy();
            $slotEnd = $cursor->copy()->addMinutes($duration);

            if ($slotStart->greaterThan(now()) && ! $this->overlaps($slotStart, $slotEnd, $existing)) {
                $slots[] = $slotStart->format('H:i');
            }

            $cursor->addMinutes(self::STEP_MINUTES);
        }

        return $slots;
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
