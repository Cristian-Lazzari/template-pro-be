<section class="public-panel public-panel--soft">
    <div class="public-panel__header">
        <p class="public-panel__eyebrow">Blocchi reali del dashboard</p>
        <h2>Onboarding costruito sui pannelli impostazioni gia presenti</h2>
    </div>

    <div class="dashboard-preview-grid">
        <article class="settings-panel settings-panel--primary">
            <div class="settings-panel__header">
                <p class="settings-kicker">Stato attuale</p>
                <h2>Stati da controllare prima del primo servizio</h2>
                <p>Qui la documentazione riusa direttamente badge e card stato del pannello impostazioni.</p>
            </div>

            <x-dashboard.status-grid :items="[
                ['label' => 'Tavoli', 'value' => 'Online', 'tone' => 'active'],
                ['label' => 'Asporto', 'value' => 'Online', 'tone' => 'active'],
                ['label' => 'Domicilio', 'value' => 'Off', 'tone' => 'off'],
                ['label' => 'Lingua', 'value' => 'IT', 'tone' => 'neutral'],
            ]" />
        </article>

        <div class="dashboard-preview-stack">
            <article class="settings-panel">
                <div class="settings-panel__header">
                    <p class="settings-kicker">Disponibilita</p>
                    <h2>Campi che il team controlla subito</h2>
                </div>

                <x-dashboard.field-list :items="[
                    ['label' => 'Latenza prenotazioni', 'value' => '00:30'],
                    ['label' => 'Orario inizio', 'value' => '12:00'],
                    ['label' => 'Orario fine', 'value' => '23:00'],
                    ['label' => 'Intervallo minuti', 'value' => '30'],
                ]" />
            </article>

            <article class="settings-panel">
                <div class="settings-panel__header">
                    <p class="settings-kicker">Consegna interna</p>
                    <h2>Prima di passare il backoffice al locale</h2>
                </div>

                <ul class="public-list">
                    <li>Conferma ruolo ed email del collaboratore.</li>
                    <li>Verifica servizi attivi davvero per il locale.</li>
                    <li>Controlla orari, capienza e intervalli del servizio.</li>
                </ul>
            </article>
        </div>
    </div>
</section>
