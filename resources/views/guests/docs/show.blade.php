@extends('layouts.public')

@section('title', $page['title'] . ' | Documentazione guest')
@section('kicker', $page['eyebrow'])
@section('headline', $page['headline'])
@section('lead', $page['lead'])

@section('hero_actions')
    <a class="public-button public-button--solid" href="#panoramica">Apri guida rapida</a>
    <a class="public-button public-button--ghost" href="{{ route('guest.documentation') }}">Torna all'indice</a>
@endsection

@section('contents')
    <section class="public-panel public-panel--soft">
        <div class="public-breadcrumbs">
            <a href="{{ route('guest.home') }}">Home</a>
            <i class="bi bi-chevron-right" aria-hidden="true"></i>
            <a href="{{ route('guest.documentation') }}">Documentazione</a>
            <i class="bi bi-chevron-right" aria-hidden="true"></i>
            <span>{{ $page['title'] }}</span>
        </div>

        <div class="doc-page-summary" id="panoramica">
            <div class="public-title-row">
                <span class="public-icon-badge">
                    @include('guests.partials.doc-icon', ['name' => $page['icon'], 'label' => $page['title']])
                </span>
                <div>
                    <p class="public-card__eyebrow">{{ $page['eyebrow'] }}</p>
                    <h2>{{ $page['title'] }}</h2>
                </div>
            </div>

            <p>{{ $page['summary'] }}</p>

            <div class="doc-badge-row">
                @foreach ($page['badges'] as $badge)
                    <x-dashboard.state-pill tone="neutral">{{ $badge }}</x-dashboard.state-pill>
                @endforeach
            </div>
        </div>
    </section>

    @includeIf('guests.docs.examples.' . $page['slug'])

    @if ($page['slug'] !== 'ordini')
        <section class="public-panel">
            <div class="public-panel__header">
                <p class="public-panel__eyebrow">Cosa trovi in questa pagina</p>
                <h2>Pannelli e controlli che ti aiutano nel lavoro quotidiano</h2>
            </div>

            <div class="doc-focus-grid">
                @foreach ($page['focus_cards'] as $card)
                    <article class="public-card doc-focus-card">
                        <div class="public-title-row">
                            <span class="public-icon-badge public-icon-badge--soft">
                                @include('guests.partials.doc-icon', ['name' => $card['icon'], 'label' => $card['title']])
                            </span>
                            <div>
                                <h3>{{ $card['title'] }}</h3>
                                <p>{{ $card['description'] }}</p>
                            </div>
                        </div>

                        <ul class="public-list">
                            @foreach ($card['items'] as $item)
                                <li>{{ $item }}</li>
                            @endforeach
                        </ul>
                    </article>
                @endforeach
            </div>
        </section>

        <section class="public-panel">
            <div class="public-panel__header">
                <p class="public-panel__eyebrow">{{ $page['flow_title'] }}</p>
                <h2>{{ $page['flow_intro'] }}</h2>
            </div>

            @include('guests.partials.doc-flow', ['steps' => $page['flow_steps']])
        </section>

        <section class="public-panel">
            <div class="public-panel__header">
                <p class="public-panel__eyebrow">{{ $page['checklist_title'] }}</p>
                <h2>Ultimo controllo prima di chiudere</h2>
            </div>

            <article class="public-card">
                <ul class="public-list">
                    @foreach ($page['checklist'] as $item)
                        <li>{{ $item }}</li>
                    @endforeach
                </ul>
            </article>
        </section>

        <section class="doc-double-grid">
            <div class="public-panel">
                <div class="public-panel__header">
                    <p class="public-panel__eyebrow">Domande frequenti</p>
                    <h2>Risposte rapide per chi lavora sul campo</h2>
                </div>

                <div class="doc-faq-list">
                    @foreach ($page['faqs'] as $faq)
                        <article class="public-card doc-faq-item">
                            <h3>{{ $faq['question'] }}</h3>
                            <p>{{ $faq['answer'] }}</p>
                        </article>
                    @endforeach
                </div>
            </div>

            <div class="public-panel">
                <div class="public-panel__header">
                    <p class="public-panel__eyebrow">Continua la lettura</p>
                    <h2>Pagine collegate</h2>
                </div>

                <div class="doc-topic-grid doc-topic-grid--compact">
                    @foreach ($page['related_pages'] as $relatedPage)
                        @include('guests.partials.doc-topic-card', ['page' => $relatedPage])
                    @endforeach
                </div>
            </div>
        </section>
    @else
        <section class="public-panel">
            <div class="public-panel__header">
                <p class="public-panel__eyebrow">Continua la lettura</p>
                <h2>Pagine collegate</h2>
            </div>

            <div class="doc-topic-grid doc-topic-grid--compact">
                @foreach ($page['related_pages'] as $relatedPage)
                    @include('guests.partials.doc-topic-card', ['page' => $relatedPage])
                @endforeach
            </div>
        </section>
    @endif
@endsection
