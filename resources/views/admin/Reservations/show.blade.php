@extends('layouts.base')

@section('contents')
<a onclick="history.back()" class="btn btn-outline-light my-5">
    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-90deg-left" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M1.146 4.854a.5.5 0 0 1 0-.708l4-4a.5.5 0 1 1 .708.708L2.707 4H12.5A2.5 2.5 0 0 1 15 6.5v8a.5.5 0 0 1-1 0v-8A1.5 1.5 0 0 0 12.5 5H2.707l3.147 3.146a.5.5 0 1 1-.708.708l-4-4z"/></svg>
</a>
    <div class="myres-c">

        <?php

        $data_ora = DateTime::createFromFormat('d/m/Y H:i', $reservation->date_slot);

        $ora_formatata = $data_ora->format('H:i');
        $data_formatata = $data_ora->format('d/m/Y');
        $giorno_settimana = $data_ora->format('w');
        ?>



        <div class="
            @if ($reservation->status == 2) my_2
            @elseif ($reservation->status == 1) my_1
            @elseif ($reservation->status == 0) my_0
            @elseif ($reservation->status == 3) my_3
            @elseif ($reservation->status == 5) my_5
            @endif myres"
        >

            <div class="mail-tel">
                <a href="{{'mailto:' . $reservation->email}}" class="mail">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-envelope-arrow-up-fill" viewBox="0 0 16 16">
                        <path d="M.05 3.555A2 2 0 0 1 2 2h12a2 2 0 0 1 1.95 1.555L8 8.414zM0 4.697v7.104l5.803-3.558zm.192 8.159 6.57-4.027L8 9.586l1.239-.757.367.225A4.49 4.49 0 0 0 8 12.5c0 .526.09 1.03.256 1.5H2a2 2 0 0 1-1.808-1.144M16 4.697v4.974A4.5 4.5 0 0 0 12.5 8a4.5 4.5 0 0 0-1.965.45l-.338-.207z"/>
                        <path d="M12.5 16a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7m.354-5.354 1.25 1.25a.5.5 0 0 1-.708.708L13 12.207V14a.5.5 0 0 1-1 0v-1.717l-.28.305a.5.5 0 0 1-.737-.676l1.149-1.25a.5.5 0 0 1 .722-.016"/>
                    </svg>
                    <span>
                        {{$reservation->email}}
                    </span>
                </a>
                <a href="{{'tel:' . $reservation->phone}}" class="tel">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-telephone-outbound-fill" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M1.885.511a1.745 1.745 0 0 1 2.61.163L6.29 2.98c.329.423.445.974.315 1.494l-.547 2.19a.68.68 0 0 0 .178.643l2.457 2.457a.68.68 0 0 0 .644.178l2.189-.547a1.75 1.75 0 0 1 1.494.315l2.306 1.794c.829.645.905 1.87.163 2.611l-1.034 1.034c-.74.74-1.846 1.065-2.877.702a18.6 18.6 0 0 1-7.01-4.42 18.6 18.6 0 0 1-4.42-7.009c-.362-1.03-.037-2.137.703-2.877zM11 .5a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 .5.5v4a.5.5 0 0 1-1 0V1.707l-4.146 4.147a.5.5 0 0 1-.708-.708L14.293 1H11.5a.5.5 0 0 1-.5-.5"/>
                    </svg>
                    <span>
                        {{$reservation->phone}}
                    </span>
                </a>
            </div>
            <div class="status">
                @if(in_array($reservation->status, [0, 6])) 
                    <div class="int null">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi null bi-x-circle" viewBox="0 0 16 16">
                            <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
                            <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708"/>
                        </svg>
                        {{$reservation->status == 6 ? 'Rimborsata' : 'Annullata'}}
                    </div>
                
                    @elseif(in_array($reservation->status, [2, 3])) 
                    <div class="int to_see">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi to_see bi-exclamation-circle-fill" viewBox="0 0 16 16">
                            <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M8 4a.905.905 0 0 0-.9.995l.35 3.507a.552.552 0 0 0 1.1 0l.35-3.507A.905.905 0 0 0 8 4m.002 6a1 1 0 1 0 0 2 1 1 0 0 0 0-2"/>
                        </svg>
                        {{$reservation->status == 2 ? 'In attesa...' : 'GIÀ PAGATA In attesa...'}}
                    
                    </div>
                    @elseif(in_array($reservation->status, [1, 5])) 
                    <div class="int okk">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi okk bi-check-circle" viewBox="0 0 16 16">
                            <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
                            <path d="m10.97 4.97-.02.022-3.473 4.425-2.093-2.094a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05"/>
                        </svg>
                        {{$reservation->status == 5 ? 'Confermata' : 'Confermatra e Incassata'}}
                    </div>
                @endif
            </div>
            <div class="body">
                <section class="myres-left">
                    <div class="data_cont">
                        <h5><strong>#R{{$reservation->id}}</strong></h5>
                        <div class="time">{{$ora_formatata}}</div>
                        <div class="day_w">
                            {{[' ','lunedì', 'martedì', 'mercoledì', 'giovedì', 'venerdì', 'sabato', 'domenica'][$giorno_settimana]}}
                            {{$data_formatata}}
                        </div>
                    </div>
                    <div class="name">
                        <p>{{$reservation->name}}</p>
                        <p>{{$reservation->surname}}</p>
                    </div>
                </section>
                <div class="n_person">
                    @php $n_person = json_decode($reservation->n_person); @endphp
                    <h3>Ospiti: </h3>
                    @if ($n_person->adult > 0)
                        <h4>
                            {{$n_person->adult }} 
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-standing" viewBox="0 0 16 16">
                                <path d="M8 3a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3M6 6.75v8.5a.75.75 0 0 0 1.5 0V10.5a.5.5 0 0 1 1 0v4.75a.75.75 0 0 0 1.5 0v-8.5a.25.25 0 1 1 .5 0v2.5a.75.75 0 0 0 1.5 0V6.5a3 3 0 0 0-3-3H7a3 3 0 0 0-3 3v2.75a.75.75 0 0 0 1.5 0v-2.5a.25.25 0 0 1 .5 0"/>
                            </svg>
                        </h4>
                    @endif
                    @if ($n_person->child > 0)
                        <h4>
                            {{$n_person->child }} 
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-arms-up" viewBox="0 0 16 16">
                                <path d="M8 3a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3"/>
                            <path d="m5.93 6.704-.846 8.451a.768.768 0 0 0 1.523.203l.81-4.865a.59.59 0 0 1 1.165 0l.81 4.865a.768.768 0 0 0 1.523-.203l-.845-8.451A1.5 1.5 0 0 1 10.5 5.5L13 2.284a.796.796 0 0 0-1.239-.998L9.634 3.84a.7.7 0 0 1-.33.235c-.23.074-.665.176-1.304.176-.64 0-1.074-.102-1.305-.176a.7.7 0 0 1-.329-.235L4.239 1.286a.796.796 0 0 0-1.24.998l2.5 3.216c.317.316.475.758.43 1.204Z"/>
                            </svg>
                        </h4>
                    @endif
                    @if ($reservation->sala !== null && $reservation->sala !== 0)
                        <h3>Sala prenota: <strong>{{$reservation->sala == 1 ? $property_adv['sala_1'] : $property_adv['sala_2']}}</strong></h3>
                    @endif
                </div>
                <div class="c_a">
                    @php \Carbon\Carbon::setLocale('it');@endphp
                    Inviato alle: {{ \Carbon\Carbon::parse($reservation->created_at)->translatedFormat('H:i:s l j F Y') }} <br>
                    Marketing sul contatto: {{$reservation->news_letter ? 'si' : 'no'}}
                </div>
                <div class="actions">
                    @if (!in_array($reservation->status, [0, 1, 5]))
                        <button type="button" data-bs-toggle="modal" data-bs-target="#confirmModal" class=" my_btn_3">Conferma</button>
                    @endif
                    @if(!in_array($reservation->status, [0, 1]))
                        <button type="button" data-bs-toggle="modal" data-bs-target="#cancelModal" class=" my_btn_5">{{$reservation->status == 5 ? 'Rimborsa e Annulla' : 'Annulla'}}</button>                   

                    @endif
                </div>
                
            </div>
        </div>

        
    </div>

      {{-- Modale per conferma --}}
      <div class="modal fade" id="confirmModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header c-1">
                    <h1 class="modal-title fs-5" id="confirmModalLabel">Gestione notifica conferma</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body c-1">
                    Ordine di: {{$reservation->name}} 
                    per il: {{$reservation->date_slot}}
                    <p>Vuoi inviare un messaggio whatsapp?</p>
                </div>
                <div class="modal-footer">
                    <form action="{{ route('admin.reservations.status') }}" method="POST">
                        @csrf
                        <input value="1" type="hidden" name="wa">
                        <input value="1" type="hidden" name="c_a">
                        <input value="{{$reservation->id}}" type="hidden" name="id">
                        <button type="submit" class="w-100 my_btn_6">Si</button>
                    </form>
                    <form action="{{ route('admin.reservations.status') }}" method="POST">
                        @csrf
                        <input value="0" type="hidden" name="wa">
                        <input value="1" type="hidden" name="c_a">
                        <input value="{{$reservation->id}}" type="hidden" name="id">
                        <button type="submit" class="w-100 my_btn_6">NO</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Modale per annullamento --}}
    <div class="modal fade" id="cancelModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="cancelModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header c-1">
                    <h1 class="modal-title fs-5" id="cancelModalLabel">Gestione notifica annullamento</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body c-1">
                    Ordine di: {{$reservation->name}} 
                    per il: {{$reservation->date_slot}}
                    <p>Vuoi inviare un messaggio whatsapp?</p>
                </div>
                <div class="modal-footer">
                    <form action="{{ route('admin.reservations.status') }}" method="POST">
                        @csrf
                        <input value="1" type="hidden" name="wa">
                        <input value="0" type="hidden" name="c_a">
                        <input value="{{$reservation->id}}" type="hidden" name="id">
                        <button type="submit" class="w-100 my_btn_6">Si</button>
                    </form>
                    <form action="{{ route('admin.reservations.status') }}" method="POST">
                        @csrf
                        <input value="0" type="hidden" name="wa">
                        <input value="0" type="hidden" name="c_a">
                        <input value="{{$reservation->id}}" type="hidden" name="id">
                        <button type="submit" class="w-100 my_btn_6">NO</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection