<x-app-layout>
    <x-slot name="header">
        <h1 class="page-title">Edit client</h1>
        <p class="page-sub">{{ $client->name }}</p>
    </x-slot>

    <div class="page-shell max-w-2xl">
        <div class="card p-6 sm:p-8">
            <form method="POST" action="{{ route('business.clients.update', $client) }}" class="space-y-4">
                @csrf
                @method('PUT')
                @include('business.clients._form', ['client' => $client])
                <div class="flex items-center justify-end gap-3 pt-2">
                    <a href="{{ route('business.clients.index') }}" class="btn-ghost">Cancel</a>
                    <x-primary-button>Update client</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
