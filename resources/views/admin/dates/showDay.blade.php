@extends('layouts.base')

@section('contents')
@if (session('success'))
@php
    $data = session('success')
@endphp
<div class="alert alert-success">
    {{ $data }}
</div>
@endif

<a class="btn btn-outline-light mb-5" href="{{ route('admin.dates.index') }}">Indietro</a>

<h1 class="p-3">{{$day[0]->day}} - {{config('configurazione.mesi')[$day[0]->month]}}</h1>
<div class="slim_cont">
    @foreach ($day as $t)
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

    <div class="slim_ slim_time ">
        <section class="s1">
            <h3>{{$t->time}}</h3>   
            <button type="button" class="my_btn_1 d" data-bs-toggle="modal" data-bs-target="#exampleModal{{$t->id}}">
                Vedi dettagli
            </button>
        </section>
        
        <form action="{{ route('admin.dates.status') }}" method="POST" class="modal fade data_set" id="exampleModal{{$t->id}}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            @csrf
            <input type="hidden" name="id" value="{{$t->id}}">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title" id="exampleModalLabel">MOODIFICA slot orario {{$t->time}}</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body data_set_body">
                        <section>
                            <h3>Prenotazioni a quest'orario</h3>
                            @php $i = 0 @endphp
                            <div class="cont">
                                @foreach ($reserving as $key => $value)
                                    <div class="not-set">
                                        <h5>{{config('configurazione.set_time')[$i]}}:</h5>
                                        <span class=""> {{$value}}</span>
                                    </div>
                                    @php $i ++ @endphp
                                @endforeach
                            </div>
                        </section>
                        <section>
                            <h3>Modifica le disponibilità</h3>
                            @php $i = 0 @endphp
                            <div class="cont">
                                @foreach ($availability as $key => $value)
                                    <div class="set">
                                        <h5>{{config('configurazione.set_time')[$i]}}:</h5>
                                        <input type="number" name="av{{$key}}" value="{{$value}}" class="">   
                                    </div>
                                    @php $i ++ @endphp
                                @endforeach
                            </div>
                        </section>
                        <section>
                            <h3>Modifica le visibilità</h3>
                            @php $i = 0 @endphp
                            <div class="cont">
                                @foreach ($visible as $key => $value)
                                    <div class="set">
                                        <h5>{{config('configurazione.set_time')[$i]}}:</h5>
                                        <select name="vis{{$key}}" class="">
                                            <option @if ($value) selected @endif value="1">SI</option>
                                            <option @if (!$value) selected @endif value="0">NO</option>
                                        </select>
                                    </div>
                                    @php $i ++ @endphp
                                @endforeach
                            </div>
                        </section>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="my_btn_2">Modifica</button>
                        <button type="button" class="my_btn_1" data-bs-dismiss="modal">Annulla</button>
                    </div>
                </div>
            </div>
        </form>
        
    </div>
    @endforeach
</div>

@endsection