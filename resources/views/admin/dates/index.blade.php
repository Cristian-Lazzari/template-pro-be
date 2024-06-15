@extends('layouts.base')



@section('contents')


@if (session('not_found'))
    @php
        $data = session('not_found')
    @endphp
    <div class="alert alert-danger">
        La data non ha orari a cui è possibile ordinare
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
<a class="btn btn-outline-light mb-5" href="{{ route('admin.dashboard') }}">Indietro</a>


@if (isset($year))
    

<div class="date_index">
    <div id="carouselExampleIndicators" class="carousel slide">
        <div class="carousel-indicators">

            @php $i = 0; @endphp
            @foreach ($year as $m)
                <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="{{$i}}"
                @if ($i == 0)
                    class="active" aria-current="true" 
                @endif
                aria-label="{{ 'Slide ' . $i }}"></button>
                @php $i ++ @endphp
            @endforeach
        </div>
        <div class="carousel-inner">
        @php $i = 0; @endphp
        @foreach ($year as $m)
            <div class="carousel-item @if ($i == 0) active @endif">
                <h2 class="my">{{config('configurazione.mesi')[$m['month']]}} - {{$m['year']}}</h2>
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
                            <form action="{{ route('admin.dates.showDay') }}" class="day {{ 'd' . $d['day_w']}} @if(!isset($d['time'])) day-off @endif " style="grid-column-start:{{$d['day_w'] }}" method="get">
                                @csrf
                                <input type="hidden" name="date" value="{{$d['date']}}">
                                @if(isset($d['asporto']))<p class="pop1"> <span>{{$d['asporto']}}</span> <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-cloud-fog-fill" viewBox="0 0 16 16">
                                    <path d="M3 13.5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m10.405-9.473a5.001 5.001 0 0 0-9.499-1.004A3.5 3.5 0 1 0 3.5 12H13a3 3 0 0 0 .405-5.973"/>
                                  </svg> </p>@endif
                                @if(isset($d['domicilio']))<p class="pop2"><span>{{$d['domicilio']}}</span> <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-mailbox-flag" viewBox="0 0 16 16">
                                    <path d="M10.5 8.5V3.707l.854-.853A.5.5 0 0 0 11.5 2.5v-2A.5.5 0 0 0 11 0H9.5a.5.5 0 0 0-.5.5v8zM5 7c0 .334-.164.264-.415.157C4.42 7.087 4.218 7 4 7s-.42.086-.585.157C3.164 7.264 3 7.334 3 7a1 1 0 0 1 2 0"/>
                                    <path d="M4 3h4v1H6.646A4 4 0 0 1 8 7v6h7V7a3 3 0 0 0-3-3V3a4 4 0 0 1 4 4v6a1 1 0 0 1-1 1H1a1 1 0 0 1-1-1V7a4 4 0 0 1 4-4m0 1a3 3 0 0 0-3 3v6h6V7a3 3 0 0 0-3-3"/>
                                  </svg> </p>@endif
                                @if(isset($d['table']))<p class="pop3"> <span>{{$d['table']}}</span> <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-lines-fill" viewBox="0 0 16 16">
                                    <path d="M6 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6m-5 6s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zM11 3.5a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 0 1h-4a.5.5 0 0 1-.5-.5m.5 2.5a.5.5 0 0 0 0 1h4a.5.5 0 0 0 0-1zm2 3a.5.5 0 0 0 0 1h2a.5.5 0 0 0 0-1zm0 3a.5.5 0 0 0 0 1h2a.5.5 0 0 0 0-1z"/>
                                  </svg></p>@endif
                                <button class="b">{{$d['day']}}</button>
                            </form>
                        @endforeach
                    </div>
                </div>
            </div>
            @php $i ++ @endphp
        @endforeach

        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="prev">
          <span class="carousel-control-prev-icon" aria-hidden="true"></span>
          <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="next">
          <span class="carousel-control-next-icon" aria-hidden="true"></span>
          <span class="visually-hidden">Next</span>
        </button>
      </div>
</div>
@endif
<h1>Date</h1>
<div class="container m-auto">
    <form class="d-flex flex-column py-5"  action="{{ route('admin.dates.generate') }}" method="post" enctype="multipart/form-data">
        @csrf
        
        <h3>GENERA NUOVE DATE</h3>
        @if ( config('configurazione.pack') == 2 ||  config('configurazione.pack') == 4)  
            <h5 class="pt-4 ">Indica il numero di posti a sedere per fascia oraria</h5>
            <div class="input-group w-auto flex-nowrap py-2 ">
                <label for="max_reservations" class="input-group-text" >N° di posti a sedere</label>
                <input name="max_reservations" id="max_reservations" type="number" class="form-control" placeholder="N° di posti a sedere" aria-label="N° di posti a sedere" aria-describedby="addon-wrapping" >
            </div>
        @endif
        @if ( config('configurazione.pack') == 3 ||  config('configurazione.pack') == 4)  
            @if (config('configurazione.typeOfOrdering'))  
                <h5 class="pt-4 ">Indica il numero massimo di pezzi al taglio (cucina 1) per l'asporto</h5>
                <div class="input-group w-auto flex-nowrap py-2 ">
                    <label for="max_cucina_1" class="input-group-text" >N° di pezzi</label>
                    <input name="max_cucina_1" id="max_cucina_1" type="number" class="form-control" placeholder="N° di pezzi">
                </div>
                
                <h5 class="pt-4 ">Indica il numero massimo di pizze al piatto (cucina 2) per l'asporto</h5>
                <div class="input-group w-auto flex-nowrap py-2 ">
                    <label for="max_cucina_2" class="input-group-text" >N° di pizze</label>
                    <input name="max_cucina_2" id="max_cucina_2" type="number" class="form-control" placeholder="N° di pezzi">
                </div>
            @else
                <h5 class="pt-4 ">Indica il numero massimo di ordini per l'asporto</h5>
                <div class="input-group w-auto flex-nowrap py-2 ">
                    <label for="max_asporto" class="input-group-text" >N° di ordini</label>
                    <input name="max_asporto" id="max_asporto" type="number" class="form-control" placeholder="N° di ordini per fascia oraria">
                </div>
                    
            @endif
            <h5 class="pt-4 ">Indica il numero massimo di ordini con la consegna a domicilio</h5>
            <div class="input-group w-auto flex-nowrap py-2 ">
                <label for="max_domicilio" class="input-group-text" >N° di oridini a domicilio</label>
                <input name="max_domicilio" id="max_domicilio" type="number" class="form-control" placeholder="N° di ordini per fascia oraria">
            </div>
        @endif
        <div>
            <h5 class="pt-4">Seleziona i giorni in cui sei attivo</h5>
            <div class="day_form" role="group" aria-label="Basic checkbox toggle button group">

                @foreach (config('configurazione.days') as $day)
                
                    <input class="btn-check"  name="day[]" data-bs-toggle="collapse" data-bs-target="#multiCollapseExample{{$day}}" aria-expanded="false" aria-controls="multiCollapseExample{{$day}}" id="day_{{ $day }}" value="{{ $day }}">
                    <label class="my_btn btn-dark scale-none  " for="day_{{ $day }}">{{ config('configurazione.days_name')[$day] }}
                        <div class="collapse multi-collapse" id="multiCollapseExample{{$day}}">
                            <div class="card card-body">
                                <input
                                    type="checkbox"
                                    class="btn-check"
                                    id="days_on_{{ $day }}"
                                    name="days_on[]"
                                    value="{{ $day }}">
                                <label class="btn btn-outline-light w-auto m-auto" for="days_on_{{ $day }}">Attiva</label>
                                <h5 class="p-3">Seleziona le fasce orarie disponibili</h5>
                                @foreach (config('configurazione.times') as $time)
                                    <select  class="form-select col" name="times_slot_{{$day}}[]" id="">
                                        @if ( config('configurazione.pack') == 2)
                                            <option value="0">{{ $time['time'] }} - ND</option>
                                            <option value="1">{{ $time['time'] }} - attivo</option>  
                                        @elseif ( config('configurazione.pack') == 3)  
                                            <option value="0">{{ $time['time'] }} - ND</option>
                                            <option value="1">{{ $time['time'] }} - asporto</option>
                                            <option value="4">{{ $time['time'] }} - domicilio</option>
                                            <option value="7">{{ $time['time'] }} - tutti</option>
                                        @elseif ( config('configurazione.pack') == 4)     
                                            <option value="0">{{ $time['time'] }} - ND</option>
                                            <option value="1">{{ $time['time'] }} - asporto</option>
                                            <option value="2">{{ $time['time'] }} - tavoli</option>
                                            <option value="3">{{ $time['time'] }} - asporto/tavoli</option>
                                            <option value="4">{{ $time['time'] }} - domicilio</option>
                                            <option value="5">{{ $time['time'] }} - domicilio/asporto</option>
                                            <option value="6">{{ $time['time'] }} - domicilio/tavoli</option>
                                            <option value="7">{{ $time['time'] }} - tutti</option>
                                        @endif
                                    </select>
                                
                                @endforeach                    
                            
                            </div>
                        </div>
                    
                
                    </label>

                @endforeach
            </div>
        </div>
        <button class="btn btn-outline-light mt-4 w-100">Modifica</button>
    </form>
</div>


@endsection