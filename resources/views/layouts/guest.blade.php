<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    {{-- Favicon --}}
    <link rel="shortcut icon" href="{{ asset('public/favicon.png') }}" type="image/x-icon">
    <title>{{ __('admin.Dashboard_') }}</title>
    @vite('resources/js/app.js')
</head>
<body>
    <header>
        <div class="container my-5" >
            <h1>{{ __('admin.Benvenuto_nellarea_damministrazione') }}</h1>
            <p>{{ __('admin.Esegui_laccesso_per_vedere_i_tuoi_contenuti') }}</p>
            @if (config('configurazione.APP_URL') == 'https://db-demo3.future-plus.it')
                <p style="font-style: italic">{{ __('admin.Le_credenziali_per_accedere_alla_demo_sono_email') }}<strong> demo@demo.it </strong>{{ __('admin.password') }}<strong> demo1   </strong></p>
            @endif
        </div>
    </header>


    <div class="container">
        <main>
            @yield('contents')
        </main>
    </div>
    

</body>
</html>
