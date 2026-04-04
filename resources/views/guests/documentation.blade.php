@extends('layouts.public')

@section('title', 'Documentazione Backoffice')
@section('kicker', 'Documentazione pubblica')
@section('headline', 'Guida completa all\'uso del gestionale')
@section('lead', 'Questa pagina e consultabile anche senza login ed e pensata per spiegare come usare le principali funzioni del Backoffice, dalla configurazione iniziale alla gestione quotidiana di ordini e prenotazioni.')

@section('hero_actions')
    <a class="public-button public-button--solid" href="#indice">Vai all'indice</a>
    <a class="public-button public-button--ghost" href="{{ route('guest.updates') }}">Vedi aggiornamenti</a>
@endsection

@section('contents')
    <section class="public-panel public-panel--soft" id="indice">
        <div class="public-panel__header">
            <p class="public-panel__eyebrow">Indice rapido</p>
            <h2>Sezioni disponibili</h2>
        </div>

        <div class="public-chip-grid">
            @foreach ($sections as $section)
                <a class="public-chip" href="#{{ $section['id'] }}">{{ $section['title'] }}</a>
            @endforeach
        </div>
    </section>

    <section class="public-grid">
        @foreach ($sections as $section)
            <article class="public-card" id="{{ $section['id'] }}">
                <p class="public-card__eyebrow">{{ $section['eyebrow'] }}</p>
                <h2>{{ $section['title'] }}</h2>
                <p>{{ $section['intro'] }}</p>

                <ul class="public-list">
                    @foreach ($section['points'] as $point)
                        <li>{{ $point }}</li>
                    @endforeach
                </ul>
            </article>
        @endforeach
    </section>
@endsection
