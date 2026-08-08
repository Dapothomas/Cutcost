<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOwnerSubscribed
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user?->isOwner() && ! $user->hasActiveSubscription()) {
            return redirect()->route('register.checkout.resume')
                ->with('status', 'Complete your subscription to access your shop.');
        }

        return $next($request);
    }
}
