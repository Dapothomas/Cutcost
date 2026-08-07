@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center rounded-lg bg-ink-950 px-3 py-1.5 text-sm font-medium text-white transition'
            : 'inline-flex items-center rounded-lg px-3 py-1.5 text-sm font-medium text-ink-500 transition hover:bg-ink-100 hover:text-ink-900';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
