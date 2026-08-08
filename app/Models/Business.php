<?php

namespace App\Models;

use App\Enums\Role;
use App\Support\BrandColor;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

#[Fillable(['owner_id', 'name', 'slug', 'phone', 'city', 'address', 'public_booking_enabled', 'primary_color', 'stripe_account_id', 'stripe_charges_enabled', 'stripe_payouts_enabled', 'stripe_onboarding_completed_at'])]
class Business extends Model
{
    protected function casts(): array
    {
        return [
            'public_booking_enabled' => 'boolean',
            'stripe_charges_enabled' => 'boolean',
            'stripe_payouts_enabled' => 'boolean',
            'stripe_onboarding_completed_at' => 'datetime',
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
     * Barbers clients can book with. Falls back to the owner if no barbers yet.
     *
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
     * @return array{primary: string, primary_deep: string, ring: string, accent: string, accent_foreground: string}|null
     */
    public function brandTheme(): ?array
    {
        return BrandColor::tokens($this->primary_color);
    }
}
