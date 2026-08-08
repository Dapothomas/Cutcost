<?php

namespace App\Models;

use App\Enums\Role;
use App\Enums\SubscriptionPlan;
use App\Enums\SubscriptionStatus;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'phone', 'business_id', 'subscription_plan', 'subscription_status', 'stripe_customer_id', 'stripe_subscription_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => Role::class,
            'subscription_plan' => SubscriptionPlan::class,
            'subscription_status' => SubscriptionStatus::class,
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function ownedBusiness(): HasOne
    {
        return $this->hasOne(Business::class, 'owner_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'barber_id');
    }

    public function isOwner(): bool
    {
        return $this->role === Role::Owner;
    }

    public function isBarber(): bool
    {
        return $this->role === Role::Barber;
    }

    public function shop(): ?Business
    {
        if ($this->isOwner()) {
            return $this->ownedBusiness;
        }

        return $this->business;
    }

    public function hasActiveSubscription(): bool
    {
        if (! $this->isOwner()) {
            return true;
        }

        return $this->subscription_status === SubscriptionStatus::Active;
    }
}
