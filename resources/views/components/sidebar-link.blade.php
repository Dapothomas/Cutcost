@props(['active' => false])

<a {{ $attributes->merge(['class' => 'sidebar-link'.($active ? ' sidebar-link-active' : '')]) }}>
    {{ $slot }}
</a>
