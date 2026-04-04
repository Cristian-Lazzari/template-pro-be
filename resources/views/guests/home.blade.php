@extends('layouts.public')

@section('title', 'Area pubblica Backoffice')
@section('kicker', 'Accesso e supporto')
@section('headline', 'Benvenuto nell\'area pubblica del gestionale')
@section('lead', 'Da qui puoi entrare nel Backoffice, leggere la documentazione operativa e consultare gli aggiornamenti del progetto senza autenticazione.')

@section('hero_actions')
    <a class="public-button public-button--solid" href="{{ route('login') }}">{{ __('admin.Accedi') }}</a>
    <a class="public-button public-button--ghost" href="{{ route('guest.documentation') }}">Apri documentazione</a>
@endsection

@section('contents')
    <section class="public-grid public-grid--home">
        <article class="public-card">
            <p class="public-card__eyebrow">Operativita</p>
            <h2>Documentazione Backoffice</h2>
            <p>Una guida unica con spiegazioni chiare su dashboard, prodotti, prenotazioni, ordini, impostazioni e tutte le altre funzioni principali.</p>
            <a class="public-inline-link" href="{{ route('guest.documentation') }}">Vai alla guida completa</a>
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
@endsection
