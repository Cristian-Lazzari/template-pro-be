@props([
    'items' => [],
])

<div {{ $attributes->class(['settings-status-grid']) }}>
    @foreach ($items as $item)
        <article class="settings-status-card">
            <span>{{ $item['label'] }}</span>
            <x-dashboard.state-pill :tone="$item['tone'] ?? 'neutral'">
                {{ $item['value'] }}
            </x-dashboard.state-pill>
            @if (!empty($item['meta']))
                <small class="dashboard-status-meta">{{ $item['meta'] }}</small>
            @endif
        </article>
    @endforeach
</div>
