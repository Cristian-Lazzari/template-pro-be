<?php

return [
    'messages' => [
        'otp_sent' => 'Am trimis codul de confirmare pe email.',
        'otp_rate_limited' => 'Ai cerut prea multe coduri. Asteapta cateva minute inainte sa incerci din nou.',
        'otp_invalid_or_expired' => 'Codul nu este valid sau a expirat.',
        'otp_too_many_attempts' => 'Prea multe incercari. Cere un cod nou.',
        'otp_incorrect' => 'Codul introdus nu este corect.',
        'otp_verified' => 'Email confirmat cu succes.',
        'checkout_verification_required' => 'Confirma mai intai emailul cu codul primit inainte de a finaliza comanda.',
        'logout_completed' => 'Deconectare efectuata cu succes.',
    ],
    'mail' => [
        'subject' => 'Cod de confirmare email',
        'eyebrow' => 'Confirmare rapida',
        'title' => 'Codul tau de verificare',
        'intro' => 'Foloseste acest cod pentru a confirma emailul si pentru a continua pe site-ul restaurantului.',
        'expires_in' => 'Codul expira in :minutes minute.',
        'ignore' => 'Daca nu ai cerut acest cod, poti ignora acest email.',
    ],
];
