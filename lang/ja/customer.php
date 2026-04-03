<?php

return [
    'messages' => [
        'email_already_registered' => 'このメールアドレスには既に顧客アカウントがあります。',
        'customer_not_found' => 'このメールアドレスの登録済み顧客が見つかりません。',
        'email_not_verified' => 'メールアドレスが未確認です。先に確認を完了してください。',
        'wait_before_new_code' => '新しいコードを要求する前に少し待ってください。',
        'otp_invalid_or_expired' => 'OTPコードが無効か有効期限切れです。',
        'otp_too_many_attempts' => '試行回数が上限を超えました。新しいコードを要求してください。',
        'otp_incorrect' => '入力したOTPコードが正しくありません。',
        'otp_sent_register' => '登録完了のためのOTPコードをメールで送信しました。',
        'otp_sent_login' => 'ログイン用のOTPコードをメールで送信しました。',
        'otp_sent_password_reset' => 'パスワード再設定用のOTPコードをメールで送信しました。',
        'register_completed' => '登録が正常に完了しました。',
        'login_completed' => 'ログインが正常に完了しました。',
        'password_reset_completed' => 'パスワードが正常に更新されました。',
        'logout_completed' => 'ログアウトが完了しました。',
    ],
    'mail' => [
        'register' => [
            'subject' => 'アカウント確認コード',
            'eyebrow' => 'アカウント確認',
            'title' => 'あなたのOTPコード',
            'intro' => 'このコードを使ってレストランサイトでの登録を完了してください。',
        ],
        'login' => [
            'subject' => 'ログインコード',
            'eyebrow' => '安全なログイン',
            'title' => 'あなたのログインコード',
            'intro' => 'このコードを使って顧客アカウントにアクセスしてください。',
        ],
        'password_reset' => [
            'subject' => 'パスワード再設定コード',
            'eyebrow' => 'パスワード再設定',
            'title' => '新しいパスワード用コード',
            'intro' => 'このコードを使って顧客アカウントの新しいパスワードを設定してください。',
        ],
        'expires_in' => 'このコードは:minutes分で期限切れになります。',
        'ignore' => 'この操作を要求していない場合は、このメールを無視してください。',
    ],
];
