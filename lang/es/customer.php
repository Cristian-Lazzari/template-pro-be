<?php

return [
    'messages' => [
        'otp_sent' => 'Hemos enviado tu codigo de confirmacion por email.',
        'otp_rate_limited' => 'Has solicitado demasiados codigos. Espera unos minutos antes de volver a intentarlo.',
        'otp_invalid_or_expired' => 'El codigo no es valido o ha caducado.',
        'otp_too_many_attempts' => 'Demasiados intentos. Solicita un nuevo codigo.',
        'otp_incorrect' => 'El codigo introducido no es correcto.',
        'otp_verified' => 'Email confirmado correctamente.',
        'checkout_verification_required' => 'Confirma tu email con el codigo recibido antes de completar el pedido.',
        'logout_completed' => 'Sesion cerrada correctamente.',
    ],
    'mail' => [
        'subject' => 'Codigo de confirmacion por email',
        'eyebrow' => 'Confirmacion rapida',
        'title' => 'Tu codigo de verificacion',
        'intro' => 'Usa este codigo para confirmar tu email y continuar en la web del restaurante.',
        'expires_in' => 'El codigo caduca en :minutes minutos.',
        'ignore' => 'Si no has solicitado este codigo, puedes ignorar este email.',
    ],
];
