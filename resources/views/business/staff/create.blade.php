<x-app-layout>
    <x-slot name="header">
        <h1 class="page-title">Add barber</h1>
        <p class="page-sub">They’ll get their own Cutcost login</p>
    </x-slot>

    <div class="page-shell max-w-2xl">
        <div class="card p-6 sm:p-8">
            <form method="POST" action="{{ route('business.staff.store') }}" class="space-y-4">
                @csrf

                <div>
                    <x-input-label for="name" value="Name" />
                    <x-text-input id="name" name="name" class="mt-1 block w-full" :value="old('name')" required />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="email" value="Email" />
                    <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email')" required />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="phone" value="Phone" />
                    <x-text-input id="phone" name="phone" class="mt-1 block w-full" :value="old('phone')" />
                    <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="password" value="Password" />
                    <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" required />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="password_confirmation" value="Confirm password" />
                    <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" required />
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <a href="{{ route('business.staff.index') }}" class="btn-ghost">Cancel</a>
                    <x-primary-button>Add barber</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
