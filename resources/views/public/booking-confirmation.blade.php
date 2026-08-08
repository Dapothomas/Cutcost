<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Booking confirmed · {{ $business->name }}</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700;800&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/blade.js'])
        @if ($tokens = $business->brandTheme())
            <style>
                :root {
                    --primary: {{ $tokens['primary'] }};
                    --primary-deep: {{ $tokens['primary_deep'] }};
                    --ring: {{ $tokens['ring'] }};
                    --accent: {{ $tokens['accent'] }};
                    --accent-foreground: {{ $tokens['accent_foreground'] }};
                    --background: {{ $tokens['background'] }};
                    --secondary: {{ $tokens['secondary'] }};
                    --secondary-foreground: {{ $tokens['secondary_foreground'] }};
                    --muted: {{ $tokens['muted'] }};
                    --muted-foreground: {{ $tokens['muted_foreground'] }};
                    --border: {{ $tokens['border'] }};
                    --input: {{ $tokens['input'] }};
                }
            </style>
        @endif
    </head>
    <body class="font-sans app-shell-bg">
        <div class="mx-auto flex min-h-screen max-w-lg items-center px-4 py-16">
            <div class="card w-full p-8 text-center sm:p-10">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-success/10 text-success ring-8 ring-success/[0.06]">
                    <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                </div>
                <h1 class="mt-5 font-display text-2xl font-semibold tracking-tight">You’re booked</h1>
                <p class="mt-1 text-sm text-muted-foreground">{{ $business->name }}</p>

                <div class="mt-8 divide-y divide-border/60 rounded-xl border border-border/70 bg-muted/30 text-left text-sm">
                    <div class="flex justify-between gap-4 px-5 py-3.5">
                        <span class="text-muted-foreground">When</span>
                        <span class="font-medium">{{ $booking->starts_at->format('D j M Y · H:i') }}</span>
                    </div>
                    <div class="flex justify-between gap-4 px-5 py-3.5">
                        <span class="text-muted-foreground">Service</span>
                        <span class="font-medium">{{ $booking->service->name }}</span>
                    </div>
                    @if ($booking->amount_cents)
                        <div class="flex justify-between gap-4 px-5 py-3.5">
                            <span class="text-muted-foreground">Paid</span>
                            <span class="font-semibold text-success">£{{ number_format($booking->amount_cents / 100, 2) }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between gap-4 px-5 py-3.5">
                        <span class="text-muted-foreground">With</span>
                        <span class="font-medium">{{ $booking->barber->name }}</span>
                    </div>
                    <div class="flex justify-between gap-4 px-5 py-3.5">
                        <span class="text-muted-foreground">Name</span>
                        <span class="font-medium">{{ $booking->client->name }}</span>
                    </div>
                </div>

                <a href="{{ route('public.booking.show', $business) }}" class="btn-secondary mt-8 inline-flex">Book another</a>
                <p class="mt-6 text-xs text-muted-foreground">Powered by Cutcost</p>
            </div>
        </div>
    </body>
</html>
