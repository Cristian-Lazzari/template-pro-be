<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="shortcut icon" href="{{ asset('public/favicon.png') }}" type="image/x-icon">
    <title>@yield('title', config('configurazione.APP_NAME', 'Backoffice'))</title>
    @vite('resources/js/app.js')
</head>
<body class="public-shell">
    <div class="public-backdrop"></div>

    <header class="public-header">
        <div class="public-header__bar">
            <a class="public-brand" href="{{ route('guest.home') }}">
                <span class="public-brand__eyebrow">Backoffice Guide</span>
                <strong>{{ config('configurazione.APP_NAME', 'Gestionale') }}</strong>
            </a>

            <nav class="public-nav" aria-label="Navigazione pubblica">
                <a href="{{ route('guest.home') }}">Home</a>
                <a href="{{ route('guest.documentation') }}">Documentazione</a>
                <a href="{{ route('guest.updates') }}">Aggiornamenti</a>
                <a class="public-nav__login" href="{{ route('login') }}">Accedi</a>
            </nav>
        </div>

        <div class="public-hero">
            <div class="public-hero__copy">
                <p class="public-kicker">@yield('kicker', 'Supporto pubblico')</p>
                <h1>@yield('headline')</h1>
                <p class="public-lead">@yield('lead')</p>
            </div>

            <div class="public-actions">
                @yield('hero_actions')
            </div>
        </div>
    </header>

    <main class="public-main">
        @yield('contents')
    </main>
</body>
</html>
