@extends('layouts.base')

@section('contents')
@if (session('success'))
    @php
        $data = session('success')
    @endphp
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ $data }} 
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
@php
$pack = ['', 'Essentials', 'Work on', 'Boost up', 'Prova gratuita','Boost up +' ];
$adv = json_decode($setting['advanced']->property, 1);
$tavoliState = match ((int) $setting['Prenotazione Tavoli']['status']) {
    2 => ['label' => 'Online', 'tone' => 'active'],
    1 => ['label' => 'Telefono', 'tone' => 'warning'],
    default => ['label' => 'Off', 'tone' => 'off'],
};
$asportoState = match ((int) $setting['Prenotazione Asporti']['status']) {
    2 => ['label' => 'Online', 'tone' => 'active'],
    1 => ['label' => 'Telefono', 'tone' => 'warning'],
    default => ['label' => 'Off', 'tone' => 'off'],
};
$domicilioState = match ((int) $setting['Possibilità di consegna a domicilio']['status']) {
    1 => ['label' => 'Attivo', 'tone' => 'active'],
    default => ['label' => 'Off', 'tone' => 'off'],
};
$ferieSetting = json_decode($setting['Periodo di Ferie']['property'], true);
$ferieState = ((int) $setting['Periodo di Ferie']['status']) === 1
    ? ['label' => 'In ferie', 'tone' => 'warning']
    : ['label' => 'Operativo', 'tone' => 'active'];
$promoState = ((int) $setting['Promozione Tavoli']['status']) === 1
    ? ['label' => 'Attiva', 'tone' => 'active']
    : ['label' => 'Off', 'tone' => 'off'];
$defaultLang = strtoupper((string) config('configurazione.default_lang'));
$menuFixState = match ((string) ($adv['menu_fix_set'] ?? '0')) {
    '0' => ['label' => 'Fisso', 'tone' => 'neutral'],
    '1' => ['label' => 'Tutti', 'tone' => 'active'],
    '2' => ['label' => 'Carta', 'tone' => 'neutral'],
    default => ['label' => 'Default', 'tone' => 'neutral'],
};
$servicesState = match ((string) ($adv['services'] ?? '4')) {
    '2' => ['label' => 'Tavoli', 'tone' => 'active'],
    '3' => ['label' => 'Asporto', 'tone' => 'active'],
    '4' => ['label' => 'Tutti', 'tone' => 'active'],
    default => ['label' => 'Custom', 'tone' => 'warning'],
};
$doubleRoomState = ((int) ($adv['dt'] ?? 0)) === 1
    ? ['label' => 'Attiva', 'tone' => 'active']
    : ['label' => 'Off', 'tone' => 'off'];

@endphp

<div class="dash_page settings-page">
    <h1 class="settings-page__title">
        <i class="bi bi-gear-wide-connected"></i>
        {{__('admin.Impostazioni')}}
    </h1>
    <section class="settings-overview">
        <div class="settings-overview__intro">
            <p class="settings-kicker">Stato attuale</p>
            <h2>Stati attivi in evidenza</h2>
            <p class="settings-lead">Verde attivo, giallo attenzione, rosso spento.</p>
            <div class="settings-status-grid">
                <article class="settings-status-card">
                    <span>Tavoli</span>
                    <strong class="settings-state settings-state--{{ $tavoliState['tone'] }}">{{ $tavoliState['label'] }}</strong>
                </article>
                <article class="settings-status-card">
                    <span>Asporto</span>
                    <strong class="settings-state settings-state--{{ $asportoState['tone'] }}">{{ $asportoState['label'] }}</strong>
                </article>
                @if (config('configurazione.subscription') > 1)
                    <article class="settings-status-card">
                        <span>Domicilio</span>
                        <strong class="settings-state settings-state--{{ $domicilioState['tone'] }}">{{ $domicilioState['label'] }}</strong>
                    </article>
                @endif
                <article class="settings-status-card">
                    <span>Ferie</span>
                    <strong class="settings-state settings-state--{{ $ferieState['tone'] }}">{{ $ferieState['label'] }}</strong>
                </article>
                <article class="settings-status-card">
                    <span>Promo tavoli</span>
                    <strong class="settings-state settings-state--{{ $promoState['tone'] }}">{{ $promoState['label'] }}</strong>
                </article>
                <article class="settings-status-card">
                    <span>Lingua</span>
                    <strong class="settings-state settings-state--neutral">{{ $defaultLang }}</strong>
                </article>
                <article class="settings-status-card">
                    <span>Valuta</span>
                    <strong class="settings-state settings-state--neutral">{{ $activeCurrency['code'] }}</strong>
                </article>
            </div>
        </div>

        <div class="settings-overview__aside">
            <div class="targhetta">

                <a href="{{config('configurazione.domain')}}" class="img_bg">
                    <img src="{{config('configurazione.domain') . '/img/favicon.png'}}" alt="">
                </a>
                <a href="{{config('configurazione.domain')}}">
                    <h2 >{{config('configurazione.APP_NAME')}}</h2>
                </a>
                <a class="pack" href="https://future-plus.it/#pacchetti">
                    <img src="https://future-plus.it/img/favicon.png" alt="">
                    {{__('admin.Pacchetto')}}: {{$pack[config('configurazione.subscription')]}}</a>

            
            </div>
            <div class="settings-theme-card">
                <p class="settings-theme-card__eyebrow">Esperienza di lettura</p>
                <div class="theme my-4"> 
                    <p>{{__('admin.Tema')}}:
                        <strong id="light_s">{{__('admin.Scuro')}}</strong>
                        <strong id="dark_s">{{__('admin.Chiaro')}}</strong>
                    </p>
                    <button id="theme-toggle" class="my_btn_3">
                        <i id="dark" class="bi bi-moon-fill"></i>

                        <i id="light" class="bi bi-sun-fill"></i>
                    </button>
                </div>
                <p class="settings-theme-card__note">Il cambio tema resta immediato e non modifica il comportamento dei campi.</p>
            </div>
        </div>
    </section>
    
    
    <form class="setting settings-form" action="{{ route('admin.settings.updateAll')}}" method="POST" enctype="multipart/form-data">
        @csrf
        <section class="settings-panel settings-panel--primary">
            <div class="settings-panel__header">
                <p class="settings-kicker">Impostazioni rapide</p>
                <h2>Servizi, lingua e promozioni usate ogni giorno</h2>
                <p>Qui cambi le opzioni che incidono subito sul locale.</p>
            </div>

       <div class="set">
            <div class="set-cont">
                <div class="g_set">
                    <div class="settings-card-head">
                        <h5>{{__('admin.Lingua_di_default')}}</h5>
                        <span class="settings-state settings-state--neutral">{{ $defaultLang }}</span>
                    </div>
                    @php   $languages = json_decode($setting['Lingua']['property'], 1)['languages']; @endphp
                    <div class="radio-inputs">
                        @foreach ($languages as $l)
                            <label class="radio">
                                <input type="radio" name="defaultLang" @if(config('configurazione.default_lang') == $l) checked @endif  value="{{$l}}" >
                                <span class="name lang">
                                    {{$l}}
                                </span>
                            </label>
                        @endforeach
                    </div>
                    {{-- <div class="radio-inputs">
                        <select class="form-select" id="floatingSelectDisabled" name="defaultLang">
                            @foreach ($languages as $l)
                                <option  value="{{$l}}">{{$l}}</option>
                            
                        </select>
                    </div> --}}
                </div>
                <div class="g_set">
                    <div class="settings-card-head">
                        <h5>Valuta prezzi</h5>
                        <span class="settings-state settings-state--neutral">{{ $activeCurrency['code'] }}</span>
                    </div>
                    <div class="input-group">
                        <label class="input-group-text" for="currency_code">Valuta</label>
                        <select class="form-select" id="currency_code" name="currency_code">
                            @foreach ($supportedCurrencies as $currency)
                                <option value="{{ $currency['code'] }}" @selected($activeCurrency['code'] === $currency['code'])>
                                    {{ $currency['label'] }} ({{ $currency['code'] }} - {{ $currency['symbol'] }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <p class="settings-theme-card__note mt-3">Aggiorna la valuta usata per scrivere i prezzi e per i pagamenti online. I valori salvati non vengono convertiti automaticamente.</p>
                </div>
            </div>
            @php
                $asporto_p = json_decode($setting['Prenotazione Asporti']['property'] , 1);
                $domicilio_p = json_decode($setting['Possibilità di consegna a domicilio']['property'] , 1);
                //dd($domicilio_p['pay'])
            @endphp
            <div class="set">
                <div class="set-cont">
                    <div class="g_set">
                        <div class="settings-card-head">
                            <h5>{{__('admin.Tavoli')}}</h5>
                            <span class="settings-state settings-state--{{ $tavoliState['tone'] }}">{{ $tavoliState['label'] }}</span>
                        </div>
                        <div class="radio-inputs">
                            <label class="radio">
                                <input type="radio" name="tavoli_status"  @if($setting['Prenotazione Tavoli']['status'] == 0) checked  @endif value="0" >
                                <span class="name">
                                    <i class="bi bi-x-circle-fill"></i>
                                </span>
                            </label>
                            <label class="radio">
                                <input type="radio" name="tavoli_status"  @if($setting['Prenotazione Tavoli']['status'] == 1) checked  @endif value="1" >
                                <span class="name">
                                    <i class="bi bi-telephone-fill"></i>
                                </span>
                            </label>
                            @if (config('configurazione.subscription') > 1 )   
                            <label class="radio">
                                <input type="radio" name="tavoli_status"  @if($setting['Prenotazione Tavoli']['status'] == 2) checked  @endif value="2" >
                                <span class="name">
                                    <i class="bi bi-window-sidebar"></i>
                                </span>
                            </label>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="set">
                <div class="set-cont">
                    <div class="g_set">
                        <div class="settings-card-head">
                            <h5>{{__('admin.Asporto')}}</h5>
                            <span class="settings-state settings-state--{{ $asportoState['tone'] }}">{{ $asportoState['label'] }}</span>
                        </div>
                        <div class="radio-inputs">
                            <label class="radio">
                                <input type="radio" name="asporto_status"  @if($setting['Prenotazione Asporti']['status'] == 0) checked  @endif value="0" >
                                <span class="name">
                                    <i class="bi bi-x-circle-fill"></i>
                                </span>
                            </label>
                            <label class="radio">
                                <input type="radio" name="asporto_status"  @if($setting['Prenotazione Asporti']['status'] == 1) checked  @endif value="1" >
                                <span class="name">
                                    <i class="bi bi-telephone-fill"></i>
                                </span>
                            </label>
                            @if (config('configurazione.subscription') > 1)   
                            <label class="radio">
                                <input type="radio" name="asporto_status"  @if($setting['Prenotazione Asporti']['status'] == 2) checked  @endif value="2" >
                                <span class="name">
                                    <i class="bi bi-window-sidebar"></i>
                                </span>
                            </label>
                            @endif
                        </div>
                    </div>
                    @if (config('configurazione.subscription') > 2)
                    <div class="g_set">
                        <h5>{{ __('admin.Pagamento') }}</h5>
                        <div class="radio-inputs">
                            <label class="radio">
                                <input type="radio" name="asporto_pay" @if($asporto_p['pay'] == 0) checked  @endif value="0" >
                                <span class="name">
                                    <i class="bi bi-cash-coin"></i>
                                </span>
                            </label>
                            <label class="radio">
                                <input type="radio" name="asporto_pay" @if($asporto_p['pay'] == 1) checked  @endif value="1" >
                                <span class="name">
                                    <i class="bi bi-cash-coin"></i>
                                    <i class="bi bi-credit-card-fill"></i>
                                </span>
                            </label>
                            <label class="radio">
                                <input type="radio" name="asporto_pay" @if($asporto_p['pay'] == 2) checked  @endif value="2" >
                                <span class="name">
                                    <i class="bi bi-credit-card-fill"></i>
                                </span>
                            </label>
                        </div>
                    </div>
                    @endif
                    @if(config('configurazione.subscription') > 1)  
                    <div class="g_set">  
                        <div class="input-group ">
                            <label class="input-group-text">{{__('admin.Prezzo_minimo')}}</label>
                            <input type="number" class="form-control"  name="min_price_a" step="{{ \App\Support\Currency::inputStep() }}" value="{{ \App\Support\Currency::formatForInput($asporto_p['min_price'] ?? 0) }}">
                        </div>
                    </div>
                    @endif
                    
                </div>
            </div>
            @if (config('configurazione.subscription') > 1)
            <div class="set">
                <div class="set-cont">
                    <div class="g_set">
                        <div class="settings-card-head">
                            <h5>{{ __('admin.Domicilio') }}</h5>
                            <span class="settings-state settings-state--{{ $domicilioState['tone'] }}">{{ $domicilioState['label'] }}</span>
                        </div>
                        <div class="radio-inputs">
                            <label class="radio">
                                <input type="radio" name="domicilio_status"  @if($setting['Possibilità di consegna a domicilio']['status'] == 0) checked  @endif value="0" >
                                <span class="name">
                                    <i class="bi bi-x-circle-fill"></i>
                                </span>
                            </label>
                            <label class="radio">
                                <input type="radio" name="domicilio_status"  @if($setting['Possibilità di consegna a domicilio']['status'] == 1) checked  @endif value="1" >
                                <span class="name">
                                    <i class="bi bi-check-circle-fill"></i>
                                </span>
                            </label>
                        </div>
                    </div>
                    @if (config('configurazione.subscription') > 2)
                    <div class="g_set">
                        <h5>{{ __('admin.Pagamento') }}</h5>
                        <div class="radio-inputs">
                            <label class="radio">
                                <input type="radio" name="domicilio_pay" @if($domicilio_p['pay'] == 0) checked  @endif value="0" >
                                <span class="name">
                                    <i class="bi bi-cash-coin"></i>
                                </span>
                            </label>
                            <label class="radio">
                                <input type="radio" name="domicilio_pay" @if($domicilio_p['pay'] == 1) checked  @endif value="1" >
                                <span class="name">
                                    <i class="bi bi-cash-coin"></i>
                                    <i class="bi bi-credit-card-fill"></i>
                                </span>
                            </label>
                            <label class="radio">
                                <input type="radio" name="domicilio_pay" @if($domicilio_p['pay'] == 2) checked  @endif value="2" >
                                <span class="name">
                                    <i class="bi bi-credit-card-fill"></i>
                                </span>
                            </label>
                        </div>
                    </div>
                    @endif
                    <div class="input-group ">
                        <label class="input-group-text" id="basic-addon1">{{__('admin.Prezzo_minimo')}}</label>
                        <input type="number" class="form-control"  name="min_price_d" step="{{ \App\Support\Currency::inputStep() }}" value="{{ \App\Support\Currency::formatForInput($domicilio_p['min_price'] ?? 0) }}">
                    </div>
                    <div class="input-group ">
                        <label class="input-group-text" id="basic-addon1">{{__('admin.Prezzo_consegna')}}</label>
                        <input type="number" class="form-control"  name="delivery_cost" step="{{ \App\Support\Currency::inputStep() }}" value="{{ \App\Support\Currency::formatForInput($domicilio_p['delivery_cost'] ?? 0) }}">
                    </div>
                </div>
            </div>
            @endif
            @php
                $setting['Periodo di Ferie']['property'] = json_decode($setting['Periodo di Ferie']['property'], true);
            @endphp
            <div class="set">
                <div class="set-cont">
                    <div class="g_set">
                        <div class="settings-card-head">
                            <h5>{{ __('admin.Ferie') }}</h5>
                            <span class="settings-state settings-state--{{ $ferieState['tone'] }}">{{ $ferieState['label'] }}</span>
                        </div>
                        <div class="radio-inputs">
                            <label class="radio">
                                <input type="radio" name="ferie_status"  @if($setting['Periodo di Ferie']['status'] == 0) checked  @endif value="0" >
                                <span class="name">{{__('admin.A_lavoro')}}</span>
                            </label>
                            <label class="radio">
                                <input type="radio" name="ferie_status"  @if($setting['Periodo di Ferie']['status'] == 1) checked  @endif value="1" >
                                <span class="name">{{__('admin.In_ferie')}}</span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="input-group flex-nowrap">
                        <label for="form" class="input-group-text">{{__('admin.Da')}}</label>
                        <input name="from" id="form" type="date" class="form-control" placeholder="da" @if($setting['Periodo di Ferie']['property']['from'] !== '') value="{{$setting['Periodo di Ferie']['property']['from']}}"  @endif>
                        <label for="to" class="input-group-text">{{__('admin.A')}}</label>
                        <input name="to" id="to" type="date" class="form-control" placeholder="da" @if($setting['Periodo di Ferie']['property']['to'] !== '') value="{{$setting['Periodo di Ferie']['property']['to']}}"  @endif>
                    </div>
                </div>
            </div>
            <div class="set">
                @php
                    $promo_table = json_decode($setting['Promozione Tavoli']['property'], true);
                @endphp
                
                <div class="set-cont promo_set">
                    <div class="g_set">
                        <div class="settings-card-head">
                            <h5>
                                <i class="bi bi-gift-fill"></i>
                                {{ __('admin.Promozione') }} 
                            </h5>
                            <span class="settings-state settings-state--{{ $promoState['tone'] }}">{{ $promoState['label'] }}</span>
                        </div>
                        <div class="radio-inputs">
                            <label class="radio">
                                <input type="radio" name="table_promo"  @if($setting['Promozione Tavoli']['status']== 0) checked  @endif value="0" >
                                <span class="name">
                                    <i class="bi bi-x-circle-fill"></i>
                                </span>
                            </label>
                            <label class="radio">
                                <input type="radio" name="table_promo"  @if($setting['Promozione Tavoli']['status']== 1) checked  @endif value="1" >
                                <span class="name">
                                    <i class="bi bi-check-circle-fill"></i>
                                </span>
                            </label>
                        </div>
                    </div>
                    <div class=" promo_b">
                        <label class="input-group-text" >{{__('admin.Titolo')}}</label>
                        <input type="text" class="form-control"  name="promo_table_title" value="{{$promo_table['title']}}">

                        <label class="input-group-text" >{{__('admin.Corpo')}}</label>
                        <textarea type="text" class="form-control"  cols="10" rows="10"  name="promo_table_body">{{$promo_table['body']}}</textarea>
                    </div>
                </div>
            </div>
            <button type="submit" class="my_btn_1 my_btn_2 w-75 m-auto">{{__('admin.Aggiorna')}}</button>
            
        </div>
        </section>

        <section class="settings-panel settings-panel--secondary">
            <div class="settings-panel__header">
                <p class="settings-kicker">Dettagli del locale</p>
                <h2>Dati pubblici, contatti e copertura del servizio</h2>
                <p>Apri solo la sezione che devi aggiornare.</p>
            </div>
        <div class="bottom-set">
            <div class="accordion accordion-flush" id="accordionFlushExample">
                <div class="accordion-item">
                    
                    <h4 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseOne" aria-expanded="false" aria-controls="flush-collapseOne">
                            {{__('admin.set_1')}}
                        </button>
                    </h4>
                    <div id="flush-collapseOne" class="accordion-collapse collapse" data-bs-parent="#accordionFlushExample">
                        <div class="accordion-body">
                            @php 
                                $property_orari = json_decode($setting['Orari di attività']['property'], true);
                                $property_posizione = json_decode($setting['Posizione']['property'], true);
                                $property_contatti = json_decode($setting['Contatti']['property'], true);
                            @endphp
                            <section class="activity-day">
                                @php
                                $days = [
                                    'lunedì' => 0,
                                    'martedì' => 1,
                                    'mercoledì' => 2,
                                    'giovedì' => 3,
                                    'venerdì' => 4,
                                    'sabato' => 5,
                                    'domenica' => 6,
                                ];
                                @endphp

                                @foreach ($days as $giorno => $index)

                                @php
                                    $label = \Carbon\Carbon::now()
                                        ->startOfWeek(\Carbon\Carbon::MONDAY)
                                        ->addDays($index)
                                        ->locale(app()->getLocale())
                                        ->isoFormat('dddd');
                                @endphp

                                <div class="input-group">
                                    <label for="{{$giorno}}" class="input-group-text">
                                        {{ ucfirst($label) }}
                                    </label>

                                    <input
                                        id="{{$giorno}}"
                                        type="text"
                                        class="form-control"
                                        placeholder="--:-- / --:--"
                                        name="{{$giorno}}"
                                        value="{{ $property_orari[$giorno] ?? '' }}"
                                        aria-label="{{$label}}"
                                    >
                                </div>

                                @endforeach
                            </section>
                            <button type="submit" class="my_btn_1 my_btn_2">{{__('admin.Aggiorna')}}</button>
                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <h4 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseTwo" aria-expanded="false" aria-controls="flush-collapseTwo">
                            {{__('admin.set_2')}}
                        </button>
                    </h4>
                    <div id="flush-collapseTwo" class="accordion-collapse collapse" data-bs-parent="#accordionFlushExample">
                        <div class="accordion-body">
                            <section>

                                @if(isset($property_posizione["foto_maps"]) && $property_posizione['foto_maps'] !== "")
                                    <img class="w-100 rounded mb-2" src="{{ asset('public/storage/' . $property_posizione['foto_maps']) }}" alt="{{ $property_posizione['foto_maps'] }}">
                                @endif
                                <div class="input-group ">    
                                    <input type="file" id="file-input" name="foto_maps">
                                </div>
                                <div class="input-group ">
                                    <label class="input-group-text" id="basic-addon1">{{__('admin.Link_Google_Maps')}}</label>
                                    <input type="text" class="form-control"  name="link_maps" @if($property_posizione) value="{{ $property_posizione['link_maps'] }}" @endif>
                                </div>
                                <div class="input-group ">
                                    <label class="input-group-text" id="basic-addon1">{{__('admin.Indirizzo')}}</label>
                                    <input type="text" class="form-control"  name="indirizzo" @if($property_posizione) value="{{ $property_posizione['indirizzo'] }}" @endif>
                                </div>          
                            </section>
                            <button type="submit" class="my_btn_1 my_btn_2">{{__('admin.Aggiorna')}}</button>
                        </div>
                    </div>
                </div>
                <div class="accordion-item"> 
                    <h4 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseThree" aria-expanded="false" aria-controls="flush-collapseThree">
                            {{__('admin.set_3')}}
                        </button>
                    </h4>
                    <div id="flush-collapseThree" class="accordion-collapse collapse" data-bs-parent="#accordionFlushExample">
                        <div class="accordion-body">
                            <section>
                                <div class="input-group ">
                                    <label for="telefono" class="input-group-text">{{__('admin.Telefono')}}</label>
                                    <input type="text" class="form-control"  name="telefono" @if($property_contatti) value="{{ $property_contatti['telefono'] }}" @endif>
                                </div>
                                <div class="input-group ">
                                    <label for="email" class="input-group-text">{{__('admin.Email')}}</label>
                                    <input type="text" class="form-control"  name="email" @if($property_contatti) value="{{ $property_contatti['email'] }}" @endif>
                                </div>        
                                <div class="input-group ">
                                    <label for="instagram" class="input-group-text">
                                        <i class="bi bi-instagram"></i>
                                    </label>
                                    <input type="text" class="form-control"  placeholder="{{__('admin.Link_instagram')}}" name="instagram" @if(isset($property_contatti['instagram'])) value="{{ $property_contatti['instagram'] }}" @endif>
                                </div>        
                                <div class="input-group ">
                                    <label for="facebook" class="input-group-text">
                                        <i class="bi bi-facebook"></i>
                                    </label>
                                    <input type="text" class="form-control" placeholder="{{__('admin.Link_facebook')}}" name="facebook" @if(isset($property_contatti['facebook'])) value="{{ $property_contatti['facebook'] }}" @endif>
                                </div>        
                                <div class="input-group ">
                                    <label for="tiktok" class="input-group-text">
                                        <i class="bi bi-tiktok"></i>
                                    </label>
                                    <input type="text" class="form-control"  placeholder="{{__('admin.Link_tiktok')}}" name="tiktok" @if(isset($property_contatti['tiktok'])) value="{{ $property_contatti['tiktok'] }}" @endif>
                                </div>        
                                <div class="input-group ">
                                    <label for="youtube" class="input-group-text">
                                        <i class="bi bi-youtube"></i>
                                    </label>
                                    <input type="text" class="form-control"  placeholder="{{__('admin.Link_youtube')}}" name="youtube" @if(isset($property_contatti['youtube'])) value="{{ $property_contatti['youtube'] }}" @endif>
                                </div>        
                                <div class="input-group ">
                                    <label for="whatsapp" class="input-group-text">
                                        <i class="bi bi-whatsapp"></i>
                                    </label>
                                    <input type="text" class="form-control" placeholder="+39001110000"  name="whatsapp" @if(isset($property_contatti['whatsapp'])) value="{{ $property_contatti['whatsapp'] }}" @endif>
                                </div>        
                            </section>
                            <button type="submit" class="my_btn_1 my_btn_2">{{__('admin.Aggiorna')}}</button>
                        </div>
                    </div>
                </div>
                
                @if (config('configurazione.subscription') > 1)
                <div class="accordion-item">
                    @csrf
                    <h4 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseFour" aria-expanded="false" aria-controls="flush-collapseFour">
                            {{__('admin.set_4')}}
                        </button>
                    </h4>
                    <div id="flush-collapseFour" class="accordion-collapse collapse" data-bs-parent="#accordionFlushExample">
                        <div class="accordion-body">                            
                            @php
                                if (is_string($setting['Comuni per il domicilio']['property'])) {
                                    $setting['Comuni per il domicilio']['property'] = json_decode($setting['Comuni per il domicilio']['property'], true);
                                } 
                            @endphp

                            <div class="address"> 
                                @foreach ($setting['Comuni per il domicilio']['property'] as $i)
                                    <span class="w-100">
                                        ({{$i['provincia']}})
                                        {{$i['comune']}} -
                                        {{$i['cap']}} -
                                        {{ $i['price'] ? \App\Support\Currency::formatCents($i['price']) : '' }}
                                    </span>    
                                @endforeach
                            </div>   
                            <div class="actions">
                                <button type="button" class=" my_btn_1 " data-bs-toggle="modal" data-bs-target="#staticBackdrop">{{__('admin.Aggiungi')}}</button>
                                <button type="button" class="my_btn_1 trash" data-bs-toggle="modal" data-bs-target="#staticBackdrop1">{{__('admin.Rimuovi')}}</button>
                            </div>                       
                        </div>
                    </div>
                </div>
                @endif 
                @if (config('configurazione.subscription') > 2)

                <div class="accordion-item">
                    @csrf
                    <h4 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseFive" aria-expanded="false" aria-controls="flush-collapseFive">
                            {{__('admin.set_5')}}
                        </button>
                    </h4>
                    <div id="flush-collapseFive" class="accordion-collapse collapse" data-bs-parent="#accordionFlushExample">
                        <div class="accordion-body">                            
                            @php
                                if (is_string($setting['wa']['property'])) {
                                    $setting['wa']['property'] = json_decode($setting['wa']['property'], true);
                                } 
                                //  dd($setting['wa']['property'])
                            @endphp

                
                            
                            <div class="address"> 
                                @foreach ($setting['wa']['property']['numbers'] as $i)
                                    <span class="">
                                        {{ $i }}
                                    </span>    
                                @endforeach
                            </div>      
                            <div class="actions">
                                <button type="button" class=" my_btn_1 " data-bs-toggle="modal" data-bs-target="#staticBackdrop2">{{__('admin.Modifica')}}</button>
                            </div>                    
                        </div>
                    </div>
                </div> 
                @endif
            </div>
        </div>
        </section>
        <div class="actions settings-form-actions">
            <button type="button" class=" my_btn_3 m-auto" data-bs-toggle="modal" data-bs-target="#staticBackdropav">
                <i class="bi bi-sliders"></i>
                {{__('admin.Impostazioni_a')}}</button>
        </div>   

    </form>


    <div class="modal fade" id="staticBackdropav" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropavLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable large_m settings-modal-dialog">
            <form action="{{ route('admin.settings.advanced')}}" method="POST" class="w-100">
                @csrf
                <x-dashboard.action-modal
                    title-id="staticBackdropavLabel"
                    class="s_advanced settings-advanced-modal"
                    title="{{ __('admin.Impostazioni_a') }}"
                    eyebrow="Controlli avanzati"
                    tone="mint"
                    description="Raccoglie i settaggi piu sensibili della dashboard: menu, servizi, sale e dati legali."
                >
                    <x-slot name="titleIcon">
                        <i class="bi bi-sliders"></i>
                    </x-slot>

                    <div class="top-set_a">
                        <div class="set_a">
                            <div class="settings-card-head">
                                <h4>{{__('admin.Gestione_menu')}}</h4>
                                <span class="settings-state settings-state--{{ $menuFixState['tone'] }}">{{ $menuFixState['label'] }}</span>
                            </div>
                            <div class="radio-inputs">
                                <label class="radio">
                                    <input @checked($adv['menu_fix_set']== '0') type="radio" name="menu_fix_set" value="0" >
                                    <span class="name">{{__('admin.Menu_fisso')}}</span>
                                </label>
                                <label class="radio">
                                    <input @checked($adv['menu_fix_set']== '1') type="radio" name="menu_fix_set" value="1" >
                                    <span class="name">{{__('admin.Tutti')}}</span>
                                </label>
                                <label class="radio">
                                    <input @checked($adv['menu_fix_set']== '2') type="radio" name="menu_fix_set" value="2" >
                                    <span class="name">{{__('admin.Menu_alla_carta')}}</span>
                                </label>
                            </div>
                        </div>
                        <div class="set_a">
                            <div class="settings-card-head">
                                <h4>{{__('admin.Servizi_attivi')}}</h4>
                                <span class="settings-state settings-state--{{ $servicesState['tone'] }}">{{ $servicesState['label'] }}</span>
                            </div>
                            <div class="radio-inputs">
                                <label class="radio">
                                    <input class="critical-radio1" @checked($adv['services']== '3') type="radio" name="services" value="3" >
                                    <span class="name">{{__('admin.Asporto')}}</span>
                                </label>
                                <label class="radio">
                                    <input class="critical-radio1" @checked($adv['services']== '4') type="radio" name="services" value="4" >
                                    <span class="name">{{__('admin.Tutti')}}</span>
                                </label>
                                <label class="radio">
                                    <input class="critical-radio1" @checked($adv['services']== '2') type="radio" name="services" value="2" >
                                    <span class="name">{{__('admin.Tavoli')}}</span>
                                </label>
                                <input type="hidden" id="attivo-originale1" value="{{$adv['services']}}">
                            </div>
                        </div>

                        <div class="set_a last">
                            <div class="settings-card-head">
                                <h4>{{__('admin.Doppia_sala')}}</h4>
                                <span class="settings-state settings-state--{{ $doubleRoomState['tone'] }}">{{ $doubleRoomState['label'] }}</span>
                            </div>
                            <div class="radio-inputs">
                                <label class="radio">
                                    <input class="critical-radio3" @checked($adv['dt']== 0) type="radio" name="dt" value="0" >
                                    <span class="name">Off</span>
                                </label>
                                <label class="radio">
                                    <input class="critical-radio3" @checked($adv['dt']== 1) type="radio" name="dt" value="1" >
                                    <span class="name">On</span>
                                </label>
                                <input type="hidden" id="attivo-originale3" value="{{$adv['dt']}}">
                            </div>
                            <div class="split">
                                <div class="input_label">
                                    <label class="" id="basic-addon1">{{__('admin.Sala_1')}}</label>
                                    <input type="text" class="" name="sala_1" value="{{$adv['sala_1']}}">
                                </div>
                                <div class="input_label">
                                    <label class="" id="basic-addon1">{{__('admin.Sala_2')}}</label>
                                    <input type="text" class="" name="sala_2" value="{{$adv['sala_2']}}">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="more_info">
                        <h4>{{__('admin.Info_legali')}}</h4>
                        <div class="split">
                            <div class="input_label">
                                <label class="" id="basic-addon1">{{__('admin.Ragione_sociale')}}</label>
                                <input type="text" name="r_sociale" value="{{$adv['r_sociale']}}">
                            </div>
                            <div class="input_label">
                                <label class="" id="basic-addon1">{{__('admin.Piva')}}</label>
                                <input type="text" name="p_iva" value="{{$adv['p_iva']}}">
                            </div>

                        </div>
                        <div class="split">
                            <div class="input_label">
                                <label class="" id="basic-addon1">{{__('admin.Codice_rea')}}</label>
                                <input type="text" name="c_rea" value="{{$adv['c_rea']}}">
                            </div>
                            <div class="input_label">
                                <label class="" id="basic-addon1">{{__('admin.Capitale_sociale')}}</label>
                                <input type="number" name="c_sociale" value="{{$adv['c_sociale']}}">
                            </div>
                        </div>

                        <div class="input_label ">
                            <label class="" id="basic-addon1">{{__('admin.Codice_ateco')}}</label>
                            <input type="text" name="c_ateco" value="{{isset($adv['c_ateco']) ? $adv['c_ateco'] : ''}}">
                        </div>
                        <div class="input_label ">
                            <label class="" id="basic-addon1">{{__('admin.Iscrizione_imprese')}}</label>
                            <input type="text" name="u_imprese" value="{{$adv['u_imprese']}}">
                        </div>
                        <div class="input_label method ">
                            <label class="" id="basic-addon1">{{__('admin.Metodi_pagamento')}}</label>
                            <div class="method_cont">
                            <input class="btn-check" type="checkbox" name="method[]" id="m_1" value="1" @if (in_array(1, $adv['method'])) checked @endif>
                            <label class="btn btn-outline-dark" for="m_1">
                                <svg class="payment-icon" xmlns="http://www.w3.org/2000/svg" role="img" aria-labelledby="pi-american_express" viewBox="0 0 38 24" width="38" height="24"><title id="pi-american_express">American Express</title><path fill="#000" d="M35 0H3C1.3 0 0 1.3 0 3v18c0 1.7 1.4 3 3 3h32c1.7 0 3-1.3 3-3V3c0-1.7-1.4-3-3-3Z" opacity=".07"></path><path fill="#006FCF" d="M35 1c1.1 0 2 .9 2 2v18c0 1.1-.9 2-2 2H3c-1.1 0-2-.9-2-2V3c0-1.1.9-2 2-2h32Z"></path><path fill="#FFF" d="M22.012 19.936v-8.421L37 11.528v2.326l-1.732 1.852L37 17.573v2.375h-2.766l-1.47-1.622-1.46 1.628-9.292-.02Z"></path><path fill="#006FCF" d="M23.013 19.012v-6.57h5.572v1.513h-3.768v1.028h3.678v1.488h-3.678v1.01h3.768v1.531h-5.572Z"></path><path fill="#006FCF" d="m28.557 19.012 3.083-3.289-3.083-3.282h2.386l1.884 2.083 1.89-2.082H37v.051l-3.017 3.23L37 18.92v.093h-2.307l-1.917-2.103-1.898 2.104h-2.321Z"></path><path fill="#FFF" d="M22.71 4.04h3.614l1.269 2.881V4.04h4.46l.77 2.159.771-2.159H37v8.421H19l3.71-8.421Z"></path><path fill="#006FCF" d="m23.395 4.955-2.916 6.566h2l.55-1.315h2.98l.55 1.315h2.05l-2.904-6.566h-2.31Zm.25 3.777.875-2.09.873 2.09h-1.748Z"></path><path fill="#006FCF" d="M28.581 11.52V4.953l2.811.01L32.84 9l1.456-4.046H37v6.565l-1.74.016v-4.51l-1.644 4.494h-1.59L30.35 7.01v4.51h-1.768Z"></path></svg>
                            </label>
                            <input class="btn-check" type="checkbox" name="method[]" id="m_2" value="2" @if (in_array(2, $adv['method'])) checked @endif>
                            <label class="btn btn-outline-dark" for="m_2">
                                <svg class="payment-icon" version="1.1" xmlns="http://www.w3.org/2000/svg" role="img" x="0" y="0" width="38" height="24" viewBox="0 0 165.521 105.965" xml:space="preserve" aria-labelledby="pi-apple_pay"><title id="pi-apple_pay">Apple Pay</title><path fill="#000" d="M150.698 0H14.823c-.566 0-1.133 0-1.698.003-.477.004-.953.009-1.43.022-1.039.028-2.087.09-3.113.274a10.51 10.51 0 0 0-2.958.975 9.932 9.932 0 0 0-4.35 4.35 10.463 10.463 0 0 0-.975 2.96C.113 9.611.052 10.658.024 11.696a70.22 70.22 0 0 0-.022 1.43C0 13.69 0 14.256 0 14.823v76.318c0 .567 0 1.132.002 1.699.003.476.009.953.022 1.43.028 1.036.09 2.084.275 3.11a10.46 10.46 0 0 0 .974 2.96 9.897 9.897 0 0 0 1.83 2.52 9.874 9.874 0 0 0 2.52 1.83c.947.483 1.917.79 2.96.977 1.025.183 2.073.245 3.112.273.477.011.953.017 1.43.02.565.004 1.132.004 1.698.004h135.875c.565 0 1.132 0 1.697-.004.476-.002.952-.009 1.431-.02 1.037-.028 2.085-.09 3.113-.273a10.478 10.478 0 0 0 2.958-.977 9.955 9.955 0 0 0 4.35-4.35c.483-.947.789-1.917.974-2.96.186-1.026.246-2.074.274-3.11.013-.477.02-.954.022-1.43.004-.567.004-1.132.004-1.699V14.824c0-.567 0-1.133-.004-1.699a63.067 63.067 0 0 0-.022-1.429c-.028-1.038-.088-2.085-.274-3.112a10.4 10.4 0 0 0-.974-2.96 9.94 9.94 0 0 0-4.35-4.35A10.52 10.52 0 0 0 156.939.3c-1.028-.185-2.076-.246-3.113-.274a71.417 71.417 0 0 0-1.431-.022C151.83 0 151.263 0 150.698 0z"></path><path fill="#FFF" d="M150.698 3.532l1.672.003c.452.003.905.008 1.36.02.793.022 1.719.065 2.583.22.75.135 1.38.34 1.984.648a6.392 6.392 0 0 1 2.804 2.807c.306.6.51 1.226.645 1.983.154.854.197 1.783.218 2.58.013.45.019.9.02 1.36.005.557.005 1.113.005 1.671v76.318c0 .558 0 1.114-.004 1.682-.002.45-.008.9-.02 1.35-.022.796-.065 1.725-.221 2.589a6.855 6.855 0 0 1-.645 1.975 6.397 6.397 0 0 1-2.808 2.807c-.6.306-1.228.511-1.971.645-.881.157-1.847.2-2.574.22-.457.01-.912.017-1.379.019-.555.004-1.113.004-1.669.004H14.801c-.55 0-1.1 0-1.66-.004a74.993 74.993 0 0 1-1.35-.018c-.744-.02-1.71-.064-2.584-.22a6.938 6.938 0 0 1-1.986-.65 6.337 6.337 0 0 1-1.622-1.18 6.355 6.355 0 0 1-1.178-1.623 6.935 6.935 0 0 1-.646-1.985c-.156-.863-.2-1.788-.22-2.578a66.088 66.088 0 0 1-.02-1.355l-.003-1.327V14.474l.002-1.325a66.7 66.7 0 0 1 .02-1.357c.022-.792.065-1.717.222-2.587a6.924 6.924 0 0 1 .646-1.981c.304-.598.7-1.144 1.18-1.623a6.386 6.386 0 0 1 1.624-1.18 6.96 6.96 0 0 1 1.98-.646c.865-.155 1.792-.198 2.586-.22.452-.012.905-.017 1.354-.02l1.677-.003h135.875"></path><g><g><path fill="#000" d="M43.508 35.77c1.404-1.755 2.356-4.112 2.105-6.52-2.054.102-4.56 1.355-6.012 3.112-1.303 1.504-2.456 3.959-2.156 6.266 2.306.2 4.61-1.152 6.063-2.858"></path><path fill="#000" d="M45.587 39.079c-3.35-.2-6.196 1.9-7.795 1.9-1.6 0-4.049-1.8-6.698-1.751-3.447.05-6.645 2-8.395 5.1-3.598 6.2-.95 15.4 2.55 20.45 1.699 2.5 3.747 5.25 6.445 5.151 2.55-.1 3.549-1.65 6.647-1.65 3.097 0 3.997 1.65 6.696 1.6 2.798-.05 4.548-2.5 6.247-5 1.95-2.85 2.747-5.6 2.797-5.75-.05-.05-5.396-2.101-5.446-8.251-.05-5.15 4.198-7.6 4.398-7.751-2.399-3.548-6.147-3.948-7.447-4.048"></path></g><g><path fill="#000" d="M78.973 32.11c7.278 0 12.347 5.017 12.347 12.321 0 7.33-5.173 12.373-12.529 12.373h-8.058V69.62h-5.822V32.11h14.062zm-8.24 19.807h6.68c5.07 0 7.954-2.729 7.954-7.46 0-4.73-2.885-7.434-7.928-7.434h-6.706v14.894z"></path><path fill="#000" d="M92.764 61.847c0-4.809 3.665-7.564 10.423-7.98l7.252-.442v-2.08c0-3.04-2.001-4.704-5.562-4.704-2.938 0-5.07 1.507-5.51 3.82h-5.252c.157-4.86 4.731-8.395 10.918-8.395 6.654 0 10.995 3.483 10.995 8.89v18.663h-5.38v-4.497h-.13c-1.534 2.937-4.914 4.782-8.579 4.782-5.406 0-9.175-3.222-9.175-8.057zm17.675-2.417v-2.106l-6.472.416c-3.64.234-5.536 1.585-5.536 3.95 0 2.288 1.975 3.77 5.068 3.77 3.95 0 6.94-2.522 6.94-6.03z"></path><path fill="#000" d="M120.975 79.652v-4.496c.364.051 1.247.103 1.715.103 2.573 0 4.029-1.09 4.913-3.899l.52-1.663-9.852-27.293h6.082l6.863 22.146h.13l6.862-22.146h5.927l-10.216 28.67c-2.34 6.577-5.017 8.735-10.683 8.735-.442 0-1.872-.052-2.261-.157z"></path></g></g></svg>
                            </label>
                            <input class="btn-check" type="checkbox" name="method[]" id="m_3" value="3" @if (in_array(3, $adv['method'])) checked @endif>
                            <label class="btn btn-outline-dark" for="m_3">
                                <svg class="payment-icon" xmlns="http://www.w3.org/2000/svg" role="img" viewBox="0 0 38 24" width="38" height="24" aria-labelledby="pi-google_pay"><title id="pi-google_pay">Google Pay</title><path d="M35 0H3C1.3 0 0 1.3 0 3v18c0 1.7 1.4 3 3 3h32c1.7 0 3-1.3 3-3V3c0-1.7-1.4-3-3-3z" fill="#000" opacity=".07"></path><path d="M35 1c1.1 0 2 .9 2 2v18c0 1.1-.9 2-2 2H3c-1.1 0-2-.9-2-2V3c0-1.1.9-2 2-2h32" fill="#FFF"></path><path d="M18.093 11.976v3.2h-1.018v-7.9h2.691a2.447 2.447 0 0 1 1.747.692 2.28 2.28 0 0 1 .11 3.224l-.11.116c-.47.447-1.098.69-1.747.674l-1.673-.006zm0-3.732v2.788h1.698c.377.012.741-.135 1.005-.404a1.391 1.391 0 0 0-1.005-2.354l-1.698-.03zm6.484 1.348c.65-.03 1.286.188 1.778.613.445.43.682 1.03.65 1.649v3.334h-.969v-.766h-.049a1.93 1.93 0 0 1-1.673.931 2.17 2.17 0 0 1-1.496-.533 1.667 1.667 0 0 1-.613-1.324 1.606 1.606 0 0 1 .613-1.336 2.746 2.746 0 0 1 1.698-.515c.517-.02 1.03.093 1.49.331v-.208a1.134 1.134 0 0 0-.417-.901 1.416 1.416 0 0 0-.98-.368 1.545 1.545 0 0 0-1.319.717l-.895-.564a2.488 2.488 0 0 1 2.182-1.06zM23.29 13.52a.79.79 0 0 0 .337.662c.223.176.5.269.785.263.429-.001.84-.17 1.146-.472.305-.286.478-.685.478-1.103a2.047 2.047 0 0 0-1.324-.374 1.716 1.716 0 0 0-1.03.294.883.883 0 0 0-.392.73zm9.286-3.75l-3.39 7.79h-1.048l1.281-2.728-2.224-5.062h1.103l1.612 3.885 1.569-3.885h1.097z" fill="#5F6368"></path><path d="M13.986 11.284c0-.308-.024-.616-.073-.92h-4.29v1.747h2.451a2.096 2.096 0 0 1-.9 1.373v1.134h1.464a4.433 4.433 0 0 0 1.348-3.334z" fill="#4285F4"></path><path d="M9.629 15.721a4.352 4.352 0 0 0 3.01-1.097l-1.466-1.14a2.752 2.752 0 0 1-4.094-1.44H5.577v1.17a4.53 4.53 0 0 0 4.052 2.507z" fill="#34A853"></path><path d="M7.079 12.05a2.709 2.709 0 0 1 0-1.735v-1.17H5.577a4.505 4.505 0 0 0 0 4.075l1.502-1.17z" fill="#FBBC04"></path><path d="M9.629 8.44a2.452 2.452 0 0 1 1.74.68l1.3-1.293a4.37 4.37 0 0 0-3.065-1.183 4.53 4.53 0 0 0-4.027 2.5l1.502 1.171a2.715 2.715 0 0 1 2.55-1.875z" fill="#EA4335"></path></svg>
                            </label>
                            <input class="btn-check" type="checkbox" name="method[]" id="m_4" value="4" @if (in_array(4, $adv['method'])) checked @endif>
                            <label class="btn btn-outline-dark" for="m_4">
                                <svg class="payment-icon" viewBox="0 0 38 24" xmlns="http://www.w3.org/2000/svg" width="38" height="24" role="img" aria-labelledby="pi-maestro"><title id="pi-maestro">Maestro</title><path opacity=".07" d="M35 0H3C1.3 0 0 1.3 0 3v18c0 1.7 1.4 3 3 3h32c1.7 0 3-1.3 3-3V3c0-1.7-1.4-3-3-3z"></path><path fill="#fff" d="M35 1c1.1 0 2 .9 2 2v18c0 1.1-.9 2-2 2H3c-1.1 0-2-.9-2-2V3c0-1.1.9-2 2-2h32"></path><circle fill="#EB001B" cx="15" cy="12" r="7"></circle><circle fill="#00A2E5" cx="23" cy="12" r="7"></circle><path fill="#7375CF" d="M22 12c0-2.4-1.2-4.5-3-5.7-1.8 1.3-3 3.4-3 5.7s1.2 4.5 3 5.7c1.8-1.2 3-3.3 3-5.7z"></path></svg>
                            </label>
                            <input class="btn-check" type="checkbox" name="method[]" id="m_5" value="5" @if (in_array(5, $adv['method'])) checked @endif>
                            <label class="btn btn-outline-dark" for="m_5">
                                <svg class="payment-icon" viewBox="0 0 38 24" xmlns="http://www.w3.org/2000/svg" role="img" width="38" height="24" aria-labelledby="pi-master"><title id="pi-master">Mastercard</title><path opacity=".07" d="M35 0H3C1.3 0 0 1.3 0 3v18c0 1.7 1.4 3 3 3h32c1.7 0 3-1.3 3-3V3c0-1.7-1.4-3-3-3z"></path><path fill="#fff" d="M35 1c1.1 0 2 .9 2 2v18c0 1.1-.9 2-2 2H3c-1.1 0-2-.9-2-2V3c0-1.1.9-2 2-2h32"></path><circle fill="#EB001B" cx="15" cy="12" r="7"></circle><circle fill="#F79E1B" cx="23" cy="12" r="7"></circle><path fill="#FF5F00" d="M22 12c0-2.4-1.2-4.5-3-5.7-1.8 1.3-3 3.4-3 5.7s1.2 4.5 3 5.7c1.8-1.2 3-3.3 3-5.7z"></path></svg>
                            </label>
                            <input class="btn-check" type="checkbox" name="method[]" id="m_6" value="6" @if (in_array(6, $adv['method'])) checked @endif>
                            <label class="btn btn-outline-dark" for="m_6">
                                <svg class="payment-icon" viewBox="0 0 38 24" xmlns="http://www.w3.org/2000/svg" role="img" width="38" height="24" aria-labelledby="pi-visa"><title id="pi-visa">Visa</title><path opacity=".07" d="M35 0H3C1.3 0 0 1.3 0 3v18c0 1.7 1.4 3 3 3h32c1.7 0 3-1.3 3-3V3c0-1.7-1.4-3-3-3z"></path><path fill="#fff" d="M35 1c1.1 0 2 .9 2 2v18c0 1.1-.9 2-2 2H3c-1.1 0-2-.9-2-2V3c0-1.1.9-2 2-2h32"></path><path d="M28.3 10.1H28c-.4 1-.7 1.5-1 3h1.9c-.3-1.5-.3-2.2-.6-3zm2.9 5.9h-1.7c-.1 0-.1 0-.2-.1l-.2-.9-.1-.2h-2.4c-.1 0-.2 0-.2.2l-.3.9c0 .1-.1.1-.1.1h-2.1l.2-.5L27 8.7c0-.5.3-.7.8-.7h1.5c.1 0 .2 0 .2.2l1.4 6.5c.1.4.2.7.2 1.1.1.1.1.1.1.2zm-13.4-.3l.4-1.8c.1 0 .2.1.2.1.7.3 1.4.5 2.1.4.2 0 .5-.1.7-.2.5-.2.5-.7.1-1.1-.2-.2-.5-.3-.8-.5-.4-.2-.8-.4-1.1-.7-1.2-1-.8-2.4-.1-3.1.6-.4.9-.8 1.7-.8 1.2 0 2.5 0 3.1.2h.1c-.1.6-.2 1.1-.4 1.7-.5-.2-1-.4-1.5-.4-.3 0-.6 0-.9.1-.2 0-.3.1-.4.2-.2.2-.2.5 0 .7l.5.4c.4.2.8.4 1.1.6.5.3 1 .8 1.1 1.4.2.9-.1 1.7-.9 2.3-.5.4-.7.6-1.4.6-1.4 0-2.5.1-3.4-.2-.1.2-.1.2-.2.1zm-3.5.3c.1-.7.1-.7.2-1 .5-2.2 1-4.5 1.4-6.7.1-.2.1-.3.3-.3H18c-.2 1.2-.4 2.1-.7 3.2-.3 1.5-.6 3-1 4.5 0 .2-.1.2-.3.2M5 8.2c0-.1.2-.2.3-.2h3.4c.5 0 .9.3 1 .8l.9 4.4c0 .1 0 .1.1.2 0-.1.1-.1.1-.1l2.1-5.1c-.1-.1 0-.2.1-.2h2.1c0 .1 0 .1-.1.2l-3.1 7.3c-.1.2-.1.3-.2.4-.1.1-.3 0-.5 0H9.7c-.1 0-.2 0-.2-.2L7.9 9.5c-.2-.2-.5-.5-.9-.6-.6-.3-1.7-.5-1.9-.5L5 8.2z" fill="#142688"></path></svg>
                            </label>
                            <input class="btn-check" type="checkbox" name="method[]" id="m_7" value="7" @if (in_array(7, $adv['method'])) checked @endif>
                            <label class="btn btn-outline-dark" for="m_7">
                                <i class="bi bi-cash-coin" style="font-size: var(--fs-500)"></i>
                            </label>
                        </div>
                        </div>
                    </div>

                    <div id="critical-warning" style="display: none; text-align:center;" class="error">
                        {{__('admin.Warning_reset_disponibilita')}}
                    </div>

                    <x-slot name="footer">
                        <button type="button" class="my_btn_1 d" data-bs-dismiss="modal">{{__('admin.Annulla')}}</button>
                        <button type="submit" class="my_btn_1 add">{{__('admin.Aggiorna')}}</button>
                    </x-slot>
                </x-dashboard.action-modal>
            </form>
        </div>
    </div>
    @if (config('configurazione.subscription') > 2)
        <div class="modal fade" id="staticBackdrop1" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdrop1Label" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <form action="{{ route('admin.settings.updateAree')}}" method="POST" class="w-100">
                    @csrf
                    <input type="hidden" name="ar" value="remove">
                    <x-dashboard.action-modal
                        title-id="staticBackdrop1Label"
                        class="settings-basic-modal"
                        title="{{ __('admin.Seleziona_i_comuni_che_vuoi_rimuovere') }}"
                        eyebrow="Domicilio"
                        tone="danger"
                        description="Seleziona i comuni da rimuovere dalla zona di consegna."
                    >
                        @php
                            if (is_string($setting['Comuni per il domicilio']['property'])) {
                                $setting['Comuni per il domicilio']['property'] = json_decode($setting['Comuni per il domicilio']['property'], true);
                            } 
                        @endphp
                        <div class="check_c">
                            @foreach ($setting['Comuni per il domicilio']['property'] as $i)
                                <input type="checkbox" class="btn-check" id="a{{ $i['comune'] }}" name="comuni[]" value="{{ $i['comune'] }}" >
                                <label class="btn btn-outline-danger" for="a{{ $i['comune'] }}">{{ $i['provincia'] }} - {{ $i['comune'] }}</label>
                            @endforeach
                        </div>

                        <x-slot name="footer">
                            <button type="button" class="my_btn_1" data-bs-dismiss="modal">{{__('admin.Annulla')}}</button>
                            <button type="submit" class="my_btn_2">{{__('admin.Rimuovi_comuni')}}</button>
                        </x-slot>
                    </x-dashboard.action-modal>
                </form>
            </div>
        </div>
        
        <div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <form action="{{ route('admin.settings.updateAree')}}" method="POST" class="w-100">
                    @csrf
                    <input type="hidden" name="ar" value="add">
                    <x-dashboard.action-modal
                        title-id="staticBackdropLabel"
                        class="settings-basic-modal"
                        title="{{__('admin.Aggiungi_comune')}}"
                        eyebrow="Domicilio"
                        tone="mint"
                        description="Aggiungi un nuovo comune e definisci subito sigla, CAP e costo extra di consegna."
                    >
                        <div class="dashboard-action-modal__field">
                            <label for="comune">{{__('admin.Comune')}}</label>
                            <input name="comune" id="comune" type="text" placeholder="comune">
                        </div>
                        <div class="dashboard-action-modal__field">
                            <label for="provincia">{{__('admin.Provincia')}}</label>
                            <input name="provincia" id="provincia" type="text" placeholder="sigla della provincia es: AN">
                        </div>
                        <div class="dashboard-action-modal__field">
                            <label for="cap">{{__('admin.Cap')}}</label>
                            <input name="cap" id="cap" type="text" placeholder="cap">
                        </div>
                        <div class="dashboard-action-modal__field">
                            <label for="price">{{__('admin.Costo_extra_consegna')}}</label>
                            <input name="price" id="price" type="number" step="{{ \App\Support\Currency::inputStep() }}" placeholder="{{ $appCurrency['symbol'] }} extra">
                        </div>

                        <x-slot name="footer">
                            <button type="button" class="my_btn_1 d" data-bs-dismiss="modal">{{__('admin.Annulla')}}</button>
                            <button type="submit" class="my_btn_1 add">{{__('admin.Aggiungi_comune')}}</button>
                        </x-slot>
                    </x-dashboard.action-modal>
                </form>
            </div>
        </div>

        <div class="modal fade" id="staticBackdrop2" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdrop2Label" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <form action="{{ route('admin.settings.numbers')}}" method="POST" class="w-100">
                    @csrf
                    <x-dashboard.action-modal
                        title-id="staticBackdrop2Label"
                        class="settings-basic-modal"
                        title="{{__('admin.Modifica_wa')}}"
                        eyebrow="WhatsApp"
                        tone="mint"
                        description="Aggiorna i numeri disponibili per l invio diretto dei messaggi WhatsApp."
                    >
                        <div class="dashboard-action-modal__field">
                            <label for="numbers_primary">1# {{__('admin.Numero')}}</label>
                            <input name="numbers[]" id="numbers_primary" type="text" placeholder="39000111000">
                        </div>
                        @if (config('configurazione.subscription') == 5)
                            <div class="dashboard-action-modal__field">
                                <label for="numbers_secondary">2# {{__('admin.Numero')}}</label>
                                <input name="numbers[]" id="numbers_secondary" type="text" placeholder="39000111000">
                            </div>
                        @endif

                        <x-slot name="footer">
                            <button type="button" class="my_btn_1 d" data-bs-dismiss="modal">{{__('admin.Annulla')}}</button>
                            <button type="submit" class="my_btn_1 add">{{__('admin.Modifica')}}</button>
                        </x-slot>
                    </x-dashboard.action-modal>
                </form>
            </div>
        </div>
    
    @endif
</div>
<script>
     document.addEventListener('DOMContentLoaded', async function() {
            const toggleButton = document.getElementById('theme-toggle');
            const currentTheme = localStorage.getItem('theme') || 'light';
            localStorage.setItem('theme', currentTheme)
            document.documentElement.setAttribute("data-theme", currentTheme);
            
            toggleButton.addEventListener('click', () => {
                const theme = localStorage.getItem('theme') == 'light' ? 'dark' : 'light';
                localStorage.setItem("theme", theme);
                console.log(theme)
                document.documentElement.setAttribute("data-theme", theme);
            });
        });
</script>
{{-- <script>  
    document.addEventListener('DOMContentLoaded', async function() {
        const originalValue1 = document.getElementById('attivo-originale1').value;
        const originalValue2 = document.getElementById('attivo-originale2').value;
        const originalValue3 = document.getElementById('attivo-originale3').value;

        const radios1 = document.querySelectorAll('.critical-radio1');
        const radios2 = document.querySelectorAll('.critical-radio2');
        const radios3 = document.querySelectorAll('.critical-radio3');
        const warning = document.getElementById('critical-warning');

        const criticalRadios = [
            { name: 'services', original: document.getElementById('attivo-originale1').value},
            { name: 'too', original: document.getElementById('attivo-originale2').value},
            { name: 'dt', original: document.getElementById('attivo-originale3').value}
        ];

        function checkChanges() {
            const isChanged = criticalRadios.some(group => {
                const selected = document.querySelector(`input[name="${group.name}"]:checked`)?.value;
                return selected !== group.original;
            });

            warning.style.display = isChanged ? 'block' : 'none';
        }

        // Aggiungiamo il listener a tutti i radio critici
        criticalRadios.forEach(group => {
            document.querySelectorAll(`input[name="${group.name}"]`).forEach(radio => {
                radio.addEventListener('change', checkChanges);
            });
        });

        // Ascolta tutti i cambiamenti nei radio
        document.querySelectorAll('input[type="radio"]').forEach(radio => {
            radio.addEventListener('change', aggiornaStato);
        });
        
    });
</script> --}}

@endsection
