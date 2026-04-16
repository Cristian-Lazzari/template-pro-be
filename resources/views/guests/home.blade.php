@extends('layouts.public')

@section('title', 'Area pubblica Backoffice')
@section('kicker', 'Accesso e supporto')
@section('headline', 'Area pubblica pronta per accesso, guida e aggiornamenti')
@section('lead', 'Da qui puoi entrare nel Backoffice, aprire la nuova documentazione guest divisa per pagine operative e consultare gli aggiornamenti del progetto senza autenticazione.')

@section('hero_actions')
    <a class="public-button public-button--solid" href="{{ route('login') }}">{{ __('admin.Accedi') }}</a>
    <a class="public-button public-button--ghost" href="{{ route('guest.documentation') }}">Apri documentazione</a>
@endsection

@section('contents')
    <section class="public-grid public-grid--home">
        <article class="public-card">
            <p class="public-card__eyebrow">Documentazione</p>
            <h2>Indice operativo guest</h2>
            <p>Apri il centro documentazione con pagine dedicate per onboarding, configurazione, prenotazioni, ordini, menu e comunicazioni.</p>
            <a class="public-inline-link" href="{{ route('guest.documentation') }}">Apri l'indice documentazione</a>
        </article>

        <article class="public-card">
            <p class="public-card__eyebrow">Release notes</p>
            <h2>Aggiornamenti del progetto</h2>
            <p>Una timeline pubblica pensata per raccogliere tutte le modifiche che verranno introdotte da ora in avanti.</p>
            <a class="public-inline-link" href="{{ route('guest.updates') }}">Apri il registro aggiornamenti</a>
        </article>

        <article class="public-card">
            <p class="public-card__eyebrow">Accesso</p>
            <h2>Entra nel gestionale</h2>
            <p>Se hai le credenziali puoi accedere direttamente al Backoffice e gestire i contenuti del locale.</p>
            <a class="public-inline-link" href="{{ route('login') }}">{{ __('admin.Accedi') }}</a>
        </article>
    </section>

    <section class="public-panel public-panel--soft">
        <div class="public-panel__header">
            <p class="public-panel__eyebrow">Pagine piu usate</p>
            <h2>Accessi diretti alle guide operative</h2>
        </div>

        <div class="doc-topic-grid doc-topic-grid--compact doc-topic-grid--home">
            @foreach (array_slice($docPages, 0, 4) as $page)
                @include('guests.partials.doc-topic-card', ['page' => $page])
            @endforeach
        </div>
    </section>
@endsection
