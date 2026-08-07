<x-app-layout>
    <x-slot name="header">
        <h1 class="page-title">My bookings</h1>
        <p class="page-sub">Appointments assigned to you</p>
    </x-slot>

    <div class="page-shell">
        <div class="panel overflow-hidden">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>When</th>
                        <th>Client</th>
                        <th>Service</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($bookings as $booking)
                        <tr>
                            <td class="font-medium text-ink-950">{{ $booking->starts_at->format('D j M · H:i') }}</td>
                            <td>{{ $booking->client->name }}</td>
                            <td>{{ $booking->service->name }}</td>
                            <td>
                                <form method="POST" action="{{ route('barber.bookings.status', $booking) }}">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status" class="rounded-xl border-ink-200 text-sm focus:border-ink-500 focus:ring-ink-500" onchange="this.form.submit()">
                                        @foreach (\App\Enums\BookingStatus::cases() as $status)
                                            <option value="{{ $status->value }}" @selected($booking->status === $status)>{{ $status->label() }}</option>
                                        @endforeach
                                    </select>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-10 text-center text-ink-500">No bookings assigned to you yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $bookings->links() }}</div>
    </div>
</x-app-layout>
