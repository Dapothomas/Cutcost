<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Cutcost') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans">
        <div class="flex min-h-screen flex-col items-center justify-center bg-muted/40 px-4 py-10">
            <a href="{{ route('home') }}" class="mb-8 flex items-center gap-2 font-semibold tracking-tight">
                <span class="flex h-9 w-9 items-center justify-center rounded-md bg-primary text-sm font-bold text-primary-foreground">C</span>
                <span class="text-lg">Cutcost</span>
            </a>

            <div class="card w-full max-w-md p-6 shadow-sm sm:p-8">
                {{ $slot }}
            </div>

            <p class="mt-6 text-center text-xs text-muted-foreground">Salon &amp; barber CRM</p>
        </div>
    </body>
</html>
