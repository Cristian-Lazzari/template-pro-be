@extends('layouts.public')

@section('title', 'Documentazione guest')
@section('kicker', 'Centro assistenza guest')
@section('headline', 'Apri la guida giusta per il lavoro che devi fare adesso')
@section('lead', 'La documentazione guest ora e divisa in pagine operative reali. Apri onboarding, configurazione, prenotazioni, ordini, menu o comunicazioni e trova subito flussi, esempi visivi e controlli finali.')

@section('hero_actions')
    <a class="public-button public-button--solid" href="#azioni-rapide">Apri indice operativo</a>
    <a class="public-button public-button--ghost" href="{{ route('guest.updates') }}">Vedi aggiornamenti</a>
@endsection

@section('contents')
    <section class="public-panel public-panel--onboarding">
        <div class="public-panel__header">
            <p class="public-panel__eyebrow">{{ $onboarding['eyebrow'] }}</p>
            <h2>{{ $onboarding['title'] }}</h2>
        </div>

        <div class="public-onboarding">
            <div class="public-onboarding__intro">
                <p>{{ $onboarding['intro'] }}</p>

                <div class="public-onboarding__tips">
                    @foreach ($onboarding['tips'] as $tip)
                        <div class="public-note">{{ $tip }}</div>
                    @endforeach
                </div>
            </div>

            <div class="public-onboarding__checklist">
                <h3>Controlli prima di iniziare</h3>
                <ul class="public-list">
                    @foreach ($onboarding['checklist'] as $item)
                        <li>{{ $item }}</li>
                    @endforeach
                </ul>
            </div>
        </div>

        <div class="public-step-grid">
            @foreach ($onboarding['steps'] as $step)
                <article class="public-step-card">
                    <div class="public-icon-badge">
                        @include('guests.partials.doc-icon', ['name' => $step['icon'], 'label' => $step['title']])
                    </div>
                    <h3>{{ $step['title'] }}</h3>
                    <p>{{ $step['description'] }}</p>
                </article>
            @endforeach
        </div>
    </section>

    <section class="public-panel public-panel--soft" id="azioni-rapide">
        <div class="public-panel__header">
            <p class="public-panel__eyebrow">Accessi rapidi</p>
            <h2>Scegli il problema da risolvere e apri subito la pagina giusta</h2>
        </div>

        <div class="doc-quick-actions">
            @foreach ($quickActions as $action)
                <article class="public-card doc-quick-action">
                    <span class="public-icon-badge public-icon-badge--soft">
                        @include('guests.partials.doc-icon', ['name' => $action['icon'], 'label' => $action['title']])
                    </span>
                    <div class="doc-quick-action__body">
                        <h3>{{ $action['title'] }}</h3>
                        <p>{{ $action['description'] }}</p>
                    </div>
                    <a class="public-button public-button--ghost" href="{{ route('guest.documentation.page', ['page' => $action['page']]) }}">
                        {{ $action['cta'] }}
                    </a>
                </article>
            @endforeach
        </div>
    </section>

    <section class="public-panel" id="pagine-documentazione">
        <div class="public-panel__header">
            <p class="public-panel__eyebrow">Pagine documentazione</p>
            <h2>Struttura reale della guida guest</h2>
        </div>

        <div class="doc-topic-grid">
            @foreach ($docPages as $page)
                @include('guests.partials.doc-topic-card', ['page' => $page])
            @endforeach
        </div>
    </section>
@endsection
