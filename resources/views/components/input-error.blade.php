@props(['messages'])

@if ($messages)
    <ul {{ $attributes->merge(['class' => 'space-y-0.5 text-[12px] font-medium text-destructive']) }}>
        @foreach ((array) $messages as $message)
            <li>{{ $message }}</li>
        @endforeach
    </ul>
@endif
