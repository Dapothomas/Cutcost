<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="page-title">Staff</h1>
                <p class="page-sub">Barbers on your floor</p>
            </div>
            <a href="{{ route('business.staff.create') }}" class="btn-primary">Add barber</a>
        </div>
    </x-slot>

    <div class="page-shell">
        <div class="panel overflow-hidden">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($barbers as $barber)
                        <tr>
                            <td class="font-medium text-ink-950">{{ $barber->name }}</td>
                            <td>{{ $barber->email }}</td>
                            <td>{{ $barber->phone ?: '—' }}</td>
                            <td class="text-right">
                                <form class="inline" method="POST" action="{{ route('business.staff.destroy', $barber) }}" onsubmit="return confirm('Remove this barber?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-sm font-medium text-red-600 hover:text-red-700">Remove</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-10 text-center text-ink-500">No barbers yet. Add a team member so you can assign bookings.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $barbers->links() }}</div>
    </div>
</x-app-layout>
