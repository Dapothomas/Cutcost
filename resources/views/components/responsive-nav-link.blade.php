@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full rounded-lg bg-ink-950 px-3 py-2 text-start text-base font-medium text-white'
            : 'block w-full rounded-lg px-3 py-2 text-start text-base font-medium text-ink-600 hover:bg-ink-100 hover:text-ink-950';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
