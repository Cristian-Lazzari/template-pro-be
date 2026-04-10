<section class="public-panel public-panel--soft">
    <div class="public-panel__header">
        <p class="public-panel__eyebrow">Step 1</p>
        <h2>Una prenotazione tavolo la puoi aprire da dashboard, calendario o lista condivisa</h2>
    </div>

    <div class="doc-route-grid">
        <article class="settings-panel doc-route-card">
            <div class="settings-panel__header">
                <p class="settings-kicker">Dashboard</p>
                <h2>Quando stai seguendo il servizio slot per slot</h2>
            </div>

            <p class="settings-lead">Apri il giorno, entri nella fascia oraria e da li apri subito la prenotazione che devi decidere.</p>

            <div class="day-details doc-route-preview">
                <div class="day-info">
                    <div class="time-list">
                        <div class="time-item">
                            <div class="time-header">
                                <strong>20:30</strong>
                                <div class="line"></div>
                                <p class="prop">
                                    <i class="bi bi-people" style="font-size: 16px"></i>
                                </p>
                            </div>

                            <div class="time-content">
                                <a href="#" class="res-item to_see">
                                    <div class="top">
                                        <div class="id">R148</div>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi to_see bi-exclamation-circle-fill" viewBox="0 0 16 16">
                                            <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M8 4a.905.905 0 0 0-.9.995l.35 3.507a.552.552 0 0 0 1.1 0l.35-3.507A.905.905 0 0 0 8 4m.002 6a1 1 0 1 0 0 2 1 1 0 0 0 0-2"/>
                                        </svg>
                                        <div class="name">Giulia Rossi</div>
                                        <div class="guest">
                                            2
                                            <i class="bi bi-person-standing" style="font-size: 16px"></i>
                                            1
                                            <i class="bi bi-person-arms-up" style="font-size: 16px"></i>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </article>

        <article class="settings-panel doc-route-card">
            <div class="settings-panel__header">
                <p class="settings-kicker">Calendario</p>
                <h2>Quando devi leggere il carico di coperti della giornata</h2>
            </div>

            <p class="settings-lead">Nel calendario il badge principale non conta le prenotazioni ma il totale ospiti del giorno.</p>

            <div class="calendar dashboard-calendar-preview doc-route-preview">
                <div class="c-name">
                    <h4>lu</h4>
                    <h4>ma</h4>
                    <h4>me</h4>
                    <h4>gi</h4>
                    <h4>ve</h4>
                    <h4>sa</h4>
                    <h4>do</h4>
                </div>

                <div class="calendar_page">
                    <button class="day" style="grid-column-start:1"><p class="p_day">7</p></button>
                    <button class="day" style="grid-column-start:2"><p class="p_day">8</p></button>
                    <button class="day" style="grid-column-start:3"><p class="p_day">9</p></button>
                    <button class="day" style="grid-column-start:4"><p class="p_day">10</p></button>
                    <button class="day" style="grid-column-start:5"><p class="p_day">11</p></button>
                    <button class="day current day-active" style="grid-column-start:6">
                        <p class="p_day">12</p>
                        <span class="bookings">
                            <strong>9</strong>
                            <i class="bi bi-person-lines-fill" style="font-size: 16px"></i>
                        </span>
                        <span class="bookings top">
                            <strong>2</strong>
                            <i class="bi bi-inboxes" style="font-size: 16px"></i>
                        </span>
                    </button>
                </div>
            </div>
        </article>

        <article class="settings-panel doc-route-card">
            <div class="settings-panel__header">
                <p class="settings-kicker">Lista</p>
                <h2>Quando vuoi filtrare solo le prenotazioni e scorrerle in ordine</h2>
            </div>

            <p class="settings-lead">La lista e condivisa con gli ordini, quindi il primo gesto utile e portare il toggle su Prenotazioni.</p>

            <div class="doc-list-preview">
                <div class="filters doc-filters-preview">
                    <div class="bar">
                        <div class="box">
                            <input type="text" class="search" value="rossi" placeholder="Cerca cliente">
                            <button class="type">Prenotazioni</button>
                            <button class="order">
                                <i class="bi bi-sort-down-alt" style="font-size: 16px"></i>
                            </button>
                        </div>
                        <label>
                            <i class="bi bi-funnel-fill" style="font-size: 16px"></i>
                        </label>
                    </div>
                </div>

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
                </div>
            </div>
        </article>
    </div>
</section>

<section class="public-panel">
    <div class="public-panel__header">
        <p class="public-panel__eyebrow">Step 2</p>
        <h2>Se lavori dalla lista, il flusso vero e cerca, filtra tipo e poi apri la scheda</h2>
    </div>

    <div class="doc-tutorial-grid">
        <article class="settings-panel settings-panel--primary doc-tutorial-copy">
            <div class="settings-panel__header">
                <p class="settings-kicker">Filtri</p>
                <h2>I controlli sono quelli reali della pagina lista</h2>
            </div>

            <p class="settings-lead">La pagina `Lista prenotazioni & ordini` usa tre controlli JavaScript: ricerca per testo, toggle del tipo e inversione del verso cronologico su `date_slot`.</p>

            <x-dashboard.field-list class="dashboard-field-list--stacked" :items="[
                ['label' => 'Cerca cliente', 'value' => 'Filtra nome e cognome direttamente nell elenco'],
                ['label' => 'Tipo', 'value' => 'Scorri da Tutti a Prenotazioni e poi a Ordini'],
                ['label' => 'Ordine', 'value' => 'Passi da piu recenti a piu vecchi e viceversa'],
            ]" />
        </article>

        <div class="dashboard-preview-stack">
            <article class="settings-panel">
                <div class="settings-panel__header">
                    <p class="settings-kicker">Passaggi</p>
                    <h2>Come la usi davvero nel turno</h2>
                </div>

                <x-dashboard.field-list class="dashboard-field-list--stacked" :items="[
                    ['label' => '1', 'value' => 'Apri la lista condivisa di prenotazioni e ordini'],
                    ['label' => '2', 'value' => 'Premi Tipo finche compare Prenotazioni'],
                    ['label' => '3', 'value' => 'Cerchi il cliente oppure scorri per fascia oraria'],
                    ['label' => '4', 'value' => 'Apri la scheda dal record che ti interessa'],
                ]" />
            </article>

            <article class="settings-panel">
                <div class="settings-panel__header">
                    <p class="settings-kicker">Quando conviene</p>
                    <h2>E la vista piu comoda se devi recuperare una richiesta al telefono</h2>
                </div>

                <p class="settings-lead">Funziona bene quando hai il cliente in linea, vuoi cercare per cognome oppure devi isolare le sole prenotazioni in pochi secondi.</p>
            </article>
        </div>
    </div>
</section>

<section class="public-panel">
    <div class="public-panel__header">
        <p class="public-panel__eyebrow">Step 3</p>
        <h2>Dal calendario leggi i coperti, entri nello slot e puoi anche bloccare l orario</h2>
    </div>

    <div class="doc-tutorial-grid">
        <div class="dashboard-preview-stack">
            <div class="calendar dashboard-calendar-preview">
                <div class="c-name">
                    <h4>lu</h4>
                    <h4>ma</h4>
                    <h4>me</h4>
                    <h4>gi</h4>
                    <h4>ve</h4>
                    <h4>sa</h4>
                    <h4>do</h4>
                </div>

                <div class="calendar_page">
                    <button class="day" style="grid-column-start:1"><p class="p_day">7</p></button>
                    <button class="day" style="grid-column-start:2"><p class="p_day">8</p></button>
                    <button class="day" style="grid-column-start:3"><p class="p_day">9</p></button>
                    <button class="day" style="grid-column-start:4"><p class="p_day">10</p></button>
                    <button class="day" style="grid-column-start:5"><p class="p_day">11</p></button>
                    <button class="day current day-active" style="grid-column-start:6">
                        <p class="p_day">12</p>
                        <span class="bookings">
                            <strong>9</strong>
                            <i class="bi bi-person-lines-fill" style="font-size: 16px"></i>
                        </span>
                        <span class="bookings top">
                            <strong>2</strong>
                            <i class="bi bi-inboxes" style="font-size: 16px"></i>
                        </span>
                    </button>
                </div>
            </div>

            <div class="day-details">
                <div class="day-info">
                    <div class="time-list">
                        <div class="time-item">
                            <div class="time-header">
                                <strong>20:30</strong>
                                <div class="line"></div>
                                <p class="prop">
                                    <i class="bi bi-people" style="font-size: 16px"></i>
                                </p>
                                <button type="button" class="block-time-btn">
                                    <i class="bi bi-toggle-on" style="font-size: 16px"></i>
                                </button>
                            </div>

                            <div class="time-content">
                                <a href="#" class="res-item to_see">
                                    <div class="top">
                                        <div class="id">R148</div>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi to_see bi-exclamation-circle-fill" viewBox="0 0 16 16">
                                            <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M8 4a.905.905 0 0 0-.9.995l.35 3.507a.552.552 0 0 0 1.1 0l.35-3.507A.905.905 0 0 0 8 4m.002 6a1 1 0 1 0 0 2 1 1 0 0 0 0-2"/>
                                        </svg>
                                        <div class="name">Giulia Rossi</div>
                                        <div class="guest">
                                            2
                                            <i class="bi bi-person-standing" style="font-size: 16px"></i>
                                            1
                                            <i class="bi bi-person-arms-up" style="font-size: 16px"></i>
                                        </div>
                                    </div>
                                </a>

                                <a href="#" class="res-item okk">
                                    <div class="top">
                                        <div class="id">R151</div>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi okk bi-check-circle" viewBox="0 0 16 16">
                                            <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
                                            <path d="m10.97 4.97-.02.022-3.473 4.425-2.093-2.094a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05"/>
                                        </svg>
                                        <div class="name">Marco Ferri</div>
                                        <div class="guest">
                                            4
                                            <i class="bi bi-person-standing" style="font-size: 16px"></i>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>

                        <div class="time-item blocked">
                            <div class="time-header">
                                <strong>21:30</strong>
                                <div class="line blocked-line"></div>
                                <p class="prop">
                                    <i class="bi bi-people" style="font-size: 16px"></i>
                                </p>
                                <button type="button" class="unblock-time-btn">
                                    <i class="bi bi-toggle-off" style="font-size: 16px"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <article class="settings-panel doc-tutorial-copy">
            <div class="settings-panel__header">
                <p class="settings-kicker">Calendario</p>
                <h2>Qui il punto e capire il carico reale della sala, non solo contare le richieste</h2>
            </div>

            <p class="settings-lead">Il calendario costruisce gli slot da `week_set`, mostra il totale ospiti nel giorno e, sul dettaglio della fascia, ti lascia bloccare o sbloccare gli orari futuri senza uscire dalla pagina.</p>

            <x-dashboard.field-list class="dashboard-field-list--stacked" :items="[
                ['label' => 'Badge giorno', 'value' => 'Il numero principale e il totale ospiti del giorno'],
                ['label' => 'Fascia oraria', 'value' => 'Vedi subito chi e in attesa e chi e gia confermato'],
                ['label' => 'Toggle slot', 'value' => 'Blocchi o riapri l orario direttamente dal dettaglio'],
            ]" />
        </article>
    </div>
</section>

<section class="public-panel">
    <div class="public-panel__header">
        <p class="public-panel__eyebrow">Step 4</p>
        <h2>Quando arriva una nuova richiesta, il sistema verifica capienza e poi attiva notifiche e dashboard</h2>
    </div>

    <div class="doc-tutorial-grid">
        <article class="settings-panel doc-tutorial-copy">
            <div class="settings-panel__header">
                <p class="settings-kicker">Evento iniziale</p>
                <h2>Il flusso reale parte da `POST /api/reservations`</h2>
            </div>

            <p class="settings-lead">La prenotazione non entra in dashboard alla cieca: prima il backend controlla slot attivo, giorno non chiuso, sala corretta e posti ancora disponibili. Solo dopo salva il record come `In attesa`.</p>

            <x-dashboard.field-list class="dashboard-field-list--stacked" :items="[
                ['label' => '1', 'value' => 'Valida nome, contatti, adulti, bambini e note'],
                ['label' => '2', 'value' => 'Controlla `week_set`, `day_off` e capienza disponibile nello slot'],
                ['label' => '3', 'value' => 'Salva la prenotazione con stato In attesa'],
                ['label' => '4', 'value' => 'Invia WhatsApp agli admin e mail sia al locale sia al cliente'],
                ['label' => '5', 'value' => 'La richiesta compare in dashboard, calendario e lista condivisa'],
            ]" />
        </article>

        <div class="dashboard-preview-stack">
            <div class="doc-whatsapp">
                <div class="doc-whatsapp__phone">
                    <div class="doc-whatsapp__top">
                        <strong>WhatsApp</strong>
                        <span>F+</span>
                    </div>

                    <div class="doc-whatsapp__screen">
                        <div class="doc-whatsapp__bubble">
                            <p><strong>Hai una nuova notifica!</strong></p>
                            <p>Contenuto della notifica: <strong>Prenotazione tavolo</strong></p>
                            <p>Giulia Rossi ha prenotato per il 12/04/2026 20:30</p>
                            <p>2 adulti e 1 bambino</p>
                            <p>Sala prenotata: Sala Interna</p>
                            <p>📞 Chiama: 333 555 1020</p>
                            <p>🔗 Vedi dalla Dashboard</p>
                        </div>

                        <div class="doc-whatsapp__actions">
                            <button type="button" class="doc-whatsapp__action">Conferma</button>
                            <button type="button" class="doc-whatsapp__action doc-whatsapp__action--ghost">Annulla</button>
                        </div>
                    </div>
                </div>
            </div>

            <x-dashboard.mail-preview
                variant="transactional"
                subject="Nuova prenotazione per sabato 12 aprile alle 20:30"
                sender="{{ config('configurazione.APP_NAME') }}"
                headline="Prenotazione ricevuta"
                subheadline="sabato 12 aprile alle 20:30"
                greeting="Ciao Giulia,"
                intro="La richiesta e stata registrata e inviata al locale. In questo momento la dashboard la mostra come In attesa."
                :items="[
                    'Sala prenotata: Sala Interna',
                    'Numero di adulti: 2',
                    'Numero di bambini: 1',
                    'Messaggio: Tavolo vicino alla vetrata, se disponibile.',
                ]"
                cta="Chiama il locale"
                footer="Se il locale conferma o annulla, il cliente riceve un nuovo aggiornamento automatico."
            />
        </div>
    </div>
</section>

<section class="public-panel">
    <div class="public-panel__header">
        <p class="public-panel__eyebrow">Step 5</p>
        <h2>La scheda prenotazione e il punto in cui controlli stato, ospiti, note e azione giusta</h2>
    </div>

    <div class="doc-tutorial-grid">
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

        <div class="dashboard-preview-stack">
            <article class="settings-panel settings-panel--primary">
                <div class="settings-panel__header">
                    <p class="settings-kicker">Dentro la scheda</p>
                    <h2>Il componente reale e `x-dashboard.reservation-detail`</h2>
                </div>

                <x-dashboard.field-list class="dashboard-field-list--stacked" :items="[
                    ['label' => 'Controlla', 'value' => 'Stato, contatti, ospiti, sala e messaggio del cliente'],
                    ['label' => 'Conferma', 'value' => 'Compare sulle richieste In attesa e anche su quelle Annullate se vuoi riaprirle'],
                    ['label' => 'Annulla', 'value' => 'Resta disponibile quando la richiesta e ancora gestibile'],
                    ['label' => 'Stati UI', 'value' => 'La scheda mostra In attesa, Confermata, Annullata e supporta anche stati pagati legacy'],
                ]" />
            </article>

            <article class="settings-panel">
                <div class="settings-panel__header">
                    <p class="settings-kicker">Pulsanti</p>
                    <h2>Le azioni dipendono dallo stato corrente</h2>
                </div>

                <div class="doc-action-strip">
                    <button type="button" class="my_btn_3">Conferma</button>
                    <button type="button" class="my_btn_5">Annulla</button>
                </div>
            </article>
        </div>
    </div>
</section>

<section class="public-panel">
    <div class="public-panel__header">
        <p class="public-panel__eyebrow">Step 6</p>
        <h2>Conferma e annulla usano la stessa scheda rapida, con scelta chiara sul canale extra</h2>
    </div>

    <div class="doc-tutorial-grid">
        <div class="dashboard-preview-stack">
            <x-dashboard.action-modal
                preview
                class="dashboard-modal-preview"
                title="Conferma prenotazione"
                eyebrow="Conferma"
                tone="success"
                entity-label="Prenotazione di"
                subject="Giulia"
                date-slot="12/04/2026 20:30"
                description="La mail automatica parte sempre. Qui scegli solo se aggiungere anche WhatsApp."
            >
                <x-slot name="details">
                    <div class="dashboard-action-modal__detail">
                        <span>Stato finale</span>
                        <strong>Confermata</strong>
                    </div>

                    <div class="dashboard-action-modal__detail">
                        <span>Canale base</span>
                        <strong>Email automatica</strong>
                    </div>
                </x-slot>

                <p class="dashboard-action-modal__hint">Le etichette dei pulsanti spiegano subito cosa cambia.</p>

                <x-slot name="footer">
                    <button type="button" class="w-100 my_btn_2">Solo email</button>
                    <button type="button" class="w-100 my_btn_3">Email + WhatsApp</button>
                </x-slot>
            </x-dashboard.action-modal>

            <x-dashboard.action-modal
                preview
                class="dashboard-modal-preview"
                title="Annulla prenotazione"
                eyebrow="Annulla"
                tone="danger"
                entity-label="Prenotazione di"
                subject="Giulia"
                date-slot="12/04/2026 20:30"
                description="Usa questa azione solo se non puoi confermare la richiesta. La mail automatica parte sempre."
            >
                <x-slot name="details">
                    <div class="dashboard-action-modal__detail">
                        <span>Stato finale</span>
                        <strong>Annullata</strong>
                    </div>

                    <div class="dashboard-action-modal__detail">
                        <span>Canale base</span>
                        <strong>Email automatica</strong>
                    </div>
                </x-slot>

                <p class="dashboard-action-modal__hint">Stessa struttura, stesso ritmo di lettura, esito diverso.</p>

                <x-slot name="footer">
                    <button type="button" class="w-100 my_btn_2">Solo email</button>
                    <button type="button" class="w-100 my_btn_5">Email + WhatsApp</button>
                </x-slot>
            </x-dashboard.action-modal>
        </div>

        <div class="dashboard-preview-stack">
            <article class="settings-panel settings-panel--primary">
                <div class="settings-panel__header">
                    <p class="settings-kicker">Esito</p>
                    <h2>Il risultato resta uguale, cambia solo se affianchi WhatsApp alla mail</h2>
                </div>

                <x-dashboard.field-list class="dashboard-field-list--stacked" :items="[
                    ['label' => 'Solo email', 'value' => 'Chiude l azione subito e invia la mail automatica'],
                    ['label' => 'Email + WhatsApp', 'value' => 'Alla mail aggiunge anche l apertura del messaggio WhatsApp'],
                    ['label' => 'Conferma o Annulla', 'value' => 'La scelta del canale extra non cambia l esito finale della prenotazione'],
                ]" />
            </article>

            <x-dashboard.mail-preview
                variant="transactional"
                subject="Conferma prenotazione per sabato 12 aprile alle 20:30"
                sender="{{ config('configurazione.APP_NAME') }}"
                headline="Prenotazione confermata"
                subheadline="sabato 12 aprile alle 20:30"
                greeting="Ciao Giulia,"
                intro="La conferma parte sempre via email. Se il locale sceglie Email + WhatsApp nella modale, si aggiunge anche il messaggio al cliente."
                :items="[
                    'Sala prenotata: Sala Interna',
                    'Numero di adulti: 2',
                    'Numero di bambini: 1',
                    'Messaggio: Tavolo vicino alla vetrata, se disponibile.',
                ]"
                cta="Chiama il locale"
                footer="Se l installazione ha WhatsApp attivo, lo stesso `whatsapp_message_id` puo essere usato anche per annullo da cliente o gestione da webhook."
            />
        </div>
    </div>
</section>
