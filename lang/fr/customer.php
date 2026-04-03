<?php

return [
    'messages' => [
        'email_already_registered' => 'Un compte client existe deja avec cet email.',
        'customer_not_found' => 'Aucun client enregistre n a ete trouve avec cet email.',
        'email_not_verified' => 'Email non verifie. Veuillez terminer la verification.',
        'wait_before_new_code' => 'Veuillez attendre avant de demander un nouveau code.',
        'otp_invalid_or_expired' => 'Le code OTP est invalide ou expire.',
        'otp_too_many_attempts' => 'Trop de tentatives. Demandez un nouveau code.',
        'otp_incorrect' => 'Le code OTP saisi est incorrect.',
        'otp_sent_register' => 'Code OTP envoye par email pour terminer l inscription.',
        'otp_sent_login' => 'Code OTP envoye par email pour la connexion.',
        'otp_sent_password_reset' => 'Code OTP envoye par email pour reinitialiser le mot de passe.',
        'register_completed' => 'Inscription terminee avec succes.',
        'login_completed' => 'Connexion effectuee avec succes.',
        'password_reset_completed' => 'Mot de passe mis a jour avec succes.',
        'logout_completed' => 'Deconnexion effectuee avec succes.',
    ],
    'mail' => [
        'register' => [
            'subject' => 'Code de verification du compte',
            'eyebrow' => 'Verification du compte',
            'title' => 'Votre code OTP',
            'intro' => 'Utilisez ce code pour terminer votre inscription sur le site du restaurant.',
        ],
        'login' => [
            'subject' => 'Code de connexion',
            'eyebrow' => 'Connexion securisee',
            'title' => 'Votre code de connexion',
            'intro' => 'Utilisez ce code pour acceder a votre compte client.',
        ],
        'password_reset' => [
            'subject' => 'Code de reinitialisation du mot de passe',
            'eyebrow' => 'Reinitialiser le mot de passe',
            'title' => 'Votre code pour le nouveau mot de passe',
            'intro' => 'Utilisez ce code pour definir un nouveau mot de passe pour votre compte client.',
        ],
        'expires_in' => 'Le code expire dans :minutes minutes.',
        'ignore' => 'Si vous n avez pas demande cette action, vous pouvez ignorer cet email.',
    ],
];
