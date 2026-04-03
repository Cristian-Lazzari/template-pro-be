<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Errore')</title>
    @vite('resources/js/app.js')
    <style>
        :root {
            --error-bg: #f7f4ed;
            --error-surface: rgba(255, 255, 255, 0.88);
            --error-text: #1f2937;
            --error-muted: #6b7280;
            --error-accent: #1e2d64;
            --error-accent-hover: #16224c;
            --error-shadow: 0 24px 80px rgba(30, 45, 100, 0.14);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Instrument Sans", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            color: var(--error-text);
            background:
                radial-gradient(circle at top left, rgba(16, 183, 147, 0.16), transparent 32%),
                radial-gradient(circle at bottom right, rgba(30, 45, 100, 0.14), transparent 35%),
                var(--error-bg);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .error-card {
            width: min(100%, 680px);
            background: var(--error-surface);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.7);
            border-radius: 28px;
            padding: 40px 32px;
            box-shadow: var(--error-shadow);
            text-align: center;
        }

        .error-code {
            margin: 0 0 12px;
            font-size: clamp(3.6rem, 12vw, 6rem);
            line-height: 0.95;
            font-weight: 800;
            letter-spacing: -0.06em;
            color: var(--error-accent);
        }
        
        .error-title {
            margin: 0;
            font-size: clamp(1.6rem, 4vw, 2.2rem);
            font-weight: 700;
            color: var(--error-accent);
        }

        .error-message {
            max-width: 46ch;
            margin: 16px auto 0;
            font-size: 1rem;
            line-height: 1.7;
            color: var(--error-muted);
        }

        .error-actions {
            margin-top: 32px;
            display: flex;
            justify-content: center;
        }

        .error-home-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 220px;
            padding: 14px 22px;
            border-radius: 999px;
            background: var(--error-accent);
            color: #fff;
            text-decoration: none;
            font-weight: 700;
            transition: transform 0.18s ease, background 0.18s ease, box-shadow 0.18s ease;
            box-shadow: 0 14px 30px rgba(30, 45, 100, 0.18);
        }

        .error-home-button:hover {
            background: var(--error-accent-hover);
            transform: translateY(-1px);
        }

        .error-home-button:focus-visible {
            outline: 3px solid rgba(16, 183, 147, 0.4);
            outline-offset: 4px;
        }

        @media (max-width: 640px) {
            .error-card {
                padding: 32px 22px;
                border-radius: 22px;
            }

            .error-home-button {
                width: 100%;
                min-width: 0;
            }
        }
    </style>
</head>
<body>
    @php
        $homeUrl = auth()->check() ? route('admin.dashboard') : url('/');
    @endphp

    <main class="error-card">
        <p class="error-code">@yield('code', 'Errore')</p>
        <h1 class="error-title">@yield('title', 'Qualcosa e andato storto')</h1>
        <p class="error-message">@yield('message', 'Si e verificato un problema imprevisto.')</p>

        <div class="error-actions">
            <a class="error-home-button" href="{{ $homeUrl }}">
                Torna alla pagina principale
            </a>
        </div>
    </main>
</body>
</html>
