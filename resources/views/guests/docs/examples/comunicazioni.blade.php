<section class="public-panel public-panel--soft">
    <div class="public-panel__header">
        <p class="public-panel__eyebrow">Esempio visivo</p>
        <h2>Composer campagna con destinatari, oggetto e programmazione</h2>
    </div>

    <div class="doc-showcase">
        <div class="doc-console">
            <div class="doc-console__header">
                <div>
                    <strong>Nuova campagna email</strong>
                    <p>Layout semplice per costruire una comunicazione chiara prima dell invio.</p>
                </div>
                <span class="badge text-bg-success">Programmata</span>
            </div>

            <div class="doc-compose-card">
                <div class="doc-detail-row">
                    <span>Oggetto</span>
                    <strong>Brunch speciale di domenica</strong>
                </div>
                <div class="doc-detail-row">
                    <span>Lista</span>
                    <strong>Clienti che hanno prenotato nelle ultime 6 settimane</strong>
                </div>
                <div class="doc-detail-row">
                    <span>Invio</span>
                    <strong>Domenica ore 11:30</strong>
                </div>
                <div class="doc-detail-row">
                    <span>Pulsante</span>
                    <strong>Prenota il tuo tavolo</strong>
                </div>
            </div>
        </div>

        <div class="doc-side-panel">
            <div class="doc-mini-list">
                <div class="doc-mini-list__item">
                    <i class="bi bi-file-earmark-text"></i>
                    <span>Template promo gia selezionato</span>
                </div>
                <div class="doc-mini-list__item">
                    <i class="bi bi-people"></i>
                    <span>184 destinatari inclusi</span>
                </div>
                <div class="doc-mini-list__item">
                    <i class="bi bi-send-check"></i>
                    <span>Programmazione confermata</span>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="public-panel">
    <div class="public-panel__header">
        <p class="public-panel__eyebrow">Email di esempio</p>
        <h2>Newsletter promo pronta in HTML</h2>
    </div>

    @include('guests.partials.doc-email-preview', [
        'subject' => 'Brunch speciale di domenica: posti limitati',
        'preheader' => 'Nuovo menu brunch con prenotazione consigliata.',
        'badge' => 'Promo domenica',
        'title' => 'Domenica torna il brunch della casa',
        'greeting' => 'Ciao,',
        'intro' => 'questa domenica trovi un brunch speciale con formula a prezzo fisso, dolce artigianale e bevanda inclusa.',
        'items' => [
            'Orario: dalle 11:30 alle 15:00',
            'Formula: 24,00 EUR a persona',
            'Prenotazione consigliata per tavoli interni',
        ],
        'cta' => 'Prenota il tuo tavolo',
        'footer' => 'Se hai gia in programma una visita, rispondi a questa email e ti aiutiamo con la prenotazione.',
    ])
</section>
