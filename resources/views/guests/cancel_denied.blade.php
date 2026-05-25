<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-Compatible" content="ie=edge">
    <link rel="shortcut icon" href="{{ asset('public/favicon.png') }}" type="image/x-icon">
    <title>{{ config('configurazione.APP_NAME') }}</title>
    @vite('resources/js/app.js')
    <style>
        body {
            margin: 0;
            font-family: system-ui, -apple-system, sans-serif;
            background: #f9fafb;
            color: #111827;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100svh;
            padding: 1.5rem;
            box-sizing: border-box;
        }
        .card {
            background: #ffffff;
            border: 1.5px solid #e5e7eb;
            border-radius: 16px;
            padding: 2.5rem 2rem;
            max-width: 480px;
            width: 100%;
            text-align: center;
            box-shadow: 0 4px 24px rgba(0,0,0,0.06);
        }
        .icon {
            font-size: 2.8rem;
            line-height: 1;
            margin-bottom: 1.25rem;
        }
        h1 {
            font-size: 1.25rem;
            font-weight: 800;
            margin: 0 0 0.75rem;
            color: #111827;
        }
        p {
            font-size: 0.93rem;
            line-height: 1.65;
            color: #4b5563;
            margin: 0 0 1rem;
        }
        .rules {
            background: #f3f4f6;
            border-radius: 10px;
            padding: 1rem 1.25rem;
            text-align: left;
            margin: 1.25rem 0;
        }
        .rules p {
            margin: 0 0 0.4rem;
            font-size: 0.88rem;
            color: #374151;
        }
        .rules p:last-child { margin-bottom: 0; }
        .rules strong { color: #111827; }
        .phone-btn {
            display: inline-block;
            margin-top: 0.5rem;
            padding: 0.7rem 1.75rem;
            background: #111827;
            color: #ffffff;
            font-size: 0.95rem;
            font-weight: 700;
            text-decoration: none;
            border-radius: 999px;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">⛔</div>

        @if (($reason ?? '') === 'link_invalid')
            <h1>Link non valido</h1>
            <p>Questo link di annullamento non è più valido o è stato modificato.</p>
        @else
            <h1>
                {{ ($type ?? 'or') === 'or' ? 'Impossibile annullare l\'ordine' : 'Impossibile annullare la prenotazione' }}
            </h1>
            <p>
                L'annullamento autonomo non è più consentito perché non sono soddisfatte le condizioni necessarie.
            </p>
            <div class="rules">
                <p>L'annullamento in autonomia è permesso solo se:</p>
                <p>✓ <strong>entro 5 minuti</strong> dalla conferma dell'ordine, oppure</p>
                <p>✓ con almeno <strong>24 ore di preavviso</strong> rispetto all'orario previsto</p>
            </div>
        @endif

        @if (!empty($phone))
            <p>Per ulteriori informazioni o per richiedere l'annullamento, contatta il locale:</p>
            <a href="tel:{{ $phone }}" class="phone-btn">{{ $phone }}</a>
        @endif
    </div>
</body>
</html>
