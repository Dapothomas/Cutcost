<?php

namespace App\Models;

use App\Enums\Role;
use App\Support\BrandColor;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

#[Fillable([
    'owner_id',
    'name',
    'slug',
    'phone',
    'city',
    'address',
    'public_booking_enabled',
    'primary_color',
    'opening_hours',
    'slot_interval_minutes',
    'booking_lead_minutes',
    'booking_horizon_days',
    'stripe_account_id',
    'stripe_charges_enabled',
    'stripe_payouts_enabled',
    'stripe_onboarding_completed_at',
])]
class Business extends Model
{
    /** @var list<string> */
    public const WEEKDAYS = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];

    protected function casts(): array
    {
        return [
            'public_booking_enabled' => 'boolean',
            'stripe_charges_enabled' => 'boolean',
            'stripe_payouts_enabled' => 'boolean',
            'stripe_onboarding_completed_at' => 'datetime',
            'opening_hours' => 'array',
            'slot_interval_minutes' => 'integer',
            'booking_lead_minutes' => 'integer',
            'booking_horizon_days' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Business $business): void {
            if (blank($business->slug)) {
                $business->slug = static::generateUniqueSlug($business->name);
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public static function generateUniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'shop';
        $slug = $base;
        $i = 2;

        while (static::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i;
            $i++;
        }

        return $slug;
    }

    /**
     * @return array<string, list<array{open: string, close: string}>>
     */
    public static function defaultOpeningHours(): array
    {
        $day = [['open' => '09:00', 'close' => '18:00']];

        return [
            'mon' => $day,
            'tue' => $day,
            'wed' => $day,
            'thu' => $day,
            'fri' => $day,
            'sat' => $day,
            'sun' => $day,
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function barbers(): HasMany
    {
        return $this->hasMany(User::class)->where('role', Role::Barber->value);
    }

    public function staff(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * @return Collection<int, User>
     */
    public function bookableStaff(): Collection
    {
        $barbers = $this->barbers()->orderBy('name')->get();

        if ($barbers->isNotEmpty()) {
            return $barbers;
        }

        return collect([$this->owner])->filter();
    }

    public function publicBookingUrl(): string
    {
        return route('public.booking.show', $this);
    }

    public function canAcceptPayments(): bool
    {
        return app(\App\Services\StripeConnectService::class)->canAcceptPayments($this);
    }

    /**
     * @return array<string, string>|null
     */
    public function brandTheme(): ?array
    {
        return BrandColor::tokens($this->primary_color);
    }

    /**
     * @return array<string, list<array{open: string, close: string}>>
     */
    public function resolvedOpeningHours(): array
    {
        $hours = $this->opening_hours;

        if (! is_array($hours) || $hours === []) {
            return static::defaultOpeningHours();
        }

        $resolved = [];

        foreach (static::WEEKDAYS as $day) {
            $ranges = $hours[$day] ?? [];
            $resolved[$day] = collect(is_array($ranges) ? $ranges : [])
                ->filter(fn ($range) => is_array($range) && filled($range['open'] ?? null) && filled($range['close'] ?? null))
                ->map(fn ($range) => [
                    'open' => substr((string) $range['open'], 0, 5),
                    'close' => substr((string) $range['close'], 0, 5),
                ])
                ->values()
                ->all();
        }

        return $resolved;
    }

    /**
     * @return list<array{open: string, close: string}>
     */
    public function openingRangesFor(CarbonInterface $date): array
    {
        $key = strtolower($date->format('D'));

        return $this->resolvedOpeningHours()[$key] ?? [];
    }

    public function openingHoursLabelFor(CarbonInterface $date): string
    {
        $ranges = $this->openingRangesFor($date);

        if ($ranges === []) {
            return 'Closed';
        }

        return collect($ranges)
            ->map(fn (array $range) => $range['open'].' – '.$range['close'])
            ->implode(', ');
    }
}
