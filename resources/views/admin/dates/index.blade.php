@extends('layouts.base')



@section('contents')
@php
    $typeOfOrdering = true; //se impostato a true gli ordini vengono presi in base ai pezzi altrimenti in base al numero di ordini
    $pack = 4;
    $times = [
            1 => ['time' => '19:00', 'set' => ''] ,
            2 => ['time' => '19:15', 'set' => ''] ,
            3 => ['time' => '19:30', 'set' => ''] ,
            4 => ['time' => '19:45', 'set' => ''] ,
            5 => ['time' => '20:00', 'set' => ''] ,
            6 => ['time' => '20:15', 'set' => ''] ,
            7 => ['time' => '20:30', 'set' => ''] ,
        ]; 
    $days = [1, 2, 3, 4, 5, 6, 7];
    $mesi = ['', 'gennaio', 'febbraio', 'marzo', 'aprile', 'maggio', 'giugno', 'luglio', 'agosto', 'settembre', 'ottobre', 'novembre', 'dicembre'];
    $days_name = [' ','lunedì', 'martedi', 'mercoledì', 'giovedì', 'venerdì', 'sabato', 'domenica'];
@endphp


@if (session('success'))
    @php
        $data = session('success')
    @endphp
    <div class="alert alert-success">
        {{ $data }}
    </div>
@endif
<a class="btn btn-outline-dark mb-5" href="{{ route('admin.dashboard') }}">Indietro</a>


@if (isset($year))
    

<div class="date_index">
    <div id="carouselExampleIndicators" class="carousel slide">
        <div class="carousel-indicators">

            @php $i = 0; @endphp
            @foreach ($year as $m)
                <button type="button" style="background: rgb(28, 3, 65); border-radius:50px; " data-bs-target="#carouselExampleIndicators" data-bs-slide-to="{{$i}}"
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
                <h2 class="my">{{$mesi[$m['month']]}} - {{$m['year']}}</h2>
                <div class="calendar-c">
                    <div class="c-name">
                        @php
                         $day_name = ['lunedì', 'martedi', 'mercoledì', 'giovedì', 'venerdì', 'sabato', 'domenica'];
                        @endphp
                        @foreach ($day_name as $item)
                            <h4>{{$item}}</h4>
                        @endforeach
                    </div>
                    <div class="calendar">

                        @foreach ($m['days'] as $d)
                            <form action="{{ route('admin.dates.showDay') }}" class="day {{ 'd' . $d['day_w']}} " style="grid-column-start:{{$d['day_w'] - 1}}" method="get">
                                @csrf
                                <input type="hidden" name="date" value="{{$d['date']}}">
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
        @if ($pack == 2 || $pack == 4)  
            <h5 class="pt-4 ">Indica il numero di posti a sedere per fascia oraria</h5>
            <div class="input-group w-auto flex-nowrap py-2 ">
                <label for="max_reservations" class="input-group-text" >N° di posti a sedere</label>
                <input name="max_reservations" id="max_reservations" type="number" class="form-control" placeholder="N° di posti a sedere" aria-label="N° di posti a sedere" aria-describedby="addon-wrapping" >
            </div>
        @endif
        @if ($pack == 3 || $pack == 4)  
            @if ($typeOfOrdering)  
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

                @foreach ($days as $day)
                
                    <input class="btn-check"  name="day[]" data-bs-toggle="collapse" data-bs-target="#multiCollapseExample{{$day}}" aria-expanded="false" aria-controls="multiCollapseExample{{$day}}" id="day_{{ $day }}" value="{{ $day }}">
                    <label class="btn btn-dark radius " for="day_{{ $day }}">{{ $days_name[$day] }}
                        <div class="collapse multi-collapse" id="multiCollapseExample{{$day}}">
                            <div class="card card-body">
                                <input
                                    type="checkbox"
                                    class="btn-check"
                                    id="days_on_{{ $day }}"
                                    name="days_on[]"
                                    value="{{ $day }}">
                                <label class="btn btn-outline-success w-auto m-auto" for="days_on_{{ $day }}">Attiva</label>
                                <h5 class="p-3">Seleziona le fasce orarie disponibili</h5>
                                @foreach ($times as $time)
                                    <select  class="form-select col" name="times_slot_{{$day}}[]" id="">
                                        @if ($pack == 2)
                                            <option value="0">{{ $time['time'] }} - ND</option>
                                            <option value="1">{{ $time['time'] }} - attivo</option>  
                                        @elseif ($pack == 3)  
                                            <option value="0">{{ $time['time'] }} - ND</option>
                                            <option value="1">{{ $time['time'] }} - asporto</option>
                                            <option value="4">{{ $time['time'] }} - domicilio</option>
                                            <option value="7">{{ $time['time'] }} - tutti</option>
                                        @elseif ($pack == 4)     
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
        
        <input type="hidden" name="times" value="{{json_encode($times)}}">
        <button class="btn btn-outline-dark mt-4 w-100">Modifica</button>
    </form>
</div>


@endsection