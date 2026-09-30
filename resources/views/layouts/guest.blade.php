<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Cutcost') }}</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/blade.js'])
    </head>
    <body class="font-sans">
        <div class="relative flex min-h-screen flex-col items-center justify-center overflow-hidden bg-background px-4 py-10">
            <div
                class="pointer-events-none absolute inset-x-0 top-0 h-80"
                style="background-image: radial-gradient(60% 100% at 50% 0%, hsl(var(--primary) / 0.09), transparent 70%)"
                aria-hidden="true"
            ></div>

            <a href="{{ route('home') }}" class="relative mb-7 transition-opacity hover:opacity-85">
                <span class="brand-logo brand-logo-gradient">Cut<span class="brand-logo-accent">cost</span></span>
            </a>

            <div class="card relative w-full max-w-md p-6 sm:p-7">
                {{ $slot }}
            </div>

            <p class="relative mt-6 text-center text-xs text-muted-foreground">Salon &amp; stylist CRM</p>
        </div>
    </body>
</html>
