@php($client = $client ?? null)

<div>
    <x-input-label for="name" value="Name" />
    <x-text-input id="name" name="name" class="mt-1 block w-full" :value="old('name', $client?->name)" required />
    <x-input-error :messages="$errors->get('name')" class="mt-2" />
</div>

<div>
    <x-input-label for="email" value="Email" />
    <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $client?->email)" />
    <x-input-error :messages="$errors->get('email')" class="mt-2" />
</div>

<div>
    <x-input-label for="phone" value="Phone" />
    <x-text-input id="phone" name="phone" class="mt-1 block w-full" :value="old('phone', $client?->phone)" />
    <x-input-error :messages="$errors->get('phone')" class="mt-2" />
</div>

<div>
    <x-input-label for="notes" value="Notes" />
    <textarea id="notes" name="notes" rows="4" class="form-textarea">{{ old('notes', $client?->notes) }}</textarea>
    <x-input-error :messages="$errors->get('notes')" class="mt-2" />
</div>
