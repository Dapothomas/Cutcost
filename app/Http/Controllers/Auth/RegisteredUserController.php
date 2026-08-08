<?php

namespace App\Http\Controllers\Auth;

use App\Enums\Role;
use App\Enums\SubscriptionPlan;
use App\Enums\SubscriptionStatus;
use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\User;
use App\Services\StripeCheckoutService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register', [
            'plans' => SubscriptionPlan::options(),
            'selectedPlan' => old('plan', request('plan', SubscriptionPlan::Shop->value)),
        ]);
    }

    /**
     * @throws ValidationException
     */
    public function store(Request $request, StripeCheckoutService $checkout): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'phone' => ['nullable', 'string', 'max:50'],
            'business_name' => ['required', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'plan' => ['required', new Enum(SubscriptionPlan::class)],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $plan = SubscriptionPlan::from($request->string('plan')->value());

        $user = DB::transaction(function () use ($request, $plan) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'role' => Role::Owner,
                'subscription_plan' => $plan,
                'subscription_status' => SubscriptionStatus::Pending,
            ]);

            $business = Business::create([
                'owner_id' => $user->id,
                'name' => $request->business_name,
                'phone' => $request->phone,
                'city' => $request->city,
            ]);

            $user->update(['business_id' => $business->id]);

            return $user;
        });

        if (StripeCheckoutService::shouldBypass()) {
            $checkout->activateWithoutCheckout($user, $plan);

            event(new Registered($user));

            Auth::login($user);

            return redirect(route('business.dashboard', absolute: false));
        }

        try {
            $session = $checkout->createCheckoutSession($user, $plan);
        } catch (\Throwable $exception) {
            throw ValidationException::withMessages([
                'plan' => 'Unable to start checkout. Please try again or contact support.',
            ]);
        }

        return redirect()->away($session->url);
    }
}
