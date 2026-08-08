<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Services\StripeCheckoutService;
use App\Support\BrandColor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    public function edit(Request $request): Response
    {
        $user = $request->user();
        $business = $user->ownedBusiness()->firstOrFail();

        return Inertia::render('Business/Settings/Edit', [
            'business' => [
                'name' => $business->name,
                'slug' => $business->slug,
                'phone' => $business->phone,
                'city' => $business->city,
                'address' => $business->address,
                'public_booking_enabled' => $business->public_booking_enabled,
                'public_booking_url' => $business->publicBookingUrl(),
                'primary_color' => $business->primary_color,
                'opening_hours' => $this->hoursForForm($business),
                'slot_interval_minutes' => $business->slot_interval_minutes ?: 15,
                'booking_lead_minutes' => $business->booking_lead_minutes ?: 0,
                'booking_horizon_days' => $business->booking_horizon_days ?: 60,
            ],
            'presets' => BrandColor::presets(),
            'defaultColor' => BrandColor::DEFAULT_HEX,
            'weekdays' => collect(Business::WEEKDAYS)->map(fn (string $day) => [
                'value' => $day,
                'label' => ucfirst($day),
            ])->values(),
            'subscription' => [
                'plan' => $user->subscription_plan?->label(),
                'plan_value' => $user->subscription_plan?->value,
                'status' => $user->subscription_status?->value,
                'status_label' => match ($user->subscription_status?->value) {
                    'active' => 'Active',
                    'past_due' => 'Past due',
                    'canceled' => 'Canceled',
                    default => 'Pending',
                },
                'cancel_at' => $user->subscription_cancel_at?->toIso8601String(),
                'cancel_at_label' => $user->subscription_cancel_at?->timezone(config('app.timezone'))->format('j M Y'),
                'can_cancel' => $user->subscription_status?->value === 'active' && blank($user->subscription_cancel_at),
            ],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $business = $request->user()->ownedBusiness()->firstOrFail();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:80',
                'alpha_dash',
                Rule::unique('businesses', 'slug')->ignore($business->id),
            ],
            'phone' => ['nullable', 'string', 'max:50'],
            'city' => ['nullable', 'string', 'max:120'],
            'address' => ['nullable', 'string', 'max:255'],
            'public_booking_enabled' => ['required', 'boolean'],
            'primary_color' => [
                'nullable',
                'string',
                'max:7',
                Rule::when(
                    filled($request->input('primary_color')),
                    ['regex:/^#?[0-9A-Fa-f]{6}$/'],
                ),
            ],
            'slot_interval_minutes' => ['required', 'integer', 'in:5,10,15,20,30,60'],
            'booking_lead_minutes' => ['required', 'integer', 'min:0', 'max:10080'],
            'booking_horizon_days' => ['required', 'integer', 'min:1', 'max:365'],
            'opening_hours' => ['required', 'array'],
            'opening_hours.*.closed' => ['required', 'boolean'],
            'opening_hours.*.open' => ['nullable', 'date_format:H:i'],
            'opening_hours.*.close' => ['nullable', 'date_format:H:i'],
        ]);

        foreach (Business::WEEKDAYS as $day) {
            $dayHours = $data['opening_hours'][$day] ?? null;
            if (! is_array($dayHours)) {
                return back()->withErrors(["opening_hours.{$day}" => 'Opening hours are incomplete.']);
            }
            if (! ($dayHours['closed'] ?? false)) {
                if (blank($dayHours['open'] ?? null) || blank($dayHours['close'] ?? null)) {
                    return back()->withErrors(["opening_hours.{$day}.open" => 'Set open and close times, or mark closed.']);
                }
                if (($dayHours['close'] ?? '') <= ($dayHours['open'] ?? '')) {
                    return back()->withErrors(["opening_hours.{$day}.close" => 'Close time must be after open time.']);
                }
            }
        }

        $business->update([
            'name' => $data['name'],
            'slug' => strtolower($data['slug']),
            'phone' => $data['phone'] ?? null,
            'city' => $data['city'] ?? null,
            'address' => $data['address'] ?? null,
            'public_booking_enabled' => $data['public_booking_enabled'],
            'primary_color' => BrandColor::normalize($data['primary_color'] ?? null),
            'slot_interval_minutes' => $data['slot_interval_minutes'],
            'booking_lead_minutes' => $data['booking_lead_minutes'],
            'booking_horizon_days' => $data['booking_horizon_days'],
            'opening_hours' => $this->normalizeHours($data['opening_hours']),
        ]);

        return back()->with('status', 'Settings saved.');
    }

    public function cancelSubscription(Request $request, StripeCheckoutService $checkout): RedirectResponse
    {
        $request->validate([
            'confirm' => ['accepted'],
        ]);

        try {
            $checkout->cancelSubscription($request->user());
        } catch (\Throwable $e) {
            return back()->withErrors(['subscription' => $e->getMessage()]);
        }

        return back()->with('status', 'Subscription cancellation scheduled.');
    }

    /**
     * @return array<string, array{closed: bool, open: string, close: string}>
     */
    private function hoursForForm(Business $business): array
    {
        $hours = $business->resolvedOpeningHours();
        $form = [];

        foreach (Business::WEEKDAYS as $day) {
            $range = $hours[$day][0] ?? null;
            $form[$day] = [
                'closed' => $range === null,
                'open' => $range['open'] ?? '09:00',
                'close' => $range['close'] ?? '18:00',
            ];
        }

        return $form;
    }

    /**
     * @param  array<string, array{closed?: bool, open?: string, close?: string}>  $hours
     * @return array<string, list<array{open: string, close: string}>>
     */
    private function normalizeHours(array $hours): array
    {
        $normalized = [];

        foreach (Business::WEEKDAYS as $day) {
            $dayHours = $hours[$day] ?? ['closed' => true];
            if (! empty($dayHours['closed'])) {
                $normalized[$day] = [];
                continue;
            }

            $normalized[$day] = [[
                'open' => $dayHours['open'],
                'close' => $dayHours['close'],
            ]];
        }

        return $normalized;
    }
}
