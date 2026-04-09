@props([
    'items' => [],
])

<div {{ $attributes->class(['dashboard-field-list']) }}>
    @foreach ($items as $item)
        <div class="dashboard-field-row">
            <span>{{ $item['label'] }}</span>

            @if (!empty($item['tone']))
                <x-dashboard.state-pill :tone="$item['tone']">
                    {{ $item['value'] }}
                </x-dashboard.state-pill>
            @else
                <strong>{{ $item['value'] }}</strong>
            @endif
        </div>
    @endforeach
</div>
