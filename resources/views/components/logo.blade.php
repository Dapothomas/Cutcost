@props([
    'tone' => 'default',
    'size' => 'md',
])

<img
    src="{{ asset($tone === 'light' ? 'images/logo-on-dark.png' : 'images/logo.png') }}"
    alt="Cutcost"
    width="2499"
    height="615"
    decoding="async"
    {{ $attributes->class(['brand-mark', 'brand-mark-sm' => $size === 'sm']) }}
>
