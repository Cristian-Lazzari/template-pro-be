<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Conferma Email</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; color: #04001d; margin: 0; padding: 0;">
    <div style="max-width: 600px; margin: 10px auto; background-color: #ffffff; padding: 20px; border-radius: 8px; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);">
        
        <!-- Informazioni automatizzate -->
        <p style="font-size: 16px; line-height: 1.8; margin: 5px;">* questa email viene automaticamente generata dal sistema, si prega di non rispondere a questa email</p>
        <center>
            @if (config('configurazione.APP_URL') === 'https://db-demo3.future-plus.it')
                <img style="width: 80px; margin: 25px; background-color: #090333; border-radius: 26px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.272); padding: 2px; border: solid 2px #090333;" src="{{config('configurazione.APP_URL') . '/public/favicon.png'}}" alt="">
            @else
                <img style="width: 80px; margin: 25px; background-color: #090333; border-radius: 26px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.272); padding: 2px; border: solid 2px #090333;" src="{{config('configurazione.domain') . '/img/favicon.png'}}" alt="">
            @endif
        </center>


        <h1 style="color: #04001d; font-size: 24px; text-align: center; margin: 5px;">{{$content_mail['title']}}</h1>
        @if (isset($content_mail['subtitle']))
        <h4 style="color: #04001de3; font-size: 16px; line-height: 1.8; margin: 5px;">{{$content_mail['subtitle']}}</h4>
        @endif
        @php
            use Carbon\Carbon;
            $dateString = "31/03/2025 18:00"; 
            $formattedDate = Carbon::createFromFormat('d/m/Y H:i', $dateString)
                ->locale('it')
                ->translatedFormat('l j F \a\l\l\e H:i');
        @endphp 
        <!-- Data prenotata -->
        <p style="color: #04001d; font-size: 16px; line-height: 1.8; margin: 5px;">Data prenotata: 
            <strong>{{ ucfirst($formattedDate) }}</strong>
        </p>
        
        <!-- Elenco prodotti -->
        @if($content_mail['type'] == 'or')
        <h3 style="color: #04001d; font-size: 16px; line-height: 1.8; margin: 10px 0;">I prodotti:</h3>
            <div class="carrello" style="width: 100%;">
                @foreach ($content_mail['cart'] as $i)               
                    <?php
                        $arrO= json_decode($i->pivot->option); 
                        $arrA= json_decode($i->pivot->add); 
                        $arrD= json_decode($i->pivot->remove); 
                        // dd($i->pivot->option);
                        // dd($i->pivot->add);
                        //dd($i->pivot->quantity);

                    ?>
                    <div style="width: 100%; margin: 5px 0;">
                        <span style="color: #04001d; font-size: 18px; font-weight: bold;">* {{$i->pivot->quantity}}</span>
                        <span style="color: #04001d; font-size: 18px; font-weight: bold; margin-left: 10px;">{{$i->name}}</span>
                        <span style="color: #04001d; font-size: 16px; line-height: 1.8; margin-left: 10px;">€{{$i->price / 100 }}</span>
                    </div>
                    <br>
                    <div style="margin: 5px;">
                        <!-- Opzioni prodotto -->
                        @if (count($arrO))
                            <div style="margin: 5px;">
                                <h5 style="color: #04001d; font-size: 16px; line-height: 1.8; margin: 5px 0;">Opzioni:</h5>
                                @foreach ($arrO as $a)
                                    <span style="color: #04001d; font-size: 16px; line-height: 1.8; margin: 2px 0;">+ {{$a}} </span>
                                @endforeach
                            </div>
                        @endif
                        <div style="margin: 5px;">
                            <!-- Ingredienti extra -->
                            @if (count($arrA))
                                <div style="margin: 5px;">
                                    <h5 style="color: #04001d; font-size: 16px; line-height: 1.8; margin: 5px 0;">Ingredienti extra:</h5>
                                    @foreach ($arrA as $a)
                                        <span style="color: #04001d; font-size: 16px; line-height: 1.8; margin: 2px 0;">+ {{$a}}</span>
                                    @endforeach
                                </div>
                            @endif
                            <!-- Ingredienti rimossi -->
                            @if (count($arrD))
                                <div style="margin: 5px;">
                                    <h5 style="color: #04001d; font-size: 16px; line-height: 1.8; margin: 5px 0;">Ingredienti rimossi:</h5>
                                    @foreach ($arrD as $a)
                                        <span style="color: #04001d; font-size: 16px; line-height: 1.8; margin: 2px 0;">- {{$a}}</span>
                                    @endforeach       
                                </div>
                            @endif
                        </div>
                    </div>
                    <hr style="height: 1px; background-color: #04001da1; border: none; margin: 10px 0; order-radius: 20px">
                @endforeach
            
            </div>
            <!-- Indirizzo per la consegna -->
            @if (isset($content_mail['comune']))
                <h3 style="color: #04001d; font-size: 16px; line-height: 1.8; margin: 10px 0;">Indirizzo per la consegna:</h3>
                <p style="color: #04001d; font-size: 16px; line-height: 1.8; margin: 5px;">{{$content_mail['address']}}, {{$content_mail['address_n']}}, {{$content_mail['comune']}}</p>
                <p style="color: #04001d; font-size: 16px; line-height: 1.8; margin: 5px;">L'importo verra pagato al momento della consegna.</p>
            @endif
            <!-- Totale carrello -->
            <h4 style="color: #04001d; font-size: 16px; line-height: 1.8; margin: 5px;">Totale carrello: €{{$content_mail['total_price'] / 100}}</h4>
        
    
        @elseif($content_mail['type'] == 'res')

            <!-- Sala prenotata (se applicabile) -->
            @if (config('configurazione.double_t') && $content_mail['sala'] !== 0)
                <h3 style="color: #04001d; font-size: 16px; line-height: 1.8; margin: 10px 0;">Sala prenota: <strong>{{$content_mail['sala'] == 1 ? config('configurazione.set_time_dt')[0] : config('configurazione.set_time_dt')[1]}}</strong></h3>
            @endif

            <!-- Numero di persone -->
            @if (is_string($content_mail['n_person']))
                @php $n_person = json_decode($content_mail['n_person'], true); @endphp
                <h3 style="color: #04001d; font-size: 16px; line-height: 1.8; margin: 10px 0;">Numero di adulti: {{ $n_person['adult'] }}</h3>
                <h3 style="color: #04001d; font-size: 16px; line-height: 1.8; margin: 10px 0;">Numero di bambini: {{ $n_person['child'] }}</h3>
            @endif
        @endif

        <!-- Messaggio opzionale -->
        @if($content_mail['message'] !== NULL)
            <h4 style="color: #04001d; font-size: 16px; line-height: 1.8; margin: 5px;">Messaggio:</h4>
            <p style="color: #04001d; font-size: 16px; line-height: 1.8; margin: 5px;">{{$content_mail['message']}}</p>
        @endif


        <!-- Se destinatario è admin -->
        @if($content_mail['to'] == 'admin')             
            <!-- Bottone per chiamare -->
            <a href="tel:{{$content_mail['phone']}}" style="display: block; width: 80%; text-align: center; padding: 10px; background-color: #119b1a; color: white; text-decoration: none; border-radius: 5px; margin: 20px auto 0 auto;">Chiama {{$content_mail['name']}}</a>
            <!-- Bottone per visualizzare nella dashboard -->
            @if ($content_mail['type'] == 'or')
                <a href="{{config('configurazione.APP_URL')}}/admin/orders/{{$content_mail['order_id']}}" style="display: block; width: 80%; text-align: center; padding: 10px; background-color: #11289b; color: white; text-decoration: none; border-radius: 5px; margin: 20px auto 0 auto;">Visualizza nella Dashboard</a>
            @elseif($content_mail['type'] == 'res')
                <a href="{{config('configurazione.APP_URL')}}/admin/reservations/{{$content_mail['res_id']}}" style="display: block; width: 80%; text-align: center; padding: 10px; background-color: #11289b; color: white; text-decoration: none; border-radius: 5px; margin: 20px auto 0 auto;">Visualizza nella Dashboard</a>
            @endif
        @endif

        @if (isset($content_mail['whatsapp_message_id']) && config('configurazione.subscription') > 2 && $content_mail['to'] == 'user' && !in_array($content_mail['status'], [0,6]))
            <p style="line-height: 1.5; margin: 15px;" >Per annullare l'ordine o la prenotazione premi questo bottone </p>
            <p style="line-height: 1.5; margin: 15px;">
                <a href="{{config('configurazione.APP_URL')}}/api/client_default/?whatsapp_message_id={{$content_mail['whatsapp_message_id']}}" style="background-color: #9f2323f0; color: rgb(255, 255, 255); padding: 8px 17px; text-align: center; text-decoration: none; border-radius: 35px; font-size: 15px;">Annulla</a>
            </p>
        @endif

        
    </div>
    <!-- Footer -->
    <div style="width: 95%; margin: 50px auto 0; background-color: #04001d; color: white; padding: 10px; text-align: center; font-size: 12px;">
        @if ($content_mail['to'] == 'user' && $content_mail['status'] !== 0)
            <p style="color: #ffffff; font-size: 12px; line-height: 1.5; margin: 5px;">
                Contatta {{config('configurazione.APP_NAME')}} se desideri annullare o modificare la tua prenotazione:
            </p>
            <p style="color: #ffffff; line-height: 1.5; margin: 15px;">
                <a href="tel:{{$content_mail['admin_phone']}}" style="background-color: #ffffff; color: rgb(0, 0, 0); padding: 8px 12px; text-align: center; text-decoration: none; border-radius: 35px; font-size: 18px;">Chiama {{config('configurazione.APP_NAME')}}</a>
            </p>
        @endif
        <p style="color: #ffffff; font-size: 12px; line-height: 1.5; margin: 5px;">&copy; 2024 {{ config('configurazione.APP_NAME') }}. Tutti i diritti riservati.</p>
        <p style="color: #ffffff; font-size: 12px; line-height: 1.5; margin: 5px;" > Powered by <a style="color: white; text-decoration: none" href="https://future-plus.it">Future +</a></p>
    </div>
    
</body>
</html>
