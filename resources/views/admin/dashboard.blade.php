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
$adv = json_decode($adv_s->property, 1);

@endphp
@if (count($notify))
    <div id="alert-container" >
        @foreach ($notify as $r)
            @if ($r['type'] == 'res')  
                <div class="alert alert-dismissible fade show fixed-alert-res" role="alert">
                <a href="{{ route('admin.reservations.show', $r['id']) }}" class="btn btn-dark-outline">Dettagli</a> 
            @else    
                <div class="alert alert-dismissible fade show fixed-alert-res" role="alert">
                <a href="{{ route('admin.orders.show', $r['id']) }}" class="btn btn-dark-outline">Dettagli</a> 
            @endif 
                {{ $r['m'] }} 
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endforeach
    </div>
@endif
<div class="dash-c">
    <div class="targhetta">
        <div class="title">
            <div class="img_bg">
                <img src="{{config('configurazione.domain') . '/img/favicon.png'}}" alt="">
            </div>
            <a href="{{config('configurazione.domain')}}">
                <h1 >{{config('configurazione.APP_NAME')}}</h1>
            </a>
            <a class="pack" href="https://future-plus.it/#pacchetti">Pacchetto: {{$pack[config('configurazione.subscription')]}}</a>
        </div>
       
        <div class="btns"> 
            @if (config('configurazione.subscription') > 1 )
            <a class="my_btn_3 " href="{{route('admin.statistics')}}">  
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-clipboard2-data" viewBox="0 0 16 16">
                    <path d="M9.5 0a.5.5 0 0 1 .5.5.5.5 0 0 0 .5.5.5.5 0 0 1 .5.5V2a.5.5 0 0 1-.5.5h-5A.5.5 0 0 1 5 2v-.5a.5.5 0 0 1 .5-.5.5.5 0 0 0 .5-.5.5.5 0 0 1 .5-.5z"/>
                    <path d="M3 2.5a.5.5 0 0 1 .5-.5H4a.5.5 0 0 0 0-1h-.5A1.5 1.5 0 0 0 2 2.5v12A1.5 1.5 0 0 0 3.5 16h9a1.5 1.5 0 0 0 1.5-1.5v-12A1.5 1.5 0 0 0 12.5 1H12a.5.5 0 0 0 0 1h.5a.5.5 0 0 1 .5.5v12a.5.5 0 0 1-.5.5h-9a.5.5 0 0 1-.5-.5z"/>
                    <path d="M10 7a1 1 0 1 1 2 0v5a1 1 0 1 1-2 0zm-6 4a1 1 0 1 1 2 0v1a1 1 0 1 1-2 0zm4-3a1 1 0 0 0-1 1v3a1 1 0 1 0 2 0V9a1 1 0 0 0-1-1"/>
                </svg> <span>Statistiche</span>
            </a>
            @endif
            @if (config('configurazione.subscription') > 2 )
                <a class="my_btn_6 " href="{{route('admin.mailer.index')}}"> 
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-envelope-arrow-up" viewBox="0 0 16 16">
                        <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v4.5a.5.5 0 0 1-1 0V5.383l-7 4.2-1.326-.795-5.64 3.47A1 1 0 0 0 2 13h5.5a.5.5 0 0 1 0 1H2a2 2 0 0 1-2-1.99zm1 7.105 4.708-2.897L1 5.383zM1 4v.217l7 4.2 7-4.2V4a1 1 0 0 0-1-1H2a1 1 0 0 0-1 1"/>
                        <path d="M12.5 16a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7m.354-5.354 1.25 1.25a.5.5 0 0 1-.708.708L13 12.207V14a.5.5 0 0 1-1 0v-1.717l-.28.305a.5.5 0 0 1-.737-.676l1.149-1.25a.5.5 0 0 1 .722-.016"/>
                      </svg> 
                    <span>Email Marketing</span>
                </a>
            @endif
            </div>
    </div>

    
    <div class="top-c">
        <div class="prod">
            <div class="top-p">
                <a class="title" href="{{ route('admin.products.index') }}"> <h2>Prodotti</h2></a>
                <a href="{{ route('admin.products.index') }}" class=" plus icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-list-ul" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M5 11.5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m-3 1a1 1 0 1 0 0-2 1 1 0 0 0 0 2m0 4a1 1 0 1 0 0-2 1 1 0 0 0 0 2m0 4a1 1 0 1 0 0-2 1 1 0 0 0 0 2"/>
                    </svg>
                </a>
                <a href="{{ route('admin.products.create') }}" class="plus">
                    <div class="line"></div>
                    <div class="line l2"></div>
                </a>
            </div>
            <div class="stat-p">
                <div class="stat">
                    <h2>{{$product_[1]}}</h2>
                    <span>visibili</span>
                </div>
                <div class="stat">
                    <h2>{{$product_[2]}}</h2>
                    <span>archiviati</span>
                </div>
                <div class="stat">
                    <h2>{{$stat[1]}}</h2>
                    <span>categorie</span>
                </div>
                <div class="stat">
                    <h2>{{$stat[2]}}</h2>
                    <span>ingredienti</span>
                </div>
            </div>
            <div class="action">
                <a href="{{ route('admin.menus.index') }}" class="my_btn_1">Menu Fissi</a>
                <a href="{{ route('admin.categories.index') }}" class="my_btn_1">Categorie</a>
                <a href="{{ route('admin.ingredients.index') }}" class="my_btn_1">Ingredienti</a>
            </div>
        </div>      
        @if (config('configurazione.subscription') > 1 )
            <div class="right-t">
                @if (config('configurazione.subscription') > 1 )
                <div class="result-bar">
                    @if ($adv['services'] > 2)
                    <div class="stat">
                        <h2>€{{$traguard[1] / 100}}</h2>
                        <span>questo mese</span>
                    </div>
                    @endif
                    @if ($adv['services'] == 2 || $adv['services'] == 4)
                    <div class="stat">
                        <h2> 
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-people-fill" viewBox="0 0 16 16">
                            <path d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1zm4-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6m-5.784 6A2.24 2.24 0 0 1 5 13c0-1.355.68-2.75 1.936-3.72A6.3 6.3 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1zM4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5"/>
                            </svg>
                            {{$traguard[3]}}</h2>
                        <span>questo mese</span>
                    </div>
                    @endif
                    @if ($adv['services'] > 2)
                    <div class="stat">
                        <h2>€{{$traguard[2] / 100}}</h2>
                        <span>questo anno</span>
                    </div>
                    @endif
                    @if ($adv['services'] == 2 || $adv['services'] == 4)
                    <div class="stat">
                        <h2> 
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-people-fill" viewBox="0 0 16 16">
                            <path d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1zm4-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6m-5.784 6A2.24 2.24 0 0 1 5 13c0-1.355.68-2.75 1.936-3.72A6.3 6.3 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1zM4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5"/>
                            </svg>
                            {{$traguard[4]}}</h2>
                        <span>questo anno</span>
                    </div>
                    @endif
                </div>
                @endif
                @if ($adv['services'] > 2)
                    <div class="delivery-c">
                        <div class="top-p">
                            <a class="title" href="{{ route('admin.orders.index') }}"> <h3>Ordini asporto/delivery</h3></a>
                            <a href="{{ route('admin.orders.index') }}" class=" plus icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-boxes" viewBox="0 0 16 16">
                                    <path d="M7.752.066a.5.5 0 0 1 .496 0l3.75 2.143a.5.5 0 0 1 .252.434v3.995l3.498 2A.5.5 0 0 1 16 9.07v4.286a.5.5 0 0 1-.252.434l-3.75 2.143a.5.5 0 0 1-.496 0l-3.502-2-3.502 2.001a.5.5 0 0 1-.496 0l-3.75-2.143A.5.5 0 0 1 0 13.357V9.071a.5.5 0 0 1 .252-.434L3.75 6.638V2.643a.5.5 0 0 1 .252-.434zM4.25 7.504 1.508 9.071l2.742 1.567 2.742-1.567zM7.5 9.933l-2.75 1.571v3.134l2.75-1.571zm1 3.134 2.75 1.571v-3.134L8.5 9.933zm.508-3.996 2.742 1.567 2.742-1.567-2.742-1.567zm2.242-2.433V3.504L8.5 5.076V8.21zM7.5 8.21V5.076L4.75 3.504v3.134zM5.258 2.643 8 4.21l2.742-1.567L8 1.076zM15 9.933l-2.75 1.571v3.134L15 13.067zM3.75 14.638v-3.134L1 9.933v3.134z"/>
                                </svg>
                            </a>
                            {{-- <a href="https://demo3-futureplus.netlify.app/ordina" class="plus">
                                <div class="line"></div>
                                <div class="line l2"></div>
                            </a> --}}
                        </div>
                        <div class="stat-p">
                            <div class="grup">
                                <div class="stat">
                                    <h3>{{$order[2]}}</h3>
                                    <span>da vedere</span>
                                </div>
                                <div class="stat">
                                    <h3>{{$order[1]}}</h3>
                                    <span>confermate</span>
                                </div>
                            </div>
                            <div class="grup">
                                <div class="stat">
                                    <h3>{{$order[3]}}</h3>
                                    <span>annullate</span>
                                </div>
                                <div class="stat">
                                    <h3>{{$order[4]}}</h3>
                                    <span>pagate</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
                @if ($adv['services'] == 2 || $adv['services'] == 4)
                    <div class="delivery-c">
                        <div class="top-p">
                            <a class="title" href="{{ route('admin.reservations.index') }}"> <h3>Prenotazioni Tavoli</h3></a>
                            <a href="{{ route('admin.reservations.index') }}" class=" plus icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-lines-fill" viewBox="0 0 16 16">
                                    <path d="M6 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6m-5 6s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zM11 3.5a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 0 1h-4a.5.5 0 0 1-.5-.5m.5 2.5a.5.5 0 0 0 0 1h4a.5.5 0 0 0 0-1zm2 3a.5.5 0 0 0 0 1h2a.5.5 0 0 0 0-1zm0 3a.5.5 0 0 0 0 1h2a.5.5 0 0 0 0-1z"/>
                                </svg>
                            </a>
                            {{-- <a href="https://demo3-futureplus.netlify.app/check-out" class="plus">
                                <div class="line"></div>
                                <div class="line l2"></div>
                            </a> --}}
                        </div>
                        <div class="stat-p">
                            <div class="grup">
                                <div class="stat">
                                    <h3>{{$reservation[2]}}</h3>
                                    <span>da vedere</span>
                                </div>
                                <div class="stat">
                                    <h3>{{$reservation[1]}}</h3>
                                    <span>confermate</span>
                                </div>
                            </div>
                            <div class="grup">
                                <div class="stat">
                                    <h3>{{$reservation[3]}}</h3>
                                    <span>annullate</span>
                                </div>
                                <div class="stat">
                                    <h3>{{$reservation[4]}}</h3>
                                    <span>pagate</span>
                                </div>
                            </div>
                            
                        </div>
                    </div>
                @endif
            </div>
        @endif
        <div class="prod post">  
            <div class="top-p">

                <a class="title" href="{{ route('admin.posts.index') }}"> <h2>Post</h2></a>
                <a href="{{ route('admin.posts.index') }}" class=" plus icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-list-ul" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M5 11.5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m-3 1a1 1 0 1 0 0-2 1 1 0 0 0 0 2m0 4a1 1 0 1 0 0-2 1 1 0 0 0 0 2m0 4a1 1 0 1 0 0-2 1 1 0 0 0 0 2"/>
                      </svg>
                </a>
                <a href="{{ route('admin.posts.create') }}" class="plus">
                    <div class="line"></div>
                    <div class="line l2"></div>
                </a>
                
            </div>
            <div class="stat-p">
                <div class="stat">
                    <h2>{{$post[1]}}</h2>
                    <span>totali</span>
                </div>
                <div class="stat">
                    <h2>{{$post[2]}}</h2>
                    <span>pronti</span>
                </div>
                <div class="stat">
                    <h2>{{$post[3]}}</h2>
                    <span>postati</span>
                </div>
                <div class="stat">
                    <h2>{{$post[4]}}</h2>
                    <span>archiviati</span>
                </div>
            </div>   
        </div>
    </div>
    <div class="bottom-c">
        <div class="date">
            @if (isset($year) && config('configurazione.subscription') > 1)
                <div class="">
                    <div id="carouselExampleIndicators" class="carousel slide my_carousel">
                        <div class="carousel-indicators">

                            @php 
                                $i = 0; 
                                $currentDay = date("d");
                                $currentMonth = date("m");
                                $currentYear = date("Y");
                            @endphp
                            @foreach ($year as $m)
                                <button  type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="{{$i}}"
                                @if ($currentMonth == $m['month'] && $currentYear == $m['year'])
                                    class="active" aria-current="true" 
                                @endif
                                aria-label="{{ 'Slide ' . $i }}"></button>
                                @php $i ++ @endphp
                            @endforeach
                        </div>
                        <div class="carousel-inner">
                        @php $i = 0; @endphp
                        @foreach ($year as $m)
                            @dump('current'.$currentMonth . " " . $currentYear)
                            @dump('passed'.$m['month'] . " " . $m['year'])
                            <div class="carousel-item
                            @if ($currentMonth == $m['month'] && $currentYear == $m['year'])
                                active 
                            @endif
                            ">
                                
                                <h2 class="my">{{['', 'gennaio', 'febbraio', 'marzo', 'aprile', 'maggio', 'giugno', 'luglio', 'agosto', 'settembre', 'ottobre', 'novembre', 'dicembre'][$m['month']]}} - {{$m['year']}}</h2>
                                <div class="calendar-c">
                                    <a href="{{route('admin.dates.index')}}" class="date-set">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-gear-wide-connected" viewBox="0 0 16 16">
                                            <path d="M7.068.727c.243-.97 1.62-.97 1.864 0l.071.286a.96.96 0 0 0 1.622.434l.205-.211c.695-.719 1.888-.03 1.613.931l-.08.284a.96.96 0 0 0 1.187 1.187l.283-.081c.96-.275 1.65.918.931 1.613l-.211.205a.96.96 0 0 0 .434 1.622l.286.071c.97.243.97 1.62 0 1.864l-.286.071a.96.96 0 0 0-.434 1.622l.211.205c.719.695.03 1.888-.931 1.613l-.284-.08a.96.96 0 0 0-1.187 1.187l.081.283c.275.96-.918 1.65-1.613.931l-.205-.211a.96.96 0 0 0-1.622.434l-.071.286c-.243.97-1.62.97-1.864 0l-.071-.286a.96.96 0 0 0-1.622-.434l-.205.211c-.695.719-1.888.03-1.613-.931l.08-.284a.96.96 0 0 0-1.186-1.187l-.284.081c-.96.275-1.65-.918-.931-1.613l.211-.205a.96.96 0 0 0-.434-1.622l-.286-.071c-.97-.243-.97-1.62 0-1.864l.286-.071a.96.96 0 0 0 .434-1.622l-.211-.205c-.719-.695-.03-1.888.931-1.613l.284.08a.96.96 0 0 0 1.187-1.186l-.081-.284c-.275-.96.918-1.65 1.613-.931l.205.211a.96.96 0 0 0 1.622-.434zM12.973 8.5H8.25l-2.834 3.779A4.998 4.998 0 0 0 12.973 8.5m0-1a4.998 4.998 0 0 0-7.557-3.779l2.834 3.78zM5.048 3.967l-.087.065zm-.431.355A4.98 4.98 0 0 0 3.002 8c0 1.455.622 2.765 1.615 3.678L7.375 8zm.344 7.646.087.065z"/>
                                        </svg>
                                    </a>
                                    <div class="c-name">
                                        @php
                                        $day_name = ['lunedì', 'martedì', 'mercoledì', 'giovedì', 'venerdì', 'sabato', 'domenica'];
                                        @endphp
                                        @foreach ($day_name as $item)
                                            <h4>{{$item}}</h4>
                                        @endforeach
                                    </div>
                                    <div class="calendar">

                                        @foreach ($m['days'] as $d)
                                            <form action="{{ route('admin.dates.showDay') }}" 
                                            class="day {{ 'd' . $d['day_w']}} @if(!isset($d['time'])) day-off @endif 
                                            @if($currentMonth == $m['month'] && $currentYear == $m['year'] && $currentDay == $d['day']) day-active @endif " 
                                            style="grid-column-start:{{$d['day_w'] }}"
                                             method="get">
                                                @csrf
                                                <input type="hidden" name="date" value="{{$d['date']}}">
                                                @if(isset($d['asporto']) && $d['asporto'] !== 0 )
                                                    <p class="pop1">
                                                        <span>{{$d['asporto']}}</span>
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-inboxes" viewBox="0 0 16 16">
                                                            <path d="M4.98 1a.5.5 0 0 0-.39.188L1.54 5H6a.5.5 0 0 1 .5.5 1.5 1.5 0 0 0 3 0A.5.5 0 0 1 10 5h4.46l-3.05-3.812A.5.5 0 0 0 11.02 1zm9.954 5H10.45a2.5 2.5 0 0 1-4.9 0H1.066l.32 2.562A.5.5 0 0 0 1.884 9h12.234a.5.5 0 0 0 .496-.438zM3.809.563A1.5 1.5 0 0 1 4.981 0h6.038a1.5 1.5 0 0 1 1.172.563l3.7 4.625a.5.5 0 0 1 .105.374l-.39 3.124A1.5 1.5 0 0 1 14.117 10H1.883A1.5 1.5 0 0 1 .394 8.686l-.39-3.124a.5.5 0 0 1 .106-.374zM.125 11.17A.5.5 0 0 1 .5 11H6a.5.5 0 0 1 .5.5 1.5 1.5 0 0 0 3 0 .5.5 0 0 1 .5-.5h5.5a.5.5 0 0 1 .496.562l-.39 3.124A1.5 1.5 0 0 1 14.117 16H1.883a1.5 1.5 0 0 1-1.489-1.314l-.39-3.124a.5.5 0 0 1 .121-.393zm.941.83.32 2.562a.5.5 0 0 0 .497.438h12.234a.5.5 0 0 0 .496-.438l.32-2.562H10.45a2.5 2.5 0 0 1-4.9 0z"/>
                                                        </svg>
                                                    </p>
                                                @endif
                                                @if(isset($d['domicilio']) && $d['domicilio'] !== 0 )
                                                <p class="pop2">
                                                    <span>{{$d['domicilio']}}</span>
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-mailbox-flag" viewBox="0 0 16 16">
                                                            <path d="M10.5 8.5V3.707l.854-.853A.5.5 0 0 0 11.5 2.5v-2A.5.5 0 0 0 11 0H9.5a.5.5 0 0 0-.5.5v8zM5 7c0 .334-.164.264-.415.157C4.42 7.087 4.218 7 4 7s-.42.086-.585.157C3.164 7.264 3 7.334 3 7a1 1 0 0 1 2 0"/>
                                                            <path d="M4 3h4v1H6.646A4 4 0 0 1 8 7v6h7V7a3 3 0 0 0-3-3V3a4 4 0 0 1 4 4v6a1 1 0 0 1-1 1H1a1 1 0 0 1-1-1V7a4 4 0 0 1 4-4m0 1a3 3 0 0 0-3 3v6h6V7a3 3 0 0 0-3-3"/>
                                                        </svg> 
                                                    </p>
                                                @endif
                                                @if(isset($d['table']) && $d['table'] !== 0 )
                                                    <p class="pop3">
                                                        <span>{{$d['table']}}</span>
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-lines-fill" viewBox="0 0 16 16">
                                                            <path d="M6 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6m-5 6s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zM11 3.5a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 0 1h-4a.5.5 0 0 1-.5-.5m.5 2.5a.5.5 0 0 0 0 1h4a.5.5 0 0 0 0-1zm2 3a.5.5 0 0 0 0 1h2a.5.5 0 0 0 0-1zm0 3a.5.5 0 0 0 0 1h2a.5.5 0 0 0 0-1z"/>
                                                        </svg>
                                                    </p>
                                                @endif
                                                <button class="b">{{$d['day']}}</button>
                                            </form>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @php $i ++ @endphp
                        @endforeach

                        </div>
                        <button class="carousel-control-prev" style="width: 7% !important;" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="prev">
                            <div class="lez-c prev">
                                <div class="line"></div>
                                <div class="line l2"></div>
                            </div>
                        </button>
                        <button class="carousel-control-next" style="width: 7% !important;" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="next">
                            <div class="lez-c ">
                                <div class="line"></div>
                                <div class="line l2"></div>
                            </div>
                        </button>
                    </div>
                </div>
            
            @elseif(config('configurazione.subscription') == 1)
            <div class="date-off d-back-g">
                <a href="https://future-plus.it/#pacchetti">Per permettere ai tuoi clienti di prenotare tavoli o ordinare a domicilio o asporto clicca qui e <strong>prenota una call con i nostri consulenti</strong></a>
            </div>
            @else 
            <div class="date-off">
                <a href="{{route('admin.dates.index')}}">Non sono ancora state impostate le disponibilita dei servizi, <strong>clicca QUI</strong> e impostale ora</a>
            </div>
            @endif
            @if (config('configurazione.subscription') > 1)
            <div class="chart">
                <canvas id="chartCanvas"></canvas>
            </div>
            @endif
        </div>
        <form class="setting" action="{{ route('admin.settings.updateAll')}}" method="POST" enctype="multipart/form-data">
            <h2>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-sliders" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M11.5 2a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3M9.05 3a2.5 2.5 0 0 1 4.9 0H16v1h-2.05a2.5 2.5 0 0 1-4.9 0H0V3zM4.5 7a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3M2.05 8a2.5 2.5 0 0 1 4.9 0H16v1H6.95a2.5 2.5 0 0 1-4.9 0H0V8zm9.45 4a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3m-2.45 1a2.5 2.5 0 0 1 4.9 0H16v1h-2.05a2.5 2.5 0 0 1-4.9 0H0v-1z"/>
                </svg>
            Impostazioni</h2>
            
            <div class="top-set">
                @csrf
                @php
                    $asporto_p = json_decode($setting['Prenotazione Asporti']['property'] , 1);
                    $domicilio_p = json_decode($setting['Possibilità di consegna a domicilio']['property'] , 1);
                    //dd($domicilio_p['pay'])
                @endphp
                <div class="set">
                    <h4>Tavoli</h4>
                    <div class="radio-inputs">
                        <label class="radio">
                            <input type="radio" name="tavoli_status"  @if($setting['Prenotazione Tavoli']['status'] == 0) checked  @endif value="0" >
                            <span class="name">Off</span>
                        </label>
                        <label class="radio">
                            <input type="radio" name="tavoli_status"  @if($setting['Prenotazione Tavoli']['status'] == 1) checked  @endif value="1" >
                            <span class="name">Chiamate</span>
                        </label>
                        @if (config('configurazione.subscription') > 1 )   
                        <label class="radio">
                            <input type="radio" name="tavoli_status"  @if($setting['Prenotazione Tavoli']['status'] == 2) checked  @endif value="2" >
                            <span class="name">Web App</span>
                        </label>
                        @endif
                    </div>
                </div>
                <div class="set">
                    <h4>Asporto</h4>
                    <div class="set-cont">
                        <h5>Servizio</h5>
                        <div class="radio-inputs">
                            <label class="radio">
                                <input type="radio" name="asporto_status"  @if($setting['Prenotazione Asporti']['status'] == 0) checked  @endif value="0" >
                                <span class="name">Off</span>
                            </label>
                            <label class="radio">
                                <input type="radio" name="asporto_status"  @if($setting['Prenotazione Asporti']['status'] == 1) checked  @endif value="1" >
                                <span class="name">Chiamate</span>
                            </label>
                            @if (config('configurazione.subscription') > 1)   
                            <label class="radio">
                                <input type="radio" name="asporto_status"  @if($setting['Prenotazione Asporti']['status'] == 2) checked  @endif value="2" >
                                <span class="name">Web App</span>
                            </label>
                            @endif
                        </div>
                        @if (config('configurazione.subscription') > 2)
                            <h5>Pagamento online</h5>
                            <div class="radio-inputs">
                                <label class="radio">
                                    <input type="radio" name="asporto_pay" @if($asporto_p['pay'] == 0) checked  @endif value="0" >
                                    <span class="name">Off</span>
                                </label>
                                <label class="radio">
                                    <input type="radio" name="asporto_pay" @if($asporto_p['pay'] == 1) checked  @endif value="1" >
                                    <span class="name">Facoltativo</span>
                                </label>
                                <label class="radio">
                                    <input type="radio" name="asporto_pay" @if($asporto_p['pay'] == 2) checked  @endif value="2" >
                                    <span class="name">Obbligatorio</span>
                                </label>
                            </div>
                        @endif
                        @if(config('configurazione.subscription') > 1)    
                            <h5>Generali</h5>
                            <div class="input-group mb-3">
                                <label class="input-group-text" id="basic-addon1">Prezzo minimo</label>
                                <input type="number" class="form-control"  name="min_price_a" value="{{$asporto_p['min_price'] / 100}}">
                            </div>
                        @endif
                        
                    </div>
                </div>
                @if (config('configurazione.subscription') > 1)
                <div class="set">
                    <h4>Domicilio</h4>
                    <div class="set-cont">

                        <h5>Servizio</h5>
                        <div class="radio-inputs">
                            <label class="radio">
                                <input type="radio" name="domicilio_status"  @if($setting['Possibilità di consegna a domicilio']['status'] == 0) checked  @endif value="0" >
                                <span class="name">Off</span>
                            </label>
                            <label class="radio">
                                <input type="radio" name="domicilio_status"  @if($setting['Possibilità di consegna a domicilio']['status'] == 1) checked  @endif value="1" >
                                <span class="name">On</span>
                            </label>
                        </div>
                        @if (config('configurazione.subscription') > 2)
                        <h5>Pagamento online</h5>
                        <div class="radio-inputs">
                            <label class="radio">
                                <input type="radio" name="domicilio_pay" @if($domicilio_p['pay'] == 0) checked  @endif value="0" >
                                <span class="name">Off</span>
                            </label>
                            <label class="radio">
                                <input type="radio" name="domicilio_pay" @if($domicilio_p['pay'] == 1) checked @endif value="1" >
                                <span class="name">Facoltativo</span>
                            </label>
                            <label class="radio">
                                <input type="radio" name="domicilio_pay" @if($domicilio_p['pay'] == 2) checked @endif value="2" >
                                <span class="name">Obbligatorio</span>
                            </label>
                        </div>
                        @endif
                        <h5>Generali</h5>
                        <div class="input-group mb-3">
                            <label class="input-group-text" id="basic-addon1">Prezzo minimo</label>
                            <input type="number" class="form-control"  name="min_price_d" value="{{$domicilio_p['min_price'] / 100}}">
                        </div>
                        <div class="input-group mb-3">
                            <label class="input-group-text" id="basic-addon1">Prezzo consegna</label>
                            <input type="number" class="form-control"  name="delivery_cost" value="{{$domicilio_p['delivery_cost'] / 100}}">
                        </div>
                    </div>
                </div>
                @endif
                @php
                    $setting['Periodo di Ferie']['property'] = json_decode($setting['Periodo di Ferie']['property'], true);
                @endphp
                <div class="set">
                    <h4>Ferie</h4>
                    <div class="sets">
                        <div class="radio-inputs">
                            <label class="radio">
                                <input type="radio" name="ferie_status"  @if($setting['Periodo di Ferie']['status'] == 0) checked  @endif value="0" >
                                <span class="name">A lavoro</span>
                            </label>
                            <label class="radio">
                                <input type="radio" name="ferie_status"  @if($setting['Periodo di Ferie']['status'] == 1) checked  @endif value="1" >
                                <span class="name">In ferie</span>
                            </label>
                        </div>
                        
                        <div class="input-group flex-nowrap">
                            <label for="form" class="input-group-text" >Da</label>
                            <input name="from" id="form" type="date" class="form-control" placeholder="da" @if($setting['Periodo di Ferie']['property']['from'] !== '') value="{{$setting['Periodo di Ferie']['property']['from']}}"  @endif>
                            <label for="to" class="input-group-text" >A</label>
                            <input name="to" id="to" type="date" class="form-control" placeholder="da" @if($setting['Periodo di Ferie']['property']['to'] !== '') value="{{$setting['Periodo di Ferie']['property']['to']}}"  @endif>
                        </div>
                    </div>
                </div>
                <div class="set">
                    @php
                        $promo_table = json_decode($setting['Promozione Tavoli']['property'], true);
                    @endphp
                    <h4>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-gift-fill" viewBox="0 0 16 16">
                            <path d="M3 2.5a2.5 2.5 0 0 1 5 0 2.5 2.5 0 0 1 5 0v.006c0 .07 0 .27-.038.494H15a1 1 0 0 1 1 1v1a1 1 0 0 1-1 1H1a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h2.038A3 3 0 0 1 3 2.506zm1.068.5H7v-.5a1.5 1.5 0 1 0-3 0c0 .085.002.274.045.43zM9 3h2.932l.023-.07c.043-.156.045-.345.045-.43a1.5 1.5 0 0 0-3 0zm6 4v7.5a1.5 1.5 0 0 1-1.5 1.5H9V7zM2.5 16A1.5 1.5 0 0 1 1 14.5V7h6v9z"/>
                        </svg>
                        Promo 
                    </h4>
                    <div class="sets promo_set">
                        <div class="radio-inputs">
                            <label class="radio">
                                <input type="radio" name="table_promo"  @if($setting['Promozione Tavoli']['status']== 0) checked  @endif value="0" >
                                <span class="name">Off</span>
                            </label>
                            <label class="radio">
                                <input type="radio" name="table_promo"  @if($setting['Promozione Tavoli']['status']== 1) checked  @endif value="1" >
                                <span class="name">On</span>
                            </label>
                        </div>
                        <div class="mb-3 promo_b">
                            <label class="input-group-text" >Titolo</label>
                            <input type="text" class="form-control"  name="promo_table_title" value="{{$promo_table['title']}}">

                            <label class="input-group-text" >Corpo</label>
                            <textarea type="text" class="form-control"  cols="10" rows="10"  name="promo_table_body">{{$promo_table['body']}}</textarea>
                        </div>
                    </div>
                </div>
                <button type="submit" class="my_btn_1 my_btn_2 w-75 m-auto">Aggiorna</button>
                
            </div>
            <div class="bottom-set">
                <div class="accordion accordion-flush" id="accordionFlushExample">
                    <div class="accordion-item">
                        
                        <h4 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseOne" aria-expanded="false" aria-controls="flush-collapseOne">
                            Giorni e orari d'apertura
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
                                    @foreach (['lunedì', 'martedì', 'mercoledì', 'giovedì', 'venerdì', 'sabato', 'domenica'] as $giorno)
                                        <div class="input-group mb-3">
                                            <label for="{{$giorno}}" class="input-group-text">{{ $giorno }}</label>
                                            <input id="{{$giorno}}" type="text" class="form-control" placeholder="--:-- / --:--" @if($property_orari) name="{{ $giorno }}" value="{{ $property_orari[$giorno] }}" @endif aria-label="{{ $giorno }}" id="{{$giorno}}">
                                        </div>
                                    @endforeach
                                </section>
                                <button type="submit" class="my_btn_1 my_btn_2">Aggiorna</button>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h4 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseTwo" aria-expanded="false" aria-controls="flush-collapseTwo">
                            Posizione del tuo locale
                            </button>
                        </h4>
                        <div id="flush-collapseTwo" class="accordion-collapse collapse" data-bs-parent="#accordionFlushExample">
                            <div class="accordion-body">
                                <section>

                                    @if(isset($property_posizione["foto_maps"]) && $property_posizione['foto_maps'] !== "")
                                        <img class="w-100 rounded mb-2" src="{{ asset('public/storage/' . $property_posizione['foto_maps']) }}" alt="{{ $property_posizione['foto_maps'] }}">
                                    @endif
                                    <div class="input-group mb-3">    
                                        <input type="file" id="file-input" name="foto_maps">
                                    </div>
                                    <div class="input-group mb-3">
                                        <label class="input-group-text" id="basic-addon1">Link Google Maps</label>
                                        <input type="text" class="form-control"  name="link_maps" @if($property_posizione) value="{{ $property_posizione['link_maps'] }}" @endif>
                                    </div>
                                    <div class="input-group mb-3">
                                        <label class="input-group-text" id="basic-addon1">Indirizzo</label>
                                        <input type="text" class="form-control"  name="indirizzo" @if($property_posizione) value="{{ $property_posizione['indirizzo'] }}" @endif>
                                    </div>          
                                </section>
                                <button type="submit" class="my_btn_1 my_btn_2">Aggiorna</button>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item"> 
                        <h4 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseThree" aria-expanded="false" aria-controls="flush-collapseThree">
                                Contatti e Social
                            </button>
                        </h4>
                        <div id="flush-collapseThree" class="accordion-collapse collapse" data-bs-parent="#accordionFlushExample">
                            <div class="accordion-body">
                                <section>
                                    <div class="input-group mb-3">
                                        <label for="telefono" class="input-group-text">Telefono</label>
                                        <input type="text" class="form-control"  name="telefono" @if($property_contatti) value="{{ $property_contatti['telefono'] }}" @endif>
                                    </div>
                                    <div class="input-group mb-3">
                                        <label for="email" class="input-group-text">Email</label>
                                        <input type="text" class="form-control"  name="email" @if($property_contatti) value="{{ $property_contatti['email'] }}" @endif>
                                    </div>        
                                    <div class="input-group mb-3">
                                        <label for="instagram" class="input-group-text">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-instagram" viewBox="0 0 16 16">
                                                <path d="M8 0C5.829 0 5.556.01 4.703.048 3.85.088 3.269.222 2.76.42a3.9 3.9 0 0 0-1.417.923A3.9 3.9 0 0 0 .42 2.76C.222 3.268.087 3.85.048 4.7.01 5.555 0 5.827 0 8.001c0 2.172.01 2.444.048 3.297.04.852.174 1.433.372 1.942.205.526.478.972.923 1.417.444.445.89.719 1.416.923.51.198 1.09.333 1.942.372C5.555 15.99 5.827 16 8 16s2.444-.01 3.298-.048c.851-.04 1.434-.174 1.943-.372a3.9 3.9 0 0 0 1.416-.923c.445-.445.718-.891.923-1.417.197-.509.332-1.09.372-1.942C15.99 10.445 16 10.173 16 8s-.01-2.445-.048-3.299c-.04-.851-.175-1.433-.372-1.941a3.9 3.9 0 0 0-.923-1.417A3.9 3.9 0 0 0 13.24.42c-.51-.198-1.092-.333-1.943-.372C10.443.01 10.172 0 7.998 0zm-.717 1.442h.718c2.136 0 2.389.007 3.232.046.78.035 1.204.166 1.486.275.373.145.64.319.92.599s.453.546.598.92c.11.281.24.705.275 1.485.039.843.047 1.096.047 3.231s-.008 2.389-.047 3.232c-.035.78-.166 1.203-.275 1.485a2.5 2.5 0 0 1-.599.919c-.28.28-.546.453-.92.598-.28.11-.704.24-1.485.276-.843.038-1.096.047-3.232.047s-2.39-.009-3.233-.047c-.78-.036-1.203-.166-1.485-.276a2.5 2.5 0 0 1-.92-.598 2.5 2.5 0 0 1-.6-.92c-.109-.281-.24-.705-.275-1.485-.038-.843-.046-1.096-.046-3.233s.008-2.388.046-3.231c.036-.78.166-1.204.276-1.486.145-.373.319-.64.599-.92s.546-.453.92-.598c.282-.11.705-.24 1.485-.276.738-.034 1.024-.044 2.515-.045zm4.988 1.328a.96.96 0 1 0 0 1.92.96.96 0 0 0 0-1.92m-4.27 1.122a4.109 4.109 0 1 0 0 8.217 4.109 4.109 0 0 0 0-8.217m0 1.441a2.667 2.667 0 1 1 0 5.334 2.667 2.667 0 0 1 0-5.334"/>
                                            </svg>
                                        </label>
                                        <input type="text" class="form-control"  placeholder="Link di instagram" name="instagram" @if(isset($property_contatti['instagram'])) value="{{ $property_contatti['instagram'] }}" @endif>
                                    </div>        
                                    <div class="input-group mb-3">
                                        <label for="facebook" class="input-group-text">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-facebook" viewBox="0 0 16 16">
                                                <path d="M16 8.049c0-4.446-3.582-8.05-8-8.05C3.58 0-.002 3.603-.002 8.05c0 4.017 2.926 7.347 6.75 7.951v-5.625h-2.03V8.05H6.75V6.275c0-2.017 1.195-3.131 3.022-3.131.876 0 1.791.157 1.791.157v1.98h-1.009c-.993 0-1.303.621-1.303 1.258v1.51h2.218l-.354 2.326H9.25V16c3.824-.604 6.75-3.934 6.75-7.951"/>
                                            </svg>
                                        </label>
                                        <input type="text" class="form-control"  placeholder="Link di facebook" name="facebook" @if(isset($property_contatti['facebook'])) value="{{ $property_contatti['facebook'] }}" @endif>
                                    </div>        
                                    <div class="input-group mb-3">
                                        <label for="tiktok" class="input-group-text">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-tiktok" viewBox="0 0 16 16">
                                                <path d="M9 0h1.98c.144.715.54 1.617 1.235 2.512C12.895 3.389 13.797 4 15 4v2c-1.753 0-3.07-.814-4-1.829V11a5 5 0 1 1-5-5v2a3 3 0 1 0 3 3z"/>
                                            </svg>
                                        </label>
                                        <input type="text" class="form-control"  placeholder="Link di tiktok" name="tiktok" @if(isset($property_contatti['tiktok'])) value="{{ $property_contatti['tiktok'] }}" @endif>
                                    </div>        
                                    <div class="input-group mb-3">
                                        <label for="youtube" class="input-group-text">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-youtube" viewBox="0 0 16 16">
                                                <path d="M8.051 1.999h.089c.822.003 4.987.033 6.11.335a2.01 2.01 0 0 1 1.415 1.42c.101.38.172.883.22 1.402l.01.104.022.26.008.104c.065.914.073 1.77.074 1.957v.075c-.001.194-.01 1.108-.082 2.06l-.008.105-.009.104c-.05.572-.124 1.14-.235 1.558a2.01 2.01 0 0 1-1.415 1.42c-1.16.312-5.569.334-6.18.335h-.142c-.309 0-1.587-.006-2.927-.052l-.17-.006-.087-.004-.171-.007-.171-.007c-1.11-.049-2.167-.128-2.654-.26a2.01 2.01 0 0 1-1.415-1.419c-.111-.417-.185-.986-.235-1.558L.09 9.82l-.008-.104A31 31 0 0 1 0 7.68v-.123c.002-.215.01-.958.064-1.778l.007-.103.003-.052.008-.104.022-.26.01-.104c.048-.519.119-1.023.22-1.402a2.01 2.01 0 0 1 1.415-1.42c.487-.13 1.544-.21 2.654-.26l.17-.007.172-.006.086-.003.171-.007A100 100 0 0 1 7.858 2zM6.4 5.209v4.818l4.157-2.408z"/>
                                            </svg>
                                        </label>
                                        <input type="text" class="form-control"  placeholder="Link di youtube" name="youtube" @if(isset($property_contatti['youtube'])) value="{{ $property_contatti['youtube'] }}" @endif>
                                    </div>        
                                    <div class="input-group mb-3">
                                        <label for="whatsapp" class="input-group-text">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-whatsapp" viewBox="0 0 16 16">
                                                <path d="M13.601 2.326A7.85 7.85 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.9 7.9 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.9 7.9 0 0 0 13.6 2.326zM7.994 14.521a6.6 6.6 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.56 6.56 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592m3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.73.73 0 0 0-.529.247c-.182.198-.691.677-.691 1.654s.71 1.916.81 2.049c.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232"/>
                                            </svg>
                                        </label>
                                        <input type="text" class="form-control" placeholder="+39001110000"  name="whatsapp" @if(isset($property_contatti['whatsapp'])) value="{{ $property_contatti['whatsapp'] }}" @endif>
                                    </div>        
                                </section>
                                <button type="submit" class="my_btn_1 my_btn_2">Aggiorna</button>
                            </div>
                        </div>
                    </div>
                    
                    @if (config('configurazione.subscription') > 1)
                    <div class="accordion-item">
                        @csrf
                        <h4 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseFour" aria-expanded="false" aria-controls="flush-collapseFour">
                            Gestione comuni consegna
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
                                            {{$i['price'] ? '€' . ($i['price'] / 100) : ''}}
                                        </span>    
                                    @endforeach
                                </div>   
                                <div class="actions">
                                    <button type="button" class=" my_btn_1 " data-bs-toggle="modal" data-bs-target="#staticBackdrop">Aggiungi </button>
                                    <button type="button" class="my_btn_1 trash" data-bs-toggle="modal" data-bs-target="#staticBackdrop1"> Rimuovi </button>
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
                                Gestione notifiche whatsapp
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
                                    <button type="button" class=" my_btn_1 " data-bs-toggle="modal" data-bs-target="#staticBackdrop2">Modifica</button>
                                </div>                    
                            </div>
                        </div>
                    </div> 
                    @endif
                </div>
            </div>
            <div class="actions">
                <button type="button" class=" my_btn_4 w-100 " data-bs-toggle="modal" data-bs-target="#staticBackdropav">Impostazioni avanzate</button>
            </div>   

        </form>
    </div>

    <div class="modal fade" id="staticBackdropav" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropavLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable large_m">
            <form action="{{ route('admin.settings.advanced')}}" method="POST" class="modal-content s_advanced">
                @csrf
                <h2>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-sliders" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M11.5 2a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3M9.05 3a2.5 2.5 0 0 1 4.9 0H16v1h-2.05a2.5 2.5 0 0 1-4.9 0H0V3zM4.5 7a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3M2.05 8a2.5 2.5 0 0 1 4.9 0H16v1H6.95a2.5 2.5 0 0 1-4.9 0H0V8zm9.45 4a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3m-2.45 1a2.5 2.5 0 0 1 4.9 0H16v1h-2.05a2.5 2.5 0 0 1-4.9 0H0v-1z"/>
                    </svg>
                Impostazioni Avanzate</h2>
                <div class="top-set_a">
                    <div class="set_a">
                        <h4>Gestione menu</h4>
                        <div class="radio-inputs">
                            <label class="radio">
                                <input @checked($adv['menu_fix_set']== '0') type="radio" name="menu_fix_set" value="0" >
                                <span class="name">Menu Fisso</span>
                            </label>
                            <label class="radio">
                                <input @checked($adv['menu_fix_set']== '1') type="radio" name="menu_fix_set" value="1" >
                                <span class="name">Tutti</span>
                            </label>
                            <label class="radio">
                                <input @checked($adv['menu_fix_set']== '2') type="radio" name="menu_fix_set" value="2" >
                                <span class="name">Menu carta</span>
                            </label>
                        </div>
                    </div>
                    <div class="set_a">
                        <h4>Servizi attivi</h4>
                        <div class="radio-inputs">
                            <label class="radio">
                                <input class="critical-radio1" @checked($adv['services']== '3') type="radio" name="services" value="3" >
                                <span class="name">Asporto</span>
                            </label>
                            <label class="radio">
                                <input class="critical-radio1" @checked($adv['services']== '4') type="radio" name="services" value="4" >
                                <span class="name">Tutti</span>
                            </label>
                            <label class="radio">
                                <input class="critical-radio1" @checked($adv['services']== '2') type="radio" name="services" value="2" >
                                <span class="name">Tavoli</span>
                            </label>
                            <input type="hidden" id="attivo-originale1" value="{{$adv['services']}}">
                        </div>
                    </div>
                    <div class="set_a name_c">
                        <h4>Gestione asporto</h4>
                        <div class="radio-inputs">
                            <label class="radio">
                                <input class="critical-radio2" @checked($adv['too']== 1) type="radio" name="too" value="1" >
                                <span class="name">Pezzi</span>
                            </label>
                            <label class="radio">
                                <input class="critical-radio2" @checked($adv['too']== 0)  type="radio" name="too" value="0" >
                                <span class="name">Ordini</span>
                            </label>
                            <input type="hidden" id="attivo-originale2" value="{{$adv['too']}}">
                        </div>
                        <div class="split">
                            <div class="input_label">
                                <label class="" id="basic-addon2">Cucina 1</label>
                                <input type="text" class="" name="too_1" value="{{$adv['too_1']}}">
                            </div>
                            <div class="input_label">
                                <label class="" id="basic-addon2">Cucina 2</label>
                                <input type="text" class="" name="too_2" value="{{$adv['too_2']}}">
                            </div>
                        </div>
                    </div>
                    <div class="set_a name_c">
                        <h4>Doppia sala</h4>
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
                                <label class="" id="basic-addon1">Sala 1</label>
                                <input type="text" class="" name="sala_1" value="{{$adv['sala_1']}}">
                            </div>
                            <div class="input_label">
                                <label class="" id="basic-addon1">Sala 2</label>
                                <input type="text" class="" name="sala_2" value="{{$adv['sala_2']}}">
                            </div>
                        </div>
                    </div>
                    <div class="set_a ">
              
                        <h4 class="w-100">
                            Gestione date
                        </h4>
                        <div class="split">
                            <div class="input_label">
                                <label class="" id="basic-addon1">Latenza orario - Ordini</label>
                                <input type="time" class="" name="delay_or" value="{{$adv['delay_or']}}">
                            </div>
                            <div class="input_label">
                                <label class="" id="basic-addon1">Latenza orario - Prenotazioni</label>
                                <input type="time" class="" name="delay_res" value="{{$adv['delay_res']}}">
                            </div>
                        </div>
                        <div class="split">
                            <div class="input_label">
                                <label class="" id="basic-addon1">Latenza prenotazioni (Giorni)</label>
                                <input type="number" class="" name="max_day_res" value="{{$adv['max_day_res']}}">
                            </div>
                            <div class="input_label">
                                <label class="" id="basic-addon1">Intervallo (minuti)</label>
                                <input type="number" class="" name="times_interval" value="{{$adv['times_interval']}}">
                            </div>
                        </div>
                        <div class="split">
                            <div class="input_label">
                                <label class="" id="basic-addon1">Orario di inzio</label>
                                <input type="time" class="" name="times_start" value="{{$adv['times_start']}}">
                            </div>
                            <div class="input_label">
                                <label class="" id="basic-addon1">Orario di fine</label>
                                <input type="time" class="" name="times_end" value="{{$adv['times_end']}}">
                            </div>
                        </div>
                        
                       
                    </div>
                </div>
                <div class="more_info">
                    <h4>Altre informazioni</h4>
                    <div class="split">
                        <div class="input_label">
                            <label class="" id="basic-addon1">Ragione sociale</label>
                            <input type="text" name="r_sociale" value="{{$adv['r_sociale']}}">
                        </div>
                        <div class="input_label">
                            <label class="" id="basic-addon1">P. iva</label>
                            <input type="text" name="p_iva" value="{{$adv['p_iva']}}">
                        </div>

                    </div>
                    <div class="split">
                        <div class="input_label">
                            <label class="" id="basic-addon1">Codice REA</label>
                            <input type="text" name="c_rea" value="{{$adv['c_rea']}}">
                        </div>
                        <div class="input_label">
                            <label class="" id="basic-addon1">Capitale sociale</label>
                            <input type="number" name="c_sociale" value="{{$adv['c_sociale']}}">
                        </div>
                    </div>

                    <div class="input_label ">
                        <label class="" id="basic-addon1">Codice Ateco</label>
                        <input type="text" name="c_ateco" value="{{isset($adv['c_ateco']) ? $adv['c_ateco'] : ''}}">
                    </div>
                    <div class="input_label ">
                        <label class="" id="basic-addon1">Iscrizione Ufficio Imprese</label>
                        <input type="text" name="u_imprese" value="{{$adv['u_imprese']}}">
                    </div>
                    <div class="input_label method ">
                        <label class="" id="basic-addon1">Metodi di pagamento accettati</label>
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
                                <svg xmlns="http://www.w3.org/2000/svg" width="38" height="24" fill="currentColor" class="bi bi-cash-coin" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M11 15a4 4 0 1 0 0-8 4 4 0 0 0 0 8m5-4a5 5 0 1 1-10 0 5 5 0 0 1 10 0"/><path d="M9.438 11.944c.047.596.518 1.06 1.363 1.116v.44h.375v-.443c.875-.061 1.386-.529 1.386-1.207 0-.618-.39-.936-1.09-1.1l-.296-.07v-1.2c.376.043.614.248.671.532h.658c-.047-.575-.54-1.024-1.329-1.073V8.5h-.375v.45c-.747.073-1.255.522-1.255 1.158 0 .562.378.92 1.007 1.066l.248.061v1.272c-.384-.058-.639-.27-.696-.563h-.668zm1.36-1.354c-.369-.085-.569-.26-.569-.522 0-.294.216-.514.572-.578v1.1zm.432.746c.449.104.655.272.655.569 0 .339-.257.571-.709.614v-1.195z"/><path d="M1 0a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h4.083q.088-.517.258-1H3a2 2 0 0 0-2-2V3a2 2 0 0 0 2-2h10a2 2 0 0 0 2 2v3.528c.38.34.717.728 1 1.154V1a1 1 0 0 0-1-1z"/><path d="M9.998 5.083 10 5a2 2 0 1 0-3.132 1.65 6 6 0 0 1 3.13-1.567"/></svg>
                            </label>
                        </div>
                    </div>

                </div>
                    <!-- Messaggio di avviso -->
                <div id="critical-warning" style="display: none; text-align:center;" class="error">
                    ⚠️  Se aggiorni impostazioni di servizi, doppia sala o gestione asporto,  le disponibilita che hai attualmentente impostato verranno resettate e dovrai reimpostarle.
                </div>
                <div class="modal-footer">
                    <button type="button" class="my_btn_1 d" data-bs-dismiss="modal">Annulla</button>
                    <button type="sumbit" class="my_btn_1 add">Aggiorna</button>
                </div>
            </form>
        </div>
    </div>
    @if (config('configurazione.subscription') > 2)
        <div class="modal fade" id="staticBackdrop1" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdrop1Label" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <form action="{{ route('admin.settings.updateAree')}}" method="POST" class="modal-content">
                    @csrf
                    <input type="hidden" name="ar" value="remove">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" style="color: black" id="staticBackdrop1Label">Seleziona i comuni che vuoi rimuovere</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        @php
                            if (is_string($setting['Comuni per il domicilio']['property'])) {
                                $setting['Comuni per il domicilio']['property'] = json_decode($setting['Comuni per il domicilio']['property'], true);
                            } 
                        @endphp
                        @foreach ($setting['Comuni per il domicilio']['property'] as $i)
                            <input type="checkbox" class="btn-check" id="a{{ $i['comune'] }}" name="comuni[]" value="{{ $i['comune'] }}" >
                            <label class="btn btn-outline-danger" for="a{{ $i['comune'] }}">{{ $i['provincia'] }} - {{ $i['comune'] }}</label>
                        @endforeach
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="my_btn_1" data-bs-dismiss="modal">Annulla</button>
                        <button type="sumbit" class="my_btn_2">Rimuovi comuni selezionati</button>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <form action="{{ route('admin.settings.updateAree')}}" method="POST" class="modal-content">
                    @csrf
                    <input type="hidden" name="ar" value="add">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" style="color: black" id="staticBackdropLabel">Aggiungi un comune per le consegne</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="input-group flex-nowrap py-2 w-auto">
                            <label for="comune" class="input-group-text" >Comune</label>
                            <input name="comune" id="comune" type="text" class="form-control" placeholder="comune">
                        </div>
                        <div class="input-group flex-nowrap py-2 w-auto">
                            <label for="provincia" class="input-group-text" >Provincia</label>
                            <input name="provincia" id="provincia" type="text" class="form-control" placeholder="sigla della provincia es: AN">
                        </div>
                        <div class="input-group flex-nowrap py-2 w-auto">
                            <label for="cap" class="input-group-text" >Cap</label>
                            <input name="cap" id="cap" type="text" class="form-control" placeholder="cap">
                        </div>
                        <div class="input-group flex-nowrap py-2 w-auto">
                            <label for="price" class="input-group-text" >Costo extra consegna</label>
                            <input name="price" id="price" type="number" step="0.01" class="form-control" placeholder="€ extra">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="my_btn_1 d" data-bs-dismiss="modal">Annulla</button>
                        <button type="sumbit" class="my_btn_1 add">Aggiungi nuovo comune</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="modal fade" id="staticBackdrop2" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdrop2Label" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <form action="{{ route('admin.settings.numbers')}}" method="POST" class="modal-content">
                    @csrf
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" style="color: black" id="staticBackdrop2Label">Modifica i numeri che possono ricevere le notifiche wa</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="input-group flex-nowrap py-2 w-auto">
                            <label for="numbers[]" class="input-group-text" >1# Numero</label>
                            <input name="numbers[]" id="numbers[]" type="text" class="form-control" placeholder="39000111000">
                        </div>
                        @if (config('configurazione.subscription') == 5)
                            
                        <div class="input-group flex-nowrap py-2 w-auto">
                            <label for="numbers[]" class="input-group-text" >2# Numero</label>
                            <input name="numbers[]" id="numbers[]" type="text" class="form-control" placeholder="39000111000">
                        </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="my_btn_1 d" data-bs-dismiss="modal">Annulla</button>
                        <button type="sumbit" class="my_btn_1 add">Modifica</button>
                    </div>
                </form>
            </div>
        </div>
    
    @endif
</div>
@if (config('configurazione.subscription') > 1 && ($setting['Prenotazione Tavoli']['status'] == 2 || $setting['Prenotazione Asporti']['status'] == 2) && config('configurazione.APP_URL') !== 'http://127.0.0.1:8000')

@endif
@if (config('configurazione.subscription') > 1 )
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns@3"></script>
    <script>
        
        document.addEventListener('DOMContentLoaded', async function() {
            // Fetch the chart data
            const data = @json($chartData); 
            console.log('Chart Data:', data);
            // Initialize the chart
        
            const ctx = document.getElementById('chartCanvas').getContext('2d');
            const totalDuration = 1000;
            const delayBetweenPoints = totalDuration / data.labels.length;
            const previousY = (ctx) => ctx.index === 0 ? ctx.chart.scales.y.getPixelForValue(10) : ctx.chart.getDatasetMeta(ctx.datasetIndex).data[ctx.index - 1].getProps(['y'], true).y;
            const animation = {
                x: {
                    type: 'number',
                    easing: 'linear',
                    duration: delayBetweenPoints,
                    from: NaN, // the point is initially skipped
                    delay(ctx) {
                    if (ctx.type !== 'data' || ctx.xStarted) {
                        return 0;
                    }
                    ctx.xStarted = true;
                    return ctx.index * delayBetweenPoints;
                    }
                },
                y: {
                    type: 'number',
                    easing: 'linear',
                    duration: delayBetweenPoints,
                    from: previousY,
                    delay(ctx) {
                    if (ctx.type !== 'data' || ctx.yStarted) {
                        return 0;
                    }
                    ctx.yStarted = true;
                    return ctx.index * delayBetweenPoints;
                    }
                }
            };
                let c3 = '#090333'
                let c2 = '#10b793'
                let c1 = '#d8dde8'
                let ct = 'rgba(244, 243, 0, 0)'
                    

            const chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: data.datasets
                },
                options: {
                    animation,
                    interaction: {
                        intersect: false
                    },
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                pointStyle: 'rectRounded',
                                color: c3
                            }
                        },
                        title: {
                            display: true,
                            text: 'Andamento ordini e prenotazioni nel tempo',
                            color: c3
                        }
                    },
                    scales: {
                        x: {
                            // grid: {
                            //     color : ct
                            // },
                            ticks: {
                                color: c3
                            },
                            title: {
                                display: false,
                                text: 'Count'
                            },
                            beginAtZero: true,
                            type: 'time', // Configura l'asse X come asse temporale
                            time: {
                                unit: 'week', // Mostra un intervallo basato sui giorni
                                tooltipFormat: 'MM-dd', // Formato per il tooltip
                                displayFormats: {
                                    week: 'MM-dd' // Formato per le etichette
                                },
                            },
                        },
                        y: {
                            grid: {
                                color : ct
                            },
                            ticks: {
                                color: c3
                            },
                            title: {
                                display: false,
                                text: 'Count'
                            },
                            beginAtZero: true
                        }
                    }
                }
            });

            // messaggio nel form advanced
        }); 
    </script>

@endif
<script>  
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
</script>

@endsection

