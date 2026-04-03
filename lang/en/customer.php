<?php

return [
    'messages' => [
        'email_already_registered' => 'A customer account with this email already exists.',
        'customer_not_found' => 'No registered customer was found with this email.',
        'email_not_verified' => 'Email not verified. Please complete verification first.',
        'wait_before_new_code' => 'Please wait before requesting a new code.',
        'otp_invalid_or_expired' => 'The OTP code is invalid or expired.',
        'otp_too_many_attempts' => 'Too many attempts. Please request a new code.',
        'otp_incorrect' => 'The OTP code you entered is incorrect.',
        'otp_sent_register' => 'OTP code sent by email to complete registration.',
        'otp_sent_login' => 'OTP code sent by email for login.',
        'otp_sent_password_reset' => 'OTP code sent by email to reset your password.',
        'register_completed' => 'Registration completed successfully.',
        'login_completed' => 'Login completed successfully.',
        'password_reset_completed' => 'Password updated successfully.',
        'logout_completed' => 'Logout completed successfully.',
    ],
    'mail' => [
        'register' => [
            'subject' => 'Account verification code',
            'eyebrow' => 'Account verification',
            'title' => 'Your OTP code',
            'intro' => 'Use this code to complete your registration on the restaurant website.',
        ],
        'login' => [
            'subject' => 'Login code',
            'eyebrow' => 'Secure login',
            'title' => 'Your login code',
            'intro' => 'Use this code to access your customer account.',
        ],
        'password_reset' => [
            'subject' => 'Password reset code',
            'eyebrow' => 'Reset password',
            'title' => 'Your new password code',
            'intro' => 'Use this code to set a new password for your customer account.',
        ],
        'expires_in' => 'The code expires in :minutes minutes.',
        'ignore' => 'If you did not request this action, you can ignore this email.',
    ],
];
