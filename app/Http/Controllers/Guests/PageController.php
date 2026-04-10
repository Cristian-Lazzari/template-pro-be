<?php

namespace App\Http\Controllers\Guests;

use App\Http\Controllers\Controller;

class PageController extends Controller
{
    public function home()
    {
        return view('guests.home', [
            'docPages' => $this->documentationList(),
        ]);
    }

    public function documentation()
    {
        return view('guests.documentation', [
            'onboarding' => $this->onboardingGuide(),
            'docPages' => $this->documentationList(),
            'quickActions' => $this->quickActions(),
        ]);
    }

    public function documentationTopic(string $page)
    {
        $pages = $this->documentationPages();

        abort_unless(isset($pages[$page]), 404);

        $topic = $pages[$page];
        $topic['related_pages'] = $this->relatedPages($topic['related'] ?? []);

        return view('guests.docs.show', [
            'page' => $topic,
            'docPages' => $this->documentationList(),
        ]);
    }

    public function updates()
    {
        return view('guests.updates', [
            'updates' => $this->releaseNotes(),
        ]);
    }

    private function documentationList(): array
    {
        return array_values($this->documentationPages());
    }

    private function relatedPages(array $slugs): array
    {
        $pages = $this->documentationPages();

        return array_values(array_filter(array_map(
            static fn (string $slug) => $pages[$slug] ?? null,
            $slugs
        )));
    }

    private function quickActions(): array
    {
        return [
            [
                'icon' => 'calendar-check',
                'title' => 'E arrivata una nuova prenotazione',
                'description' => 'Vai subito alla pagina Prenotazioni per vedere filtri, stati e conferme da inviare al cliente.',
                'page' => 'prenotazioni',
                'cta' => 'Apri prenotazioni',
            ],
            [
                'icon' => 'bag-check',
                'title' => 'Devi seguire un ordine in corso',
                'description' => 'Apri Ordini per controllare coda, pagamento, tempi di consegna e cambi di stato.',
                'page' => 'ordini',
                'cta' => 'Apri ordini',
            ],
            [
                'icon' => 'journal-richtext',
                'title' => 'Devi aggiornare menu e prodotti',
                'description' => 'Entra nella guida Menu e prodotti per inserire nuove schede, allergeni e formule.',
                'page' => 'menu-prodotti',
                'cta' => 'Apri menu e prodotti',
            ],
            [
                'icon' => 'envelope-paper',
                'title' => 'Vuoi inviare una comunicazione ai clienti',
                'description' => 'Apri Comunicazioni per usare modelli email, liste e notifiche con un flusso semplice.',
                'page' => 'comunicazioni',
                'cta' => 'Apri comunicazioni',
            ],
        ];
    }

    private function documentationPages(): array
    {
        return [
            'onboarding' => [
                'slug' => 'onboarding',
                'icon' => 'rocket-takeoff',
                'eyebrow' => 'Primi passi',
                'title' => 'Onboarding e prima configurazione',
                'headline' => 'Imposta il gestionale senza saltare i passaggi importanti',
                'lead' => 'Questa pagina accompagna il titolare o il collaboratore che entra per la prima volta nel Backoffice. Ti mostra cosa controllare prima del primo servizio e in quale ordine farlo.',
                'summary' => 'Segui questa sequenza quando devi avviare il progetto, fare affiancamento o controllare che il locale sia pronto a ricevere richieste reali.',
                'badges' => [
                    'Ideale per nuovi collaboratori',
                    'Da completare prima del primo servizio',
                    'Riduce errori su orari, menu e accessi',
                ],
                'focus_cards' => [
                    [
                        'icon' => 'shop',
                        'title' => 'Dati del locale',
                        'description' => 'Controlla nome del locale, contatti, lingua e servizi attivi prima di toccare il resto.',
                        'items' => [
                            'Verifica nome visibile ai clienti',
                            'Conferma telefono e email di contatto',
                            'Controlla lingua e impostazioni generali',
                        ],
                    ],
                    [
                        'icon' => 'calendar3',
                        'title' => 'Disponibilita del servizio',
                        'description' => 'Assicurati che giorni, orari e blocchi corrispondano davvero al lavoro in sala e in cucina.',
                        'items' => [
                            'Orari reali di apertura',
                            'Giorni chiusi e festivita',
                            'Servizi attivi: sala, asporto, delivery',
                        ],
                    ],
                    [
                        'icon' => 'shield-check',
                        'title' => 'Accessi e verifica finale',
                        'description' => 'Chiude il giro con un controllo rapido su utenti, password e prova delle pagine principali.',
                        'items' => [
                            'Utenti autorizzati',
                            'Password aggiornate',
                            'Controllo rapido su prenotazioni e ordini',
                        ],
                    ],
                ],
                'flow_title' => 'Sequenza consigliata',
                'flow_intro' => 'Questo e il flusso piu sicuro per partire senza creare incongruenze tra impostazioni, menu e operativita quotidiana.',
                'flow_steps' => [
                    [
                        'icon' => '1-circle',
                        'title' => 'Imposta i dati base',
                        'description' => 'Apri Configurazione e verifica subito nome del locale, contatti, metodi di pagamento e servizi attivi.',
                    ],
                    [
                        'icon' => '2-circle',
                        'title' => 'Controlla calendario e disponibilita',
                        'description' => 'Sistema orari, giorni chiusi e limiti del servizio prima di pubblicare il menu.',
                    ],
                    [
                        'icon' => '3-circle',
                        'title' => 'Prepara menu e prodotti',
                        'description' => 'Crea categorie, compila i prodotti e aggiungi ingredienti o allergeni dove servono.',
                    ],
                    [
                        'icon' => '4-circle',
                        'title' => 'Fai il controllo operativo',
                        'description' => 'Apri Prenotazioni e Ordini per verificare che il team sappia dove intervenire durante il servizio.',
                    ],
                ],
                'notification' => [
                    'tone' => 'info',
                    'icon' => 'person-workspace',
                    'badge' => 'Nuovo accesso',
                    'title' => 'Collaboratore pronto per il primo accesso',
                    'message' => 'Mario Bianchi e stato aggiunto come collaboratore. Prima di consegnare la password controlla ruolo, email e pagina da cui deve partire.',
                    'items' => [
                        'Ruolo: collaboratore operativo',
                        'Email: sala@trattoriacentro.it',
                        'Pagina consigliata: Prenotazioni',
                    ],
                ],
                'checklist_title' => 'Controlli finali prima di iniziare',
                'checklist' => [
                    'Il locale mostra nome, contatti e lingua corretti.',
                    'Giorni e orari attivi coincidono con il servizio reale.',
                    'Almeno le categorie principali del menu sono gia pronte.',
                    'Prenotazioni e ordini si aprono senza dubbi operativi.',
                ],
                'faqs' => [
                    [
                        'question' => 'Chi deve leggere questa pagina?',
                        'answer' => 'Il titolare, il responsabile di sala o chiunque debba impostare il gestionale per la prima volta.',
                    ],
                    [
                        'question' => 'Quanto tempo serve per completare il giro iniziale?',
                        'answer' => 'Di solito bastano 20-30 minuti se hai gia a portata di mano orari, contatti e menu base.',
                    ],
                ],
                'related' => ['configurazione', 'prenotazioni'],
            ],
            'configurazione' => [
                'slug' => 'configurazione',
                'icon' => 'sliders',
                'eyebrow' => 'Impostazioni base',
                'title' => 'Configurazione del locale',
                'headline' => 'Configura servizi, pagamenti e regole del locale con un ordine chiaro',
                'lead' => 'Questa guida serve quando devi controllare i dati del locale o cambiare il modo in cui il gestionale accetta richieste e pagamenti.',
                'summary' => 'Prima di modificare menu, ordini o promozioni conviene passare da qui. Una configurazione corretta evita richieste fuori orario, pagamenti incoerenti e informazioni sbagliate per il cliente.',
                'badges' => [
                    'Da rivedere quando cambiano orari o servizi',
                    'Include pagamenti e canali di vendita',
                    'Adatta per titolare e responsabili',
                ],
                'focus_cards' => [
                    [
                        'icon' => 'shop-window',
                        'title' => 'Anagrafica locale',
                        'description' => 'Qui aggiorni nome, riferimenti pubblici e informazioni che il cliente puo leggere.',
                        'items' => [
                            'Nome locale',
                            'Telefono ed email',
                            'Lingua principale',
                        ],
                    ],
                    [
                        'icon' => 'credit-card-2-back',
                        'title' => 'Servizi e pagamenti',
                        'description' => 'Attiva solo i canali che il locale gestisce davvero e verifica i pagamenti disponibili.',
                        'items' => [
                            'Sala, asporto e delivery',
                            'Pagamento online o in loco',
                            'Regole minime per l ordine',
                        ],
                    ],
                    [
                        'icon' => 'calendar-range',
                        'title' => 'Regole del servizio',
                        'description' => 'Usa questa parte per ritardi minimi, blocchi, slot e capienza.',
                        'items' => [
                            'Tempo minimo di preavviso',
                            'Limiti per tavoli e coperti',
                            'Blocchi straordinari',
                        ],
                    ],
                ],
                'flow_title' => 'Come applicare una modifica senza creare problemi',
                'flow_intro' => 'Quando cambi una regola importante del locale, questo e il flusso piu semplice da seguire.',
                'flow_steps' => [
                    [
                        'icon' => '1-circle',
                        'title' => 'Apri la sezione giusta',
                        'description' => 'Entra nella parte dedicata a dati, servizio o pagamenti a seconda della modifica da fare.',
                    ],
                    [
                        'icon' => '2-circle',
                        'title' => 'Aggiorna solo cio che serve',
                        'description' => 'Evita cambi doppi in piu pagine: modifica un blocco alla volta e salva.',
                    ],
                    [
                        'icon' => '3-circle',
                        'title' => 'Controlla l impatto',
                        'description' => 'Verifica subito se la modifica tocca prenotazioni, ordini o comunicazioni automatiche.',
                    ],
                    [
                        'icon' => '4-circle',
                        'title' => 'Avvisa il team',
                        'description' => 'Se cambia una regola di servizio condividila con sala, cucina o amministrazione.',
                    ],
                ],
                'notification' => [
                    'tone' => 'warning',
                    'icon' => 'exclamation-triangle',
                    'badge' => 'Controllo richiesto',
                    'title' => 'Pagamento online disattivato',
                    'message' => 'Il locale ha lasciato attivo solo il pagamento in cassa. Controlla che questa scelta sia coerente con asporto e delivery prima di salvare.',
                    'items' => [
                        'Servizi attivi: sala e asporto',
                        'Pagamento online: disattivo',
                        'Ultimo aggiornamento: oggi ore 09:15',
                    ],
                ],
                'checklist_title' => 'Prima di chiudere la pagina',
                'checklist' => [
                    'Hai controllato il canale di vendita corretto.',
                    'I metodi di pagamento attivi sono davvero disponibili.',
                    'Gli orari salvati coincidono con il servizio reale.',
                    'Il team sa che e stata fatta una modifica operativa.',
                ],
                'faqs' => [
                    [
                        'question' => 'Quando devo tornare in questa pagina?',
                        'answer' => 'Ogni volta che cambiano orari, chiusure, regole di servizio, pagamenti o dati del locale.',
                    ],
                    [
                        'question' => 'Serve passare da qui anche per una sola giornata speciale?',
                        'answer' => 'Si, soprattutto se la giornata richiede blocchi o regole diverse dal calendario standard.',
                    ],
                ],
                'related' => ['onboarding', 'comunicazioni'],
            ],
            'menu-prodotti' => [
                'slug' => 'menu-prodotti',
                'icon' => 'journal-richtext',
                'eyebrow' => 'Catalogo operativo',
                'title' => 'Menu, prodotti e formule',
                'headline' => 'Aggiorna il menu in modo ordinato e leggibile anche per chi non e tecnico',
                'lead' => 'Questa guida ti aiuta a costruire o correggere il catalogo del locale: categorie, prodotti, ingredienti, allergeni e menu fissi.',
                'summary' => 'Usa questa pagina quando devi pubblicare un nuovo piatto, cambiare un prezzo o sistemare un prodotto stagionale. La struttura giusta rende tutto piu veloce anche nei giorni di punta.',
                'badges' => [
                    'Include prodotti, categorie e allergeni',
                    'Utile per cambi stagionali',
                    'Pensata per evitare errori di compilazione',
                ],
                'focus_cards' => [
                    [
                        'icon' => 'grid-1x2',
                        'title' => 'Categorie ben ordinate',
                        'description' => 'Prima di creare i prodotti conviene controllare in che sezione del menu devono comparire.',
                        'items' => [
                            'Antipasti, primi, dessert',
                            'Ordine di lettura del menu',
                            'Sezioni visibili o nascoste',
                        ],
                    ],
                    [
                        'icon' => 'fork-knife',
                        'title' => 'Scheda prodotto completa',
                        'description' => 'Ogni prodotto deve avere i dati essenziali per chi ordina e per chi gestisce il servizio.',
                        'items' => [
                            'Nome e prezzo',
                            'Descrizione breve',
                            'Ingredienti e allergeni',
                        ],
                    ],
                    [
                        'icon' => 'card-checklist',
                        'title' => 'Formule e menu fissi',
                        'description' => 'Usa questa parte per degustazioni, menu pranzo o offerte che hanno un prezzo unico.',
                        'items' => [
                            'Contenuto della formula',
                            'Prezzo finale',
                            'Periodo di validita',
                        ],
                    ],
                ],
                'flow_title' => 'Flusso veloce per pubblicare un nuovo piatto',
                'flow_intro' => 'Quando arriva una novita in menu ti conviene seguire questi passaggi, cosi il prodotto esce gia completo e coerente.',
                'flow_steps' => [
                    [
                        'icon' => '1-circle',
                        'title' => 'Scegli categoria e visibilita',
                        'description' => 'Definisci in quale parte del menu andra il prodotto e se deve essere subito visibile.',
                    ],
                    [
                        'icon' => '2-circle',
                        'title' => 'Compila la scheda',
                        'description' => 'Inserisci nome, prezzo, descrizione, immagine e note utili per il cliente.',
                    ],
                    [
                        'icon' => '3-circle',
                        'title' => 'Aggiungi ingredienti e allergeni',
                        'description' => 'Completa sempre la parte informativa per evitare errori in sala e in cucina.',
                    ],
                    [
                        'icon' => '4-circle',
                        'title' => 'Controlla il risultato finale',
                        'description' => 'Rileggi la scheda e verifica che il prodotto compaia nel punto giusto del catalogo.',
                    ],
                ],
                'notification' => [
                    'tone' => 'danger',
                    'icon' => 'slash-circle',
                    'badge' => 'Prodotto nascosto',
                    'title' => 'Ravioli al brasato non disponibili per oggi',
                    'message' => 'Il prodotto e stato nascosto dalla vendita online ma resta nel catalogo per mantenere storico e dati completi.',
                    'items' => [
                        'Categoria: Primi piatti',
                        'Stato: nascosto',
                        'Azione consigliata: riattiva quando torna disponibile',
                    ],
                ],
                'checklist_title' => 'Controlli utili prima di pubblicare',
                'checklist' => [
                    'Il prodotto e nella categoria giusta.',
                    'Prezzo e descrizione sono aggiornati.',
                    'Allergeni e ingredienti sono presenti se necessari.',
                    'Le formule scadute sono state archiviate o nascoste.',
                ],
                'faqs' => [
                    [
                        'question' => 'Meglio cancellare un prodotto o archiviarlo?',
                        'answer' => 'Se vuoi conservare lo storico conviene nasconderlo o archiviarlo, non cancellarlo subito.',
                    ],
                    [
                        'question' => 'Posso pubblicare un prodotto senza immagine?',
                        'answer' => 'Si, ma nome, prezzo e descrizione devono comunque essere chiari e completi.',
                    ],
                ],
                'related' => ['ordini', 'comunicazioni'],
            ],
            'prenotazioni' => [
                'slug' => 'prenotazioni',
                'icon' => 'calendar-check',
                'eyebrow' => 'Gestione tavoli',
                'title' => 'Prenotazioni',
                'headline' => 'Conferma, aggiorna e controlla le prenotazioni senza perdere il filo',
                'lead' => 'Questa guida segue il flusso reale delle prenotazioni tavolo: arrivo della richiesta, controllo capienza, comparsa in dashboard, gestione dalla scheda e invio automatico di mail o WhatsApp.',
                'summary' => 'Qui trovi i blocchi reali della dashboard per le prenotazioni: lista condivisa, calendario con slot, scheda dettaglio e modali di conferma o annullo. Il flusso segue davvero quello del codice, senza stati inventati.',
                'badges' => [
                    'Lista condivisa con ordini',
                    'Calendario con coperti e blocco slot',
                    'Mail e WhatsApp nel flusso reale',
                ],
                'focus_cards' => [
                    [
                        'icon' => 'list-task',
                        'title' => 'Lista condivisa',
                        'description' => 'Le prenotazioni si lavorano dentro la stessa lista di ordini e prenotazioni, usando ricerca, toggle tipo e ordinamento.',
                        'items' => [
                            'Ricerca per nome o cognome',
                            'Toggle da Tutti a Prenotazioni',
                            'Ordinamento cronologico su date_slot',
                        ],
                    ],
                    [
                        'icon' => 'calendar-week',
                        'title' => 'Calendario e slot',
                        'description' => 'Il calendario mostra ospiti del giorno, dettaglio per fascia e toggle per bloccare o riaprire gli orari futuri.',
                        'items' => [
                            'Badge giorno con totale ospiti',
                            'Dettaglio slot con res-item reali',
                            'Blocca o sblocca l orario',
                        ],
                    ],
                    [
                        'icon' => 'window-stack',
                        'title' => 'Scheda e notifiche',
                        'description' => 'La scheda prenotazione e le modali di conferma o annullo sono il punto in cui decidi lo stato e il canale di risposta.',
                        'items' => [
                            'reservation-detail con stato e ospiti',
                            'Modali con scelta mail o WhatsApp',
                            'Email automatiche per cliente e locale',
                        ],
                    ],
                ],
                'flow_title' => 'Flusso reale degli eventi',
                'flow_intro' => 'Dal submit della richiesta fino alla risposta al cliente, questi sono i passaggi che esistono davvero nel progetto.',
                'flow_steps' => [
                    [
                        'icon' => '1-circle',
                        'title' => 'Arriva la richiesta API',
                        'description' => 'Il backend valida dati, sala, slot attivo, giorno non chiuso e capienza disponibile prima di salvare.',
                    ],
                    [
                        'icon' => '2-circle',
                        'title' => 'La dashboard si aggiorna',
                        'description' => 'La prenotazione entra in lista, calendario e dettaglio slot con stato In attesa.',
                    ],
                    [
                        'icon' => '3-circle',
                        'title' => 'Partono le notifiche',
                        'description' => 'Il sistema invia WhatsApp agli admin configurati e email al locale e al cliente.',
                    ],
                    [
                        'icon' => '4-circle',
                        'title' => 'Conferma o annulla',
                        'description' => 'L operatore puo lavorare dalla scheda oppure dai pulsanti WhatsApp; in entrambi i casi lo stato e le mail si aggiornano.',
                    ],
                ],
                'notification' => [
                    'tone' => 'warning',
                    'icon' => 'bell',
                    'badge' => 'Nuova prenotazione',
                    'title' => 'Richiesta in attesa da lavorare',
                    'message' => 'Giulia Rossi ha richiesto un tavolo per sabato alle 20:30. La richiesta e gia comparsa in dashboard e puo essere confermata o annullata dalla scheda o da WhatsApp.',
                    'items' => [
                        'Data: sabato 12 aprile',
                        'Orario: 20:30',
                        'Ospiti: 2 adulti e 1 bambino',
                    ],
                ],
                'checklist_title' => 'Controllo rapido per ogni prenotazione',
                'checklist' => [
                    'Hai verificato che il filtro Tipo sia su Prenotazioni quando lavori dalla lista.',
                    'Hai letto ospiti, sala e messaggio prima di cambiare stato.',
                    'Hai scelto se affiancare WhatsApp alla mail automatica nella modale.',
                    'Il cliente ha ricevuto l esito corretto di conferma o annullo.',
                ],
                'faqs' => [
                    [
                        'question' => 'Come faccio a vedere solo le prenotazioni nella lista?',
                        'answer' => 'Premi il toggle Tipo finche il pulsante mostra Prenotazioni. La pagina e condivisa con gli ordini.',
                    ],
                    [
                        'question' => 'A cosa servono i pulsanti No e Si nella modale?',
                        'answer' => 'Il risultato sullo stato e lo stesso. Cambia solo se vuoi aggiungere anche WhatsApp oltre alla mail automatica.',
                    ],
                ],
                'related' => ['ordini', 'configurazione'],
            ],
            'ordini' => [
                'slug' => 'ordini',
                'icon' => 'bag-check',
                'eyebrow' => 'Ordini online',
                'title' => 'Ordini',
                'headline' => 'Come si gestiscono gli ordini in dashboard',
                'lead' => 'Gli ordini arrivano via WhatsApp, email e dashboard. Da qui il lavoro e semplice: apri la richiesta, controlli la scheda e decidi se confermare, annullare o posticipare.',
                'summary' => 'Qui trovi i passaggi reali della gestione ordini: come leggere la lista, cosa guardare nella scheda, quando appare rimborsa e annulla e come usare posticipa con blocco orario.',
                'badges' => [
                    'Delivery e asporto',
                    'Conferma o annulla',
                    'Posticipa se serve',
                ],
                'focus_cards' => [
                    [
                        'icon' => 'list-task',
                        'title' => 'Apri l ordine dalla lista',
                        'description' => 'La lista serve per entrare subito nelle richieste da decidere, senza altri passaggi.',
                        'items' => [
                            'Apri la richiesta che vedi in dashboard',
                            'Controlla subito se e delivery o asporto',
                            'Da li entri nella scheda completa dell ordine',
                        ],
                    ],
                    [
                        'icon' => 'cash-coin',
                        'title' => 'Decidi dalla scheda ordine',
                        'description' => 'Nella scheda trovi gia tutto quello che ti serve per decidere.',
                        'items' => [
                            'Carrello completo',
                            'Opzioni, extra, rimossi e costo consegna',
                            'Contatti, note e tipo di pagamento',
                        ],
                    ],
                    [
                        'icon' => 'clock-history',
                        'title' => 'Sposta l orario quando serve',
                        'description' => 'Se non riesci a tenere la fascia richiesta, puoi confermare con un nuovo orario.',
                        'items' => [
                            'Scegli il nuovo orario',
                            'Il cliente riceve il posticipo',
                            'Se serve blocchi la fascia originale',
                        ],
                    ],
                ],
                'flow_title' => 'Tutorial operativo',
                'flow_intro' => 'Questi sono i passaggi che usi davvero nella gestione quotidiana.',
                'flow_steps' => [
                    [
                        'icon' => '1-circle',
                        'title' => 'Apri la richiesta',
                        'description' => 'Puoi arrivarci dalla notifica, dalla lista ordini o dal calendario del giorno.',
                    ],
                    [
                        'icon' => '2-circle',
                        'title' => 'Leggi la scheda',
                        'description' => 'Guarda carrello, modifiche ai prodotti, totale, note, indirizzo o ritiro e pagamento.',
                    ],
                    [
                        'icon' => '3-circle',
                        'title' => 'Conferma o annulla',
                        'description' => 'Se confermi un ordine da vedere passa a confermato. Se era gia pagato passa a confermato e incassato. Se non lo accetti lo annulli o lo rimborsi.',
                    ],
                    [
                        'icon' => '4-circle',
                        'title' => 'Posticipa e blocca la fascia',
                        'description' => 'Quando sposti un ordine puoi anche chiudere lo slot originale, cosi non ti arrivano altre richieste in quell orario.',
                    ],
                ],
                'notification' => [
                    'tone' => 'warning',
                    'icon' => 'bag-check',
                    'badge' => 'Ordine ricevuto',
                    'title' => 'Nuovo ordine da valutare',
                    'message' => 'Apri la richiesta e decidi se confermare o annullare. Se la fascia e satura puoi anche posticipare l ordine e bloccare l orario.',
                    'items' => [
                        'Asporto o delivery',
                        'Pagamento online o alla consegna',
                        'Carrello completo con modifiche',
                    ],
                ],
                'checklist_title' => 'Controllo rapido',
                'checklist' => [
                    'Se e delivery, controlla indirizzo e costo consegna.',
                    'Se e asporto, ti bastano orario, carrello e contatti.',
                    'Se era gia pagato, l azione di chiusura e rimborsa e annulla.',
                    'Se lo posticipi, decidi subito se bloccare la fascia originale.',
                ],
                'faqs' => [
                    [
                        'question' => 'Quando compare Rimborsa e Annulla?',
                        'answer' => 'Compare sugli ordini gia pagati. Se non li accetti, non fai un annullamento semplice: passi da rimborso e annullamento.',
                    ],
                    [
                        'question' => 'Quando uso Posticipa?',
                        'answer' => 'Quando accetti l ordine ma non riesci a rispettare l orario richiesto. In quel passaggio puoi anche bloccare la fascia originale.',
                    ],
                    [
                        'question' => 'Dove conviene guardare un ordine?',
                        'answer' => 'La lista ti serve per aprire subito la richiesta. Il calendario ti aiuta quando devi ragionare sugli slot e capire se tenere o spostare un orario.',
                    ],
                ],
                'related' => ['prenotazioni', 'menu-prodotti'],
            ],
            'comunicazioni' => [
                'slug' => 'comunicazioni',
                'icon' => 'envelope-paper',
                'eyebrow' => 'Contatto clienti',
                'title' => 'Comunicazioni, notifiche ed email',
                'headline' => 'Invia comunicazioni chiare ai clienti senza riscrivere tutto ogni volta',
                'lead' => 'Questa guida raccoglie i passaggi base per template, invii e notifiche, con esempi pronti da leggere anche da chi non ha esperienza tecnica.',
                'summary' => 'Usala per campagne email, avvisi di servizio, messaggi su promozioni o conferme operative. Il flusso e semplice: scegli il messaggio, controlla i destinatari e invia solo dopo una verifica finale.',
                'badges' => [
                    'Include email HTML di esempio',
                    'Valida per avvisi e promo',
                    'Adatta a chi gestisce clienti e marketing',
                ],
                'focus_cards' => [
                    [
                        'icon' => 'file-earmark-text',
                        'title' => 'Template riutilizzabili',
                        'description' => 'Prepara modelli semplici per non ripartire ogni volta da una pagina vuota.',
                        'items' => [
                            'Conferme e promemoria',
                            'Promo stagionali',
                            'Avvisi di servizio',
                        ],
                    ],
                    [
                        'icon' => 'people',
                        'title' => 'Liste clienti ordinate',
                        'description' => 'Controlla sempre a chi andra il messaggio prima di premere invia.',
                        'items' => [
                            'Clienti abituali',
                            'Chi ha prenotato recentemente',
                            'Contatti inseriti manualmente',
                        ],
                    ],
                    [
                        'icon' => 'send-check',
                        'title' => 'Invio con controllo finale',
                        'description' => 'Un ultimo controllo evita errori su testo, destinatari e call to action.',
                        'items' => [
                            'Oggetto email',
                            'Link e pulsanti',
                            'Data e ora di invio',
                        ],
                    ],
                ],
                'flow_title' => 'Flusso semplice per una comunicazione ben fatta',
                'flow_intro' => 'Segui questi passaggi quando devi inviare una promo, un avviso o una conferma ai clienti.',
                'flow_steps' => [
                    [
                        'icon' => '1-circle',
                        'title' => 'Scegli il modello',
                        'description' => 'Parti da un template gia pronto per non dimenticare le parti importanti del messaggio.',
                    ],
                    [
                        'icon' => '2-circle',
                        'title' => 'Controlla i destinatari',
                        'description' => 'Verifica lista, segmenti e contatti extra prima dell invio.',
                    ],
                    [
                        'icon' => '3-circle',
                        'title' => 'Rileggi titolo e contenuto',
                        'description' => 'Assicurati che oggetto, testo e pulsante siano chiari e coerenti.',
                    ],
                    [
                        'icon' => '4-circle',
                        'title' => 'Invia o programma',
                        'description' => 'Chiudi il lavoro con un invio immediato o pianificato e tieni traccia della comunicazione.',
                    ],
                ],
                'notification' => [
                    'tone' => 'success',
                    'icon' => 'check2-circle',
                    'badge' => 'Invio programmato',
                    'title' => 'Campagna brunch di domenica pronta',
                    'message' => 'Il messaggio verra inviato domani alle 11:30 ai clienti che hanno prenotato nelle ultime sei settimane.',
                    'items' => [
                        'Destinatari: 184',
                        'Oggetto: Brunch speciale di domenica',
                        'Stato: programmata',
                    ],
                ],
                'checklist_title' => 'Prima di inviare una comunicazione',
                'checklist' => [
                    'Lista destinatari controllata.',
                    'Oggetto e testo riletti una volta.',
                    'Pulsante o link principale funzionante.',
                    'Periodo dell offerta o dell avviso chiaramente scritto.',
                ],
                'faqs' => [
                    [
                        'question' => 'Quando conviene usare un template?',
                        'answer' => 'Sempre, soprattutto per conferme, promemoria, promo ricorrenti o messaggi stagionali.',
                    ],
                    [
                        'question' => 'Meglio inviare subito o programmare?',
                        'answer' => 'Se il messaggio non e urgente conviene programmarlo in un orario leggibile per il cliente.',
                    ],
                ],
                'related' => ['menu-prodotti', 'configurazione'],
            ],
        ];
    }

    private function onboardingGuide(): array
    {
        return [
            'eyebrow' => 'Percorso consigliato',
            'title' => 'Come leggere questa documentazione senza perderti',
            'intro' => 'La guida guest adesso e organizzata come un piccolo centro assistenza: un indice iniziale e pagine operative separate. Parti dall onboarding se e la tua prima volta, poi apri solo la sezione che ti serve davvero.',
            'steps' => [
                [
                    'icon' => '1-circle',
                    'title' => 'Parti dall onboarding',
                    'description' => 'Usa la pagina iniziale per controllare dati del locale, servizi e accessi.',
                ],
                [
                    'icon' => '2-circle',
                    'title' => 'Apri la pagina giusta',
                    'description' => 'Scegli Prenotazioni, Ordini, Menu o Comunicazioni in base a quello che devi fare subito.',
                ],
                [
                    'icon' => '3-circle',
                    'title' => 'Segui il flusso visivo',
                    'description' => 'Ogni pagina mostra box reali, badge, notifiche e passaggi da seguire senza interpretazioni tecniche.',
                ],
                [
                    'icon' => '4-circle',
                    'title' => 'Chiudi con la checklist',
                    'description' => 'Prima di uscire controlla sempre la checklist finale per non dimenticare passaggi importanti.',
                ],
            ],
            'checklist' => [
                'Hai aperto la pagina piu adatta al problema del momento.',
                'Hai seguito i box con esempi reali e non solo il testo descrittivo.',
                'Hai controllato la checklist finale della sezione.',
                'Se la modifica tocca il servizio, hai avvisato il team.',
            ],
            'tips' => [
                'Le pagine sono pensate per ristoratori e collaboratori: testi brevi, icone grandi e passaggi in ordine.',
                'Se lavori durante il servizio, usa prima Prenotazioni e Ordini. Se stai preparando il locale, parti da Configurazione e Menu.',
            ],
        ];
    }

    private function releaseNotes(): array
    {
        return [
            [
                'version' => 'v1.1',
                'date' => '04/04/2026',
                'title' => 'Documentazione guest divisa in pagine operative',
                'summary' => 'La guida pubblica non e piu una sola pagina lunga: ora include un indice centrale e pagine dedicate per onboarding, configurazione, prenotazioni, ordini, menu e comunicazioni.',
                'items' => [
                    'Creato un hub documentazione con accessi rapidi ai flussi piu usati.',
                    'Aggiunte pagine Blade dedicate con esempi visivi reali per prenotazioni, ordini, email e notifiche.',
                    'Allineate le icone alla libreria Bootstrap Icons con markup reale pronto all uso.',
                ],
            ],
            [
                'version' => 'Prossimo update',
                'date' => 'Da compilare',
                'title' => 'Spazio riservato ai prossimi rilasci',
                'summary' => 'Usa questa sezione per tracciare i prossimi miglioramenti del Backoffice con impatto operativo chiaro.',
                'items' => [
                    'Aggiungi una voce nuova per ogni rilascio importante.',
                    'Scrivi sempre cosa cambia per il ristoratore o per il collaboratore.',
                    'Segnala se il team deve fare un controllo manuale dopo il deploy.',
                ],
            ],
        ];
    }
}
