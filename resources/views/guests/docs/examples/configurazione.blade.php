@php
    $packLabels = ['', 'Essentials', 'Work on', 'Boost up', 'Prova gratuita', 'Boost up +'];
@endphp

<section class="public-panel public-panel--soft">
    <div class="public-panel__header">
        <p class="public-panel__eyebrow">Blocchi reali del dashboard</p>
        <h2>Configurazione allineata ai pannelli e agli stati della pagina impostazioni</h2>
    </div>

    <div class="settings-overview">
        <article class="settings-overview__intro">
            <p class="settings-kicker">Stato attuale</p>
            <h2>Servizi e canali visibili nello stesso linguaggio del dashboard</h2>
            <p class="settings-lead">Nessun riepilogo sintetico inventato: solo stati reali come Online, Telefono, Attivo, Off e lingua di default.</p>

            <x-dashboard.status-grid :items="[
                ['label' => 'Tavoli', 'value' => 'Online', 'tone' => 'active'],
                ['label' => 'Asporto', 'value' => 'Telefono', 'tone' => 'warning'],
                ['label' => 'Domicilio', 'value' => 'Off', 'tone' => 'off'],
                ['label' => 'Ferie', 'value' => 'Operativo', 'tone' => 'active'],
                ['label' => 'Promo tavoli', 'value' => 'Off', 'tone' => 'off'],
                ['label' => 'Lingua', 'value' => 'IT', 'tone' => 'neutral'],
            ]" />
        </article>

        <aside class="settings-overview__aside">
            <div class="targhetta">
                <a href="{{ config('configurazione.domain') }}" class="img_bg">
                    <img src="{{ config('configurazione.domain') . '/img/favicon.png' }}" alt="">
                </a>
                <a href="{{ config('configurazione.domain') }}">
                    <h2>{{ config('configurazione.APP_NAME') }}</h2>
                </a>
                <a class="pack" href="https://future-plus.it/#pacchetti">
                    <img src="https://future-plus.it/img/favicon.png" alt="">
                    Pacchetto: {{ $packLabels[config('configurazione.subscription')] ?? 'Attivo' }}
                </a>
            </div>

            <article class="settings-theme-card">
                <p class="settings-theme-card__eyebrow">Campi chiave</p>
                <x-dashboard.field-list :items="[
                    ['label' => 'Latenza ordini', 'value' => '00:20'],
                    ['label' => 'Latenza prenotazioni', 'value' => '00:30'],
                    ['label' => 'Orario inizio', 'value' => '12:00'],
                    ['label' => 'Orario fine', 'value' => '23:00'],
                ]" />
            </article>
        </aside>
    </div>

    <div class="settings-page">
        <div class="setting">
            <div class="set">
                <div class="set-cont">
                    <div class="g_set">
                        <div class="settings-card-head">
                            <h5>Lingua di default</h5>
                            <x-dashboard.state-pill tone="neutral">IT</x-dashboard.state-pill>
                        </div>

                        <div class="radio-inputs">
                            <label class="radio">
                                <input type="radio" checked disabled>
                                <span class="name lang">IT</span>
                            </label>
                            <label class="radio">
                                <input type="radio" disabled>
                                <span class="name lang">EN</span>
                            </label>
                            <label class="radio">
                                <input type="radio" disabled>
                                <span class="name lang">DE</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="set">
                    <div class="set-cont">
                        <div class="g_set">
                            <div class="settings-card-head">
                                <h5>Tavoli</h5>
                                <x-dashboard.state-pill tone="active">Online</x-dashboard.state-pill>
                            </div>
                            <div class="radio-inputs">
                                <label class="radio">
                                    <input type="radio" disabled>
                                    <span class="name">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x-circle-fill" viewBox="0 0 16 16">
                                            <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M5.354 4.646a.5.5 0 1 0-.708.708L7.293 8l-2.647 2.646a.5.5 0 0 0 .708.708L8 8.707l2.646 2.647a.5.5 0 0 0 .708-.708L8.707 8l2.647-2.646a.5.5 0 0 0-.708-.708L8 7.293z"/>
                                        </svg>
                                    </span>
                                </label>
                                <label class="radio">
                                    <input type="radio" disabled>
                                    <span class="name">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-telephone-fill" viewBox="0 0 16 16">
                                            <path fill-rule="evenodd" d="M1.885.511a1.745 1.745 0 0 1 2.61.163L6.29 2.98c.329.423.445.974.315 1.494l-.547 2.19a.68.68 0 0 0 .178.643l2.457 2.457a.68.68 0 0 0 .644.178l2.189-.547a1.75 1.75 0 0 1 1.494.315l2.306 1.794c.829.645.905 1.87.163 2.611l-1.034 1.034c-.74.74-1.846 1.065-2.877.702a18.6 18.6 0 0 1-7.01-4.42 18.6 18.6 0 0 1-4.42-7.009c-.362-1.03-.037-2.137.703-2.877z"/>
                                        </svg>
                                    </span>
                                </label>
                                <label class="radio">
                                    <input type="radio" checked disabled>
                                    <span class="name">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-window-sidebar" viewBox="0 0 16 16">
                                            <path d="M2.5 4a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1m2-.5a.5.5 0 1 1-1 0 .5.5 0 0 1 1 0m1 .5a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1"/>
                                            <path d="M2 1a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V3a2 2 0 0 0-2-2zm12 1a1 1 0 0 1 1 1v2H1V3a1 1 0 0 1 1-1zM1 13V6h4v8H2a1 1 0 0 1-1-1m5 1V6h9v7a1 1 0 0 1-1 1z"/>
                                        </svg>
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="set">
                    <div class="set-cont">
                        <div class="g_set">
                            <div class="settings-card-head">
                                <h5>Asporto</h5>
                                <x-dashboard.state-pill tone="warning">Telefono</x-dashboard.state-pill>
                            </div>
                            <div class="radio-inputs">
                                <label class="radio">
                                    <input type="radio" disabled>
                                    <span class="name">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x-circle-fill" viewBox="0 0 16 16">
                                            <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M5.354 4.646a.5.5 0 1 0-.708.708L7.293 8l-2.647 2.646a.5.5 0 0 0 .708.708L8 8.707l2.646 2.647a.5.5 0 0 0 .708-.708L8.707 8l2.647-2.646a.5.5 0 0 0-.708-.708L8 7.293z"/>
                                        </svg>
                                    </span>
                                </label>
                                <label class="radio">
                                    <input type="radio" checked disabled>
                                    <span class="name">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-telephone-fill" viewBox="0 0 16 16">
                                            <path fill-rule="evenodd" d="M1.885.511a1.745 1.745 0 0 1 2.61.163L6.29 2.98c.329.423.445.974.315 1.494l-.547 2.19a.68.68 0 0 0 .178.643l2.457 2.457a.68.68 0 0 0 .644.178l2.189-.547a1.75 1.75 0 0 1 1.494.315l2.306 1.794c.829.645.905 1.87.163 2.611l-1.034 1.034c-.74.74-1.846 1.065-2.877.702a18.6 18.6 0 0 1-7.01-4.42 18.6 18.6 0 0 1-4.42-7.009c-.362-1.03-.037-2.137.703-2.877z"/>
                                        </svg>
                                    </span>
                                </label>
                                <label class="radio">
                                    <input type="radio" disabled>
                                    <span class="name">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-window-sidebar" viewBox="0 0 16 16">
                                            <path d="M2.5 4a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1m2-.5a.5.5 0 1 1-1 0 .5.5 0 0 1 1 0m1 .5a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1"/>
                                            <path d="M2 1a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V3a2 2 0 0 0-2-2zm12 1a1 1 0 0 1 1 1v2H1V3a1 1 0 0 1 1-1zM1 13V6h4v8H2a1 1 0 0 1-1-1m5 1V6h9v7a1 1 0 0 1-1 1z"/>
                                        </svg>
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
