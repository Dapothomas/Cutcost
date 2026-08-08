<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Cutcost') }}</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/blade.js'])
    </head>
    <body class="font-sans">
        <div class="flex min-h-screen flex-col items-center justify-center bg-gradient-to-br from-background via-brand-50/50 to-brand-100/40 px-4 py-10">
            <a href="{{ route('home') }}" class="mb-8 flex items-center gap-2.5 font-semibold tracking-tight">
                <span class="brand-mark">C</span>
                <span class="text-lg text-foreground">Cutcost</span>
            </a>

            <div class="card w-full max-w-lg border-primary/10 p-6 shadow-card sm:p-8">
                {{ $slot }}
            </div>

            <p class="mt-6 text-center text-xs text-muted-foreground">Salon &amp; barber CRM</p>
        </div>
    </body>
</html>
