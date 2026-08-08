<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\Business;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

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
     * Dashboard earnings panel data for every period (client-side switching).
     *
     * @return array<string, array{
     *     summary: array{period: string, label: string, amount_cents: int, amount_label: string, paid_bookings_count: int},
     *     series: list<array{label: string, amount_cents: int, amount_label: string}>,
     *     breakdown: list<array{label: string, amount_cents: int, amount_label: string, percent: float}>
     * }>
     */
    public function panelByPeriod(Business $business): array
    {
        $rows = Booking::query()
            ->where('business_id', $business->id)
            ->where('payment_status', PaymentStatus::Paid)
            ->where('status', '!=', BookingStatus::Cancelled)
            ->with(['service:id,name'])
            ->get(['id', 'service_id', 'amount_cents', 'updated_at']);

        $panel = [];

        foreach (self::PERIODS as $period) {
            $filtered = $rows->filter(fn (Booking $booking) => $this->fallsInPeriod($booking->updated_at, $period));
            $cents = (int) $filtered->sum('amount_cents');

            $panel[$period] = [
                'summary' => [
                    'period' => $period,
                    'label' => $this->periodLabel($period),
                    'amount_cents' => $cents,
                    'amount_label' => $this->formatMoney($cents),
                    'paid_bookings_count' => $filtered->count(),
                ],
                'series' => $this->buildSeries($filtered, $period),
                'breakdown' => $this->buildBreakdown($filtered),
            ];
        }

        return $panel;
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

    private function fallsInPeriod(CarbonInterface $moment, string $period): bool
    {
        return match ($period) {
            'today' => $moment->isSameDay(today()),
            'week' => $moment->greaterThanOrEqualTo(now()->startOfWeek()),
            'month' => $moment->greaterThanOrEqualTo(now()->startOfMonth()),
            'year' => $moment->greaterThanOrEqualTo(now()->startOfYear()),
            default => true,
        };
    }

    /**
     * @param  Collection<int, Booking>  $bookings
     * @return list<array{label: string, amount_cents: int, amount_label: string}>
     */
    private function buildSeries(Collection $bookings, string $period): array
    {
        $buckets = match ($period) {
            'today' => $this->hourlyBuckets(),
            'week' => $this->dailyBuckets(now()->startOfWeek(), 7, 'D'),
            'month' => $this->dailyBuckets(now()->startOfMonth(), (int) now()->daysInMonth, 'j'),
            'year' => $this->monthlyBuckets((int) now()->month, now()->startOfYear()),
            'all' => $this->monthlyBuckets(12),
            default => $this->dailyBuckets(now()->startOfMonth(), (int) now()->daysInMonth, 'j'),
        };

        foreach ($bookings as $booking) {
            $key = match ($period) {
                'today' => $booking->updated_at->format('Y-m-d H'),
                'week', 'month' => $booking->updated_at->format('Y-m-d'),
                'year', 'all' => $booking->updated_at->format('Y-m'),
                default => $booking->updated_at->format('Y-m-d'),
            };

            if (! array_key_exists($key, $buckets)) {
                continue;
            }

            $buckets[$key]['amount_cents'] += (int) $booking->amount_cents;
        }

        return collect($buckets)
            ->map(fn (array $bucket) => [
                'label' => $bucket['label'],
                'amount_cents' => $bucket['amount_cents'],
                'amount_label' => $this->formatMoney($bucket['amount_cents']),
            ])
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, Booking>  $bookings
     * @return list<array{label: string, amount_cents: int, amount_label: string, percent: float}>
     */
    private function buildBreakdown(Collection $bookings): array
    {
        $total = (int) $bookings->sum('amount_cents');

        if ($total === 0) {
            return [];
        }

        return $bookings
            ->groupBy(fn (Booking $booking) => $booking->service?->name ?: 'Service')
            ->map(function (Collection $group, string $label) use ($total) {
                $cents = (int) $group->sum('amount_cents');

                return [
                    'label' => $label,
                    'amount_cents' => $cents,
                    'amount_label' => $this->formatMoney($cents),
                    'percent' => round(($cents / $total) * 100, 1),
                ];
            })
            ->sortByDesc('amount_cents')
            ->values()
            ->take(6)
            ->all();
    }

    /**
     * @return array<string, array{label: string, amount_cents: int}>
     */
    private function hourlyBuckets(): array
    {
        $buckets = [];
        $day = today();

        for ($hour = 8; $hour <= 20; $hour++) {
            $moment = $day->copy()->setTime($hour, 0);
            $buckets[$moment->format('Y-m-d H')] = [
                'label' => $moment->format('ga'),
                'amount_cents' => 0,
            ];
        }

        return $buckets;
    }

    /**
     * @return array<string, array{label: string, amount_cents: int}>
     */
    private function dailyBuckets(CarbonInterface $start, int $count, string $labelFormat): array
    {
        $buckets = [];

        for ($i = 0; $i < $count; $i++) {
            $day = $start->copy()->addDays($i);
            $buckets[$day->format('Y-m-d')] = [
                'label' => $day->format($labelFormat),
                'amount_cents' => 0,
            ];
        }

        return $buckets;
    }

    /**
     * @return array<string, array{label: string, amount_cents: int}>
     */
    private function monthlyBuckets(int $count, ?CarbonInterface $start = null): array
    {
        $buckets = [];
        $cursor = $start?->copy()->startOfMonth()
            ?? now()->startOfMonth()->subMonths(max($count - 1, 0));

        for ($i = 0; $i < $count; $i++) {
            $month = $cursor->copy()->addMonths($i);
            $buckets[$month->format('Y-m')] = [
                'label' => $month->format('M'),
                'amount_cents' => 0,
            ];
        }

        return $buckets;
    }
}
