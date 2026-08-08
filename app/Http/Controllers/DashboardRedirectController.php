<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DashboardRedirectController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        return match ($request->user()->role) {
            Role::Owner => $request->user()->hasActiveSubscription()
                ? redirect()->route('business.dashboard')
                : redirect()->route('register.checkout.resume'),
            Role::Barber => redirect()->route('barber.dashboard'),
        };
    }
}
