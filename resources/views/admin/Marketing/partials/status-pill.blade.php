@php
    $statusValue = (string) ($status ?? '');
    $statusTones = [
        'draft' => 'neutral',
        'assigned' => 'neutral',
        'active' => 'active',
        'sent' => 'active',
        'used' => 'active',
        'clicked' => 'active',
        'paused' => 'warning',
        'opened' => 'warning',
        'archived' => 'off',
    ];
@endphp

<x-dashboard.state-pill :tone="$statusTones[$statusValue] ?? 'neutral'">
    {{ $label ?? ($statusValue !== '' ? $statusValue : '-') }}
</x-dashboard.state-pill>
