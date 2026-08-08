<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WaitlistSignup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WaitlistController extends Controller
{
    public function index(Request $request): Response
    {
        $signups = WaitlistSignup::query()
            ->latest()
            ->paginate(30)
            ->through(fn (WaitlistSignup $signup) => [
                'id' => $signup->id,
                'email' => $signup->email,
                'name' => $signup->name,
                'shop_name' => $signup->shop_name,
                'source' => $signup->source,
                'created_at' => $signup->created_at?->timezone(config('app.timezone'))->toIso8601String(),
                'created_at_label' => $signup->created_at?->timezone(config('app.timezone'))->format('D j M · g:i A'),
            ]);

        return Inertia::render('Admin/Waitlist/Index', [
            'signups' => $signups,
            'total' => WaitlistSignup::query()->count(),
        ]);
    }

    public function destroy(WaitlistSignup $waitlist): RedirectResponse
    {
        $waitlist->delete();

        return back()->with('status', 'Signup removed.');
    }
}
