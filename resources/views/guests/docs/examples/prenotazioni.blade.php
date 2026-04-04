<section class="public-panel public-panel--soft">
    <div class="public-panel__header">
        <p class="public-panel__eyebrow">Esempio visivo</p>
        <h2>Schermata prenotazioni con filtri, stati e dettaglio tavolo</h2>
    </div>

    <div class="doc-showcase">
        <div class="doc-console">
            <div class="doc-console__header">
                <div>
                    <strong>Prenotazioni di oggi</strong>
                    <p>Filtro rapido per vedere solo le richieste da gestire nel turno attuale.</p>
                </div>
                <div class="doc-tag-row">
                    <span class="badge text-bg-light">Oggi</span>
                    <span class="badge text-bg-warning">In attesa</span>
                    <span class="badge text-bg-light">4 persone</span>
                </div>
            </div>

            <div class="doc-record-list">
                <article class="doc-record-card">
                    <div class="doc-record-card__header">
                        <div>
                            <strong>Giulia Rossi</strong>
                            <span>Sabato 12 aprile, ore 20:30</span>
                        </div>
                        <span class="badge text-bg-warning">In attesa</span>
                    </div>
                    <div class="doc-record-card__meta">
                        <span><i class="bi bi-people"></i> 4 persone</span>
                        <span><i class="bi bi-telephone"></i> 333 555 1020</span>
                        <span><i class="bi bi-chat-left-text"></i> Richiesto tavolo vicino vetrata</span>
                    </div>
                </article>

                <article class="doc-record-card">
                    <div class="doc-record-card__header">
                        <div>
                            <strong>Marco Ferri</strong>
                            <span>Sabato 12 aprile, ore 21:00</span>
                        </div>
                        <span class="badge text-bg-success">Confermata</span>
                    </div>
                    <div class="doc-record-card__meta">
                        <span><i class="bi bi-people"></i> 2 persone</span>
                        <span><i class="bi bi-telephone"></i> 348 441 9821</span>
                        <span><i class="bi bi-chat-left-text"></i> Anniversario, tavolo tranquillo</span>
                    </div>
                </article>
            </div>
        </div>

        <div class="doc-side-panel">
            <div class="doc-detail-card">
                <h3>Dettaglio prenotazione</h3>
                <div class="doc-detail-row">
                    <span>Stato</span>
                    <strong>Da confermare</strong>
                </div>
                <div class="doc-detail-row">
                    <span>Contatto</span>
                    <strong>giulia.rossi@email.it</strong>
                </div>
                <div class="doc-detail-row">
                    <span>Note operative</span>
                    <strong>Nessun seggiolone richiesto</strong>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="public-panel">
    <div class="public-panel__header">
        <p class="public-panel__eyebrow">Email di esempio</p>
        <h2>Messaggio di conferma prenotazione</h2>
    </div>

    @include('guests.partials.doc-email-preview', [
        'subject' => 'Prenotazione confermata per sabato 12 aprile alle 20:30',
        'preheader' => 'Conferma tavolo per 4 persone presso Trattoria Centro.',
        'badge' => 'Conferma tavolo',
        'title' => 'La tua prenotazione e confermata',
        'greeting' => 'Ciao Giulia,',
        'intro' => 'ti confermiamo il tavolo per 4 persone da Trattoria Centro per sabato 12 aprile alle 20:30.',
        'items' => [
            'Nome prenotazione: Giulia Rossi',
            'Numero persone: 4',
            'Richiesta tavolo: vicino vetrata',
        ],
        'cta' => 'Chiama il locale',
        'footer' => 'Se hai bisogno di modificare orario o numero persone, contattaci prima possibile.',
    ])
</section>
