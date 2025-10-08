@extends('layouts.base')

@section('contents')
@if (session('success'))
    @php
        $data = session('success')
    @endphp
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ $data }} 
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if (count($notify))
    <div id="alert-container" >
        @foreach ($notify as $r)
            @if ($r['type'] == 'res')  
                <div class="alert alert-dismissible fade show fixed-alert-res" role="alert">
                <a href="{{ route('admin.reservations.show', $r['id']) }}" class="btn btn-dark-outline">Dettagli</a> 
            @else    
                <div class="alert alert-dismissible fade show fixed-alert-res" role="alert">
                <a href="{{ route('admin.orders.show', $r['id']) }}" class="btn btn-dark-outline">Dettagli</a> 
            @endif 
                {{ $r['m'] }} 
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endforeach
    </div>
@endif
@php
    $day_time = [];
    $start = new DateTime($property_adv['times_start']);
    $end = new DateTime($property_adv['times_end']);
    $index = 1;
    $interval = $property_adv['times_interval'];

    // Loop finché l'orario di inizio è inferiore all'orario di fine
    while ($start <= $end) {
        $day_time[$index] = [
            'time' => $start->format('H:i'),
            'set' => ''
        ];
        // Incrementa l'orario di inizio con l'intervallo specificato
        $start->modify("+$interval minutes");
        $index++;
    }
    $pack = $property_adv['services'];
    $double = $property_adv['dt'];
@endphp

<div class="dash_page">
    <h1>
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar2-check-fill" viewBox="0 0 16 16">
            <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5m9.954 3H2.545c-.3 0-.545.224-.545.5v1c0 .276.244.5.545.5h10.91c.3 0 .545-.224.545-.5v-1c0-.276-.244-.5-.546-.5m-2.6 5.854a.5.5 0 0 0-.708-.708L7.5 10.793 6.354 9.646a.5.5 0 1 0-.708.708l1.5 1.5a.5.5 0 0 0 .708 0z"/>
        </svg>
        Ordini e Prenotazioni
    </h1>
    <div class="top_action my-4">

        <a class="my_btn_3" href="{{ route('admin.reservations.index') }}">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-credit-card-2-front-fill" viewBox="0 0 16 16">
            <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm2.5 1a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h2a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5zm0 3a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1zm0 2a.5.5 0 0 0 0 1h1a.5.5 0 0 0 0-1zm3 0a.5.5 0 0 0 0 1h1a.5.5 0 0 0 0-1zm3 0a.5.5 0 0 0 0 1h1a.5.5 0 0 0 0-1zm3 0a.5.5 0 0 0 0 1h1a.5.5 0 0 0 0-1z"/>
            </svg>
            Prenotazioni
            {{-- Vedi tutti --}}
        </a> 
        <a class="my_btn_3" href="{{ route('admin.orders.index') }}">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-credit-card-2-front-fill" viewBox="0 0 16 16">
            <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm2.5 1a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h2a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5zm0 3a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1zm0 2a.5.5 0 0 0 0 1h1a.5.5 0 0 0 0-1zm3 0a.5.5 0 0 0 0 1h1a.5.5 0 0 0 0-1zm3 0a.5.5 0 0 0 0 1h1a.5.5 0 0 0 0-1zm3 0a.5.5 0 0 0 0 1h1a.5.5 0 0 0 0-1z"/>
            </svg>
            Ordni
            {{-- Vedi tutti --}}
        </a> 
        <button id="editToggle" class="my_btn_2" data-bs-toggle="modal" data-bs-target="#staticBackdropav" >
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-repeat" viewBox="0 0 16 16">
                <path d="M11.534 7h3.932a.25.25 0 0 1 .192.41l-1.966 2.36a.25.25 0 0 1-.384 0l-1.966-2.36a.25.25 0 0 1 .192-.41m-11 2h3.932a.25.25 0 0 0 .192-.41L2.692 6.23a.25.25 0 0 0-.384 0L.342 8.59A.25.25 0 0 0 .534 9"/>
                <path fill-rule="evenodd" d="M8 3c-1.552 0-2.94.707-3.857 1.818a.5.5 0 1 1-.771-.636A6.002 6.002 0 0 1 13.917 7H12.9A5 5 0 0 0 8 3M3.1 9a5.002 5.002 0 0 0 8.757 2.182.5.5 0 1 1 .771.636A6.002 6.002 0 0 1 2.083 9z"/>
            </svg>
            {{-- Reset disponibilità --}}
        </button>
        <button  type="button" class=" my_btn_2 btn_delete" data-bs-toggle="modal" data-bs-target="#exampleModal1">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-ban" viewBox="0 0 16 16">
                <path d="M15 8a6.97 6.97 0 0 0-1.71-4.584l-9.874 9.875A7 7 0 0 0 15 8M2.71 12.584l9.874-9.875a7 7 0 0 0-9.874 9.874ZM16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0"/>
                </svg>
            {{-- Blocca Giorni --}}
        </button>
    </div>
    <div class="date">
        @if (count($calendar))
            @php 
                $i = 0; 
                $currentDay = date("d");
                $currentMonth = date("m");
                $currentYear = date("Y");
            @endphp
            <div id="calendar_1" class="carousel slide my_carousel">
                <div class="carousel-indicators">
                    @foreach ($calendar as $m)
                        <button  type="button" data-bs-target="#calendar_1" data-bs-slide-to="{{$i}}"
                        @if ($currentMonth == $m['month'] && $currentYear == $m['year'])
                            class="active" aria-current="true" 
                        @endif
                        aria-label="{{ 'Slide ' . $i }}"></button>
                        @php $i ++ @endphp
                    @endforeach
                    @php $i = 0; @endphp
                </div>
                <div class="top_line">
                    <button class="prev_btn" type="button" data-bs-target="#calendar_1" data-bs-slide="prev">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-caret-left-fill" viewBox="0 0 16 16">
                        <path d="m3.86 8.753 5.482 4.796c.646.566 1.658.106 1.658-.753V3.204a1 1 0 0 0-1.659-.753l-5.48 4.796a1 1 0 0 0 0 1.506z"/>
                        </svg>
                    </button>
                    <button class="post_btn" type="button" data-bs-target="#calendar_1" data-bs-slide="next">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-caret-right-fill" viewBox="0 0 16 16">
                        <path d="m12.14 8.753-5.482 4.796c-.646.566-1.658.106-1.658-.753V3.204a1 1 0 0 1 1.659-.753l5.48 4.796a1 1 0 0 1 0 1.506z"/>
                        </svg>
                    </button>
                </div>
                <div class="carousel-inner">
                    @foreach ($calendar as $m)
                        {{-- @dump('current'.$currentMonth . " " . $currentYear)
                        @dump('passed'.$m['month'] . " " . $m['year']) --}}
                        <div class="carousel-item @if ($currentMonth == $m['month'] && $currentYear == $m['year']) active @endif">
                            <h2>{{['', 'gennaio', 'febbraio', 'marzo', 'aprile', 'maggio', 'giugno', 'luglio', 'agosto', 'settembre', 'ottobre', 'novembre', 'dicembre'][$m['month']]}} - {{$m['year']}}</h2>
                            <div class="top_stat">
                                <div class="line">
                                    <h4>Prenotazioni</h4>
                                    <div class="stat">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-card-heading" viewBox="0 0 16 16">
                                            <path d="M14.5 3a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-13a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5zm-13-1A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h13a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2z"/>
                                            <path d="M3 8.5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h6a.5.5 0 0 1 0 1h-6a.5.5 0 0 1-.5-.5m0-5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-9a.5.5 0 0 1-.5-.5z"/>
                                        </svg>
                                        <span>{{$m['n_res']}}</span>
                                    </div>
                                    
                                    <div class="stat">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-people-fill" viewBox="0 0 16 16">
                                            <path d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1zm4-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6m-5.784 6A2.24 2.24 0 0 1 5 13c0-1.355.68-2.75 1.936-3.72A6.3 6.3 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1zM4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5"/>
                                        </svg>
                                        <span>{{$m['guests']}}</span>
                                    </div>
                                </div>
                                <div class="line">
                                    <h4>Ordini</h4>
                                    <div class="stat">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-inboxes" viewBox="0 0 16 16">
                                            <path d="M4.98 1a.5.5 0 0 0-.39.188L1.54 5H6a.5.5 0 0 1 .5.5 1.5 1.5 0 0 0 3 0A.5.5 0 0 1 10 5h4.46l-3.05-3.812A.5.5 0 0 0 11.02 1zm9.954 5H10.45a2.5 2.5 0 0 1-4.9 0H1.066l.32 2.562A.5.5 0 0 0 1.884 9h12.234a.5.5 0 0 0 .496-.438zM3.809.563A1.5 1.5 0 0 1 4.981 0h6.038a1.5 1.5 0 0 1 1.172.563l3.7 4.625a.5.5 0 0 1 .105.374l-.39 3.124A1.5 1.5 0 0 1 14.117 10H1.883A1.5 1.5 0 0 1 .394 8.686l-.39-3.124a.5.5 0 0 1 .106-.374zM.125 11.17A.5.5 0 0 1 .5 11H6a.5.5 0 0 1 .5.5 1.5 1.5 0 0 0 3 0 .5.5 0 0 1 .5-.5h5.5a.5.5 0 0 1 .496.562l-.39 3.124A1.5 1.5 0 0 1 14.117 16H1.883a1.5 1.5 0 0 1-1.489-1.314l-.39-3.124a.5.5 0 0 1 .121-.393zm.941.83.32 2.562a.5.5 0 0 0 .497.438h12.234a.5.5 0 0 0 .496-.438l.32-2.562H10.45a2.5 2.5 0 0 1-4.9 0z"/>
                                        </svg>
                                        <span>{{$m['n_order']}}</span>
                                    </div>
                                    <div class="stat">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-piggy-bank-fill" viewBox="0 0 16 16">
                                        <path d="M7.964 1.527c-2.977 0-5.571 1.704-6.32 4.125h-.55A1 1 0 0 0 .11 6.824l.254 1.46a1.5 1.5 0 0 0 1.478 1.243h.263c.3.513.688.978 1.145 1.382l-.729 2.477a.5.5 0 0 0 .48.641h2a.5.5 0 0 0 .471-.332l.482-1.351c.635.173 1.31.267 2.011.267.707 0 1.388-.095 2.028-.272l.543 1.372a.5.5 0 0 0 .465.316h2a.5.5 0 0 0 .478-.645l-.761-2.506C13.81 9.895 14.5 8.559 14.5 7.069q0-.218-.02-.431c.261-.11.508-.266.705-.444.315.306.815.306.815-.417 0 .223-.5.223-.461-.026a1 1 0 0 0 .09-.255.7.7 0 0 0-.202-.645.58.58 0 0 0-.707-.098.74.74 0 0 0-.375.562c-.024.243.082.48.32.654a2 2 0 0 1-.259.153c-.534-2.664-3.284-4.595-6.442-4.595m7.173 3.876a.6.6 0 0 1-.098.21l-.044-.025c-.146-.09-.157-.175-.152-.223a.24.24 0 0 1 .117-.173c.049-.027.08-.021.113.012a.2.2 0 0 1 .064.199m-8.999-.65a.5.5 0 1 1-.276-.96A7.6 7.6 0 0 1 7.964 3.5c.763 0 1.497.11 2.18.315a.5.5 0 1 1-.287.958A6.6 6.6 0 0 0 7.964 4.5c-.64 0-1.255.09-1.826.254ZM5 6.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0"/>
                                        </svg>
                                        <span class="cash" ><strong>€</strong>{{$m['cash'] / 100}}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="calendar">
                                <div class="c-name">
                                    @php
                                    $day_name = ['LU', 'MA', 'ME', 'GI', 'VE', 'SA', 'DO'];
                                    @endphp
                                    @foreach ($day_name as $item)
                                        <h4>{{$item}}</h4>
                                    @endforeach
                                </div>
                                <div class="calendar_page">
                                    @foreach ($m['days'] as $d)
                                        <button data-day='@json($d)'
                                        class="day  
                                        @if($currentMonth == $m['month'] && $currentYear == $m['year'] && $currentDay == $d['day']) current @endif 
                                        @if(in_array($d['status'], [0,3])) day_off @endif " 
                                        style="grid-column-start:{{$d['day_w'] }}">        
                                            <p class="p_day">{{$d['day']}}</p>
                                            @if ($d['guests'] > 0)
                                                <span class="bookings"> <strong> {{$d['guests']}} </strong>
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-lines-fill" viewBox="0 0 16 16">
                                                        <path d="M6 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6m-5 6s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zM11 3.5a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 0 1h-4a.5.5 0 0 1-.5-.5m.5 2.5a.5.5 0 0 0 0 1h4a.5.5 0 0 0 0-1zm2 3a.5.5 0 0 0 0 1h2a.5.5 0 0 0 0-1zm0 3a.5.5 0 0 0 0 1h2a.5.5 0 0 0 0-1z"/>
                                                    </svg>
                                                </span>
                                            @endif
                                            @if ($d['n_order'] > 0)
                                                <span class="bookings top"> <strong> {{$d['n_order']}} </strong>
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-inboxes" viewBox="0 0 16 16">
                                                        <path d="M4.98 1a.5.5 0 0 0-.39.188L1.54 5H6a.5.5 0 0 1 .5.5 1.5 1.5 0 0 0 3 0A.5.5 0 0 1 10 5h4.46l-3.05-3.812A.5.5 0 0 0 11.02 1zm9.954 5H10.45a2.5 2.5 0 0 1-4.9 0H1.066l.32 2.562A.5.5 0 0 0 1.884 9h12.234a.5.5 0 0 0 .496-.438zM3.809.563A1.5 1.5 0 0 1 4.981 0h6.038a1.5 1.5 0 0 1 1.172.563l3.7 4.625a.5.5 0 0 1 .105.374l-.39 3.124A1.5 1.5 0 0 1 14.117 10H1.883A1.5 1.5 0 0 1 .394 8.686l-.39-3.124a.5.5 0 0 1 .106-.374zM.125 11.17A.5.5 0 0 1 .5 11H6a.5.5 0 0 1 .5.5 1.5 1.5 0 0 0 3 0 .5.5 0 0 1 .5-.5h5.5a.5.5 0 0 1 .496.562l-.39 3.124A1.5 1.5 0 0 1 14.117 16H1.883a1.5 1.5 0 0 1-1.489-1.314l-.39-3.124a.5.5 0 0 1 .121-.393zm.941.83.32 2.562a.5.5 0 0 0 .497.438h12.234a.5.5 0 0 0 .496-.438l.32-2.562H10.45a2.5 2.5 0 0 1-4.9 0z"/>
                                                    </svg>
                                                </span>
                                            @endif
                                        </button>
                                    @endforeach

                                </div>
                            </div>
                        </div>
                        @php $i ++ @endphp
                    @endforeach
                </div>
                
            </div>
        @elseif(config('configurazione.subscription') == 1)
        <div class="date-off d-back-g">
            <a href="https://future-plus.it/#pacchetti">Per permettere ai tuoi clienti di prenotare tavoli o ordinare a domicilio o asporto clicca qui e <strong>prenota una call con i nostri consulenti</strong></a>
        </div>
        @else 
        <div class="date-off">
            <a href="{{route('admin.dates.index')}}">Non sono ancora state impostate le disponibilita dei servizi, <strong>clicca QUI</strong> e impostale ora</a>
        </div>
        @endif
       
    </div>
    <div id="day-details"></div>
    


    <div class="modal fade " id="staticBackdropav" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropavLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable large_m">
            <form class="modal-content s_advanced edit_modal" action="{{ route('admin.dates.generate') }}" method="post">
                @csrf
                <h2>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-sliders" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M11.5 2a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3M9.05 3a2.5 2.5 0 0 1 4.9 0H16v1h-2.05a2.5 2.5 0 0 1-4.9 0H0V3zM4.5 7a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3M2.05 8a2.5 2.5 0 0 1 4.9 0H16v1H6.95a2.5 2.5 0 0 1-4.9 0H0V8zm9.45 4a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3m-2.45 1a2.5 2.5 0 0 1 4.9 0H16v1h-2.05a2.5 2.5 0 0 1-4.9 0H0v-1z"/>
                    </svg> Modifica le disponibilità
                </h2>

                <div class="inputs iv2">
                    <div class="input_c @if(!(in_array($pack, [2,4]) && $double)) d-none @endif" >
                        <label for="max_table_1">N° di posti per {{$property_adv['sala_1']}}</label>
                        <input name="max_table_1" id="max_table_1" type="number" placeholder="N° di posti in {{$property_adv['sala_1']}} per fascia oraria" value="{{$property_adv['max_table_1'] ?? ''}}"> 
                        @error('max_table_1') <p class="error">{{ $message }}</p> @enderror
                    </div>
                    <div class="input_c @if(!(in_array($pack, [2,4]) && $double)) d-none @endif" >
                        <label for="max_table_2">N° di posti per {{$property_adv['sala_2']}}</label>
                        <input name="max_table_2" id="max_table_2" type="number" placeholder="N° di posti in {{$property_adv['sala_2']}} per fascia oraria" value="{{$property_adv['max_table_2'] ?? ''}}"> 
                        @error('max_table_2') <p class="error">{{ $message }}</p> @enderror
                    </div>
                    <div class="input_c @if(!(in_array($pack, [2,4]) && !$double)) d-none @endif" >
                        <label for="max_table">N° di posti</label>
                        <input name="max_table" id="max_table" type="number" placeholder="N° di posti per fascia oraria" value="{{$property_adv['max_table'] ?? ''}}"> 
                        @error('max_table') <p class="error">{{ $message }}</p> @enderror
                    </div>
                    
                    <div class="input_c @if(!(in_array($pack, [3,4]))) d-none @endif" >
                        <label for="max_asporto">N° di ordini d'asporto</label>
                        <input name="max_asporto" id="max_asporto" type="number" placeholder="N° di ordini per fascia oraria" value="{{$property_adv['max_asporto'] ?? ''}}"> 
                        @error('max_asporto') <p class="error">{{ $message }}</p> @enderror
                    </div>
                    <div class="input_c @if(!(in_array($pack, [3,4]))) d-none @endif" >
                        <label for="max_domicilio">N° di oridini a domicilio</label>
                        <input name="max_domicilio" id="max_domicilio" type="number" placeholder="N° di ordini per fascia oraria" value="{{$property_adv['max_domicilio'] ?? ''}}"> 
                        @error('max_domicilio') <p class="error">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="days">
                    @foreach ([1, 2, 3, 4, 5, 6, 7] as $day)
                    <input class="btn-check" style="visibility: hidden; position: absolute;"  name="day[]" data-bs-toggle="collapse" data-bs-target="#multiCollapseExample{{$day}}" aria-expanded="true" aria-controls="multiCollapseExample{{$day}}" id="day_{{ $day }}" value="{{ $day }}">
                    <div class="day" >
                        <div class="top_day">
                           {{ [' ','lunedì', 'martedì', 'mercoledì', 'giovedì', 'venerdì', 'sabato', 'domenica'][$day] }}
                        </div>
                        <label for="day_{{ $day }}" class="btn_close">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-bar-down" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M1 3.5a.5.5 0 0 1 .5-.5h13a.5.5 0 0 1 0 1h-13a.5.5 0 0 1-.5-.5M8 6a.5.5 0 0 1 .5.5v5.793l2.146-2.147a.5.5 0 0 1 .708.708l-3 3a.5.5 0 0 1-.708 0l-3-3a.5.5 0 0 1 .708-.708L7.5 12.293V6.5A.5.5 0 0 1 8 6"/>
                            </svg>
                        </label>
                    </div>
                    <div class="collapse multi-collapse show" id="multiCollapseExample{{$day}}">
                        <div class="modal_body">
                            <div class="scroller">
                                @foreach ($day_time as $t) @php $time = $t['time']; @endphp
                                    @php
                                        $checked_1 = isset($property_adv['week_set'][$day]) && isset($property_adv['week_set'][$day][$time]) && in_array(1, $property_adv['week_set'][$day][$time]);
                                        $checked_2 = isset($property_adv['week_set'][$day]) && isset($property_adv['week_set'][$day][$time]) && in_array(2, $property_adv['week_set'][$day][$time]);
                                        $checked_3 = isset($property_adv['week_set'][$day]) && isset($property_adv['week_set'][$day][$time]) && in_array(3, $property_adv['week_set'][$day][$time]);
                                    @endphp
                                    <div class="time">
                                        <h5 onclick="event.preventDefault()">{{$time}}</h5>
                                        @if (in_array($pack, [2, 4]))
                                        <input type="checkbox" @if($checked_2) checked @endif class="btn-check" id="times_{{$day}}_{{$time}}t" name="times_slot_[{{$day}}][{{$time}}][]" value="1">
                                        <label class="btn btn-outline-light shadow-sm left " for="times_{{$day}}_{{$time}}t">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-people" viewBox="0 0 16 16">
                                                <path d="M15 14s1 0 1-1-1-4-5-4-5 3-5 4 1 1 1 1zm-7.978-1L7 12.996c.001-.264.167-1.03.76-1.72C8.312 10.629 9.282 10 11 10c1.717 0 2.687.63 3.24 1.276.593.69.758 1.457.76 1.72l-.008.002-.014.002zM11 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4m3-2a3 3 0 1 1-6 0 3 3 0 0 1 6 0M6.936 9.28a6 6 0 0 0-1.23-.247A7 7 0 0 0 5 9c-4 0-5 3-5 4q0 1 1 1h4.216A2.24 2.24 0 0 1 5 13c0-1.01.377-2.042 1.09-2.904.243-.294.526-.569.846-.816M4.92 10A5.5 5.5 0 0 0 4 13H1c0-.26.164-1.03.76-1.724.545-.636 1.492-1.256 3.16-1.275ZM1.5 5.5a3 3 0 1 1 6 0 3 3 0 0 1-6 0m3-2a2 2 0 1 0 0 4 2 2 0 0 0 0-4"/>
                                            </svg>
                                        </label>
                                        @endif 
                                        @if (in_array($pack, [3, 4]))
                                            <input type="checkbox" @if($checked_1) checked @endif  class="btn-check" id="times_{{$day}}_{{$time}}a" name="times_slot_[{{$day}}][{{$time}}][]" value="2">
                                            <label class="btn btn-outline-light shadow-sm center " for="times_{{$day}}_{{$time}}a">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-bag" viewBox="0 0 16 16">
                                                    <path d="M8 1a2.5 2.5 0 0 1 2.5 2.5V4h-5v-.5A2.5 2.5 0 0 1 8 1m3.5 3v-.5a3.5 3.5 0 1 0-7 0V4H1v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V4zM2 5h12v9a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1z"/>
                                                </svg>
                                            </label>
                                        @endif 
                                        @if (in_array($pack, [3, 4]))
                                            <input type="checkbox" @if($checked_3) checked @endif class="btn-check" id="times_{{$day}}_{{$time}}d" name="times_slot_[{{$day}}][{{$time}}][]" value="3">
                                            <label class="btn btn-outline-light shadow-sm right" for="times_{{$day}}_{{$time}}d">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-truck" viewBox="0 0 16 16">
                                                    <path d="M0 3.5A1.5 1.5 0 0 1 1.5 2h9A1.5 1.5 0 0 1 12 3.5V5h1.02a1.5 1.5 0 0 1 1.17.563l1.481 1.85a1.5 1.5 0 0 1 .329.938V10.5a1.5 1.5 0 0 1-1.5 1.5H14a2 2 0 1 1-4 0H5a2 2 0 1 1-3.998-.085A1.5 1.5 0 0 1 0 10.5zm1.294 7.456A2 2 0 0 1 4.732 11h5.536a2 2 0 0 1 .732-.732V3.5a.5.5 0 0 0-.5-.5h-9a.5.5 0 0 0-.5.5v7a.5.5 0 0 0 .294.456M12 10a2 2 0 0 1 1.732 1h.768a.5.5 0 0 0 .5-.5V8.35a.5.5 0 0 0-.11-.312l-1.48-1.85A.5.5 0 0 0 13.02 6H12zm-9 1a1 1 0 1 0 0 2 1 1 0 0 0 0-2m9 0a1 1 0 1 0 0 2 1 1 0 0 0 0-2"/>
                                                </svg>
                                            </label>
                                        @endif 
                                    </div>
                                @endforeach             
                            </div>
                        </div>        
                    </div>
                    @endforeach
                </div>
                @error('days_on') <p class="error m-2">seleziona "Attiva" nei giorni i cui sei operativo</p> @enderror
                
                <div class="modal-footer">
                    <button type="button" class="my_btn_1 d " data-bs-dismiss="modal">Annulla</button>
                    <button type="sumbit" class="my_btn_1 add ">Aggiorna</button>
                </div>
            </form>
        </div>
    </div>
        <form  action="{{ route('admin.settings.cancelDates')}}"   method="POST">
        @csrf
        <!-- Modal -->
        @php $i= 0; @endphp
        <div class="modal fade" id="exampleModal1" tabindex="-1" aria-labelledby="exampleModal1Label" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered mymodal_calendar">
                <div class="modal-content  mymodal_make_res">
                    <div class="modal-body box_container">
                        <div id="c2" class="carousel slide my_carousel" >
                            <div class="carousel-indicators">
                                @foreach ($calendar as $m)
                                    <button  type="button" data-bs-target="#c2" data-bs-slide-to="{{$i}}"
                                    @if ($currentMonth == $m['month'] && $currentYear == $m['year']) class="active" aria-current="true"@endif
                                    aria-label="{{ 'Slide ' . $i }}"></button>
                                    @php $i ++ @endphp
                                @endforeach
                            </div>
                            <div class="top_line">
                                <button class="prev_btn" type="button" data-bs-target="#c2" data-bs-slide="prev">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-caret-left-fill" viewBox="0 0 16 16">
                                    <path d="m3.86 8.753 5.482 4.796c.646.566 1.658.106 1.658-.753V3.204a1 1 0 0 0-1.659-.753l-5.48 4.796a1 1 0 0 0 0 1.506z"/>
                                    </svg>
                                </button>
                                <button class="post_btn" type="button" data-bs-target="#c2" data-bs-slide="next">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-caret-right-fill" viewBox="0 0 16 16">
                                    <path d="m12.14 8.753-5.482 4.796c-.646.566-1.658.106-1.658-.753V3.204a1 1 0 0 1 1.659-.753l5.48 4.796a1 1 0 0 1 0 1.506z"/>
                                    </svg>
                                </button>
                            </div>
                            <div class="carousel-inner date_modal">
                                @php $i = 0; @endphp
                                @foreach ($calendar as $m)
                                    <div class="carousel-item @if ($currentMonth == $m['month'] && $currentYear == $m['year'])  active @endif ">
                                        <h2>{{['', 'gennaio', 'febbraio', 'marzo', 'aprile', 'maggio', 'giugno', 'luglio', 'agosto', 'settembre', 'ottobre', 'novembre', 'dicembre'][$m['month']]}} - {{$m['year']}}</h2>
                                        <div class="calendar">
                                        
                                            <div class="c-name">
                                                @php
                                                $day_name = ['lunedì', 'martedì', 'mercoledì', 'giovedì', 'venerdì', 'sabato', 'domenica'];
                                                @endphp
                                                @foreach ($day_name as $item)
                                                    <h4>{{$item}}</h4>
                                                @endforeach
                                            </div>
                                            <div class="calendar_page">

                                                @foreach ($m['days'] as $d)
                                                    @if($d['status'] !== 0)
                                                        <input type="checkbox" name="day_off[]" id="{{$d['date']}}" value="{{$d['date']}}"
                                                        @if ($d['status'] == 3) checked @endif>
                                                    @endif
                                                    <label for="{{$d['date']}}"
                                                        class="day  
                                                        @if($currentMonth == $m['month'] && $currentYear == $m['year'] && $currentDay == $d['day']) day-active @endif 
                                                        @if($d['status'] == 0) day_off @endif "
                                                        style="grid-column-start:{{$d['day_w'] }}"
                                                        @if($currentMonth == $m['month'] && $currentYear == $m['year'] && $currentDay == $d['day']) current @endif 
                                                    >        
                                                        <p class="p_day">{{$d['day']}}</p>
                                                        @if ($d['guests'] > 0)
                                                            <span class="bookings"> <strong> {{$d['guests']}} </strong>
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-lines-fill" viewBox="0 0 16 16">
                                                                    <path d="M6 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6m-5 6s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zM11 3.5a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 0 1h-4a.5.5 0 0 1-.5-.5m.5 2.5a.5.5 0 0 0 0 1h4a.5.5 0 0 0 0-1zm2 3a.5.5 0 0 0 0 1h2a.5.5 0 0 0 0-1zm0 3a.5.5 0 0 0 0 1h2a.5.5 0 0 0 0-1z"/>
                                                                </svg>
                                                            </span>
                                                        @endif
                                                        @if ($d['n_order'] > 0)
                                                            <span class="bookings top"> <strong> {{$d['n_order']}} </strong>
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-inboxes" viewBox="0 0 16 16">
                                                                    <path d="M4.98 1a.5.5 0 0 0-.39.188L1.54 5H6a.5.5 0 0 1 .5.5 1.5 1.5 0 0 0 3 0A.5.5 0 0 1 10 5h4.46l-3.05-3.812A.5.5 0 0 0 11.02 1zm9.954 5H10.45a2.5 2.5 0 0 1-4.9 0H1.066l.32 2.562A.5.5 0 0 0 1.884 9h12.234a.5.5 0 0 0 .496-.438zM3.809.563A1.5 1.5 0 0 1 4.981 0h6.038a1.5 1.5 0 0 1 1.172.563l3.7 4.625a.5.5 0 0 1 .105.374l-.39 3.124A1.5 1.5 0 0 1 14.117 10H1.883A1.5 1.5 0 0 1 .394 8.686l-.39-3.124a.5.5 0 0 1 .106-.374zM.125 11.17A.5.5 0 0 1 .5 11H6a.5.5 0 0 1 .5.5 1.5 1.5 0 0 0 3 0 .5.5 0 0 1 .5-.5h5.5a.5.5 0 0 1 .496.562l-.39 3.124A1.5 1.5 0 0 1 14.117 16H1.883a1.5 1.5 0 0 1-1.489-1.314l-.39-3.124a.5.5 0 0 1 .121-.393zm.941.83.32 2.562a.5.5 0 0 0 .497.438h12.234a.5.5 0 0 0 .496-.438l.32-2.562H10.45a2.5 2.5 0 0 1-4.9 0z"/>
                                                                </svg>
                                                            </span>
                                                        @endif
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    @php $i ++ @endphp
                                @endforeach
                            </div>
                            
                        </div>
                        <div class="actions w-100">
                            <button class="my_btn_2 btn_delete" type="button" data-bs-dismiss="modal" >Annulla</button>
                            <button class="my_btn_3" type="submit">Conferma</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const dayButtons = document.querySelectorAll("#calendar_1 .day");
    const detailsContainer = document.getElementById("day-details");

    dayButtons.forEach(button => {
        button.addEventListener("click", () => {
            // Rimuove evidenza precedente e la aggiunge a quella cliccata
            document.querySelectorAll(".day.day-active").forEach(d => d.classList.remove("day-active"));
            button.classList.add("day-active");

            // Legge i dati del giorno
            const dayData = JSON.parse(button.dataset.day);
            const { date, times, status } = dayData;

            // Costruisce l’HTML degli orari
            let html = `
                <div class="day-info">
                    <div class="time-list ${status == 3 || status == 0 ? 'op' : ''}">
            `;

            for (const [time, data] of Object.entries(times)) {
                const res = data.res;
                const or = data.or;
                const properties = data.property.join(", ");

                html += `
                    <div class="time-item">
                        <div class="time-header">
                            <strong>${time}</strong>
                             <div class="line"></div>
                            <p class="prop"> 
                `;
                if(properties.includes(1)){
                    html += `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-people" viewBox="0 0 16 16">
                            <path d="M15 14s1 0 1-1-1-4-5-4-5 3-5 4 1 1 1 1zm-7.978-1L7 12.996c.001-.264.167-1.03.76-1.72C8.312 10.629 9.282 10 11 10c1.717 0 2.687.63 3.24 1.276.593.69.758 1.457.76 1.72l-.008.002-.014.002zM11 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4m3-2a3 3 0 1 1-6 0 3 3 0 0 1 6 0M6.936 9.28a6 6 0 0 0-1.23-.247A7 7 0 0 0 5 9c-4 0-5 3-5 4q0 1 1 1h4.216A2.24 2.24 0 0 1 5 13c0-1.01.377-2.042 1.09-2.904.243-.294.526-.569.846-.816M4.92 10A5.5 5.5 0 0 0 4 13H1c0-.26.164-1.03.76-1.724.545-.636 1.492-1.256 3.16-1.275ZM1.5 5.5a3 3 0 1 1 6 0 3 3 0 0 1-6 0m3-2a2 2 0 1 0 0 4 2 2 0 0 0 0-4"/>
                        </svg>`;
                    }
                    if(properties.includes(2)){
                    html += `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-bag" viewBox="0 0 16 16">
                            <path d="M8 1a2.5 2.5 0 0 1 2.5 2.5V4h-5v-.5A2.5 2.5 0 0 1 8 1m3.5 3v-.5a3.5 3.5 0 1 0-7 0V4H1v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V4zM2 5h12v9a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1z"/>
                        </svg>`;
                }
                if(properties.includes(3)){
                    html += ` <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-truck" viewBox="0 0 16 16">
                            <path d="M0 3.5A1.5 1.5 0 0 1 1.5 2h9A1.5 1.5 0 0 1 12 3.5V5h1.02a1.5 1.5 0 0 1 1.17.563l1.481 1.85a1.5 1.5 0 0 1 .329.938V10.5a1.5 1.5 0 0 1-1.5 1.5H14a2 2 0 1 1-4 0H5a2 2 0 1 1-3.998-.085A1.5 1.5 0 0 1 0 10.5zm1.294 7.456A2 2 0 0 1 4.732 11h5.536a2 2 0 0 1 .732-.732V3.5a.5.5 0 0 0-.5-.5h-9a.5.5 0 0 0-.5.5v7a.5.5 0 0 0 .294.456M12 10a2 2 0 0 1 1.732 1h.768a.5.5 0 0 0 .5-.5V8.35a.5.5 0 0 0-.11-.312l-1.48-1.85A.5.5 0 0 0 13.02 6H12zm-9 1a1 1 0 1 0 0 2 1 1 0 0 0 0-2m9 0a1 1 0 1 0 0 2 1 1 0 0 0 0-2"/>
                        </svg>`;
                }
                html += `</p>
                        </div>
                    <div class="time-content">
                `;
                confirm_svg = `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi okk bi-check-circle" viewBox="0 0 16 16">
                    <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
                    <path d="m10.97 4.97-.02.022-3.473 4.425-2.093-2.094a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05"/>
                    </svg>`
                to_see_svg = `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi to_see bi-exclamation-circle-fill" viewBox="0 0 16 16">
                    <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M8 4a.905.905 0 0 0-.9.995l.35 3.507a.552.552 0 0 0 1.1 0l.35-3.507A.905.905 0 0 0 8 4m.002 6a1 1 0 1 0 0 2 1 1 0 0 0 0-2"/>
                    </svg>`
                null_svg = `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi null bi-x-circle" viewBox="0 0 16 16">
                    <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
                    <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708"/>
                    </svg>`
                // Prenotazioni
                let status = ['null', 'okk', 'to_see', 'to_see', 'okk', 'null']
                if (res.length > 0) {
                    res.forEach(r => {
                        n_person =JSON.parse(r.n_person)
                        let child = n_person.child
                        let adult = n_person.adult
                        child_svg = `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-arms-up" viewBox="0 0 16 16">
                        <path d="M8 3a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3"/>
                        <path d="m5.93 6.704-.846 8.451a.768.768 0 0 0 1.523.203l.81-4.865a.59.59 0 0 1 1.165 0l.81 4.865a.768.768 0 0 0 1.523-.203l-.845-8.451A1.5 1.5 0 0 1 10.5 5.5L13 2.284a.796.796 0 0 0-1.239-.998L9.634 3.84a.7.7 0 0 1-.33.235c-.23.074-.665.176-1.304.176-.64 0-1.074-.102-1.305-.176a.7.7 0 0 1-.329-.235L4.239 1.286a.796.796 0 0 0-1.24.998l2.5 3.216c.317.316.475.758.43 1.204Z"/>
                        </svg>`
                        adult_svg = `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-standing" viewBox="0 0 16 16">
                        <path d="M8 3a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3M6 6.75v8.5a.75.75 0 0 0 1.5 0V10.5a.5.5 0 0 1 1 0v4.75a.75.75 0 0 0 1.5 0v-8.5a.25.25 0 1 1 .5 0v2.5a.75.75 0 0 0 1.5 0V6.5a3 3 0 0 0-3-3H7a3 3 0 0 0-3 3v2.75a.75.75 0 0 0 1.5 0v-2.5a.25.25 0 0 1 .5 0"/>
                        </svg>`

                        domain_link = "{{config('configurazione.APP_URL')}}" + '/admin/reservations/' + r.id

                        html += `<a href="${domain_link}" class="res-item ${status[r.status]}">
                                    <div class="top">
                                        <div class="id">R${r.id ?? ''} </div>
                                        ${[0, 6].includes(r.status) ? null_svg : ''}
                                        ${[2,3].includes(r.status) ? to_see_svg : ''}
                                        ${[1, 5].includes(r.status) ? confirm_svg : ''}
                                        <div class="name">${r.name + ' ' + r.surname} </div>
                                        <div class="guest">
                                            ${adult + adult_svg ?? ''} 
                                            ${child + child_svg ?? ''}
                                        </div>
                                    </div>
                                </a>`;
                    });
                }
                // Ordini
                if (or.length > 0) {
                    or.forEach(o => {
                        domain_link = "{{config('configurazione.APP_URL')}}" + '/admin/orders/' + o.id
                        html += `<a href="${domain_link}" class="order-item ${status[o.status]}">
                                    <div class="top">
                                        <div class="id">O${o.id ?? ''} </div>
                                        ${[0, 6].includes(o.status) ? null_svg : ''}
                                        ${[2,3].includes(o.status) ? to_see_svg : ''}
                                        ${[1, 5].includes(o.status) ? confirm_svg : ''}
                                        <div class="name">${o.name + ' ' + o.surname} </div>
                                        <div class="price">€${o.tot_price / 100}</div>
                                    </div>
                                    <div class="cart">`;
                            o.products.forEach(p => {
                                html += `<div class="item_cart">
                                            <div class="name">${p.name}</div>
                                            <div class="price">€${p.price / 100}</div>
                                        </div>`;
                            });
                                    html += `</div>`
                        });
                        html += `</a>`;
                }

                // Nessun dato
                // if (res.length === 0 && or.length === 0) {
                //     html += `<div class="no-data">Nessuna prenotazione o ordine per questo orario</div>`;
                // }

                html += `
                        </div>
                    </div>
                `;
            }

            html += `</div></div>`;

            // Mostra nel contenitore
            detailsContainer.innerHTML = html;
        });
    });
});
</script>


@endsection

