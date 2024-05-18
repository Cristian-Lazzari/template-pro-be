@extends('layouts.base')



@section('contents')
@php
    $typeOfOrdering = true; //se impostato a true gli ordini vengono presi in base ai pezzi altrimenti in base al numero di ordini
    $pack = 1
@endphp
@if (session('ingredient_success'))
    @php
        $data = session('ingredient_success')
    @endphp
    <div class="alert alert-primary">
        {{ $data }}
    </div>
@endif
<a class="btn btn-outline-dark mb-5" href="{{ route('admin.dashboard') }}">Indietro</a>

<h1>Date</h1>

<div class="action-page">
   
</div>

<div class="container w-75 m-auto w-small-100">
    <form class="d-flex flex-column py-5"  action="{{ route('admin.dates.generate') }}" method="post" enctype="multipart/form-data">
        @csrf
        <h3>GENERA NUOVE DATE</h3>
        @if ($pack == 2 || $pack == 4)  
            <h5 class="pt-4 ">Indica il numero di posti a sedere per fascia oraria</h5>
            <div class="input-group w-auto flex-nowrap py-2 ">
                <label for="max_reservations" class="input-group-text" >N° di posti a sedere</label>
                <input name="max_reservations" id="max_reservations" type="number" class="form-control" placeholder="N° di posti a sedere" aria-label="N° di posti a sedere" aria-describedby="addon-wrapping" value="0">
            </div>
        @endif
        @if ($pack == 3 || $pack == 4)  
            @if ($typeOfOrdering)  
                <h5 class="pt-4 ">Indica il numero massimo di pezzi al taglio per l'asporto</h5>
                <div class="input-group w-auto flex-nowrap py-2 ">
                    <label for="max_pz_q" class="input-group-text" >N° di pezzi</label>
                    <input name="max_pz_q" id="max_pz_q" type="number" class="form-control" placeholder="N° di pezzi">
                </div>
                
                <h5 class="pt-4 ">Indica il numero massimo di pizze al piatto per l'asporto</h5>
                <div class="input-group w-auto flex-nowrap py-2 ">
                    <label for="max_pz_t" class="input-group-text" >N° di pizze</label>
                    <input name="max_pz_t" id="max_pz_t" type="number" class="form-control" placeholder="N° di pezzi">
                </div>
            @else
                <h5 class="pt-4 ">Indica il numero massimo di ordini per l'asporto</h5>
                <div class="input-group w-auto flex-nowrap py-2 ">
                    <label for="max_asporto" class="input-group-text" >N° di ordini</label>
                    <input name="max_asporto" id="max_asporto" type="number" class="form-control" placeholder="N° di pezzi">
                </div>
                    
            @endif
            <h5 class="pt-4 ">Indica il numero massimo di ordini con la consegna a domicilio</h5>
            <div class="input-group w-auto flex-nowrap py-2 ">
                <label for="max_domicilio" class="input-group-text" >N° di oridini a domicilio</label>
                <input name="max_domicilio" id="max_domicilio" type="number" class="form-control" placeholder="N° di pezzi">
            </div>
        @endif
        <div>
            <h5 class="pt-4">Seleziona i giorni in cui sei attivo</h5>
            <div class="btn-group py-1 w-100 row g-2 " role="group" aria-label="Basic checkbox toggle button group">

                @foreach ($days as $day)
                
                    <input class="btn-check "  name="day_off[]" data-bs-toggle="collapse" data-bs-target="#multiCollapseExample{{$day}}" aria-expanded="false" aria-controls="multiCollapseExample{{$day}}" id="day_off_{{ $day }}" value="{{ $day }}">
                    <label class="btn btn-dark radius col w-100" for="day_off_{{ $day }}">{{ $days_name[$day] }}
                        <div class="collapse multi-collapse" id="multiCollapseExample{{$day}}">
                            <div class="card card-body">
                                <input
                                    type="checkbox"
                                    class="btn-check @error ('tags') is-invalid @enderror"
                                    id="days_off_{{ $day }}"
                                    name="days_off[]"
                                    value="{{ $day }}">
                                <label class="btn btn-outline-dark" for="days_off_{{ $day }}">Attiva</label>
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
        

        <button class="btn btn-outline-dark mt-4 w-100">Modifica</button>
    </form>
</div>


@endsection