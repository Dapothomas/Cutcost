@php($service = $service ?? null)

<div>
    <x-input-label for="name" value="Name" />
    <x-text-input id="name" name="name" class="mt-1 block w-full" :value="old('name', $service?->name)" required />
    <x-input-error :messages="$errors->get('name')" class="mt-2" />
</div>

<div>
    <x-input-label for="duration_minutes" value="Duration (minutes)" />
    <x-text-input id="duration_minutes" name="duration_minutes" type="number" min="5" class="mt-1 block w-full" :value="old('duration_minutes', $service?->duration_minutes ?? 30)" required />
    <x-input-error :messages="$errors->get('duration_minutes')" class="mt-2" />
</div>

<div>
    <x-input-label for="price" value="Price (£)" />
    <x-text-input id="price" name="price" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('price', $service ? number_format($service->price_cents / 100, 2, '.', '') : '0.00')" required />
    <x-input-error :messages="$errors->get('price')" class="mt-2" />
</div>

<label class="inline-flex items-center gap-2">
    <input type="checkbox" name="is_active" value="1" class="rounded border-ink-300 text-primary shadow-sm focus:ring-primary"
        @checked(old('is_active', $service?->is_active ?? true))>
    <span class="text-sm text-ink-700">Active</span>
</label>
