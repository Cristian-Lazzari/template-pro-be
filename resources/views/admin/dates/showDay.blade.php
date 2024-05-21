@extends('layouts.base')

@section('contents')
    
@php
    $typeOfOrdering = true; //se impostato a true gli ordini vengono presi in base ai pezzi altrimenti in base al numero di ordini
    $pack = 4; 
    $days = [1, 2, 3, 4, 5, 6, 7];
    $mesi = ['', 'gennaio', 'febbraio', 'marzo', 'aprile', 'maggio', 'giugno', 'luglio', 'agosto', 'settembre', 'ottobre', 'novembre', 'dicembre'];
    $days_name = [' ','lunedì', 'martedi', 'mercoledì', 'giovedì', 'venerdì', 'sabato', 'domenica'];
    
@endphp
<a class="btn btn-outline-dark mb-5" href="{{ route('admin.dates.index') }}">Indietro</a>

<h1 class="p-3">{{$times[0]->day}} - {{$mesi[$times[0]->month]}}</h1>
<div class="slim_cont">
    @foreach ($times as $t)
    @php
        if($t->reserving !== '0'){
            $reserving = json_decode($t->reserving);
        }else{
            $reserving = false;
        }
        if($t->availability !== '0'){
            $availability = json_decode($t->availability);
        }else{
            $availability = false;
        }
        if($t->visible !== '0'){
            $visible = json_decode($t->visible);
        }else{
            $visible = false;
        }
    @endphp

    <div class="slim_ ">
        <section class="s1">
            <h3>{{$t->time}}</h3>     
        </section>
        @if ($visible)
            <section>
                <button type="button" class="my_btn d" data-bs-toggle="modal" data-bs-target="#exampleModal">
                    Vedi dettagli
                </button>
            </section>
        @endif
        <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="exampleModalLabel">Dettagli slot orario {{$t->time}}</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <h3 class="m-3">Prenotazioni</h3>
                        @foreach ($reserving as $key => $value)
                            <span class="px-3">{{$key}}: {{$value}}</span>
                        @endforeach
                        <h3 class="m-3">Disponibilità</h3>
                        @foreach ($availability as $key => $value)
                            <span class="px-3">{{$key}}: {{$value}} </span>
                        @endforeach
                        <h3 class="m-3">Visibilità</h3>
                        @foreach ($visible as $key => $value)
                            <span class="px-3">{{$key}}: {{$value ? 'si' : 'no'}}</span>
                        @endforeach
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">chiudi</button>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
    @endforeach
</div>

@endsection