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
</head>
<body>
    @php
          //se impostato a true gli ordini vengono presi in base ai pezzi altrimenti in base al numero di ordini
        $domain = 'https://db.qualcosa.it/public/';
        $allergien = [
                    1 => ['img' => $domain . 'glutine.png', 'name' => 'glutine'] ,
                    2 => ['img' => $domain . 'pesce.png', 'name' => 'pesce'] ,
                    3 => ['img' => $domain . 'crostacei.png', 'name' => 'crostacei'] ,
                    4 => ['img' => $domain . 'latticini.png', 'name' => 'latticini'] ,
                    5 => ['img' => $domain . '', 'name' => ''] ,
                    6 => ['img' => $domain . '', 'name' => ''] ,
                    7 => ['img' => $domain . '', 'name' => ''] ,
                ]; 
    @endphp
    <header>
        @include('admin.includes.nav')
    </header>

    <div class="container py-4">
        @yield('contents')
    </div>

</body>
</html>
