<article class="public-card doc-topic-card">
    <div class="doc-topic-card__top">
        <span class="public-icon-badge">
            @include('guests.partials.doc-icon', ['name' => $page['icon'], 'label' => $page['title']])
        </span>

        <div>
            <p class="public-card__eyebrow">{{ $page['eyebrow'] }}</p>
            <h2>{{ $page['title'] }}</h2>
        </div>
    </div>

    <p>{{ $page['summary'] }}</p>

    <ul class="doc-topic-card__list">
        @foreach (array_slice($page['focus_cards'], 0, 3) as $card)
            <li>{{ $card['title'] }}</li>
        @endforeach
    </ul>

    <div class="doc-badge-row">
        @foreach (array_slice($page['badges'], 0, 2) as $badge)
            <x-dashboard.state-pill tone="neutral">{{ $badge }}</x-dashboard.state-pill>
        @endforeach
    </div>

    <a class="public-inline-link" href="{{ route('guest.documentation.page', ['page' => $page['slug']]) }}">
        Apri pagina
        <i class="bi bi-arrow-right-short" aria-hidden="true"></i>
    </a>
</article>
