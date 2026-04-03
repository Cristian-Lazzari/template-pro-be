<?php

return [
    'messages' => [
        'email_already_registered' => 'Exista deja un cont de client cu acest email.',
        'customer_not_found' => 'Nu a fost gasit niciun client inregistrat cu acest email.',
        'email_not_verified' => 'Emailul nu este verificat. Finalizeaza mai intai verificarea.',
        'wait_before_new_code' => 'Asteapta inainte de a solicita un cod nou.',
        'otp_invalid_or_expired' => 'Codul OTP este invalid sau expirat.',
        'otp_too_many_attempts' => 'Ai depasit numarul maxim de incercari. Solicita un cod nou.',
        'otp_incorrect' => 'Codul OTP introdus nu este corect.',
        'otp_sent_register' => 'Codul OTP a fost trimis prin email pentru finalizarea inregistrarii.',
        'otp_sent_login' => 'Codul OTP a fost trimis prin email pentru autentificare.',
        'otp_sent_password_reset' => 'Codul OTP a fost trimis prin email pentru resetarea parolei.',
        'register_completed' => 'Inregistrarea a fost finalizata cu succes.',
        'login_completed' => 'Autentificarea a fost finalizata cu succes.',
        'password_reset_completed' => 'Parola a fost actualizata cu succes.',
        'logout_completed' => 'Deconectare efectuata cu succes.',
    ],
    'mail' => [
        'register' => [
            'subject' => 'Cod de verificare cont',
            'eyebrow' => 'Verificare cont',
            'title' => 'Codul tau OTP',
            'intro' => 'Foloseste acest cod pentru a finaliza inregistrarea pe site-ul restaurantului.',
        ],
        'login' => [
            'subject' => 'Cod de autentificare',
            'eyebrow' => 'Autentificare sigura',
            'title' => 'Codul tau de autentificare',
            'intro' => 'Foloseste acest cod pentru a accesa contul tau de client.',
        ],
        'password_reset' => [
            'subject' => 'Cod pentru resetarea parolei',
            'eyebrow' => 'Resetare parola',
            'title' => 'Codul tau pentru parola noua',
            'intro' => 'Foloseste acest cod pentru a seta o parola noua pentru contul tau de client.',
        ],
        'expires_in' => 'Codul expira in :minutes minute.',
        'ignore' => 'Daca nu ai solicitat aceasta actiune, poti ignora acest email.',
    ],
];
