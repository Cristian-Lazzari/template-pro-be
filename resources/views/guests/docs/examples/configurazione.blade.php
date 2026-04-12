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
                                        <i class="bi bi-x-circle-fill"></i>
                                    </span>
                                </label>
                                <label class="radio">
                                    <input type="radio" disabled>
                                    <span class="name">
                                        <i class="bi bi-telephone-fill"></i>
                                    </span>
                                </label>
                                <label class="radio">
                                    <input type="radio" checked disabled>
                                    <span class="name">
                                        <i class="bi bi-window-sidebar"></i>
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
                                        <i class="bi bi-x-circle-fill"></i>
                                    </span>
                                </label>
                                <label class="radio">
                                    <input type="radio" checked disabled>
                                    <span class="name">
                                        <i class="bi bi-telephone-fill"></i>
                                    </span>
                                </label>
                                <label class="radio">
                                    <input type="radio" disabled>
                                    <span class="name">
                                        <i class="bi bi-window-sidebar"></i>
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
