@extends('layouts.base')

@section('contents')
<button onclick="history.back()" class="btn btn-outline-light my-5">
    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-90deg-left" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M1.146 4.854a.5.5 0 0 1 0-.708l4-4a.5.5 0 1 1 .708.708L2.707 4H12.5A2.5 2.5 0 0 1 15 6.5v8a.5.5 0 0 1-1 0v-8A1.5 1.5 0 0 0 12.5 5H2.707l3.147 3.146a.5.5 0 1 1-.708.708l-4-4z"/></svg>
</button>
    <div class="myres-c">

        <?php

        $data_ora = DateTime::createFromFormat('d/m/Y H:i', $reservation->date_slot);

        $ora_formatata = $data_ora->format('H:i');
        $data_formatata = $data_ora->format('d/m/Y');
        $giorno_settimana = $data_ora->format('w');
        ?>



        @if ($reservation->status == 2)
        <div class="myres my_2">
        @elseif ($reservation->status == 1)
        <div class="myres my_1 ">
        @elseif ($reservation->status == 0)
        <div class="myres my_0">
        @endif

            <div class="mail-tel">
                <a href="{{'mailto:' . $reservation->email}}" class="mail">{{$reservation->email}}</a>
                <a href="{{'tel:' . $reservation->phone}}" class="tel">{{$reservation->phone}}</a>
            </div>
            <div class="body">
                <section class="myres-left">
                    <div class="name">{{$reservation->name}}</div>
                    <div  class="myres-left-c">
                        <div class="time">{{$ora_formatata}}</div>

                        <div class="day_w">{{config('configurazione.days_name')[$giorno_settimana]}}</div>
                        <div class="date">{{$data_formatata}}</div>
                    </div>
                    <div class="c_a">inviato alle: {{$reservation->created_at}}</div>
                </section>
                <section class="myres-center-res">
                   <h5>Numero di Ospiti</h5> 
                    <h4>{{$reservation->n_person}}</h4>
                </section>
                <section class="myres-right">
                    @if(!$reservation->status !== 1)
                    <form class="w-100" action="{{ route('admin.reservations.status') }}" method="POST">
                        @csrf
                        <input value="1" type="hidden" name="c_a">
                        <input value="{{$reservation->id}}" type="hidden" name="id">

                        <button type="submit" class="my_btn_3 w-100">Conferma</button>
                    </form>
                    @endif
                    @if(!$reservation->status == 0)
                    <form class="w-100" action="{{ route('admin.reservations.status') }}" method="POST">
                        @csrf
                        <input value="0" type="hidden" name="c_a">
                        <input value="{{$reservation->id}}" type="hidden" name="id">
                        
                        <button type="submit" class="my_btn_2 w-100">Annulla</button>
                    </form>
                    @endif
                </section>
            </div>
            <div class="visible">
                @if ($reservation->status == 2)
                <span>in elaborazione</span>
                @elseif ($reservation->status == 1)
                <span>confermato</span>
                @elseif ($reservation->status == 0)
                <span>annullato</span>
                @endif
            </div>
        </div>

        
    </div>

@endsection