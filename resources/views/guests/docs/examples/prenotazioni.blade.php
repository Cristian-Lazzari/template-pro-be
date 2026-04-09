<section class="public-panel public-panel--soft">
    <div class="public-panel__header">
        <p class="public-panel__eyebrow">Blocchi reali del dashboard</p>
        <h2>Lista prenotazioni e dettaglio costruiti sul markup reale della dashboard</h2>
    </div>

    <div class="dashboard-preview-grid">
        <div class="dashboard-preview-stack">
            <div class="time-list res_index">
                <p class="date_time">
                    <span class="time">20:30</span>
                    <span class="line"></span>
                    <span class="data">sabato 12 aprile</span>
                </p>

                @include('shared.dashboard.queue-item', [
                    'type' => 'reservation',
                    'status' => 2,
                    'name' => 'Giulia',
                    'surname' => 'Rossi',
                    'nPerson' => ['adult' => 2, 'child' => 1],
                ])

                <p class="date_time">
                    <span class="time">21:00</span>
                    <span class="line"></span>
                    <span class="data">sabato 12 aprile</span>
                </p>

                @include('shared.dashboard.queue-item', [
                    'type' => 'reservation',
                    'status' => 1,
                    'name' => 'Marco',
                    'surname' => 'Ferri',
                    'nPerson' => ['adult' => 2, 'child' => 0],
                ])
            </div>
        </div>

        <div class="reservation-detail-page">
            <x-dashboard.reservation-detail
                :status="2"
                reservation-code="R148"
                time="20:30"
                date-label="sabato 12/04/2026"
                customer="Giulia Rossi"
                email="giulia.rossi@email.it"
                phone="333 555 1020"
                :adults="2"
                :children="1"
                room-label="Sala Interna"
                note="Tavolo vicino alla vetrata, se disponibile."
                sent-at="09:18:00 sabato 12 aprile 2026"
                marketing="si"
            >
                <button type="button" class="my_btn_3">Conferma</button>
                <button type="button" class="my_btn_5">Annulla</button>
            </x-dashboard.reservation-detail>
        </div>
    </div>
</section>

<section class="public-panel">
    <div class="public-panel__header">
        <p class="public-panel__eyebrow">Email reale</p>
        <h2>Conferma prenotazione nello stesso schema dei template automatici</h2>
    </div>

    <x-dashboard.mail-preview
        variant="transactional"
        subject="Conferma prenotazione per sabato 12 aprile alle 20:30"
        sender="{{ config('configurazione.APP_NAME') }}"
        headline="Prenotazione confermata"
        subheadline="sabato 12 aprile alle 20:30"
        greeting="Ciao Giulia,"
        intro="Ti confermiamo la prenotazione registrata nel gestionale e inviata al locale."
        :items="[
            'Data prenotata: sabato 12 aprile alle 20:30',
            'Sala prenota: Sala Interna',
            'Numero di adulti: 2',
            'Numero di bambini: 1',
            'Messaggio: Tavolo vicino alla vetrata, se disponibile.',
        ]"
        cta="Chiama il locale"
        footer="Se devi cambiare orario o numero ospiti, contatta il ristorante prima possibile."
    />
</section>
