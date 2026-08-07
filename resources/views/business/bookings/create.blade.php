<x-app-layout>
    <x-slot name="header">
        <h1 class="page-title">Book appointment</h1>
        <p class="page-sub">Assign client, service, and barber</p>
    </x-slot>

    <div class="page-shell max-w-2xl">
        <div class="card p-6 sm:p-8">
            @if ($clients->isEmpty() || $services->isEmpty())
                <p class="text-sm text-ink-600">
                    Add at least one
                    <a href="{{ route('business.clients.create') }}" class="font-medium text-ink-950 underline">client</a>
                    and one
                    <a href="{{ route('business.services.create') }}" class="font-medium text-ink-950 underline">service</a>
                    before booking.
                </p>
            @else
                <form method="POST" action="{{ route('business.bookings.store') }}" class="space-y-4">
                    @csrf

                    <div>
                        <x-input-label for="client_id" value="Client" />
                        <select id="client_id" name="client_id" class="form-select" required>
                            @foreach ($clients as $client)
                                <option value="{{ $client->id }}" @selected(old('client_id') == $client->id)>{{ $client->name }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('client_id')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="service_id" value="Service" />
                        <select id="service_id" name="service_id" class="form-select" required>
                            @foreach ($services as $service)
                                <option value="{{ $service->id }}" @selected(old('service_id') == $service->id)>{{ $service->name }} ({{ $service->duration_minutes }} min)</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('service_id')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="barber_id" value="Barber" />
                        <select id="barber_id" name="barber_id" class="form-select" required>
                            @foreach ($barbers as $barber)
                                <option value="{{ $barber->id }}" @selected(old('barber_id') == $barber->id)>{{ $barber->name }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('barber_id')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="starts_at" value="Starts at" />
                        <x-text-input id="starts_at" name="starts_at" type="datetime-local" class="mt-1 block w-full" :value="old('starts_at')" required />
                        <x-input-error :messages="$errors->get('starts_at')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="notes" value="Notes" />
                        <textarea id="notes" name="notes" rows="3" class="form-textarea">{{ old('notes') }}</textarea>
                        <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <a href="{{ route('business.bookings.index') }}" class="btn-ghost">Cancel</a>
                        <x-primary-button>Book</x-primary-button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</x-app-layout>
