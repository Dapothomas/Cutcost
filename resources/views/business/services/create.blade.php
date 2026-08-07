<x-app-layout>
    <x-slot name="header">
        <h1 class="page-title">Add service</h1>
        <p class="page-sub">Set duration and price</p>
    </x-slot>

    <div class="page-shell max-w-2xl">
        <div class="card p-6 sm:p-8">
            <form method="POST" action="{{ route('business.services.store') }}" class="space-y-4">
                @csrf
                @include('business.services._form')
                <div class="flex items-center justify-end gap-3 pt-2">
                    <a href="{{ route('business.services.index') }}" class="btn-ghost">Cancel</a>
                    <x-primary-button>Save service</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
