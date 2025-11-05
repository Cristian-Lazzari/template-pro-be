@extends('layouts.base')

@section('contents')
@if (session('success'))
    @php
        $data = session('success')
    @endphp
    <div class="alert alert-info">
        {{ $data }}
    </div>
@endif
@if (session('error'))
    @php
        $data = session('error')
    @endphp
    <div class="alert alert-danger">
        {{ $data }}
    </div>
@endif




<a onclick="history.back()" class="btn btn-outline-light my-5">
    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-90deg-left" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M1.146 4.854a.5.5 0 0 1 0-.708l4-4a.5.5 0 1 1 .708.708L2.707 4H12.5A2.5 2.5 0 0 1 15 6.5v8a.5.5 0 0 1-1 0v-8A1.5 1.5 0 0 0 12.5 5H2.707l3.147 3.146a.5.5 0 1 1-.708.708l-4-4z"/></svg>
</a>
    <div class="myres-c">

        <?php

        $data_ora = DateTime::createFromFormat('d/m/Y H:i', $order->date_slot);

        $ora_formatata = $data_ora->format('H:i');
        $data_formatata = $data_ora->format('d/m/Y');
        $giorno_settimana = $data_ora->format('w');
        ?>



        <div class="
            @if ($order->status == 2)
                my_2
                @elseif ($order->status == 1)
                my_1
                @elseif ($order->status == 0)
                my_0
                @elseif ($order->status == 3)
                my_3
                @elseif ($order->status == 5)
                my_5
                @elseif ($order->status == 6)
                my_6
            @endif myres"
        >

            <div class="mail-tel">
                <a href="{{'mailto:' . $order->email}}" class="mail">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-envelope-arrow-up-fill" viewBox="0 0 16 16">
                        <path d="M.05 3.555A2 2 0 0 1 2 2h12a2 2 0 0 1 1.95 1.555L8 8.414zM0 4.697v7.104l5.803-3.558zm.192 8.159 6.57-4.027L8 9.586l1.239-.757.367.225A4.49 4.49 0 0 0 8 12.5c0 .526.09 1.03.256 1.5H2a2 2 0 0 1-1.808-1.144M16 4.697v4.974A4.5 4.5 0 0 0 12.5 8a4.5 4.5 0 0 0-1.965.45l-.338-.207z"/>
                        <path d="M12.5 16a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7m.354-5.354 1.25 1.25a.5.5 0 0 1-.708.708L13 12.207V14a.5.5 0 0 1-1 0v-1.717l-.28.305a.5.5 0 0 1-.737-.676l1.149-1.25a.5.5 0 0 1 .722-.016"/>
                    </svg>
                    {{$order->email}}
                </a>
                <a href="{{'tel:' . $order->phone}}" class="tel">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-telephone-outbound-fill" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M1.885.511a1.745 1.745 0 0 1 2.61.163L6.29 2.98c.329.423.445.974.315 1.494l-.547 2.19a.68.68 0 0 0 .178.643l2.457 2.457a.68.68 0 0 0 .644.178l2.189-.547a1.75 1.75 0 0 1 1.494.315l2.306 1.794c.829.645.905 1.87.163 2.611l-1.034 1.034c-.74.74-1.846 1.065-2.877.702a18.6 18.6 0 0 1-7.01-4.42 18.6 18.6 0 0 1-4.42-7.009c-.362-1.03-.037-2.137.703-2.877zM11 .5a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 .5.5v4a.5.5 0 0 1-1 0V1.707l-4.146 4.147a.5.5 0 0 1-.708-.708L14.293 1H11.5a.5.5 0 0 1-.5-.5"/>
                    </svg>
                    {{$order->phone}}
                </a>
            </div>
            <div class="status">
                @if(in_array($order->status, [0, 6])) 
                    <div class="int null">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi null bi-x-circle" viewBox="0 0 16 16">
                            <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
                            <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708"/>
                        </svg>
                        {{$order->status == 6 ? 'Rimborsata' : 'Annullata'}}
                    </div>
                
                    @elseif(in_array($order->status, [2, 3])) 
                    <div class="int to_see">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi to_see bi-exclamation-circle-fill" viewBox="0 0 16 16">
                            <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M8 4a.905.905 0 0 0-.9.995l.35 3.507a.552.552 0 0 0 1.1 0l.35-3.507A.905.905 0 0 0 8 4m.002 6a1 1 0 1 0 0 2 1 1 0 0 0 0-2"/>
                        </svg>
                        {{$order->status == 2 ? 'In attesa...' : 'GIÀ PAGATA In attesa...'}}
                    
                    </div>
                    @elseif(in_array($order->status, [1, 5])) 
                    <div class="int okk">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi okk bi-check-circle" viewBox="0 0 16 16">
                            <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
                            <path d="m10.97 4.97-.02.022-3.473 4.425-2.093-2.094a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05"/>
                        </svg>
                        {{$order->status == 5 ? 'Confermata' : 'Confermatra e Incassata'}}
                    </div>
                @endif
            </div>
            <div class="body">
                <section class="myres-left">
                    <div class="data_cont">
                        <h5><strong>#O{{$order->id}}</strong></h5>
                        <div class="time">{{$ora_formatata}}</div>

                        <div class="day_w">
                            {{[' ','lunedì', 'martedì', 'mercoledì', 'giovedì', 'venerdì', 'sabato', 'domenica'][$giorno_settimana]}}
                            {{$data_formatata}}
                        </div>
                    </div>
                    <div class="name">
                        <p>{{$order->name}}</p>
                        <p>{{$order->surname}}</p>
                    </div>
                </section>
                <section class="products">
                    <h3>Prodotti ordinati</h3>

                    @foreach ($order->menus as $o)                
                        <div class="product">
                            <div class="top_p">
                                <div class="counter">* {{$o->pivot->quantity}}</div>              
                                <div class="name">{{$o->name}}</div>
                            </div>
                            <div class="variations">
                                @if($o->fixed_menu == '2')
                                    <div class="choices">
                                        <h5>Prodotti:</h5>
                                        @php
                                            $right_c = [];
                                            $scelti = json_decode($o->pivot->choices);
                                            foreach ($scelti as $id) {
                                                foreach ($o->products as $p) {
                                                    if($p->id == $id){
                                                        array_push($right_c , $p);
                                                        break;
                                                    }
                                                }
                                            }
                                        @endphp
                                        @foreach ($right_c as $c)

                                            <strong>{{$c->pivot->label}}: </strong>
                                            <span>{{$c->name}}({{$c->category->name}})</span>

                                        @endforeach
                                        
                                    </div>
                                @else
                                    <div class="prod">
                                        <h5>Prodotti:</h5>
                                        @foreach ($o->products as $c)
                                        <span>{{$c->name}}:</span>
                                        <span>({{$c->category->name}})</span>
                                        @endforeach
                                    </div>
                                @endif
 
                            </div>
                        </div>
                    @endforeach
                    @foreach ($order->products as $o)                
                        <?php
                            $arrO= json_decode($o->pivot->option); 
                            $arrA= json_decode($o->pivot->add); 
                            $arrD= json_decode($o->pivot->remove); 
                        ?>
                        <div class="product">
                            <div class="top_p">
                                <div class="counter">* {{$o->pivot->quantity}}</div>              
                                <div class="name">{{$o->name}}</div>
                            </div>
                            <div class="variations">
                                @if ($arrO !== [])
                                <div class="options">
                                    <h5>Opzioni:</h5>
                                    @foreach ($arrO as $a)
                                    <span>+ {{$a}}</span>
                                    @endforeach
                                </div>
                                @endif
                                <div class="bottom-var">
                                    @if ($arrA !== [])
                                    <div class="add">
                                        <h5>Ingredienti extra:</h5>
                                        @foreach ($arrA as $a)
                                        <span>+ {{$a}}</span>
                                        @endforeach
                                    </div>
                                    @endif
                                    @if ($arrD !== [])
                                    <div class="removed">
                                        <h5>Ingredienti rimossi:</h5>
                                        @foreach ($arrD as $a)
                                        <span>- {{$a}}</span>
                                        @endforeach       
                                    </div>
                                    @endif
                                </div>
                            </div>
                            
                        </div>
                    @endforeach

                    @if ($delivery_cost)
                        
                    <div class="price">Costo di consegna €{{$delivery_cost / 100}}</div>
                    @endif
                    <div class="t_price">€{{$order->tot_price / 100}}</div>
                </section>
                <section class="myres-right">
                    @if (isset($order->comune))
                        <h3>
                            Consegnare a domicilio
                            <p>{{$order->comune}}, {{$order->address}}, {{$order->address_n}}</p>
                        </h3>
                    @else
                        <h3>
                            Ritiro d'asporto
                        </h3>
                    @endif
                </section>
                <div class="c_a">
                    @php \Carbon\Carbon::setLocale('it');@endphp
                    Inviato alle: {{ \Carbon\Carbon::parse($order->created_at)->translatedFormat('H:i:s l j F Y') }} <br>
                    Marketing sul contatto: {{$order->news_letter ? 'si' : 'no'}}
                </div>
                <div class="actions">
                    @if (in_array($order->status, [2, 3]))
                        <button type="button" data-bs-toggle="modal" data-bs-target="#confirmModal" class="w-100 my_btn_3">Conferma</button>
                    @endif
                    @if(in_array($order->status, [2, 3, 5]))
                        <button type="button" data-bs-toggle="modal" data-bs-target="#cancelModal" class="w-100 my_btn_1">{{in_array($order->status, [3, 5]) ? 'Rimborsa e Annulla' : 'Annulla'}}</button>                   
                        <button type="button" data-bs-toggle="modal" data-bs-target="#changeModal" class="w-100 my_btn_1">Posticipa e Conferma</button>                   
                    @endif         
                </div>
                
            </div>
        </div>

        
    </div>

<!-- Modale per la posticipazione -->
<div class="modal fade" id="changeModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="changeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('admin.orders.changetime') }}" method="POST" class="modal-content">
            @csrf
            <input value="{{$order->id}}" type="hidden" name="id">
            <div class="modal-header c-1">
                <h1 class="modal-title fs-2" id="changeModalLabel">Conferma e posticipa questo ordine</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body c-1 fs-4">
                Ordine di: {{$order->name}} 
                per il: {{$order->date_slot}}
                <p>Seleziona l'orario corretto:</p>
                <input required class="form-control" type="time" name="new_time">
                <h3 class="mt-4 mb-3">Vuoi bloccare altri ordini per questa fascia oraria?</h3>
                <button type="submit" name="cancel" value="1" class="w-100 m-2 my_btn_1">Lascia attivo</button>
                <button type="submit" name="cancel" value="0" class="w-100 m-2 my_btn_2">Blocca questo orario</button>
            </div>


        </form>
    </div>
</div>

<!-- Modale per la conferma -->
<div class="modal fade" id="confirmModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header c-1">
                <h1 class="modal-title fs-3" id="confirmModalLabel">Gestione notifica per conferma</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body c-1 fs-4">
                Ordine di: {{$order->name}} 
                per il: {{$order->date_slot}}
                <p>Vuoi inviare un messaggio whatsapp?</p>
            </div>
            <div class="modal-footer">
                <form action="{{ route('admin.orders.status') }}" method="POST">
                    @csrf
                    <input value="1" type="hidden" name="wa">
                    <input value="1" type="hidden" name="c_a">
                    <input value="{{$order->id}}" type="hidden" name="id">
                    <button type="submit" class="w-100 my_btn_1">Si</button>
                </form>
                <form action="{{ route('admin.orders.status') }}" method="POST">
                    @csrf
                    <input value="0" type="hidden" name="wa">
                    <input value="1" type="hidden" name="c_a">
                    <input value="{{$order->id}}" type="hidden" name="id">
                    <button type="submit" class="w-100 my_btn_2">No</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modale per l'annullamento -->
<div class="modal fade" id="cancelModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="cancelModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header c-1">
                <h1 class="modal-title fs-3" id="cancelModalLabel">Gestione notifica per annullamento</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body c-1 fs-4">
                Ordine di: {{$order->name}} 
                per il: {{$order->date_slot}}
                <p>Vuoi inviare un messaggio whatsapp?</p>
            </div>
            <div class="modal-footer">
                <form action="{{ route('admin.orders.status') }}" method="POST">
                    @csrf
                    <input value="1" type="hidden" name="wa">
                    <input value="0" type="hidden" name="c_a">
                    <input value="{{$order->id}}" type="hidden" name="id">
                    <button type="submit" class="w-100 my_btn_1">Si</button>
                </form>
                <form action="{{ route('admin.orders.status') }}" method="POST">
                    @csrf
                    <input value="0" type="hidden" name="wa">
                    <input value="0" type="hidden" name="c_a">
                    <input value="{{$order->id}}" type="hidden" name="id">
                    <button type="submit" class="w-100 my_btn_2">No</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection