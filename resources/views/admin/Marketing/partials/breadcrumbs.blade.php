@php
    $items = $items ?? [];
@endphp

@if (! empty($items))
    <nav class="public-breadcrumbs" aria-label="Breadcrumb">
        @foreach ($items as $item)
            @php
                $isLast = $loop->last;
                $label = $item['label'] ?? '';
                $url = $item['url'] ?? null;
            @endphp

            @if (! $isLast && $url)
                <a href="{{ $url }}">{{ $label }}</a>
            @else
                <span>{{ $label }}</span>
            @endif

            @if (! $isLast)
                <i class="bi bi-chevron-right" aria-hidden="true"></i>
            @endif
        @endforeach
    </nav>
@endif
