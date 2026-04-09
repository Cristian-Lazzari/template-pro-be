@props([
    'subject' => '',
    'headline' => '',
    'subheadline' => null,
    'greeting' => null,
    'intro' => '',
    'items' => [],
    'sender' => '',
    'footer' => null,
    'cta' => null,
    'variant' => 'campaign',
])

<article {{ $attributes->class(['dashboard-mail-preview', 'dashboard-mail-preview--' . $variant]) }}>
    <div class="dashboard-mail-preview__top">
        <span>{{ $subject }}</span>
        <span>{{ $sender }}</span>
    </div>

    <div class="dashboard-mail-preview__body">
        <div class="dashboard-mail-preview__hero">
            <h3>{{ $headline }}</h3>
            @if ($subheadline)
                <p>{{ $subheadline }}</p>
            @endif
        </div>

        <div class="dashboard-mail-preview__content">
            @if ($greeting)
                <p>{{ $greeting }}</p>
            @endif

            <p>{{ $intro }}</p>

            @if (count($items))
                <div class="dashboard-mail-preview__details">
                    @foreach ($items as $item)
                        <div class="dashboard-mail-preview__detail">{{ $item }}</div>
                    @endforeach
                </div>
            @endif

            @if ($cta)
                <span class="dashboard-mail-preview__cta">{{ $cta }}</span>
            @endif

            @if ($footer)
                <p class="dashboard-mail-preview__footer">{{ $footer }}</p>
            @endif
        </div>
    </div>
</article>
