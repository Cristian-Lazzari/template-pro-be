@extends('layouts.base')



@section('contents')


@if (session('not_found'))
    @php
        $data = session('not_found')
    @endphp
    <div class="alert alert-danger">
        La data non ha orari a cui è possibile ordinare o prenotare
    </div>
@endif
@if (session('success'))
    @php
        $data = session('success')
    @endphp
    <div class="alert alert-success">
        {{ $data }}
    </div>
@endif
@php
    $day_time = [];
    $start = new DateTime($times_start);
    $end = new DateTime($times_end);
    $index = 1;
    $interval = $times_interval;

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
@endphp

@if (isset($year))
    <div class="date_index">
        <div id="carouselExampleIndicators" class="carousel slide my_carousel">
            <div class="carousel-indicators">
                @php 
                    $i = 0; 
                    $currentDay = date("d");
                    $currentMonth = date("m");
                    $currentYear = date("Y");
                @endphp
                @foreach ($year as $m)
                    <button  type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="{{$i}}"
                    @if ($currentMonth == $m['month'] && $currentYear == $m['year'])
                        class="active" aria-current="true" 
                    @endif
                    aria-label="{{ 'Slide ' . $i }}"></button>
                    @php $i ++ @endphp
                @endforeach
            </div>
            
            <div class="carousel-inner">
            @php $i = 0; @endphp
            @foreach ($year as $m)
                <div class="carousel-item
                @if ($currentMonth == $m['month'] && $currentYear == $m['year'])
                    active 
                @endif
                ">   
                    <h2 class="my">{{['', 'gennaio', 'febbraio', 'marzo', 'aprile', 'maggio', 'giugno', 'luglio', 'agosto', 'settembre', 'ottobre', 'novembre', 'dicembre'][$m['month']]}} - {{$m['year']}}</h2>
                    <div class="calendar-c">
                        
                        <div class="c-name">
                            @php
                            $day_name = ['lunedì', 'martedì', 'mercoledì', 'giovedì', 'venerdì', 'sabato', 'domenica'];
                            @endphp
                            @foreach ($day_name as $item)
                                <h4>{{$item}}</h4>
                            @endforeach
                        </div>
                        <div class="calendar">

                            @foreach ($m['days'] as $d)
                            
                                <form action="{{ route('admin.dates.showDay') }}"  style="grid-column-start:{{$d['day_w'] }}" method="get">
                                    @csrf
                                    @if(!isset($d['time']))
                                        <input 
                                            type="checkbox" 
                                            class="btn-check d-none day-checkbox" 
                                            value="{{$d['id']}}"
                                            data-day="{{ $d['date']}}" 
                                            id="{{$d['id']}}"
                                            {{ isset($d['time']) ? 'disabled' : '' }}
                                        >
                                        <label class="labels" for="{{$d['id']}}"></label>
                                    @endif
                                    <div class="day  @if(!isset($d['time'])) day-off @endif @if($currentMonth == $m['month'] && $currentYear == $m['year'] && $currentDay == $d['day']) day-active @endif ">
                                        <input type="hidden" name="date" value="{{$d['date']}}">
                                        @if(isset($d['asporto']) && $d['asporto'] !== 0)
                                            <p class="pop1">
                                                <span>{{$d['asporto']}}</span>
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-inboxes" viewBox="0 0 16 16">
                                                    <path d="M4.98 1a.5.5 0 0 0-.39.188L1.54 5H6a.5.5 0 0 1 .5.5 1.5 1.5 0 0 0 3 0A.5.5 0 0 1 10 5h4.46l-3.05-3.812A.5.5 0 0 0 11.02 1zm9.954 5H10.45a2.5 2.5 0 0 1-4.9 0H1.066l.32 2.562A.5.5 0 0 0 1.884 9h12.234a.5.5 0 0 0 .496-.438zM3.809.563A1.5 1.5 0 0 1 4.981 0h6.038a1.5 1.5 0 0 1 1.172.563l3.7 4.625a.5.5 0 0 1 .105.374l-.39 3.124A1.5 1.5 0 0 1 14.117 10H1.883A1.5 1.5 0 0 1 .394 8.686l-.39-3.124a.5.5 0 0 1 .106-.374zM.125 11.17A.5.5 0 0 1 .5 11H6a.5.5 0 0 1 .5.5 1.5 1.5 0 0 0 3 0 .5.5 0 0 1 .5-.5h5.5a.5.5 0 0 1 .496.562l-.39 3.124A1.5 1.5 0 0 1 14.117 16H1.883a1.5 1.5 0 0 1-1.489-1.314l-.39-3.124a.5.5 0 0 1 .121-.393zm.941.83.32 2.562a.5.5 0 0 0 .497.438h12.234a.5.5 0 0 0 .496-.438l.32-2.562H10.45a2.5 2.5 0 0 1-4.9 0z"/>
                                                </svg> 
                                            </p>
                                        @endif
                                        @if(isset($d['domicilio'])  && $d['domicilio'] !== 0)
                                        <p class="pop2">
                                            <span>{{$d['domicilio']}}</span>
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-mailbox-flag" viewBox="0 0 16 16">
                                                    <path d="M10.5 8.5V3.707l.854-.853A.5.5 0 0 0 11.5 2.5v-2A.5.5 0 0 0 11 0H9.5a.5.5 0 0 0-.5.5v8zM5 7c0 .334-.164.264-.415.157C4.42 7.087 4.218 7 4 7s-.42.086-.585.157C3.164 7.264 3 7.334 3 7a1 1 0 0 1 2 0"/>
                                                    <path d="M4 3h4v1H6.646A4 4 0 0 1 8 7v6h7V7a3 3 0 0 0-3-3V3a4 4 0 0 1 4 4v6a1 1 0 0 1-1 1H1a1 1 0 0 1-1-1V7a4 4 0 0 1 4-4m0 1a3 3 0 0 0-3 3v6h6V7a3 3 0 0 0-3-3"/>
                                                </svg> 
                                            </p>
                                        @endif
                                        @if(isset($d['table']) && $d['table'] !== 0)
                                            <p class="pop3">
                                                <span>{{$d['table']}}</span>
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-lines-fill" viewBox="0 0 16 16">
                                                    <path d="M6 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6m-5 6s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zM11 3.5a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 0 1h-4a.5.5 0 0 1-.5-.5m.5 2.5a.5.5 0 0 0 0 1h4a.5.5 0 0 0 0-1zm2 3a.5.5 0 0 0 0 1h2a.5.5 0 0 0 0-1zm0 3a.5.5 0 0 0 0 1h2a.5.5 0 0 0 0-1z"/>
                                                </svg>
                                            </p>
                                        @endif
                                        <button class="b">{{$d['day']}}</button>
                                    </div>
                                    


                                </form>
                            @endforeach
                        </div>
                    </div>
                </div>
                @php $i ++ @endphp
            @endforeach

            </div>
            <button class="carousel-control-prev" style="width: 7% !important;" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="prev">
                <div class="lez-c prev">
                    <div class="line"></div>
                    <div class="line l2"></div>
                </div>
            </button>
            <button class="carousel-control-next" style="width: 7% !important;" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="next">
                <div class="lez-c ">
                    <div class="line"></div>
                    <div class="line l2"></div>
                </div>
            </button>
        </div>
        
        <form id="edit_modal" class="edit_modal d-none" action="{{ route('admin.dates.editDays') }}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="top">
                <h4 class="modal-title">Giorni selezionati:</h4>
                <div id="dayModalLabel">
                </div>
                <div id="selectedDayIds"></div>
            </div>
            <div class="inputs">
                <div class="input_c @if(!(in_array($pack, [2,4]) && $double)) d-none @endif" >
                    <label for="max_table_1">N° di posti per {{$property_adv['sala_1']}}</label>
                    <input name="max_table_1" id="max_table_1" type="number" placeholder="N° di posti in {{$property_adv['sala_1']}} per fascia oraria" value="0"> 
                    @error('max_table_1') <p class="error">{{ $message }}</p> @enderror
                </div>
                <div class="input_c @if(!(in_array($pack, [2,4]) && $double)) d-none @endif" >
                    <label for="max_table_2">N° di posti per {{$property_adv['sala_2']}}</label>
                    <input name="max_table_2" id="max_table_2" type="number" placeholder="N° di posti in {{$property_adv['sala_2']}} per fascia oraria" value="0"> 
                    @error('max_table_2') <p class="error">{{ $message }}</p> @enderror
                </div>
                <div class="input_c @if(!(in_array($pack, [2,4]) && !$double)) d-none @endif" >
                    <label for="max_table">N° di posti</label>
                    <input name="max_table" id="max_table" type="number" placeholder="N° di posti per fascia oraria" value="0"> 
                    @error('max_table_2') <p class="error">{{ $message }}</p> @enderror
                </div>
                
                <div class="input_c @if(!(in_array($pack, [3,4]) && !$type)) d-none @endif" >
                    <label for="max_asporto">N° di ordini d'asporto</label>
                    <input name="max_asporto" id="max_asporto" type="number" placeholder="N° di ordini per fascia oraria" value="0"> 
                    @error('max_asporto') <p class="error">{{ $message }}</p> @enderror
                </div>
                <div class="input_c @if(!(in_array($pack, [3,4]) && $type)) d-none @endif" >
                    <label for="max_cucina_1">N° porzioni di {{$property_adv['too_1']}}</label>
                    <input name="max_cucina_1" id="max_cucina_1" type="number" placeholder="N° di{{$property_adv['too_1']}} per fascia oraria" value="0"> 
                    @error('max_cucina_1') <p class="error">{{ $message }}</p> @enderror
                </div>
                <div class="input_c @if(!(in_array($pack, [3,4]) && $type)) d-none @endif" >
                    <label for="max_cucina_2">N° porzioni di {{$property_adv['too_2']}}</label>
                    <input name="max_cucina_2" id="max_cucina_2" type="number" placeholder="N° di{{$property_adv['too_2']}} per fascia oraria" value="0"> 
                    @error('max_cucina_2') <p class="error">{{ $message }}</p> @enderror
                </div>
                <div class="input_c @if(!(in_array($pack, [3,4]))) d-none @endif" >
                    <label for="max_domicilio">N° di oridini a domicilio</label>
                    <input name="max_domicilio" id="max_domicilio" type="number" placeholder="N° di ordini per fascia oraria" value="0"> 
                    @error('max_domicilio') <p class="error">{{ $message }}</p> @enderror
                </div>
                
            </div>

            <div class="modal_body">
                <h4>Seleziona le fasce orarie disponibili</h4>
                <div class="scroller">
                    @foreach ($day_time as $time) @php $time = $time['time']; @endphp
                        <div class="time">
                            <h5>{{$time}}</h5>
                            <input type="checkbox" class="btn-check" id="times[{{$time}}]t" name="times[{{$time}}][]" value="2">
                            <label class="btn btn-outline-light shadow-sm left" for="times[{{$time}}]t">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-people" viewBox="0 0 16 16">
                                    <path d="M15 14s1 0 1-1-1-4-5-4-5 3-5 4 1 1 1 1zm-7.978-1L7 12.996c.001-.264.167-1.03.76-1.72C8.312 10.629 9.282 10 11 10c1.717 0 2.687.63 3.24 1.276.593.69.758 1.457.76 1.72l-.008.002-.014.002zM11 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4m3-2a3 3 0 1 1-6 0 3 3 0 0 1 6 0M6.936 9.28a6 6 0 0 0-1.23-.247A7 7 0 0 0 5 9c-4 0-5 3-5 4q0 1 1 1h4.216A2.24 2.24 0 0 1 5 13c0-1.01.377-2.042 1.09-2.904.243-.294.526-.569.846-.816M4.92 10A5.5 5.5 0 0 0 4 13H1c0-.26.164-1.03.76-1.724.545-.636 1.492-1.256 3.16-1.275ZM1.5 5.5a3 3 0 1 1 6 0 3 3 0 0 1-6 0m3-2a2 2 0 1 0 0 4 2 2 0 0 0 0-4"/>
                                </svg>
                            </label>
                            <input type="checkbox" class="btn-check" id="times[{{$time}}]a" name="times[{{$time}}][]" value="1">
                            <label class="btn btn-outline-light shadow-sm center" for="times[{{$time}}]a">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-bag" viewBox="0 0 16 16">
                                    <path d="M8 1a2.5 2.5 0 0 1 2.5 2.5V4h-5v-.5A2.5 2.5 0 0 1 8 1m3.5 3v-.5a3.5 3.5 0 1 0-7 0V4H1v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V4zM2 5h12v9a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1z"/>
                                </svg>
                            </label>
                            <input type="checkbox" class="btn-check" id="times[{{$time}}]d" name="times[{{$time}}][]" value="3">
                            <label class="btn btn-outline-light shadow-sm right" for="times[{{$time}}]d">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-truck" viewBox="0 0 16 16">
                                    <path d="M0 3.5A1.5 1.5 0 0 1 1.5 2h9A1.5 1.5 0 0 1 12 3.5V5h1.02a1.5 1.5 0 0 1 1.17.563l1.481 1.85a1.5 1.5 0 0 1 .329.938V10.5a1.5 1.5 0 0 1-1.5 1.5H14a2 2 0 1 1-4 0H5a2 2 0 1 1-3.998-.085A1.5 1.5 0 0 1 0 10.5zm1.294 7.456A2 2 0 0 1 4.732 11h5.536a2 2 0 0 1 .732-.732V3.5a.5.5 0 0 0-.5-.5h-9a.5.5 0 0 0-.5.5v7a.5.5 0 0 0 .294.456M12 10a2 2 0 0 1 1.732 1h.768a.5.5 0 0 0 .5-.5V8.35a.5.5 0 0 0-.11-.312l-1.48-1.85A.5.5 0 0 0 13.02 6H12zm-9 1a1 1 0 1 0 0 2 1 1 0 0 0 0-2m9 0a1 1 0 1 0 0 2 1 1 0 0 0 0-2"/>
                                </svg>
                            </label>
                        </div>
                    @endforeach             
                </div>
            </div>
            <button type="submit" class="my_btn_3">Modifica</button>
        </form>
    </div>
@endif

<button id="editToggle" class="my_btn_3 w-auto mt-4 w_200p" >Aggiungi Giorni</button>
<button id="editToggle" class="my_btn_2 w-auto my-2 w_200p" data-bs-toggle="modal" data-bs-target="#staticBackdropav" >Reset disponibilità</button>


<div class="modal fade " id="staticBackdropav" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropavLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable large_m">
        <form class="modal-content s_advanced edit_modal" action="{{ route('admin.dates.generate') }}" method="post">
            @csrf
            <h2>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-sliders" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M11.5 2a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3M9.05 3a2.5 2.5 0 0 1 4.9 0H16v1h-2.05a2.5 2.5 0 0 1-4.9 0H0V3zM4.5 7a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3M2.05 8a2.5 2.5 0 0 1 4.9 0H16v1H6.95a2.5 2.5 0 0 1-4.9 0H0V8zm9.45 4a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3m-2.45 1a2.5 2.5 0 0 1 4.9 0H16v1h-2.05a2.5 2.5 0 0 1-4.9 0H0v-1z"/>
                </svg> Genera nuove disponibilità
            </h2>

            <div class="inputs iv2">
                <div class="input_c @if(!(in_array($pack, [2,4]) && $double)) d-none @endif" >
                    <label for="max_table_1">N° di posti per {{$property_adv['sala_1']}}</label>
                    <input name="max_table_1" id="max_table_1" type="number" placeholder="N° di posti in {{$property_adv['sala_1']}} per fascia oraria" value="0"> 
                    @error('max_table_1') <p class="error">{{ $message }}</p> @enderror
                </div>
                <div class="input_c @if(!(in_array($pack, [2,4]) && $double)) d-none @endif" >
                    <label for="max_table_2">N° di posti per {{$property_adv['sala_2']}}</label>
                    <input name="max_table_2" id="max_table_2" type="number" placeholder="N° di posti in {{$property_adv['sala_2']}} per fascia oraria" value="0"> 
                    @error('max_table_2') <p class="error">{{ $message }}</p> @enderror
                </div>
                <div class="input_c @if(!(in_array($pack, [2,4]) && !$double)) d-none @endif" >
                    <label for="max_table">N° di posti</label>
                    <input name="max_table" id="max_table" type="number" placeholder="N° di posti per fascia oraria" value="0"> 
                    @error('max_table_2') <p class="error">{{ $message }}</p> @enderror
                </div>
                
                <div class="input_c @if(!(in_array($pack, [3,4]) && !$type)) d-none @endif" >
                    <label for="max_asporto">N° di ordini d'asporto</label>
                    <input name="max_asporto" id="max_asporto" type="number" placeholder="N° di ordini per fascia oraria" value="0"> 
                    @error('max_asporto') <p class="error">{{ $message }}</p> @enderror
                </div>
                <div class="input_c @if(!(in_array($pack, [3,4]) && $type)) d-none @endif" >
                    <label for="max_cucina_1">N° porzioni di {{$property_adv['too_1']}}</label>
                    <input name="max_cucina_1" id="max_cucina_1" type="number" placeholder="N° di{{$property_adv['too_1']}} per fascia oraria" value="0"> 
                    @error('max_cucina_1') <p class="error">{{ $message }}</p> @enderror
                </div>
                <div class="input_c @if(!(in_array($pack, [3,4]) && $type)) d-none @endif" >
                    <label for="max_cucina_2">N° porzioni di {{$property_adv['too_2']}}</label>
                    <input name="max_cucina_2" id="max_cucina_2" type="number" placeholder="N° di{{$property_adv['too_2']}} per fascia oraria" value="0"> 
                    @error('max_cucina_2') <p class="error">{{ $message }}</p> @enderror
                </div>
                <div class="input_c @if(!(in_array($pack, [3,4]))) d-none @endif" >
                    <label for="max_domicilio">N° di oridini a domicilio</label>
                    <input name="max_domicilio" id="max_domicilio" type="number" placeholder="N° di ordini per fascia oraria" value="0"> 
                    @error('max_domicilio') <p class="error">{{ $message }}</p> @enderror
                </div>
            </div>

            <h4>Seleziona i giorni in cui sei attivo</h4>
            <div class="days">
                @foreach ([1, 2, 3, 4, 5, 6, 7] as $day)
                <input class="btn-check"  name="day[]" data-bs-toggle="collapse" data-bs-target="#multiCollapseExample{{$day}}" aria-expanded="false" aria-controls="multiCollapseExample{{$day}}" id="day_{{ $day }}" value="{{ $day }}">
                <label class="day" for="day_{{ $day }}">
                    <div class="split">
                        <h4>{{ [' ','lunedì', 'martedì', 'mercoledì', 'giovedì', 'venerdì', 'sabato', 'domenica'][$day] }}
                        </h4>
                        <label class="theme-switch" for="days_on_{{ $day }}" id="themeswitch">
                            <input type="checkbox" id="days_on_{{ $day }}" name="days_on[]" value="{{ $day }}">
                            <div class="slider round"></div>
                            <span class="name"></span>
                            <div class="back"></div>
                        </label>
                    </div>
                    <div class="collapse multi-collapse" id="multiCollapseExample{{$day}}">
                        <div class="modal_body">
                            <h5>Seleziona le fasce orarie disponibili</h5>
                            <div class="scroller">
                                @foreach ($day_time as $time) @php $time = $time['time']; @endphp
                                    <div class="time">
                                        <h5>{{$time}}</h5>
                                        <input type="checkbox" class="btn-check" id="times_{{$day}}_{{$time}}t" name="times_slot_{{$day}}[{{$time}}][]" value="2">
                                        <label class="btn btn-outline-light shadow-sm left" for="times_{{$day}}_{{$time}}t">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-people" viewBox="0 0 16 16">
                                                <path d="M15 14s1 0 1-1-1-4-5-4-5 3-5 4 1 1 1 1zm-7.978-1L7 12.996c.001-.264.167-1.03.76-1.72C8.312 10.629 9.282 10 11 10c1.717 0 2.687.63 3.24 1.276.593.69.758 1.457.76 1.72l-.008.002-.014.002zM11 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4m3-2a3 3 0 1 1-6 0 3 3 0 0 1 6 0M6.936 9.28a6 6 0 0 0-1.23-.247A7 7 0 0 0 5 9c-4 0-5 3-5 4q0 1 1 1h4.216A2.24 2.24 0 0 1 5 13c0-1.01.377-2.042 1.09-2.904.243-.294.526-.569.846-.816M4.92 10A5.5 5.5 0 0 0 4 13H1c0-.26.164-1.03.76-1.724.545-.636 1.492-1.256 3.16-1.275ZM1.5 5.5a3 3 0 1 1 6 0 3 3 0 0 1-6 0m3-2a2 2 0 1 0 0 4 2 2 0 0 0 0-4"/>
                                            </svg>
                                        </label>
                                        <input type="checkbox" class="btn-check" id="times_{{$day}}_{{$time}}a" name="times_slot_{{$day}}[{{$time}}][]" value="1">
                                        <label class="btn btn-outline-light shadow-sm center" for="times_{{$day}}_{{$time}}a">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-bag" viewBox="0 0 16 16">
                                                <path d="M8 1a2.5 2.5 0 0 1 2.5 2.5V4h-5v-.5A2.5 2.5 0 0 1 8 1m3.5 3v-.5a3.5 3.5 0 1 0-7 0V4H1v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V4zM2 5h12v9a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1z"/>
                                            </svg>
                                        </label>
                                        <input type="checkbox" class="btn-check" id="times_{{$day}}_{{$time}}d" name="times_slot_{{$day}}[{{$time}}][]" value="3">
                                        <label class="btn btn-outline-light shadow-sm right" for="times_{{$day}}_{{$time}}d">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-truck" viewBox="0 0 16 16">
                                                <path d="M0 3.5A1.5 1.5 0 0 1 1.5 2h9A1.5 1.5 0 0 1 12 3.5V5h1.02a1.5 1.5 0 0 1 1.17.563l1.481 1.85a1.5 1.5 0 0 1 .329.938V10.5a1.5 1.5 0 0 1-1.5 1.5H14a2 2 0 1 1-4 0H5a2 2 0 1 1-3.998-.085A1.5 1.5 0 0 1 0 10.5zm1.294 7.456A2 2 0 0 1 4.732 11h5.536a2 2 0 0 1 .732-.732V3.5a.5.5 0 0 0-.5-.5h-9a.5.5 0 0 0-.5.5v7a.5.5 0 0 0 .294.456M12 10a2 2 0 0 1 1.732 1h.768a.5.5 0 0 0 .5-.5V8.35a.5.5 0 0 0-.11-.312l-1.48-1.85A.5.5 0 0 0 13.02 6H12zm-9 1a1 1 0 1 0 0 2 1 1 0 0 0 0-2m9 0a1 1 0 1 0 0 2 1 1 0 0 0 0-2"/>
                                            </svg>
                                        </label>
                                    </div>
                                @endforeach             
                            </div>
                        </div>        
                    </div>
                </label>
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


<script>
    document.addEventListener('DOMContentLoaded', function () {
        const editButton = document.getElementById('editToggle');
        const checkboxes = document.querySelectorAll('.day-checkbox');
        const labels = document.querySelectorAll('.labels');
        const modal = document.getElementById('edit_modal');
        const hiddenInputsContainer = document.getElementById('selectedDayIds');
        const labelDays = document.getElementById('dayModalLabel');
        let editing = false;

        editButton.addEventListener('click', function () {
            editing = !editing;
            !editing ? modal.classList.add('d-none') : modal.classList.remove('d-none')
            !editing ? editButton.innerHTML = 'Aggiungi giorni' : editButton.innerHTML = 'Annulla'

            labels.forEach(l => {
                l.style.display = !editing ? 'none' : 'block';
            });
            checkboxes.forEach(cb => {
                cb.checked = false
            });
        });

        checkboxes.forEach(cb => {
                cb.addEventListener('change', () => {
                    hiddenInputsContainer.innerHTML = '';
                    labelDays.innerHTML = '';
                    checkboxes.forEach(cb => {
                        if(cb.checked){
                            const hidden = document.createElement('input');
                            hidden.type = 'hidden';
                            hidden.name = 'dates[]';
                            hidden.value = cb.value;
                            hiddenInputsContainer.appendChild(hidden);
    
                            const label = document.createElement('div');
                            labelDays.appendChild(label)
                            label.textContent = cb.dataset.day
                        }
                    })
                });

        });
    });


</script>
@endsection