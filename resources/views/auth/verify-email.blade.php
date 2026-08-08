<x-guest-layout>
    <div class="mb-6 space-y-1">
        <h2 class="font-display text-xl font-semibold tracking-tight">Verify your email</h2>
        <p class="text-sm text-muted-foreground">
            {{ __('Thanks for signing up! Click the link we emailed you to get started. Didn’t get it? We’ll send another.') }}
        </p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="flash-ok mb-4">
            {{ __('A new verification link has been sent to your email address.') }}
        </div>
    @endif

    <div class="mt-2 flex flex-wrap items-center justify-between gap-3">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <x-primary-button>{{ __('Resend verification email') }}</x-primary-button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn-ghost">{{ __('Log out') }}</button>
        </form>
    </div>
</x-guest-layout>
