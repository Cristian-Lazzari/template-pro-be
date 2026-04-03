<?php

return [
    'messages' => [
        'email_already_registered' => 'Ya existe una cuenta de cliente con este email.',
        'customer_not_found' => 'No existe ningun cliente registrado con este email.',
        'email_not_verified' => 'Email no verificado. Completa antes la verificacion.',
        'wait_before_new_code' => 'Espera antes de solicitar un nuevo codigo.',
        'otp_invalid_or_expired' => 'El codigo OTP no es valido o ha caducado.',
        'otp_too_many_attempts' => 'Has superado el numero maximo de intentos. Solicita un nuevo codigo.',
        'otp_incorrect' => 'El codigo OTP introducido no es correcto.',
        'otp_sent_register' => 'Codigo OTP enviado por email para completar el registro.',
        'otp_sent_login' => 'Codigo OTP enviado por email para el acceso.',
        'otp_sent_password_reset' => 'Codigo OTP enviado por email para restablecer la contrasena.',
        'register_completed' => 'Registro completado correctamente.',
        'login_completed' => 'Acceso completado correctamente.',
        'password_reset_completed' => 'Contrasena actualizada correctamente.',
        'logout_completed' => 'Sesion cerrada correctamente.',
    ],
    'mail' => [
        'register' => [
            'subject' => 'Codigo de verificacion de la cuenta',
            'eyebrow' => 'Verificacion de cuenta',
            'title' => 'Tu codigo OTP',
            'intro' => 'Usa este codigo para completar el registro en el sitio del restaurante.',
        ],
        'login' => [
            'subject' => 'Codigo de acceso',
            'eyebrow' => 'Acceso seguro',
            'title' => 'Tu codigo de acceso',
            'intro' => 'Usa este codigo para acceder a tu cuenta de cliente.',
        ],
        'password_reset' => [
            'subject' => 'Codigo para restablecer la contrasena',
            'eyebrow' => 'Restablecer contrasena',
            'title' => 'Tu codigo para la nueva contrasena',
            'intro' => 'Usa este codigo para establecer una nueva contrasena para tu cuenta de cliente.',
        ],
        'expires_in' => 'El codigo caduca en :minutes minutos.',
        'ignore' => 'Si no has solicitado esta operacion, puedes ignorar este email.',
    ],
];
