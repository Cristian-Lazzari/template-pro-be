@props([
    'name' => 'circle-square',
    'label' => null,
])

@php
    if (str_starts_with($name, 'bi ')) {
        $baseClass = $name;
    } elseif (str_starts_with($name, 'bi-')) {
        $baseClass = 'bi ' . $name;
    } else {
        $baseClass = 'bi bi-' . $name;
    }
@endphp

<i {{ $attributes->class([$baseClass]) }} aria-hidden="true"></i>

@if ($label)
    <span class="visually-hidden">{{ $label }}</span>
@endif
