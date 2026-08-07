<header class="sticky top-0 z-30 flex h-14 items-center gap-4 border-b bg-background/95 px-4 backdrop-blur supports-[backdrop-filter]:bg-background/60 md:px-6">
    <button
        type="button"
        @click="sidebarOpen = true"
        class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-input bg-background text-muted-foreground shadow-sm hover:bg-accent hover:text-accent-foreground lg:hidden"
    >
        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="18" y2="18"/></svg>
        <span class="sr-only">Open sidebar</span>
    </button>

    @isset($header)
        <div class="flex min-w-0 flex-1 items-center justify-between gap-4">
            {{ $header }}
        </div>
    @else
        <div class="flex-1"></div>
    @endisset
</header>
