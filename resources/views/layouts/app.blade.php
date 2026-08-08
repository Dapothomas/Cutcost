<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Cutcost') }}</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700;800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/blade.js'])
    </head>
    <body class="font-sans">
        <div
            x-data="{ sidebarOpen: false }"
            class="relative flex min-h-screen bg-background"
        >
            {{-- Mobile overlay --}}
            <div
                x-show="sidebarOpen"
                x-transition:enter="transition-opacity ease-linear duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition-opacity ease-linear duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 z-40 bg-black/50 lg:hidden"
                @click="sidebarOpen = false"
                x-cloak
            ></div>

            @include('layouts.partials.sidebar')

            <div class="flex min-h-screen flex-1 flex-col lg:pl-64">
                @include('layouts.partials.topbar')

                <main class="flex-1">
                    @if (session('status'))
                        <div class="page-shell pb-0">
                            <div class="flash-ok">{{ session('status') }}</div>
                        </div>
                    @endif

                    {{ $slot }}
                </main>
            </div>
        </div>

        <style>[x-cloak]{display:none!important}</style>
    </body>
</html>
