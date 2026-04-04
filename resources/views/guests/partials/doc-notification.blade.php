@php
    $tone = $notification['tone'] ?? 'info';
    $badgeTone = [
        'success' => 'success',
        'warning' => 'warning',
        'danger' => 'danger',
        'info' => 'info',
    ][$tone] ?? 'info';
@endphp

<article class="doc-notification doc-notification--{{ $tone }}">
    <div class="doc-notification__icon">
        @include('guests.partials.doc-icon', ['name' => $notification['icon'], 'label' => $notification['title']])
    </div>

    <div class="doc-notification__body">
        <div class="doc-notification__top">
            <span class="badge text-bg-{{ $badgeTone }}">{{ $notification['badge'] }}</span>
            <strong>{{ $notification['title'] }}</strong>
        </div>

        <p>{{ $notification['message'] }}</p>

        <div class="doc-notification__meta">
            @foreach ($notification['items'] as $item)
                <span>{{ $item }}</span>
            @endforeach
        </div>
    </div>
</article>
