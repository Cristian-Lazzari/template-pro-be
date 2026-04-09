<section class="public-panel public-panel--soft">
    <div class="public-panel__header">
        <p class="public-panel__eyebrow">Blocchi reali del dashboard</p>
        <h2>Comunicazioni costruite su liste contatti e modelli email del pannello Mailer</h2>
    </div>

    <div class="email-m dashboard-mailer-preview">
        <section class="lists">
            <div class="list_wrap">
                <h3>Contatti dalle prenotazioni</h3>
                <div class="list">
                    <div class="contact">
                        <span class="name">Giulia Rossi</span>
                        <div class="mail"><span>giulia.rossi@email.it</span></div>
                    </div>
                    <div class="contact">
                        <span class="name">Marco Ferri</span>
                        <div class="mail"><span>marco.ferri@email.it</span></div>
                    </div>
                </div>
                <div class="params act">
                    <p><span>2 contatti</span></p>
                </div>
            </div>

            <div class="list_wrap">
                <h3>Contatti extra</h3>
                <div class="list">
                    <div class="contact">
                        <span class="name">Eventi aziendali</span>
                        <div class="mail"><span>eventi@azienda.it</span></div>
                    </div>
                    <div class="contact">
                        <span class="name">Wedding planner</span>
                        <div class="mail"><span>wedding@planner.it</span></div>
                    </div>
                </div>
                <div class="params act">
                    <p><span>2 contatti</span></p>
                    <button type="button" class="my_btn_1">Modifica</button>
                </div>
            </div>
        </section>

        <section>
            <h2>Modelli per Email</h2>

            <div class="models">
                <div class="model">
                    <div class="name my_btn_4 mb-4">Promo brunch</div>
                    <h1>Brunch della domenica</h1>
                    <div class="corpo">
                        <p>Menu dedicato, tavoli limitati e prenotazione consigliata.</p>
                        <p>Il contenuto resta leggibile direttamente dalla card del pannello Mailer.</p>
                    </div>
                    <p class="ending">Disponibile questa settimana fino a esaurimento posti.</p>
                    <div class="sender" style="color: #04001d">
                        <p>Trattoria Centro</p>
                        <p class="date">{{ __('admin.martedi_3_gennaio') }}</p>
                    </div>
                </div>
            </div>
        </section>
    </div>
</section>

<section class="public-panel">
    <div class="public-panel__header">
        <p class="public-panel__eyebrow">Anteprima riusabile</p>
        <h2>Email marketing su pattern condiviso pronto per altre pagine interne</h2>
    </div>

    <x-dashboard.mail-preview
        variant="campaign"
        subject="Brunch speciale di domenica"
        sender="Trattoria Centro"
        headline="Domenica torna il brunch della casa"
        subheadline="Template marketing del pannello Mailer"
        greeting="Ciao,"
        intro="Questa anteprima riprende la struttura dei modelli mail e usa un componente condiviso invece di un layout solo documentazione."
        :items="[
            'Lista destinatari: contatti da prenotazioni e lista extra',
            'Oggetto mail: Brunch speciale di domenica',
            'Mittente: Trattoria Centro',
        ]"
        cta="Prenota il tuo tavolo"
        footer="Il contenuto finale resta gestito dal sistema Mailer e dai template HTML reali."
    />
</section>
