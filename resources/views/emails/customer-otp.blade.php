<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Codice di verifica</title>
</head>
<body style="margin:0; padding:0; background:#f5f7fb; font-family:Arial, Helvetica, sans-serif; color:#1e2d64;">
    <div style="max-width:560px; margin:0 auto; padding:32px 16px;">
        <div style="background:#ffffff; border-radius:20px; padding:32px 24px; box-shadow:0 12px 30px rgba(0,0,0,.08);">
            <p style="margin:0 0 12px; font-size:14px; letter-spacing:.08em; text-transform:uppercase; color:#10b793;">{{ __('customer.mail.' . $purpose . '.eyebrow') }}</p>
            <h1 style="margin:0 0 16px; font-size:28px; line-height:1.2;">{{ __('customer.mail.' . $purpose . '.title') }}</h1>
            <p style="margin:0 0 24px; font-size:16px; line-height:1.6;">
                {{ __('customer.mail.' . $purpose . '.intro') }}
            </p>

            <div style="margin:0 0 24px; padding:18px 20px; border-radius:16px; background:#1e2d64; color:#ffffff; text-align:center;">
                <span style="display:block; font-size:34px; font-weight:700; letter-spacing:.35em; text-indent:.35em;">{{ $code }}</span>
            </div>

            <p style="margin:0 0 10px; font-size:15px; line-height:1.6;">
                {{ __('customer.mail.expires_in', ['minutes' => $expiresInMinutes]) }}
            </p>
            <p style="margin:0; font-size:13px; line-height:1.6; color:#5f6c91;">
                {{ __('customer.mail.ignore') }}
            </p>
        </div>
    </div>
</body>
</html>
