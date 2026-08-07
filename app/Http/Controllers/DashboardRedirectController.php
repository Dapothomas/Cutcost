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
            Role::Owner => redirect()->route('business.dashboard'),
            Role::Barber => redirect()->route('barber.dashboard'),
        };
    }
}
