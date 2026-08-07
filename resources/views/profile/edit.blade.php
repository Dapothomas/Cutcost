<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="page-title">{{ __('Profile') }}</h1>
            <p class="page-sub">Manage your account settings</p>
        </div>
    </x-slot>

    <div class="page-shell max-w-2xl space-y-4">
        <div class="card p-6">
            @include('profile.partials.update-profile-information-form')
        </div>
        <div class="card p-6">
            @include('profile.partials.update-password-form')
        </div>
        <div class="card p-6">
            @include('profile.partials.delete-user-form')
        </div>
    </div>
</x-app-layout>
