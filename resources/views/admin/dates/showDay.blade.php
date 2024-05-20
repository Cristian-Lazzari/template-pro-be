@extends('layouts.base')

@section('contents')
    
@php
    $typeOfOrdering = true; //se impostato a true gli ordini vengono presi in base ai pezzi altrimenti in base al numero di ordini
    $pack = 4; 
    $days = [1, 2, 3, 4, 5, 6, 7];
    $mesi = ['', 'gennaio', 'febbraio', 'marzo', 'aprile', 'maggio', 'giugno', 'luglio', 'agosto', 'settembre', 'ottobre', 'novembre', 'dicembre'];
    $days_name = [' ','lunedì', 'martedi', 'mercoledì', 'giovedì', 'venerdì', 'sabato', 'domenica'];
@endphp

<div class="container">
    @foreach ($times as $t)
        {{$t->time}}
    @endforeach
</div>

@endsection