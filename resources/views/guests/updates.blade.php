@extends('layouts.public')

@section('title', 'Aggiornamenti del gestionale')
@section('kicker', 'Registro novita')
@section('headline', 'Pagina dedicata a tutti gli aggiornamenti futuri')
@section('lead', 'Qui possiamo raccogliere le release da questo momento in avanti, con data, obiettivo e impatto operativo. La pagina e pubblica, quindi puo essere consultata anche da chi non accede al Backoffice.')

@section('hero_actions')
    <a class="public-button public-button--solid" href="#timeline">Apri timeline</a>
    <a class="public-button public-button--ghost" href="{{ route('guest.documentation') }}">Apri documentazione</a>
@endsection

@section('contents')
    <section class="public-panel public-panel--soft">
        <div class="public-panel__header">
            <p class="public-panel__eyebrow">Come usare questa pagina</p>
            <h2>Regola semplice per i prossimi rilasci</h2>
        </div>

        <div class="public-notes">
            <p>Per ogni aggiornamento conviene inserire una nuova voce con data, titolo breve, riassunto del cambiamento e 2-3 punti pratici su cosa e stato introdotto o modificato.</p>
            <p>In questo modo la pagina resta leggibile sia per chi gestisce il locale sia per chi segue lo sviluppo del progetto nel tempo.</p>
        </div>
    </section>

    <section class="public-timeline" id="timeline">
        @foreach ($updates as $update)
            <article class="public-timeline__item">
                <div class="public-timeline__meta">
                    <span class="public-badge">{{ $update['version'] }}</span>
                    <strong>{{ $update['date'] }}</strong>
                </div>

                <div class="public-card public-card--timeline">
                    <h2>{{ $update['title'] }}</h2>
                    <p>{{ $update['summary'] }}</p>

                    <ul class="public-list">
                        @foreach ($update['items'] as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ul>
                </div>
            </article>
        @endforeach
    </section>
@endsection
