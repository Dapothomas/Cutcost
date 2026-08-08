<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\Business;
use Illuminate\Database\Eloquent\Builder;

class BookingRevenueService
{
    /** @var list<string> */
    public const PERIODS = ['today', 'week', 'month', 'year', 'all'];

    /**
     * @return array<string, array{amount_cents: int, amount_label: string, paid_bookings_count: int}>
     */
    public function summary(Business $business): array
    {
        return [
            'today' => $this->forPeriod($business, 'today'),
            'month' => $this->forPeriod($business, 'month'),
            'all_time' => $this->forPeriod($business, 'all'),
        ];
    }

    /**
     * @return array{
     *     period: string,
     *     label: string,
     *     amount_cents: int,
     *     amount_label: string,
     *     paid_bookings_count: int
     * }
     */
    public function forPeriod(Business $business, string $period): array
    {
        $period = $this->normalizePeriod($period);
        $cents = (int) $this->paidBookingsQuery($business, $period)->sum('amount_cents');

        return [
            'period' => $period,
            'label' => $this->periodLabel($period),
            'amount_cents' => $cents,
            'amount_label' => $this->formatMoney($cents),
            'paid_bookings_count' => $this->paidBookingsQuery($business, $period)->count(),
        ];
    }

    /**
     * @return list<array{client_name: string, service_name: string, amount_label: string, paid_at_label: string}>
     */
    public function recentPaidBookings(Business $business, string $period, int $limit = 10): array
    {
        $period = $this->normalizePeriod($period);

        return $this->paidBookingsQuery($business, $period)
            ->with(['client', 'service'])
            ->latest('updated_at')
            ->limit($limit)
            ->get()
            ->map(fn ($booking) => [
                'client_name' => $booking->client->name,
                'service_name' => $booking->service->name,
                'amount_label' => $this->formatMoney($booking->amount_cents ?? 0),
                'paid_at_label' => $booking->updated_at->format('D j M · H:i'),
            ])
            ->all();
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public function periodOptions(): array
    {
        return collect(self::PERIODS)
            ->map(fn (string $period) => [
                'value' => $period,
                'label' => $this->periodLabel($period),
            ])
            ->all();
    }

    public function normalizePeriod(string $period): string
    {
        return in_array($period, self::PERIODS, true) ? $period : 'month';
    }

    public function formatMoney(int $cents): string
    {
        return '£'.number_format($cents / 100, 2);
    }

    private function periodLabel(string $period): string
    {
        return match ($period) {
            'today' => 'Today',
            'week' => 'This week',
            'month' => 'This month',
            'year' => 'This year',
            'all' => 'All time',
        };
    }

    private function paidBookingsQuery(Business $business, string $period): Builder
    {
        $query = Booking::query()
            ->where('business_id', $business->id)
            ->where('payment_status', PaymentStatus::Paid)
            ->where('status', '!=', BookingStatus::Cancelled);

        return match ($period) {
            'today' => $query->whereDate('updated_at', today()),
            'week' => $query->where('updated_at', '>=', now()->startOfWeek()),
            'month' => $query->where('updated_at', '>=', now()->startOfMonth()),
            'year' => $query->where('updated_at', '>=', now()->startOfYear()),
            default => $query,
        };
    }
}
