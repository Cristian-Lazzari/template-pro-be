<?php

namespace App\Http\Controllers\Guests;

use App\Http\Controllers\Controller;

class PageController extends Controller
{
    public function home()
    {
        return view('guests.home');
    }

    public function documentation()
    {
        return view('guests.documentation', [
            'sections' => $this->documentationSections(),
        ]);
    }

    public function updates()
    {
        return view('guests.updates', [
            'updates' => $this->releaseNotes(),
        ]);
    }

    private function documentationSections(): array
    {
        return [
            [
                'id' => 'workflow',
                'eyebrow' => 'Metodo consigliato',
                'title' => 'Ordine di lavoro nel Backoffice',
                'intro' => 'Per usare bene il gestionale conviene seguire sempre la stessa sequenza: configurazione iniziale, struttura del menu, pubblicazione contenuti e poi gestione quotidiana di ordini e prenotazioni.',
                'points' => [
                    'Apri Impostazioni per inserire i dati del locale, i contatti e i parametri base del servizio.',
                    'Crea prima Categorie, Ingredienti e Allergeni: sono la base per compilare schede prodotto chiare e complete.',
                    'Inserisci Prodotti, Menu fissi e Post promozionali solo dopo avere definito bene disponibilita, sale e fasce orarie.',
                    'Controlla ogni giorno Dashboard, Date, Ordini e Prenotazioni per mantenere il flusso operativo sempre aggiornato.',
                ],
            ],
            [
                'id' => 'dashboard',
                'eyebrow' => 'Controllo rapido',
                'title' => 'Dashboard',
                'intro' => 'La Dashboard e il centro operativo del gestionale. Da qui controlli il calendario, le disponibilita giornaliere e accedi velocemente ai dettagli di prenotazioni e ordini.',
                'points' => [
                    'Usa il calendario per visualizzare i giorni con prenotazioni, ordini e capienza occupata.',
                    'Apri la modal di modifica disponibilita per cambiare latenze, fasce orarie, coperti e parametri di accettazione.',
                    'Blocca giorni o orari quando il locale e chiuso, al completo oppure deve sospendere un servizio specifico.',
                    'Controlla le notifiche operative e apri subito il dettaglio della richiesta da gestire.',
                ],
            ],
            [
                'id' => 'menu',
                'eyebrow' => 'Catalogo',
                'title' => 'Menu e navigazione commerciale',
                'intro' => 'La sezione Menu raccoglie la struttura generale dell\'offerta e ti aiuta a mostrare al cliente i prodotti nel modo piu ordinato possibile.',
                'points' => [
                    'Organizza il catalogo in gruppi chiari, separando per esempio cucina, beverage, dessert o offerte speciali.',
                    'Verifica l\'ordine con cui i contenuti vengono presentati per rendere piu veloce la consultazione lato cliente.',
                    'Usa la struttura menu insieme alle categorie per evitare duplicazioni e mantenere una logica coerente.',
                ],
            ],
            [
                'id' => 'categories',
                'eyebrow' => 'Struttura prodotti',
                'title' => 'Categorie',
                'intro' => 'Le categorie servono a classificare piatti, bevande e contenuti commerciali. Una buona organizzazione migliora sia il lavoro interno sia la lettura del menu pubblico.',
                'points' => [
                    'Crea categorie semplici e riconoscibili, con nomi brevi e facili da distinguere.',
                    'Riordina categorie e prodotti tramite le funzioni di ordinamento per decidere la priorita di visualizzazione.',
                    'Usa categorie separate per piatti del giorno, fuori menu o sezioni stagionali se vuoi aggiornarle rapidamente.',
                ],
            ],
            [
                'id' => 'ingredients-allergens',
                'eyebrow' => 'Trasparenza',
                'title' => 'Ingredienti e allergeni',
                'intro' => 'Queste sezioni servono a compilare prodotti accurati e piu sicuri da consultare per il cliente finale.',
                'points' => [
                    'Inserisci gli ingredienti ricorrenti una sola volta e poi richiamali nei prodotti per velocizzare il lavoro.',
                    'Associa gli allergeni corretti a ogni prodotto per offrire informazioni chiare e ridurre errori operativi.',
                    'Aggiorna subito ingredienti e allergeni quando cambia una ricetta o un fornitore.',
                ],
            ],
            [
                'id' => 'products',
                'eyebrow' => 'Vendita',
                'title' => 'Prodotti',
                'intro' => 'La sezione Prodotti e il cuore del menu digitale. Qui puoi creare schede complete con nome, descrizione, prezzo, immagini, disponibilita e stato di pubblicazione.',
                'points' => [
                    'Compila nome, descrizione e prezzo in modo coerente: sono i dati che il cliente usa per decidere l\'acquisto.',
                    'Associa categoria, ingredienti e allergeni per ottenere schede prodotto complete e professionali.',
                    'Usa filtri, ricerca, quick view e archivio per gestire rapidamente cataloghi ampi o prodotti stagionali.',
                    'Disattiva temporaneamente un prodotto quando non e disponibile, senza cancellarne lo storico.',
                ],
            ],
            [
                'id' => 'fixed-menus',
                'eyebrow' => 'Offerte composte',
                'title' => 'Menu fissi',
                'intro' => 'I menu fissi permettono di vendere combinazioni gia pronte di prodotti o esperienze. Sono utili per menu pranzo, degustazioni, eventi o promozioni ricorrenti.',
                'points' => [
                    'Crea un menu fisso quando vuoi proporre un pacchetto con prezzo unico e struttura predefinita.',
                    'Mantieni una descrizione molto chiara di cosa e incluso per ridurre richieste di chiarimento.',
                    'Aggiorna o archivia i menu fissi quando finiscono campagne stagionali o promozioni temporanee.',
                ],
            ],
            [
                'id' => 'posts',
                'eyebrow' => 'Comunicazione',
                'title' => 'Post e contenuti promozionali',
                'intro' => 'La sezione Post ti aiuta a pubblicare novita, promozioni, avvisi o contenuti visuali da mettere in evidenza sul front-end.',
                'points' => [
                    'Usa i post per comunicare eventi, serate speciali, menu tematici o promozioni a tempo.',
                    'Mantieni attivi solo i contenuti attuali e archivia quelli scaduti per evitare confusione.',
                    'Sfrutta ricerca, filtri e ordinamento per gestire facilmente campagne diverse durante l\'anno.',
                ],
            ],
            [
                'id' => 'orders',
                'eyebrow' => 'Operativita quotidiana',
                'title' => 'Ordini',
                'intro' => 'Da questa sezione controlli gli ordini ricevuti, ne cambi lo stato e monitori tempi e valore economico delle vendite.',
                'points' => [
                    'Filtra gli ordini per stato o periodo per concentrarti prima sulle richieste da evadere.',
                    'Aggiorna lo stato man mano che l\'ordine viene confermato, preparato, completato o annullato.',
                    'Usa il cambio orario quando devi concordare una fascia diversa con il cliente.',
                    'Apri il dettaglio ordine per vedere prodotti, totale, dati cliente e note operative.',
                ],
            ],
            [
                'id' => 'reservations',
                'eyebrow' => 'Sala',
                'title' => 'Prenotazioni',
                'intro' => 'Le prenotazioni gestiscono coperti, turni e presenza dei clienti in sala. E la sezione da consultare ogni giorno insieme al calendario disponibilita.',
                'points' => [
                    'Controlla numero persone, data, orario e note prima di confermare una prenotazione.',
                    'Aggiorna gli stati per distinguere richieste confermate, da ricontattare, completate o annullate.',
                    'Usa i filtri per visualizzare velocemente le richieste del giorno o di un periodo specifico.',
                    'Apri il dettaglio prenotazione per avere subito sotto mano tutti i dati utili al servizio.',
                ],
            ],
            [
                'id' => 'dates',
                'eyebrow' => 'Disponibilita',
                'title' => 'Date, giorni e fasce orarie',
                'intro' => 'La gestione Date definisce il comportamento reale del locale: orari disponibili, giorni aperti, blocchi, capienza e regole di prenotazione.',
                'points' => [
                    'Genera o aggiorna i giorni disponibili quando cambiano stagionalita, turni o orari di servizio.',
                    'Blocca date e fasce orarie in anticipo per festivita, ferie, eventi privati o manutenzioni.',
                    'Controlla la disponibilita per singolo giorno quando devi fare una modifica puntuale senza alterare tutto il calendario.',
                    'Verifica sempre le latenze di ordini e prenotazioni dopo ogni cambiamento organizzativo.',
                ],
            ],
            [
                'id' => 'customers',
                'eyebrow' => 'Relazione cliente',
                'title' => 'Clienti',
                'intro' => 'La sezione Clienti raccoglie i contatti che interagiscono con il gestionale. Serve per consultazione, storico e iniziative di fidelizzazione.',
                'points' => [
                    'Consulta i dati cliente per riconoscere utenti abituali e gestire richieste ricorrenti con piu velocita.',
                    'Usa le informazioni raccolte per organizzare comunicazioni mirate o verifiche operative.',
                    'Mantieni i dati puliti e coerenti per facilitare assistenza e marketing.',
                ],
            ],
            [
                'id' => 'statistics',
                'eyebrow' => 'Analisi',
                'title' => 'Statistiche',
                'intro' => 'Le statistiche aiutano a leggere l\'andamento del locale nel tempo: prodotti piu venduti, ricavi, ordini, prenotazioni e distribuzione dei risultati.',
                'points' => [
                    'Usa i grafici per capire quali prodotti trainano le vendite e quali categorie rendono meno.',
                    'Controlla l\'andamento dei ricavi per giorno o periodo per individuare trend e picchi.',
                    'Confronta conferme e cancellazioni di ordini e prenotazioni per migliorare organizzazione e offerta.',
                    'Questa sezione puo dipendere dal piano attivo, quindi potrebbe non essere presente in tutte le installazioni.',
                ],
            ],
            [
                'id' => 'mailer',
                'eyebrow' => 'Comunicazione diretta',
                'title' => 'Mailer',
                'intro' => 'Il Mailer serve per inviare comunicazioni ai clienti e gestire modelli pronti all\'uso per campagne o messaggi ricorrenti.',
                'points' => [
                    'Crea modelli email riutilizzabili per promozioni, avvisi o comunicazioni stagionali.',
                    'Prepara invii rapidi quando vuoi raggiungere una lista clienti senza riscrivere il contenuto ogni volta.',
                    'Rivedi il testo del modello prima dell\'invio, soprattutto se contiene offerte a tempo o informazioni logistiche.',
                    'Anche questa funzione puo dipendere dal piano attivo.',
                ],
            ],
            [
                'id' => 'settings',
                'eyebrow' => 'Configurazione',
                'title' => 'Impostazioni',
                'intro' => 'In Impostazioni definisci i parametri generali del gestionale e i dati strutturali del locale. E la sezione piu importante nella fase iniziale e ogni volta che cambia il servizio.',
                'points' => [
                    'Aggiorna numeri, recapiti, configurazioni avanzate e aree di servizio quando cambiano le esigenze del locale.',
                    'Usa le opzioni avanzate per affinare comportamento del sistema, limiti, tempi e disponibilita.',
                    'Controlla con attenzione le modifiche prima di salvare se incidono su prenotazioni, ordini o calendario.',
                    'Quando necessario usa le funzioni dedicate per annullare disponibilita o aggiornare impostazioni in blocco.',
                ],
            ],
            [
                'id' => 'profile',
                'eyebrow' => 'Sicurezza',
                'title' => 'Profilo e accesso',
                'intro' => 'L\'area profilo permette di mantenere aggiornati i dati dell\'utente amministratore e le credenziali di accesso.',
                'points' => [
                    'Aggiorna email e password periodicamente per mantenere il Backoffice sicuro.',
                    'Concedi l\'accesso solo a persone autorizzate e cambia subito le credenziali in caso di turnover.',
                    'Verifica sempre che l\'indirizzo email principale sia corretto per ricevere eventuali comunicazioni di sistema.',
                ],
            ],
        ];
    }

    private function releaseNotes(): array
    {
        return [
            [
                'version' => 'v1.0',
                'date' => '04/04/2026',
                'title' => 'Apertura area pubblica di supporto',
                'summary' => 'Sono state introdotte due pagine pubbliche consultabili senza login: una documentazione completa del Backoffice e una pagina dedicata al registro aggiornamenti.',
                'items' => [
                    'Creata una pagina Documentazione con panoramica operativa e spiegazione di tutte le principali sezioni del gestionale.',
                    'Creata una pagina Aggiornamenti pronta ad accogliere le prossime release e modifiche evolutive del progetto.',
                    'Aggiornata la home pubblica con accessi rapidi a login, documentazione e changelog.',
                ],
            ],
            [
                'version' => 'Prossimi update',
                'date' => 'Da compilare',
                'title' => 'Spazio riservato alle prossime novita',
                'summary' => 'Da questo punto in avanti conviene aggiungere qui ogni modifica rilevante, indicando data, obiettivo e impatto operativo.',
                'items' => [
                    'Aggiungi un nuovo blocco per ogni rilascio o intervento importante.',
                    'Indica sempre cosa cambia per l\'utente Backoffice e se serve un nuovo flusso operativo.',
                    'Se una modifica richiede attenzione, specifica anche eventuali passaggi manuali o controlli da fare dopo il deploy.',
                ],
            ],
        ];
    }
}
