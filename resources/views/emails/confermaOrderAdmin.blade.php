<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Conferma Email</title>
    <style>
        *{
            margin: 0;
            padding: 5px;
            box-sizing: border-box;
        }
        .products{
            width: 100%;
        }
        .line, .option, .add, .remove{
            width: 100%
        }
        .name, .counter{
            font-size: 18px;
            font-weight: bold;
        }

        hr{
            height: 2px;
        }
        img{
            width: 20%;
            margin: 0 auto;
            border-radius: 10px
        }
    </style>
</head>
<body>
    <p>* questa email viene automaticamente generata dal sistema, si prega di non rispondere a questa email</p>
@if ($content_mail['type'] == 'or')

    @if ($content_mail['to'] == 'admin')
        <h1>Il sign/gr {{ $content_mail['name'] }}, ha prenotato un asporto!</h1>
    @elseif($content_mail['to'] == 'user' && $content_mail['status'] == 2)
        <img src="https://db.dashboardristorante.it/public/images/or.png" alt="logo-locale">
        <h1>Ciao {{ $content_mail['name'] }}, grazie per aver prenotato tramite il nostro servizio asporto!</h1>
        <h4>Il tuo ordine è nella nostra coda, a breve riceverai l'esito del processamento</h4>
    @elseif($content_mail['to'] == 'user' && $content_mail['status'] == 1)
        <img src="https://db.dashboardristorante.it/public/images/or.png" alt="logo-locale">
        <h1>Ciao {{ $content_mail['name'] }}, ti informiamo che il tuo ordine è stato confermato!</h1>
    @elseif($content_mail['to'] == 'user' && $content_mail['status'] == 0)
        <img src="https://db.dashboardristorante.it/public/images/or.png" alt="logo-locale">
        <h1>Ciao {{ $content_mail['name'] }}, ci dispiace informarti che il tuo ordine è stato annullato!</h1>
    @endif
    <p>Data prenotata: {{ $content_mail['date_slot'] }}</p>
    <h3>I prodotti:</h3>
    <div class="products" >
        {{-- @foreach ($content_mail['cart'] as $p)
            <div class="product" style="width: 100%; margin-top: 20px; margin-bottom: 5px">
                <div class="line">
                    <span class="counter">* {{$p['counter']}}</span>
                    <span class="name">{{$p['name']}}</span>
                    <span class="price">€{{$p['price'] / 100}}</span>
                </div>
                <br>
                <div class="variation">
                    @if (count($p['option']) !==0)
                        <div class="option">
                            <h5>Opzioni aggiunte al prodotto:</h5>      
                            @foreach ($p['option'] as $var)
                                    + {{$var}}
                            @endforeach
                        </div>
                    @endif
                    @if (count($p['remove']) !==0)
                        <div class="remove">
                            <h5>Ingredienti tolti:</h5>      
                            @foreach ($p['remove'] as $var)
                                    - {{$var}}
                            @endforeach
                        </div>
                    @endif
                    @if (count($p['add']) !==0)
                        <div class="add">
                            <h5>Ingredienti aggiunti:</h5>
                            @foreach ($p['add'] as $var)
                                + {{$var}}
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
            <hr>
        @endforeach --}}
        @if ($content_mail['to'] == 'user' && ($content_mail['status'] == 0 || $content_mail['status'] == 1))
            @foreach ($content_mail['orderProduct'] as $i)          
                @if ($content_mail['order_id'] == $i->order_id)
                    @foreach ($content_mail['cart'] as $o)
                        @if ($o->id == $i->product_id)
                        <?php $name= $o->name ?>
                        @endif
                        
                    @endforeach
                    <?php
                        $arrO= json_decode($i->option); 
                        $arrA= json_decode($i->add); 
                        $arrD= json_decode($i->remove); 
                    ?>
                    
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
                @endif
            @endforeach
        @else
            @foreach ($content_mail['cart'] as $p)
                <div class="product" style="width: 100%; margin-top: 20px; margin-bottom: 5px">
                    <div class="line">
                        <span class="counter">* {{$p['counter']}}</span>
                        <span class="name">{{$p['name']}}</span>
                        <span class="price">€{{$p['price'] / 100}}</span>
                    </div>
                    <br>
                    <div class="variation">
                        @if (count($p['option']) !==0)
                            <div class="option">
                                <h5>Opzioni aggiunte al prodotto:</h5>      
                                @foreach ($p['option'] as $var)
                                        + {{$var}}
                                @endforeach
                            </div>
                        @endif
                        @if (count($p['remove']) !==0)
                            <div class="remove">
                                <h5>Ingredienti tolti:</h5>      
                                @foreach ($p['remove'] as $var)
                                        - {{$var}}
                                @endforeach
                            </div>
                        @endif
                        @if (count($p['add']) !==0)
                            <div class="add">
                                <h5>Ingredienti aggiunti:</h5>
                                @foreach ($p['add'] as $var)
                                    + {{$var}}
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
                <hr>
            @endforeach
        @endif
        
       
    </div>
    @if (isset($content_mail['comune']))
        <h3>Indirizzo per la consegna:</h3>
        <p>{{$content_mail['address']}}, {{$content_mail['address_n']}}, {{$content_mail['comune']}}</p>
        <p>L'importo verra pagato al momento della consegna.</p>
    @endif
    <h4>Totale carrello: €{{$content_mail['total_price'] / 100}}</h4>
    
    @if($content_mail['message'] !== NULL) <h4>Messaggio:</h4> <p>{{$content_mail['message']}}</p> @endif
    @if ($content_mail['to'] == 'user' && $content_mail['status'] !== 0)
        <p>
            <span>Contatta nome locale se desideri annullare la tua prenotazione:</span>
            <a href="tel:{{$content_mail['admin_phone']}}" class="call-btn">tocca o tieni premuto per chiamare Nome locale</a>
        </p>
    @elseif($content_mail['to'] == 'admin')
        <p>
            <span>Contatta {{$content_mail['name']}}</span>
            <a href="tel:{{$content_mail['phone']}}" class="call-btn">tocca o tieni premuto per chiamare {{$content_mail['name']}}</a>
        </p>
    @endif
    
@elseif($content_mail['type'] == 'res')

    @if ($content_mail['to'] == 'admin')
        <h1>Il sign/gr {{ $content_mail['name'] }}, ha prenotato un tavolo!</h1>
    @elseif($content_mail['to'] == 'user' && $content_mail['status'] == 2)
        <img src="https://db.dashboardristorante.it/public/images/or.png" alt="logo-locale">
        <h1>Ciao {{ $content_mail['name'] }}, grazie per aver prenotato un tavolo tramite il nostro sito web!</h1>
        <h4>La tua prenotazione è nella nostra coda, a breve riceverai l'esito del processamento</h4>
    @elseif($content_mail['to'] == 'user' && $content_mail['status'] == 1)
        <img src="https://db.dashboardristorante.it/public/images/or.png" alt="logo-locale">
        <h1>Ciao {{ $content_mail['name'] }}, ti informiamo che la tua prenotazione è stata confermata!</h1>
    @elseif($content_mail['to'] == 'user' && $content_mail['status'] == 0)
        <img src="https://db.dashboardristorante.it/public/images/or.png" alt="logo-locale">
        <h1>Ciao {{ $content_mail['name'] }}, ci dispiace informarti che la tua prenotazione è stata annullata!</h1>
    @endif
    <p>Data prenotata: {{ $content_mail['date_slot'] }}</p>
    <h3>Numer di ospiti: {{ $content_mail['n_person'] }}</h3>
    
    @if($content_mail['message'] !== NULL) <h4>Messaggio:</h4> <p>{{$content_mail['message']}}</p> @endif
    @if ($content_mail['to'] == 'user' && $content_mail['status'] !== 0)
        <p>
            <span>Contatta nome locale se desideri annullare la tua prenotazione:</span>
            <a href="tel:{{$content_mail['admin_phone']}}" class="call-btn">tocca o tieni premuto per chiamare Nome locale</a>
        </p>
    @elseif($content_mail['to'] == 'admin')
        <p>
            <span>Contatta {{$content_mail['name']}}</span>
            <a href="tel:{{$content_mail['phone']}}" class="call-btn">tocca o tieni premuto per chiamare {{$content_mail['name']}}</a>
        </p>
    @endif
@endif
    
</body>
</html>