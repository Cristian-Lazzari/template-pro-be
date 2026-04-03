<?php

return [
    'messages' => [
        'email_already_registered' => 'Es gibt bereits ein Kundenkonto mit dieser E-Mail.',
        'customer_not_found' => 'Es wurde kein registrierter Kunde mit dieser E-Mail gefunden.',
        'email_not_verified' => 'E-Mail nicht verifiziert. Bitte schliesse zuerst die Verifizierung ab.',
        'wait_before_new_code' => 'Bitte warte, bevor du einen neuen Code anforderst.',
        'otp_invalid_or_expired' => 'Der OTP-Code ist ungueltig oder abgelaufen.',
        'otp_too_many_attempts' => 'Zu viele Versuche. Bitte fordere einen neuen Code an.',
        'otp_incorrect' => 'Der eingegebene OTP-Code ist nicht korrekt.',
        'otp_sent_register' => 'OTP-Code per E-Mail gesendet, um die Registrierung abzuschliessen.',
        'otp_sent_login' => 'OTP-Code per E-Mail fuer den Login gesendet.',
        'otp_sent_password_reset' => 'OTP-Code per E-Mail zum Zuruecksetzen des Passworts gesendet.',
        'register_completed' => 'Registrierung erfolgreich abgeschlossen.',
        'login_completed' => 'Login erfolgreich abgeschlossen.',
        'password_reset_completed' => 'Passwort erfolgreich aktualisiert.',
        'logout_completed' => 'Erfolgreich abgemeldet.',
    ],
    'mail' => [
        'register' => [
            'subject' => 'Kontobestaetigungscode',
            'eyebrow' => 'Kontobestaetigung',
            'title' => 'Dein OTP-Code',
            'intro' => 'Verwende diesen Code, um deine Registrierung auf der Restaurant-Website abzuschliessen.',
        ],
        'login' => [
            'subject' => 'Anmeldecode',
            'eyebrow' => 'Sicherer Login',
            'title' => 'Dein Login-Code',
            'intro' => 'Verwende diesen Code, um auf dein Kundenkonto zuzugreifen.',
        ],
        'password_reset' => [
            'subject' => 'Code zum Zuruecksetzen des Passworts',
            'eyebrow' => 'Passwort zuruecksetzen',
            'title' => 'Dein Code fuer ein neues Passwort',
            'intro' => 'Verwende diesen Code, um ein neues Passwort fuer dein Kundenkonto festzulegen.',
        ],
        'expires_in' => 'Der Code laeuft in :minutes Minuten ab.',
        'ignore' => 'Wenn du diese Aktion nicht angefordert hast, kannst du diese E-Mail ignorieren.',
    ],
];
