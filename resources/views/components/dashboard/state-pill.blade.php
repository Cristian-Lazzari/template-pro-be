@props([
    'tone' => 'neutral',
    'label' => null,
])

<span {{ $attributes->class(['settings-state', 'settings-state--' . $tone]) }}>
    {{ $label ?? trim((string) $slot) }}
</span>
