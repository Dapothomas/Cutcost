@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'flash-ok']) }}>
        {{ $status }}
    </div>
@endif
