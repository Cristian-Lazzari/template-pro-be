<?php

return [
    'messages' => [
        'email_already_registered' => 'Esiste gia un account cliente registrato con questa email.',
        'customer_not_found' => 'Non esiste alcun cliente registrato con questa email.',
        'email_not_verified' => 'Email non verificata. Completa prima la verifica del tuo account.',
        'wait_before_new_code' => 'Attendi prima di richiedere un nuovo codice.',
        'otp_invalid_or_expired' => 'Il codice OTP non e valido o e scaduto.',
        'otp_too_many_attempts' => 'Hai superato il numero massimo di tentativi. Richiedi un nuovo codice.',
        'otp_incorrect' => 'Il codice OTP inserito non e corretto.',
        'otp_sent_register' => 'Codice OTP inviato via email per completare la registrazione.',
        'otp_sent_login' => 'Codice OTP inviato via email per il login.',
        'otp_sent_password_reset' => 'Codice OTP inviato via email per reimpostare la password.',
        'register_completed' => 'Registrazione completata con successo.',
        'login_completed' => 'Login effettuato con successo.',
        'password_reset_completed' => 'Password aggiornata con successo.',
        'logout_completed' => 'Logout effettuato con successo.',
    ],
    'mail' => [
        'register' => [
            'subject' => 'Codice di verifica account',
            'eyebrow' => 'Verifica account',
            'title' => 'Il tuo codice OTP',
            'intro' => 'Usa questo codice per completare la registrazione sul sito del ristorante.',
        ],
        'login' => [
            'subject' => 'Codice di accesso',
            'eyebrow' => 'Accesso sicuro',
            'title' => 'Il tuo codice per il login',
            'intro' => 'Usa questo codice per accedere al tuo account cliente.',
        ],
        'password_reset' => [
            'subject' => 'Codice reimpostazione password',
            'eyebrow' => 'Reimposta password',
            'title' => 'Il tuo codice per la nuova password',
            'intro' => 'Usa questo codice per impostare una nuova password del tuo account cliente.',
        ],
        'expires_in' => 'Il codice scade tra :minutes minuti.',
        'ignore' => 'Se non hai richiesto questa operazione, puoi ignorare questa email.',
    ],
];
