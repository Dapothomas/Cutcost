<?php

namespace App\Http\Controllers;

use App\Mail\WaitlistJoinedNotification;
use App\Models\WaitlistSignup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class WaitlistController extends Controller
{
    public function show(): View
    {
        return view('waitlist');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
            'name' => ['nullable', 'string', 'max:255'],
            'shop_name' => ['nullable', 'string', 'max:255'],
        ]);

        $email = strtolower(trim($data['email']));

        $existing = WaitlistSignup::query()->where('email', $email)->first();

        $redirect = $request->input('source') === 'home'
            ? redirect()->to(route('home').'#waitlist')
            : back();

        if ($existing) {
            return $redirect->with('status', 'You’re already on the waitlist — we’ll email you when it’s your turn.');
        }

        $signup = WaitlistSignup::query()->create([
            'email' => $email,
            'name' => $data['name'] ?? null,
            'shop_name' => $data['shop_name'] ?? null,
            'source' => $request->input('source', 'waitlist'),
        ]);

        $this->notifyOwner($signup);

        return $redirect->with('status', 'You’re on the list. We’ll reach out when Cutcost opens for your shop.');
    }

    private function notifyOwner(WaitlistSignup $signup): void
    {
        $to = config('mail.waitlist_notify');

        if (blank($to)) {
            return;
        }

        try {
            Mail::to($to)->send(new WaitlistJoinedNotification($signup));
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
