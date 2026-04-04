@php
    $icon = $name ?? 'circle-square';

    if (str_starts_with($icon, 'bi ')) {
        $baseClass = $icon;
    } elseif (str_starts_with($icon, 'bi-')) {
        $baseClass = 'bi ' . $icon;
    } else {
        $baseClass = 'bi bi-' . $icon;
    }
@endphp

<i class="{{ trim($baseClass . ' ' . ($class ?? '')) }}" aria-hidden="true"></i>

@if (!empty($label))
    <span class="visually-hidden">{{ $label }}</span>
@endif
