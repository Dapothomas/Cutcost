<x-app-layout>
    <x-slot name="header">
        <h1 class="page-title">Add client</h1>
        <p class="page-sub">Save contact details and notes</p>
    </x-slot>

    <div class="page-shell max-w-2xl">
        <div class="card p-6 sm:p-8">
            <form method="POST" action="{{ route('business.clients.store') }}" class="space-y-4">
                @csrf
                @include('business.clients._form')
                <div class="flex items-center justify-end gap-3 pt-2">
                    <a href="{{ route('business.clients.index') }}" class="btn-ghost">Cancel</a>
                    <x-primary-button>Save client</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
