<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="page-title">Services</h1>
                <p class="page-sub">Menu of cuts and treatments</p>
            </div>
            <a href="{{ route('business.services.create') }}" class="btn-primary">Add service</a>
        </div>
    </x-slot>

    <div class="page-shell">
        <div class="panel overflow-hidden">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Service</th>
                        <th>Duration</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($services as $service)
                        <tr>
                            <td class="font-medium text-ink-950">{{ $service->name }}</td>
                            <td>{{ $service->duration_minutes }} min</td>
                            <td>{{ $service->priceLabel() }}</td>
                            <td><span class="status-pill">{{ $service->is_active ? 'Active' : 'Inactive' }}</span></td>
                            <td class="space-x-3 text-right">
                                <a href="{{ route('business.services.edit', $service) }}" class="btn-ghost">Edit</a>
                                <form class="inline" method="POST" action="{{ route('business.services.destroy', $service) }}" onsubmit="return confirm('Remove this service?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-sm font-medium text-red-600 hover:text-red-700">Remove</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-10 text-center text-ink-500">No services yet. Add a cut, fade, or colour.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $services->links() }}</div>
    </div>
</x-app-layout>
