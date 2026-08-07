<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="page-title">Bookings</h1>
                <p class="page-sub">Appointments across your shop</p>
            </div>
            <a href="{{ route('business.bookings.create') }}" class="btn-primary">Book appointment</a>
        </div>
    </x-slot>

    <div class="page-shell">
        <div class="panel overflow-hidden">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>When</th>
                        <th>Client</th>
                        <th>Service</th>
                        <th>Barber</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($bookings as $booking)
                        <tr>
                            <td class="font-medium text-ink-950">{{ $booking->starts_at->format('D j M · H:i') }}</td>
                            <td>{{ $booking->client->name }}</td>
                            <td>{{ $booking->service->name }}</td>
                            <td>{{ $booking->barber->name }}</td>
                            <td>
                                <form method="POST" action="{{ route('business.bookings.status', $booking) }}">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status" class="rounded-xl border-ink-200 text-sm focus:border-ink-500 focus:ring-ink-500" onchange="this.form.submit()">
                                        @foreach (\App\Enums\BookingStatus::cases() as $status)
                                            <option value="{{ $status->value }}" @selected($booking->status === $status)>{{ $status->label() }}</option>
                                        @endforeach
                                    </select>
                                </form>
                            </td>
                            <td class="text-right">
                                <form class="inline" method="POST" action="{{ route('business.bookings.destroy', $booking) }}" onsubmit="return confirm('Delete this appointment?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-sm font-medium text-red-600 hover:text-red-700">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-ink-500">No bookings yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $bookings->links() }}</div>
    </div>
</x-app-layout>
