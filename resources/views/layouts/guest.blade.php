<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Cutcost') }}</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/blade.js'])
    </head>
    <body class="font-sans">
        <div class="relative flex min-h-screen flex-col items-center justify-center overflow-hidden bg-gradient-to-br from-background via-brand-50/50 to-brand-100/40 px-4 py-10">
            <div class="landing-grid-bg pointer-events-none absolute inset-0" aria-hidden="true"></div>
            <div class="pointer-events-none absolute -top-32 left-1/2 h-72 w-[560px] -translate-x-1/2 rounded-full bg-primary/10 blur-3xl" aria-hidden="true"></div>

            <a href="{{ route('home') }}" class="relative mb-8 transition-opacity hover:opacity-85">
                <span class="brand-logo brand-logo-gradient">Cut<span class="brand-logo-accent">cost</span></span>
            </a>

            <div class="card relative w-full max-w-lg p-6 sm:p-8">
                {{ $slot }}
            </div>

            <p class="relative mt-6 text-center text-xs text-muted-foreground">Salon &amp; stylist CRM</p>
        </div>
    </body>
</html>
