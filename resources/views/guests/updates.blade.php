@extends('layouts.public')

@section('title', __('admin.public.updates.eyebrow'))
@section('kicker', __('admin.public.updates.title'))
@section('headline', __('admin.public.updates.subtitle'))
@section('lead', __('admin.public.updates.lead'))

@section('hero_actions')
    <a class="public-button public-button--solid" href="#changelog">{{ __('admin.public.updates.open_timeline') }}</a>
    <a class="public-button public-button--ghost" href="{{ route('guest.documentation') }}">{{ __('admin.public.updates.open_documentation') }}</a>
@endsection

@section('contents')
    @php
        $totalFeatures = collect($updates)->sum(fn($u) => count($u['items']));
        $firstYear = isset($updates[0]) ? substr($updates[0]['date'], -4) : '—';
        $lastYear  = isset($updates[count($updates) - 1]) ? substr($updates[count($updates) - 1]['date'], -4) : '—';
        $yearRange = $firstYear === $lastYear ? $firstYear : $firstYear . ' — ' . $lastYear;
    @endphp

    {{-- Barra statistiche --}}
    <div class="changelog-stats">
        <div class="changelog-stats__item">
            <strong>{{ count($updates) }}</strong>
            <span>rilasci</span>
        </div>
        <div class="changelog-stats__item">
            <strong>{{ $totalFeatures }}+</strong>
            <span>novità</span>
        </div>
        <div class="changelog-stats__item">
            <strong>{{ $yearRange }}</strong>
            <span>sviluppo attivo</span>
        </div>
    </div>

    {{-- Pannello regole di rilascio --}}
    <section class="public-panel public-panel--soft">
        <div class="public-panel__header">
            <p class="public-panel__eyebrow">{{ __('admin.public.updates.how_to_use') }}</p>
            <h2>{{ __('admin.public.updates.simple_rule') }}</h2>
        </div>
        <div class="public-notes">
            <p>{{ __('admin.public.updates.release_rule_1') }}</p>
            <p>{{ __('admin.public.updates.release_rule_2') }}</p>
        </div>
    </section>

    {{-- Changelog --}}
    <div class="changelog" id="changelog">
        @foreach ($updates as $index => $update)
            @php
                // Identificazione aggiornamento per tipo di preview
                $isSett  = str_contains($update['version'], 'settembre'); // stat oggi dashboard
                $isOtt   = str_contains($update['version'], 'ottobre');   // blocco/sblocco orari
                $isNov   = str_contains($update['version'], 'novembre');  // notifica WhatsApp ordine
                $isMar   = str_contains($update['version'], 'marzo');     // filtro date statistiche
                $isApr   = str_contains($update['version'], 'aprile');    // promo su ordine + prenotazione
                $isMag   = str_contains($update['version'], 'maggio');    // campagna in invio
                $hasPrev = $isSett || $isOtt || $isNov || $isMar || $isApr || $isMag;
                $delay   = min($index * 55, 360);
            @endphp

            <article
                class="changelog__entry {{ $hasPrev ? 'changelog__entry--featured' : '' }}"
                style="animation-delay: {{ $delay }}ms"
            >
                {{-- Intestazione card --}}
                <div class="changelog__head">
                    <div class="changelog__meta">
                        <span class="changelog__dot"></span>
                        <span class="changelog__badge">{{ $update['version'] }}</span>
                        <span class="changelog__date">{{ $update['date'] }}</span>
                    </div>
                    <span class="changelog__count">{{ count($update['items']) }} novità</span>
                </div>

                {{-- Corpo card --}}
                <div class="changelog__body {{ $hasPrev ? 'changelog__body--split' : '' }}">

                    {{-- Colonna testo --}}
                    <div class="changelog__copy">
                        <h2 class="changelog__title">{{ $update['title'] }}</h2>
                        <p class="changelog__summary">{{ $update['summary'] }}</p>

                        <ul class="changelog__features {{ $hasPrev ? 'changelog__features--single' : '' }}">
                            @foreach ($update['items'] as $item)
                                <li class="changelog__feature">
                                    <span class="changelog__feature-icon">
                                        <i class="bi bi-check-lg"></i>
                                    </span>
                                    <span class="changelog__feature-text">{{ $item }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    {{-- Colonna preview (solo per aggiornamenti in evidenza) --}}
                    @if ($hasPrev)
                        <div class="changelog__preview-col">
                            <p class="changelog__preview-label">Anteprima dashboard</p>

                            <div class="changelog__preview-frame">
                                <div class="changelog__preview-bar">
                                    <div class="changelog__preview-dots">
                                        <span></span>
                                        <span></span>
                                        <span></span>
                                    </div>
                                    <span class="changelog__preview-title">Dashboard Admin</span>
                                </div>

                                <div class="changelog__preview-content">

                                    {{-- ── settembre 2025: stat giornaliere (ora presenti nella dashboard) ──────── --}}
                                    @if ($isSett)
                                        <div class="menu-dashboard__stat-grid" style="grid-template-columns: repeat(2, 1fr);">
                                            <div class="menu-dashboard__stat-card">
                                                <span class="menu-dashboard__stat-label">Ordini oggi</span>
                                                <strong>3</strong>
                                                <small>richieste ricevute</small>
                                            </div>
                                            <div class="menu-dashboard__stat-card">
                                                <span class="menu-dashboard__stat-label">Coperti in sala</span>
                                                <strong>18</strong>
                                                <small>su 30 disponibili</small>
                                            </div>
                                            <div class="menu-dashboard__stat-card" style="grid-column: 1 / -1;">
                                                <span class="menu-dashboard__stat-label">Incasso oggi</span>
                                                <strong>€124,00</strong>
                                                <small>da ordini confermati</small>
                                            </div>
                                        </div>
                                    @endif

                                    {{-- ── ottobre 2025: blocco/sblocco orario — solo fasce future ─────────────── --}}
                                    @if ($isOtt)
                                        <div style="pointer-events: none; user-select: none;">
                                            <div class="time-list">

                                                {{-- Fascia passata: nessun pulsante --}}
                                                <div class="time-item" style="opacity: .38;">
                                                    <div class="time-header">
                                                        <strong>12:30</strong>
                                                        <div class="line"></div>
                                                        <p class="prop">
                                                            <i class="bi bi-people"></i>
                                                            <i class="bi bi-bag"></i>
                                                        </p>
                                                        {{-- passata: nessun toggle --}}
                                                    </div>
                                                    <div class="time-content"></div>
                                                </div>

                                                {{-- Fascia futura attiva: pulsante blocca --}}
                                                <div class="time-item">
                                                    <div class="time-header">
                                                        <strong>19:30</strong>
                                                        <div class="line"></div>
                                                        <p class="prop">
                                                            <i class="bi bi-people"></i>
                                                            <i class="bi bi-bag"></i>
                                                        </p>
                                                        <button type="button" class="block-time-btn">
                                                            <i class="bi bi-toggle-on"></i>
                                                        </button>
                                                    </div>
                                                    <div class="time-content">
                                                        <a href="#" class="res-item okk">
                                                            <div class="top">
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi okk bi-check-circle" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/><path d="m10.97 4.97-.02.022-3.473 4.425-2.093-2.094a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05"/></svg>
                                                                <div class="name">Chiara Riva</div>
                                                                <div class="guest">2<i class="bi bi-person-standing"></i></div>
                                                            </div>
                                                        </a>
                                                    </div>
                                                </div>

                                                {{-- Fascia futura bloccata: pulsante sblocca --}}
                                                <div class="time-item blocked">
                                                    <div class="time-header">
                                                        <strong>20:00</strong>
                                                        <div class="line blocked-line"></div>
                                                        <p class="prop">
                                                            <i class="bi bi-people"></i>
                                                        </p>
                                                        <button type="button" class="unblock-time-btn">
                                                            <i class="bi bi-toggle-off"></i>
                                                        </button>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    @endif

                                    {{-- ── novembre 2025: notifica WhatsApp ordine ─────────────────────────── --}}
                                    @if ($isNov)
                                        <div style="pointer-events: none; user-select: none; display: flex; justify-content: center;">
                                            <div class="doc-whatsapp__phone" style="max-width: 240px; width: 100%;">
                                                <div class="doc-whatsapp__top">
                                                    <strong>WhatsApp</strong>
                                                    <span>F+</span>
                                                </div>
                                                <div class="doc-whatsapp__screen">
                                                    <div class="doc-whatsapp__bubble">
                                                        <p><strong>Nuova notifica!</strong></p>
                                                        <p>Tipo: <strong>Ordine d'asporto</strong></p>
                                                        <p>Luca Verdi — 12/04 21:00</p>
                                                        <p>2× Burger classico</p>
                                                        <p>1× Patate rustiche</p>
                                                        <p>📞 348 441 9821 &nbsp;·&nbsp; 🔗 Dashboard</p>
                                                    </div>
                                                    <div class="doc-whatsapp__actions">
                                                        <button type="button" class="doc-whatsapp__action">Conferma</button>
                                                        <button type="button" class="doc-whatsapp__action doc-whatsapp__action--ghost">Annulla</button>
                                                    </div>
                                                    <div class="doc-whatsapp__reply">
                                                        <p>Ordine confermato per le 21:00. A presto!</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    {{-- ── marzo 2026: filtro periodo statistiche ──────────────────────────────── --}}
                                    @if ($isMar)
                                        <div style="pointer-events: none; user-select: none;">
                                            <div class="statistics-page__floating-filter" style="position: static; box-shadow: none;">
                                                <label class="statistics-page__period-control">
                                                    <span>Periodo analizzato</span>
                                                    <select>
                                                        <option>Ultimi 7 giorni</option>
                                                        <option selected>Ultimi 30 giorni</option>
                                                        <option>Ultimi 90 giorni</option>
                                                        <option>Ultimi 12 mesi</option>
                                                        <option>Tutti i dati</option>
                                                    </select>
                                                </label>
                                                <div class="statistics-page__hero-badges statistics-page__hero-badges--floating">
                                                    <span class="settings-state settings-state--neutral">1 mar — 30 mar 2026</span>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    {{-- ── aprile 2026: promozione applicata su ordine e su prenotazione ──────── --}}
                                    @if ($isApr)
                                        <div style="pointer-events: none; user-select: none;">
                                            <div class="time-list">

                                                {{-- Ordine con promo applicata --}}
                                                <div class="time-item">
                                                    <div class="time-header">
                                                        <strong>20:00</strong>
                                                        <div class="line"></div>
                                                        <p class="prop">
                                                            <i class="bi bi-bag"></i>
                                                        </p>
                                                    </div>
                                                    <div class="time-content">
                                                        <a href="#" class="order-item okk">
                                                            <div class="top">
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi okk bi-check-circle" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/><path d="m10.97 4.97-.02.022-3.473 4.425-2.093-2.094a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05"/></svg>
                                                                <div class="name">Luca Verdi</div>
                                                                <div class="paid status"><i class="bi bi-credit-card-2-back"></i> Pagato</div>
                                                                <div class="promo status paid" title="Benvenuto asporto"><i class="bi bi-gift-fill"></i> -€5,00</div>
                                                                <div class="price">€27,00</div>
                                                            </div>
                                                            <div class="cart">
                                                                <div class="item_cart">
                                                                    <div class="name">Promo: Benvenuto asporto</div>
                                                                    <div class="price">-€5,00</div>
                                                                </div>
                                                                <div class="item_cart">
                                                                    <div class="name">Totale scontato</div>
                                                                    <div class="price">€27,00</div>
                                                                </div>
                                                                <div class="item_cart">
                                                                    <div class="name">2× Burger classico</div>
                                                                    <div class="price">€24,00</div>
                                                                </div>
                                                            </div>
                                                        </a>
                                                    </div>
                                                </div>

                                                {{-- Prenotazione con promo applicata --}}
                                                <div class="time-item">
                                                    <div class="time-header">
                                                        <strong>20:30</strong>
                                                        <div class="line"></div>
                                                        <p class="prop">
                                                            <i class="bi bi-people"></i>
                                                        </p>
                                                    </div>
                                                    <div class="time-content">
                                                        <a href="#" class="res-item okk">
                                                            <div class="top">
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi okk bi-check-circle" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/><path d="m10.97 4.97-.02.022-3.473 4.425-2.093-2.094a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05"/></svg>
                                                                <div class="name">Chiara Riva</div>
                                                                <div class="paid status"><i class="bi bi-credit-card-2-back"></i> Pagato</div>
                                                                <div class="promo status paid" title="Compleanno speciale"><i class="bi bi-gift-fill"></i> -€10,00</div>
                                                                <div class="guest">4<i class="bi bi-person-standing"></i></div>
                                                            </div>
                                                            <div class="cart">
                                                                <div class="item_cart">
                                                                    <div class="name">Promo: Compleanno speciale</div>
                                                                    <div class="price">-€10,00</div>
                                                                </div>
                                                                <div class="item_cart">
                                                                    <div class="name">Totale scontato</div>
                                                                    <div class="price">€50,00</div>
                                                                </div>
                                                            </div>
                                                        </a>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    @endif

                                    {{-- ── maggio 2026: campagna email in invio con progress bar animata ──────── --}}
                                    @if ($isMag)
                                        <div class="cl-campaign-preview">
                                            <div class="cl-campaign-meta">
                                                <span class="cl-campaign-badge">
                                                    <i class="bi bi-play-circle-fill"></i>
                                                    In invio
                                                </span>
                                                <span class="cl-campaign-name">Campagna primavera 2026</span>
                                            </div>

                                            <div class="cl-campaign-bar-wrap">
                                                <div class="cl-campaign-bar-label">
                                                    <span>62 di 100 email inviate</span>
                                                    <span>62%</span>
                                                </div>
                                                <div class="cl-campaign-bar-shell">
                                                    <div class="cl-campaign-bar" style="width: 62%;"></div>
                                                    <div class="cl-campaign-bar-pct">62%</div>
                                                </div>
                                            </div>

                                            <div class="cl-campaign-kpis">
                                                <div class="cl-campaign-kpi">
                                                    <span>Aperture</span>
                                                    <strong>28</strong>
                                                </div>
                                                <div class="cl-campaign-kpi">
                                                    <span>Click</span>
                                                    <strong>14</strong>
                                                </div>
                                                <div class="cl-campaign-kpi cl-campaign-kpi--accent">
                                                    <span>Promo usate</span>
                                                    <strong>6</strong>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                </div>
                            </div>
                        </div>
                    @endif

                </div>
            </article>
        @endforeach
    </div>
@endsection
