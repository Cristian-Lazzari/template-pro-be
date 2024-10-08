<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    {{-- Favicon --}}
    <link rel="shortcut icon" href="{{ asset('public/favicon.png') }}" type="image/x-icon">
    <title>Dashboard +</title>
    @vite('resources/js/app.js')
    <style>
        /* CSS per il loader */
        .loader {
            position: fixed;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            border: 16px solid #f3f3f3;
            border-radius: 50%;
            border-top: 16px solid #3498db;
            width: 120px;
            height: 120px;
            animation: spin 2s linear infinite;
            z-index: 9999;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Nascondi il contenuto della pagina fino a quando il loader è visibile */
        body.loading {
            overflow: hidden;
        }

        body.loading .container, body.loading header {
            display: none;
        }
    </style>
</head>
<body class="loading">

    <div class="loader"></div>

    <header>
        @include('admin.includes.nav')
    </header>

    <div class="p-3 container">
        @yield('contents')
    </div>

    <script>
        // Rimuovi il loader e mostra il contenuto della pagina quando tutto è caricato
        window.addEventListener('load', function() {
            document.body.classList.remove('loading');
            document.querySelector('.loader').style.display = 'none';
        });
    </script>

</body>
</html>

