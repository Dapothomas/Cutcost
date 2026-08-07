<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="page-title">Clients</h1>
                <p class="page-sub">Your shop’s CRM contacts</p>
            </div>
            <a href="{{ route('business.clients.create') }}" class="btn-primary">Add client</a>
        </div>
    </x-slot>

    <div class="page-shell">
        <div class="panel overflow-hidden">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Contact</th>
                        <th>Bookings</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($clients as $client)
                        <tr>
                            <td class="font-medium text-ink-950">{{ $client->name }}</td>
                            <td>
                                {{ $client->phone ?: '—' }}
                                @if ($client->email)<div class="text-xs text-ink-400">{{ $client->email }}</div>@endif
                            </td>
                            <td>{{ $client->bookings_count }}</td>
                            <td class="space-x-3 text-right">
                                <a href="{{ route('business.clients.edit', $client) }}" class="btn-ghost">Edit</a>
                                <form class="inline" method="POST" action="{{ route('business.clients.destroy', $client) }}" onsubmit="return confirm('Remove this client?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-sm font-medium text-red-600 hover:text-red-700">Remove</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-10 text-center text-ink-500">No clients yet. Add your first regular.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $clients->links() }}</div>
    </div>
</x-app-layout>
