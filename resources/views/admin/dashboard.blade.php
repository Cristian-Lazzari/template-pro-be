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
                <a href="{{ route('admin.reservations.show', $r['id']) }}" class="btn btn-dark-outline">{{ __('admin.Dettagli') }}</a> 
            @else    
                <div class="alert alert-dismissible fade show fixed-alert-res" role="alert">
                <a href="{{ route('admin.orders.show', $r['id']) }}" class="btn btn-dark-outline">{{ __('admin.Dettagli') }}</a> 
            @endif 
                {{ $r['m'] }} 
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endforeach
    </div>
@endif
@php
    $pack = (int) ($property_adv['services'] ?? 0);
    $double = (int) ($property_adv['dt'] ?? 0);
    $weekSet = $property_adv['week_set'] ?? [1 => [], 2 => [], 3 => [], 4 => [], 5 => [], 6 => [], 7 => []];
@endphp

<div class="dash_page">
    <h1>
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar2-check-fill" viewBox="0 0 16 16">
            <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5m9.954 3H2.545c-.3 0-.545.224-.545.5v1c0 .276.244.5.545.5h10.91c.3 0 .545-.224.545-.5v-1c0-.276-.244-.5-.546-.5m-2.6 5.854a.5.5 0 0 0-.708-.708L7.5 10.793 6.354 9.646a.5.5 0 1 0-.708.708l1.5 1.5a.5.5 0 0 0 .708 0z"/>
        </svg>
        {{__('admin.t_dashboard')}}
    </h1>
    <div class="top_action my-5">

        
        <button id="editToggle" class="my_btn_2 " data-bs-toggle="modal" data-bs-target="#staticBackdropav" >
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-repeat" viewBox="0 0 16 16">
                <path d="M11.534 7h3.932a.25.25 0 0 1 .192.41l-1.966 2.36a.25.25 0 0 1-.384 0l-1.966-2.36a.25.25 0 0 1 .192-.41m-11 2h3.932a.25.25 0 0 0 .192-.41L2.692 6.23a.25.25 0 0 0-.384 0L.342 8.59A.25.25 0 0 0 .534 9"/>
                <path fill-rule="evenodd" d="M8 3c-1.552 0-2.94.707-3.857 1.818a.5.5 0 1 1-.771-.636A6.002 6.002 0 0 1 13.917 7H12.9A5 5 0 0 0 8 3M3.1 9a5.002 5.002 0 0 0 8.757 2.182.5.5 0 1 1 .771.636A6.002 6.002 0 0 1 2.083 9z"/>
            </svg>
            {{-- Reset disponibilità --}}
        </button>
        <button  type="button" class=" my_btn_1 btn_delete" data-bs-toggle="modal" data-bs-target="#exampleModal1">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-ban" viewBox="0 0 16 16">
                <path d="M15 8a6.97 6.97 0 0 0-1.71-4.584l-9.874 9.875A7 7 0 0 0 15 8M2.71 12.584l9.874-9.875a7 7 0 0 0-9.874 9.874ZM16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0"/>
                </svg>
            {{-- Blocca Giorni --}}
        </button>
        <a class="my_btn_3 ml-auto" href="{{ route('admin.reservations.index') }}">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-credit-card-2-front-fill" viewBox="0 0 16 16">
            <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm2.5 1a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h2a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5zm0 3a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1zm0 2a.5.5 0 0 0 0 1h1a.5.5 0 0 0 0-1zm3 0a.5.5 0 0 0 0 1h1a.5.5 0 0 0 0-1zm3 0a.5.5 0 0 0 0 1h1a.5.5 0 0 0 0-1zm3 0a.5.5 0 0 0 0 1h1a.5.5 0 0 0 0-1z"/>
            </svg>
            {{__('admin.Vedi_tutti')}}
        </a> 
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
                        <div class="carousel-item @if ($currentMonth == $m['month'] && $currentYear == $m['year']) active @endif">
                            <h2> {{ \Carbon\Carbon::create()->month($m['month'])->translatedFormat('F') }} - {{$m['year']}} </h2>
                            <div class="top_stat">
                                @if($m['n_res'])
                                    <div class="line">
                                        <h4>{{__('admin.Prenotazioni')}}</h4>
                                        <div class="stat first">
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
                                @endif
                                @if($m['n_order'])
                                    <div class="line">
                                        <h4>{{__('admin.Ordini')}}</h4>
                                        <div class="stat first">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-inboxes" viewBox="0 0 16 16">
                                                <path d="M4.98 1a.5.5 0 0 0-.39.188L1.54 5H6a.5.5 0 0 1 .5.5 1.5 1.5 0 0 0 3 0A.5.5 0 0 1 10 5h4.46l-3.05-3.812A.5.5 0 0 0 11.02 1zm9.954 5H10.45a2.5 2.5 0 0 1-4.9 0H1.066l.32 2.562A.5.5 0 0 0 1.884 9h12.234a.5.5 0 0 0 .496-.438zM3.809.563A1.5 1.5 0 0 1 4.981 0h6.038a1.5 1.5 0 0 1 1.172.563l3.7 4.625a.5.5 0 0 1 .105.374l-.39 3.124A1.5 1.5 0 0 1 14.117 10H1.883A1.5 1.5 0 0 1 .394 8.686l-.39-3.124a.5.5 0 0 1 .106-.374zM.125 11.17A.5.5 0 0 1 .5 11H6a.5.5 0 0 1 .5.5 1.5 1.5 0 0 0 3 0 .5.5 0 0 1 .5-.5h5.5a.5.5 0 0 1 .496.562l-.39 3.124A1.5 1.5 0 0 1 14.117 16H1.883a1.5 1.5 0 0 1-1.489-1.314l-.39-3.124a.5.5 0 0 1 .121-.393zm.941.83.32 2.562a.5.5 0 0 0 .497.438h12.234a.5.5 0 0 0 .496-.438l.32-2.562H10.45a2.5 2.5 0 0 1-4.9 0z"/>
                                            </svg>
                                            <span>{{$m['n_order']}}</span>
                                        </div>
                                        <div class="stat ">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-piggy-bank-fill" viewBox="0 0 16 16">
                                            <path d="M7.964 1.527c-2.977 0-5.571 1.704-6.32 4.125h-.55A1 1 0 0 0 .11 6.824l.254 1.46a1.5 1.5 0 0 0 1.478 1.243h.263c.3.513.688.978 1.145 1.382l-.729 2.477a.5.5 0 0 0 .48.641h2a.5.5 0 0 0 .471-.332l.482-1.351c.635.173 1.31.267 2.011.267.707 0 1.388-.095 2.028-.272l.543 1.372a.5.5 0 0 0 .465.316h2a.5.5 0 0 0 .478-.645l-.761-2.506C13.81 9.895 14.5 8.559 14.5 7.069q0-.218-.02-.431c.261-.11.508-.266.705-.444.315.306.815.306.815-.417 0 .223-.5.223-.461-.026a1 1 0 0 0 .09-.255.7.7 0 0 0-.202-.645.58.58 0 0 0-.707-.098.74.74 0 0 0-.375.562c-.024.243.082.48.32.654a2 2 0 0 1-.259.153c-.534-2.664-3.284-4.595-6.442-4.595m7.173 3.876a.6.6 0 0 1-.098.21l-.044-.025c-.146-.09-.157-.175-.152-.223a.24.24 0 0 1 .117-.173c.049-.027.08-.021.113.012a.2.2 0 0 1 .064.199m-8.999-.65a.5.5 0 1 1-.276-.96A7.6 7.6 0 0 1 7.964 3.5c.763 0 1.497.11 2.18.315a.5.5 0 1 1-.287.958A6.6 6.6 0 0 0 7.964 4.5c-.64 0-1.255.09-1.826.254ZM5 6.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0"/>
                                            </svg>
                                            <span class="cash" ><strong>€</strong>{{$m['cash'] / 100}}</span>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div class="calendar">
                                <div class="c-name">

                                    @for ($i = 1; $i <= 7; $i++)
                                        <h4>{{ Str::substr(\Carbon\Carbon::create()->startOfWeek()->addDays($i-1)->translatedFormat('D'),0,2) }}</h4>
                                    @endfor

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
                <a href="https://future-plus.it/#pacchetti">{{__('admin.up_sell')}}</a>
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
                    <div class="input_c">
                        <label for="delay_or">{{ __('admin.Latenza_ordini') }}</label>
                        <input name="delay_or" id="delay_or" type="time" value="{{$property_adv['delay_or'] ?? ''}}">
                        @error('delay_or') <p class="error">{{ $message }}</p> @enderror
                    </div>
                    <div class="input_c">
                        <label for="delay_res">{{ __('admin.Latenza_prenotazioni') }}</label>
                        <input name="delay_res" id="delay_res" type="time" value="{{$property_adv['delay_res'] ?? ''}}">
                        @error('delay_res') <p class="error">{{ $message }}</p> @enderror
                    </div>
                    <div class="input_c">
                        <label for="max_day_res">{{ __('admin.Latenza_prenotazioni_giorni') }}</label>
                        <input name="max_day_res" id="max_day_res" type="number" min="1" value="{{$property_adv['max_day_res'] ?? ''}}">
                        @error('max_day_res') <p class="error">{{ $message }}</p> @enderror
                    </div>
                    <div class="input_c">
                        <label for="times_interval">{{ __('admin.Intervallo_minuti') }}</label>
                        <input name="times_interval" id="times_interval" type="number" min="1" value="{{$property_adv['times_interval'] ?? ''}}">
                        @error('times_interval') <p class="error">{{ $message }}</p> @enderror
                    </div>
                    <div class="input_c">
                        <label for="times_start">{{ __('admin.Orario_inizio') }}</label>
                        <input name="times_start" id="times_start" type="time" value="{{$property_adv['times_start'] ?? ''}}">
                        @error('times_start') <p class="error">{{ $message }}</p> @enderror
                    </div>
                    <div class="input_c">
                        <label for="times_end">{{ __('admin.Orario_fine') }}</label>
                        <input name="times_end" id="times_end" type="time" value="{{$property_adv['times_end'] ?? ''}}">
                        @error('times_end') <p class="error">{{ $message }}</p> @enderror
                    </div>
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
                        <label for="max_table">{{ __('admin.N_di_posti') }}</label>
                        <input name="max_table" id="max_table" type="number" placeholder="N° di posti per fascia oraria" value="{{$property_adv['max_table'] ?? ''}}"> 
                        @error('max_table') <p class="error">{{ $message }}</p> @enderror
                    </div>
                    
                    <div class="input_c @if(!(in_array($pack, [3,4]))) d-none @endif" >
                        <label for="max_asporto">{{ __('admin.N_di_ordini_dasporto') }}</label>
                        <input name="max_asporto" id="max_asporto" type="number" placeholder="N° di ordini per fascia oraria" value="{{$property_adv['max_asporto'] ?? ''}}"> 
                        @error('max_asporto') <p class="error">{{ $message }}</p> @enderror
                    </div>
                    <div class="input_c @if(!(in_array($pack, [3,4]))) d-none @endif" >
                        <label for="max_domicilio">{{ __('admin.N_di_oridini_a_domicilio') }}</label>
                        <input name="max_domicilio" id="max_domicilio" type="number" placeholder="N° di ordini per fascia oraria" value="{{$property_adv['max_domicilio'] ?? ''}}"> 
                        @error('max_domicilio') <p class="error">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="days" id="availability-days"></div>
                <p id="availability-slots-feedback" class="error m-2 d-none"></p>
                @error('days_on') <p class="error m-2">{{ __('admin.seleziona_Attiva_nei_giorni_i_cui_sei_operativo') }}</p> @enderror
                
                <div class="modal-footer">
                    <button type="button" class="my_btn_1 d " data-bs-dismiss="modal">{{ __('admin.Annulla') }}</button>
                    <button type="submit" class="my_btn_1 add ">{{__('admin.Aggiorna')}}</button>
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
                                                    @if($d['status'] !== 0) <label
                                                        for="{{$d['date']}}"
                                                    @else
                                                        <div
                                                    @endif
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
                                                    @if($d['status'] !== 0)
                                                    </label> @else </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    @php $i ++ @endphp
                                @endforeach
                            </div>
                            
                        </div>
                        <div class="actions w-100">
                            <button class="my_btn_2 btn_delete" type="button" data-bs-dismiss="modal" >{{ __('admin.Annulla') }}</button>
                            <button class="my_btn_3" type="submit">{{ __('admin.Conferma') }}</button>
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
    const blockTimeUrl = "{{ route('admin.dates.blockTime') }}";
    const availabilityDaysContainer = document.getElementById("availability-days");
    const availabilityFeedback = document.getElementById("availability-slots-feedback");
    const availabilityForm = document.querySelector("#staticBackdropav form");
    const availabilitySubmitButton = availabilityForm?.querySelector('button[type="submit"]');
    const startInput = document.getElementById("times_start");
    const endInput = document.getElementById("times_end");
    const intervalInput = document.getElementById("times_interval");
    const initialWeekSet = @json($weekSet);
    const enabledServices = {
        table: @json(in_array($pack, [2, 4])),
        takeAway: @json(in_array($pack, [3, 4])),
        delivery: @json(in_array($pack, [3, 4])),
    };
    const dayNames = {
        1: "{{ \Carbon\Carbon::create()->startOfWeek()->addDays(0)->translatedFormat('l') }}",
        2: "{{ \Carbon\Carbon::create()->startOfWeek()->addDays(1)->translatedFormat('l') }}",
        3: "{{ \Carbon\Carbon::create()->startOfWeek()->addDays(2)->translatedFormat('l') }}",
        4: "{{ \Carbon\Carbon::create()->startOfWeek()->addDays(3)->translatedFormat('l') }}",
        5: "{{ \Carbon\Carbon::create()->startOfWeek()->addDays(4)->translatedFormat('l') }}",
        6: "{{ \Carbon\Carbon::create()->startOfWeek()->addDays(5)->translatedFormat('l') }}",
        7: "{{ \Carbon\Carbon::create()->startOfWeek()->addDays(6)->translatedFormat('l') }}",
    };

    const timeServiceIcons = {
        table: `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-people" viewBox="0 0 16 16"><path d="M15 14s1 0 1-1-1-4-5-4-5 3-5 4 1 1 1 1zm-7.978-1L7 12.996c.001-.264.167-1.03.76-1.72C8.312 10.629 9.282 10 11 10c1.717 0 2.687.63 3.24 1.276.593.69.758 1.457.76 1.72l-.008.002-.014.002zM11 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4m3-2a3 3 0 1 1-6 0 3 3 0 0 1 6 0M6.936 9.28a6 6 0 0 0-1.23-.247A7 7 0 0 0 5 9c-4 0-5 3-5 4q0 1 1 1h4.216A2.24 2.24 0 0 1 5 13c0-1.01.377-2.042 1.09-2.904.243-.294.526-.569.846-.816M4.92 10A5.5 5.5 0 0 0 4 13H1c0-.26.164-1.03.76-1.724.545-.636 1.492-1.256 3.16-1.275ZM1.5 5.5a3 3 0 1 1 6 0 3 3 0 0 1-6 0m3-2a2 2 0 1 0 0 4 2 2 0 0 0 0-4"/></svg>`,
        takeAway: `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-bag" viewBox="0 0 16 16"><path d="M8 1a2.5 2.5 0 0 1 2.5 2.5V4h-5v-.5A2.5 2.5 0 0 1 8 1m3.5 3v-.5a3.5 3.5 0 1 0-7 0V4H1v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V4zM2 5h12v9a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1z"/></svg>`,
        delivery: `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-truck" viewBox="0 0 16 16"><path d="M0 3.5A1.5 1.5 0 0 1 1.5 2h9A1.5 1.5 0 0 1 12 3.5V5h1.02a1.5 1.5 0 0 1 1.17.563l1.481 1.85a1.5 1.5 0 0 1 .329.938V10.5a1.5 1.5 0 0 1-1.5 1.5H14a2 2 0 1 1-4 0H5a2 2 0 1 1-3.998-.085A1.5 1.5 0 0 1 0 10.5zm1.294 7.456A2 2 0 0 1 4.732 11h5.536a2 2 0 0 1 .732-.732V3.5a.5.5 0 0 0-.5-.5h-9a.5.5 0 0 0-.5.5v7a.5.5 0 0 0 .294.456M12 10a2 2 0 0 1 1.732 1h.768a.5.5 0 0 0 .5-.5V8.35a.5.5 0 0 0-.11-.312l-1.48-1.85A.5.5 0 0 0 13.02 6H12zm-9 1a1 1 0 1 0 0 2 1 1 0 0 0 0-2m9 0a1 1 0 1 0 0 2 1 1 0 0 0 0-2"/></svg>`,
    };

    const detailIcons = {
        confirm: `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi okk bi-check-circle" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/><path d="m10.97 4.97-.02.022-3.473 4.425-2.093-2.094a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05"/></svg>`,
        warning: `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi to_see bi-exclamation-circle-fill" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M8 4a.905.905 0 0 0-.9.995l.35 3.507a.552.552 0 0 0 1.1 0l.35-3.507A.905.905 0 0 0 8 4m.002 6a1 1 0 1 0 0 2 1 1 0 0 0 0-2"/></svg>`,
        null: `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi null bi-x-circle" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/><path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708"/></svg>`,
        paid: `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-credit-card-2-back" viewBox="0 0 16 16"><path d="M11 5.5a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1-.5-.5z"/><path d="M2 2a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2zm13 2v5H1V4a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1m-1 9H2a1 1 0 0 1-1-1v-1h14v1a1 1 0 0 1-1 1"/></svg>`,
        adult: `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-standing" viewBox="0 0 16 16"><path d="M8 3a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3M6 6.75v8.5a.75.75 0 0 0 1.5 0V10.5a.5.5 0 0 1 1 0v4.75a.75.75 0 0 0 1.5 0v-8.5a.25.25 0 1 1 .5 0v2.5a.75.75 0 0 0 1.5 0V6.5a3 3 0 0 0-3-3H7a3 3 0 0 0-3 3v2.75a.75.75 0 0 0 1.5 0v-2.5a.25.25 0 0 1 .5 0"/></svg>`,
        child: `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-arms-up" viewBox="0 0 16 16"><path d="M8 3a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3"/><path d="m5.93 6.704-.846 8.451a.768.768 0 0 0 1.523.203l.81-4.865a.59.59 0 0 1 1.165 0l.81 4.865a.768.768 0 0 0 1.523-.203l-.845-8.451A1.5 1.5 0 0 1 10.5 5.5L13 2.284a.796.796 0 0 0-1.239-.998L9.634 3.84a.7.7 0 0 1-.33.235c-.23.074-.665.176-1.304.176-.64 0-1.074-.102-1.305-.176a.7.7 0 0 1-.329-.235L4.239 1.286a.796.796 0 0 0-1.24.998l2.5 3.216c.317.316.475.758.43 1.204Z"/></svg>`,
    };

    function normalizeWeekSet(weekSet) {
        const normalized = {};
        for (let day = 1; day <= 7; day += 1) {
            normalized[day] = weekSet?.[day] ?? weekSet?.[String(day)] ?? {};
        }
        return normalized;
    }

    function collectWeekSelections() {
        const selections = normalizeWeekSet({});
        if (!availabilityDaysContainer) {
            return selections;
        }

        availabilityDaysContainer.querySelectorAll('input[type="checkbox"][name^="times_slot_"]').forEach((input) => {
            const match = input.name.match(/^times_slot_\[(\d+)\]\[([0-9]{2}:[0-9]{2})\]\[\]$/);
            if (!match || !input.checked) {
                return;
            }

            const day = match[1];
            const time = match[2];
            if (!Array.isArray(selections[day][time])) {
                selections[day][time] = [];
            }
            selections[day][time].push(Number(input.value));
        });

        return selections;
    }

    function parseMinutes(value) {
        if (!value || !value.includes(':')) {
            return null;
        }

        const [hours, minutes] = value.split(':').map(Number);
        if (Number.isNaN(hours) || Number.isNaN(minutes)) {
            return null;
        }

        return (hours * 60) + minutes;
    }

    function formatMinutes(totalMinutes) {
        const hours = String(Math.floor(totalMinutes / 60)).padStart(2, '0');
        const minutes = String(totalMinutes % 60).padStart(2, '0');
        return `${hours}:${minutes}`;
    }

    function buildTimeSlots(startValue, endValue, intervalValue) {
        const startMinutes = parseMinutes(startValue);
        const endMinutes = parseMinutes(endValue);
        const interval = Number(intervalValue);

        if (startMinutes === null || endMinutes === null || !Number.isInteger(interval) || interval <= 0 || startMinutes > endMinutes) {
            return [];
        }

        const slots = [];
        for (let minute = startMinutes; minute <= endMinutes; minute += interval) {
            slots.push(formatMinutes(minute));
            if (interval === 0) {
                break;
            }
        }

        return slots;
    }

    function buildServiceToggle(day, time, suffix, value, positionClass, icon, checked) {
        const safeTime = time.replace(':', '-');
        const inputId = `times_${day}_${safeTime}_${suffix}`;

        return `
            <input type="checkbox" ${checked ? 'checked' : ''} class="btn-check" id="${inputId}" name="times_slot_[${day}][${time}][]" value="${value}">
            <label class="btn btn-outline-light shadow-sm ${positionClass}" for="${inputId}">
                ${icon}
            </label>
        `;
    }

    function renderAvailabilityDays() {
        if (!availabilityDaysContainer || !startInput || !endInput || !intervalInput) {
            return;
        }

        const previousSelections = availabilityDaysContainer.children.length
            ? collectWeekSelections()
            : normalizeWeekSet(initialWeekSet);

        const collapseState = {};
        availabilityDaysContainer.querySelectorAll('.multi-collapse').forEach((collapse) => {
            collapseState[collapse.dataset.day] = collapse.classList.contains('show');
        });

        const slots = buildTimeSlots(startInput.value, endInput.value, intervalInput.value);
        const isValid = slots.length > 0;

        availabilityFeedback.classList.toggle('d-none', isValid);
        availabilityFeedback.textContent = isValid
            ? ''
            : "Inserisci un orario di inizio e fine valido con un intervallo maggiore di 0.";

        if (availabilitySubmitButton) {
            availabilitySubmitButton.disabled = !isValid;
        }

        if (!isValid) {
            availabilityDaysContainer.innerHTML = '';
            return;
        }

        let html = '';
        for (let day = 1; day <= 7; day += 1) {
            const daySelections = previousSelections[day] ?? {};
            const hasSelections = Object.keys(daySelections).length > 0;
            const showCollapse = collapseState[day] ?? hasSelections;

            html += `
                <input class="btn-check" style="visibility: hidden; position: absolute;" name="day[]" data-bs-toggle="collapse" data-bs-target="#multiCollapseExample${day}" aria-expanded="${showCollapse}" aria-controls="multiCollapseExample${day}" id="day_${day}" value="${day}">
                <div class="day">
                    <div class="top_day">${dayNames[day]}</div>
                    <label for="day_${day}" class="btn_close">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-bar-down" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M1 3.5a.5.5 0 0 1 .5-.5h13a.5.5 0 0 1 0 1h-13a.5.5 0 0 1-.5-.5M8 6a.5.5 0 0 1 .5.5v5.793l2.146-2.147a.5.5 0 0 1 .708.708l-3 3a.5.5 0 0 1-.708 0l-3-3a.5.5 0 0 1 .708-.708L7.5 12.293V6.5A.5.5 0 0 1 8 6"/></svg>
                    </label>
                </div>
                <div class="collapse multi-collapse ${showCollapse ? 'show' : ''}" id="multiCollapseExample${day}" data-day="${day}">
                    <div class="modal_body">
                        <div class="scroller">
            `;

            slots.forEach((time) => {
                const selectedServices = Array.isArray(daySelections[time]) ? daySelections[time].map(Number) : [];
                html += `<div class="time"><h5 onclick="event.preventDefault()">${time}</h5>`;

                if (enabledServices.table) {
                    html += buildServiceToggle(day, time, 't', 1, 'left', timeServiceIcons.table, selectedServices.includes(1));
                }
                if (enabledServices.takeAway) {
                    html += buildServiceToggle(day, time, 'a', 2, 'center', timeServiceIcons.takeAway, selectedServices.includes(2));
                }
                if (enabledServices.delivery) {
                    html += buildServiceToggle(day, time, 'd', 3, 'right', timeServiceIcons.delivery, selectedServices.includes(3));
                }

                html += `</div>`;
            });

            html += `
                        </div>
                    </div>
                </div>
            `;
        }

        availabilityDaysContainer.innerHTML = html;
    }

    function getStatusClass(statusValue) {
        const parsedStatus = Number(statusValue);
        const statusMap = {
            0: 'null',
            1: 'okk',
            2: 'to_see',
            3: 'to_see',
            4: 'okk',
            5: 'okk',
            6: 'null',
        };

        return statusMap[parsedStatus] ?? 'to_see';
    }

    function renderDayDetails(button) {
        document.querySelectorAll(".day.day-active").forEach((dayButton) => dayButton.classList.remove("day-active"));
        button.classList.add("day-active");

        const dayData = JSON.parse(button.dataset.day);
        const { date, times, status } = dayData;
        let html = `<div class="day-info"><div class="time-list ${status == 3 || status == 0 ? 'op' : ''}">`;

        for (const [time, data] of Object.entries(times)) {
            const res = data.res;
            const or = data.or;
            const properties = (data.property ?? []).map(Number);
            const isBlocked = data.blocked === true;
            const selectedDate = new Date(`${date}T00:00:00`);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            const canBlock = !isBlocked && selectedDate >= today;

            html += `<div class="time-item ${isBlocked ? 'blocked' : ''}"><div class="time-header"><strong>${time}</strong><div class="line ${isBlocked ? 'blocked-line' : ''}"></div><p class="prop">`;
            if (properties.includes(1)) html += timeServiceIcons.table;
            if (properties.includes(2)) html += timeServiceIcons.takeAway;
            if (properties.includes(3)) html += timeServiceIcons.delivery;
            html += `</p>
                ${isBlocked ? `<button type="button" class="unblock-time-btn" data-date="${date}" data-time="${time}"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-toggle-off" viewBox="0 0 16 16"><path d="M11 4a4 4 0 0 1 0 8H8a5 5 0 0 0 2-4 5 5 0 0 0-2-4zm-6 8a4 4 0 1 1 0-8 4 4 0 0 1 0 8M0 8a5 5 0 0 0 5 5h6a5 5 0 0 0 0-10H5a5 5 0 0 0-5 5"/></svg></button>` : (canBlock ? `<button type="button" class="block-time-btn" data-date="${date}" data-time="${time}"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-toggle-on" viewBox="0 0 16 16"><path d="M5 3a5 5 0 0 0 0 10h6a5 5 0 0 0 0-10zm6 9a4 4 0 1 1 0-8 4 4 0 0 1 0 8"/></svg></button>` : '')}
            </div><div class="time-content">`;

            if (res.length > 0) {
                res.forEach((reservation) => {
                    const reservationStatus = Number(reservation.status);
                    const people = JSON.parse(reservation.n_person);
                    const paidLabel = reservationStatus === 6 ? `{{ __('admin.Rimborsato') }}` : `{{ __('admin.Pagato') }}`;
                    const reservationLink = "{{config('configurazione.APP_URL')}}" + '/admin/reservations/' + reservation.id;

                    html += `<a href="${reservationLink}" class="res-item ${getStatusClass(reservationStatus)}">
                        <div class="top">
                            <div class="id">R${reservation.id ?? ''}</div>
                            ${[0, 6].includes(reservationStatus) ? detailIcons.null : ''}
                            ${[2, 3].includes(reservationStatus) ? detailIcons.warning : ''}
                            ${[1, 4, 5].includes(reservationStatus) ? detailIcons.confirm : ''}
                            <div class="name">${reservation.name + ' ' + reservation.surname}</div>
                            ${[3, 5, 6].includes(reservationStatus) ? `<div class="${reservationStatus === 6 ? 'refound' : 'paid'} status">${detailIcons.paid} ${paidLabel}</div>` : ''}
                            <div class="guest">
                                ${people.adult > 0 ? people.adult + detailIcons.adult : ''}
                                ${people.child > 0 ? people.child + detailIcons.child : ''}
                            </div>
                        </div>
                    </a>`;
                });
            }

            if (or.length > 0) {
                or.forEach((order) => {
                    const orderStatus = Number(order.status);
                    const paidLabel = orderStatus === 6 ? `{{ __('admin.Rimborsato') }}` : `{{ __('admin.Pagato') }}`;
                    const orderLink = "{{config('configurazione.APP_URL')}}" + '/admin/orders/' + order.id;

                    html += `<a href="${orderLink}" class="order-item ${getStatusClass(orderStatus)}">
                        <div class="top">
                            <div class="id">O${order.id ?? ''}</div>
                            ${[0, 6].includes(orderStatus) ? detailIcons.null : ''}
                            ${[2, 3].includes(orderStatus) ? detailIcons.warning : ''}
                            ${[1, 4, 5].includes(orderStatus) ? detailIcons.confirm : ''}
                            <div class="name">${order.name + ' ' + order.surname}</div>
                            ${[3, 5, 6].includes(orderStatus) ? `<div class="${orderStatus === 6 ? 'refound' : 'paid'} status">${detailIcons.paid} ${paidLabel}</div>` : ''}
                            <div class="price">€${order.tot_price / 100}</div>
                        </div>
                        <div class="cart">`;

                    order.products.forEach((product) => {
                        html += `<div class="item_cart"><div class="name">${product.pivot?.quantity ?? 1}* ${product.name}</div><div class="price">€${product.price / 100}</div></div>`;
                    });
                    order.menus.forEach((menu) => {
                        html += `<div class="item_cart"><div class="name">${menu.pivot?.quantity ?? 1}* ${menu.name}</div><div class="price">€${menu.price / 100}</div></div>`;
                    });

                    html += `</div></a>`;
                });
            }

            html += `</div></div>`;
        }

        html += `</div></div>`;
        detailsContainer.innerHTML = html;
        attachBlockButtons();
    }

    function attachBlockButtons() {
        document.querySelectorAll('.block-time-btn, .unblock-time-btn').forEach((button) => {
            button.addEventListener('click', async () => {
                if (button.disabled) return;
                button.disabled = true;

                const date = button.dataset.date;
                const time = button.dataset.time;
                const action = button.classList.contains('block-time-btn') ? 'block' : 'unblock';

                try {
                    const response = await fetch(blockTimeUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ date, time, action }),
                    });

                    const result = await response.json();
                    if (!result.success) {
                        console.error(`Errore nell'${action === 'block' ? 'blocco' : 'sblocco'} orario:`, result.message);
                        button.disabled = false;
                        return;
                    }

                    const timeItem = button.closest('.time-item');
                    const timeHeader = timeItem?.querySelector('.time-header');
                    const line = timeItem?.querySelector('.line');

                    if (!timeItem || !timeHeader) {
                        return;
                    }

                    if (action === 'block') {
                        timeItem.classList.add('blocked');
                        if (line) line.classList.add('blocked-line');
                        button.remove();

                        const unblockButton = document.createElement('button');
                        unblockButton.type = 'button';
                        unblockButton.className = 'unblock-time-btn';
                        unblockButton.dataset.date = date;
                        unblockButton.dataset.time = time;
                        unblockButton.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-toggle-off" viewBox="0 0 16 16"><path d="M11 4a4 4 0 0 1 0 8H8a5 5 0 0 0 2-4 5 5 0 0 0-2-4zm-6 8a4 4 0 1 1 0-8 4 4 0 0 1 0 8M0 8a5 5 0 0 0 5 5h6a5 5 0 0 0 0-10H5a5 5 0 0 0-5 5"/></svg>`;
                        timeHeader.appendChild(unblockButton);
                    } else {
                        timeItem.classList.remove('blocked');
                        if (line) line.classList.remove('blocked-line');
                        button.remove();

                        const blockButton = document.createElement('button');
                        blockButton.type = 'button';
                        blockButton.className = 'block-time-btn';
                        blockButton.dataset.date = date;
                        blockButton.dataset.time = time;
                        blockButton.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-toggle-on" viewBox="0 0 16 16"><path d="M5 3a5 5 0 0 0 0 10h6a5 5 0 0 0 0-10zm6 9a4 4 0 1 1 0-8 4 4 0 0 1 0 8"/></svg>`;
                        timeHeader.appendChild(blockButton);
                    }

                    attachBlockButtons();

                    dayButtons.forEach((dayButton) => {
                        const dayData = JSON.parse(dayButton.dataset.day);
                        if (dayData.date === date && dayData.times[time]) {
                            dayData.times[time].blocked = (action === 'block');
                            dayButton.dataset.day = JSON.stringify(dayData);
                        }
                    });
                } catch (error) {
                    console.error(`Error ${action}ing time:`, error);
                    button.disabled = false;
                }
            });
        });
    }

    [startInput, endInput, intervalInput].forEach((input) => {
        input?.addEventListener('input', renderAvailabilityDays);
        input?.addEventListener('change', renderAvailabilityDays);
    });

    renderAvailabilityDays();

    dayButtons.forEach((button) => {
        button.addEventListener("click", () => renderDayDetails(button));
    });
});
</script>


@endsection
