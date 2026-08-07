@props(['value'])

<label {{ $attributes->merge(['class' => 'text-sm font-medium leading-none text-foreground']) }}>
    {{ $value ?? $slot }}
</label>
