@extends('layouts.base')

@section('contents')
<a href="{{ route('admin.orders.index') }}" class="btn btn-outline-light my-3">
    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-90deg-left" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M1.146 4.854a.5.5 0 0 1 0-.708l4-4a.5.5 0 1 1 .708.708L2.707 4H12.5A2.5 2.5 0 0 1 15 6.5v8a.5.5 0 0 1-1 0v-8A1.5 1.5 0 0 0 12.5 5H2.707l3.147 3.146a.5.5 0 1 1-.708.708l-4-4z"/></svg>
</a>

    {{-- <img src="{{ Vite::asset('resources/img/picsum30.jpg') }}" alt=""> --}}
    <div class="myres-c">

        <?php

        $data_ora = DateTime::createFromFormat('d/m/Y H:i', $order->date_slot);

        $ora_formatata = $data_ora->format('H:i');
        $data_formatata = $data_ora->format('d/m/Y');
        $giorno_settimana = $data_ora->format('w');
        ?>



        @if ($order->status == 2)
        <div class="myres my_2">
        @elseif ($order->status == 1)
        <div class="myres my_1 ">
        @elseif ($order->status == 0)
        <div class="myres my_0">
        @endif

            <div class="mail-tel">
                <a href="{{'mailto:' . $order->email}}" class="mail">{{$order->email}}</a>
                <a href="{{'tel:' . $order->phone}}" class="tel">{{$order->phone}}</a>
            </div>
            <div class="body">
                <section class="myres-left">
                    <div class="name">{{$order->name}}</div>
                    <div  class="myres-left-c">
                        <div class="time">{{$ora_formatata}}</div>

                        <div class="day_w">{{config('configurazione.days_name')[$giorno_settimana]}}</div>
                        <div class="date">{{$data_formatata}}</div>
                    </div>
                    <div class="c_a">inviato alle: {{$order->created_at}}</div>
                    <div class="c_a">Marcketing sul contatto: {{$order->news_letter ? 'si' : 'no'}}</div>
                </section>
                <section class="myres-center">
                    <h3>Prodotti</h3>

                    @foreach ($orderProduct as $i)
                    
                        @if ($order->id == $i->order_id)
                            @foreach ($order->products as $o)
                            
                                @if ($o->id == $i->product_id)
                                <?php $name= $o->name ?>
                                @endif
                                
                            @endforeach
                            <?php
                                $arrO= json_decode($i->option); 
                                $arrA= json_decode($i->add); 
                                $arrD= json_decode($i->remove); 
                            ?>
                            <div class="product">
                                <div class="counter">* {{$i->quantity}}</div>              
                                <div class="name">{{$name}}</div>
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
                        @endif
                    @endforeach
                    <div class="t_price">€{{$order->tot_price / 100}}</div>
                    {{-- <div class="t_price">{{$order->total_pz_q}} pezzi taglio</div>
                    <div class="t_price">{{$order->total_pz_t}} pizze piatte</div>
                     --}}
                </section>
                <section class="myres-right">
                    @if(!$order->status !== 1)
                    <form class="w-100" action="{{ route('admin.orders.status') }}" method="POST">
                        @csrf
                        <input value="1" type="hidden" name="c_a">
                        <input value="{{$order->id}}" type="hidden" name="id">

                        <button type="submit" class="my_btn_3 w-100">Conferma</button>
                    </form>
                    @endif
                    @if(!$order->status == 0)
                    <form class="w-100" action="{{ route('admin.orders.status') }}" method="POST">
                        @csrf
                        <input value="0" type="hidden" name="c_a">
                        <input value="{{$order->id}}" type="hidden" name="id">
                        
                        <button type="submit" class="my_btn_2 w-100">Annulla</button>
                    </form>
                    @endif
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
            </div>
            <div class="visible">
                @if ($order->status == 2)
                <span>in elaborazione</span>
                @elseif ($order->status == 1)
                <span>confermato</span>
                @elseif ($order->status == 0)
                <span>annullato</span>
                @endif
            </div>
        </div>

        
    </div>

@endsection