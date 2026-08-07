<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Booking confirmed · {{ $business->name }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans bg-muted/40">
        <div class="mx-auto flex min-h-screen max-w-lg items-center px-4 py-16">
            <div class="card w-full p-8 text-center shadow-sm">
                <span class="badge-default">Confirmed</span>
                <h1 class="mt-4 text-2xl font-bold tracking-tight">You’re booked</h1>
                <p class="mt-1 text-sm text-muted-foreground">{{ $business->name }}</p>

                <div class="mt-8 space-y-3 rounded-lg border bg-muted/50 px-5 py-5 text-left text-sm">
                    <div class="flex justify-between gap-4">
                        <span class="text-muted-foreground">When</span>
                        <span class="font-medium">{{ $booking->starts_at->format('D j M Y · H:i') }}</span>
                    </div>
                    <div class="flex justify-between gap-4">
                        <span class="text-muted-foreground">Service</span>
                        <span class="font-medium">{{ $booking->service->name }}</span>
                    </div>
                    <div class="flex justify-between gap-4">
                        <span class="text-muted-foreground">With</span>
                        <span class="font-medium">{{ $booking->barber->name }}</span>
                    </div>
                    <div class="flex justify-between gap-4">
                        <span class="text-muted-foreground">Name</span>
                        <span class="font-medium">{{ $booking->client->name }}</span>
                    </div>
                </div>

                <a href="{{ route('public.booking.show', $business) }}" class="btn-secondary mt-8 inline-flex">Book another</a>
            </div>
        </div>
    </body>
</html>
