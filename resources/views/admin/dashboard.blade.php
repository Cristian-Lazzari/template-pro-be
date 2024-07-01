@extends('layouts.base')

@section('contents')
@if (session('success'))
    @php
        $data = session('success')
    @endphp
    <div class="alert alert-light">
        {{ $data }} 
    </div>
@endif
<div class="dash-c">
    <div class="top-c">
        <div class="prod">
            <div class="top-p">
                <a href="{{ route('admin.products.index') }}"> <h2>I tuoi prodotti</h2></a>
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
                <a href="{{ route('admin.categories.index') }}" class="my_btn_1">Categorie</a>
                <a href="{{ route('admin.ingredients.index') }}" class="my_btn_1">Ingredienti</a>
            </div>
        </div>      
        @if (config('configurazione.pack') > 2 )
            <div class="right-t">
                @if (config('configurazione.pack') > 3 )
                <div class="result-bar">
                    <div class="stat">
                        <h2>€{{$traguard[1]}}</h2>
                        <span>questo mese</span>
                    </div>
                    <div class="stat">
                        <h2>€{{$traguard[2]}}</h2>
                        <span>questo anno</span>
                    </div>
                </div>
                <div class="delivery-c">
                    <div class="top-p">
                        <h3>Ordini asporto/delivery</h3>
                        <a href="{{ route('admin.orders.index') }}" class="plus">
                            <div class="line"></div>
                            <div class="line l2"></div>
                        </a>
                    </div>
                    <div class="stat-p">
                        <div class="stat">
                            <h3>{{$order[1]}}</h3>
                            <span>in elaborazione</span>
                        </div>
                        <div class="stat">
                            <h3>{{$order[2]}}</h3>
                            <span>confermate</span>
                        </div>
                        <div class="stat">
                            <h3>{{$order[3]}}</h3>
                            <span>annullate</span>
                        </div>
                    </div>
                </div>
                @endif
                <div class="delivery-c">
                    <div class="top-p">
                        <h3>Prenotazioni Tavoli</h3>
                        <a href="{{ route('admin.reservations.index') }}" class="plus">
                            <div class="line"></div>
                            <div class="line l2"></div>
                        </a>
                    </div>
                    <div class="stat-p">
                        <div class="stat">
                            <h3>{{$reservation[1]}}</h3>
                            <span>in elaborazione</span>
                        </div>
                        <div class="stat">
                            <h3>{{$reservation[2]}}</h3>
                            <span>confermate</span>
                        </div>
                        <div class="stat">
                            <h3>{{$reservation[3]}}</h3>
                            <span>annullate</span>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        <div class="prod post">  
            <div class="top-p">
                <h3>I tuoi post</h3>
                <a href="{{ route('admin.posts.index') }}" class="plus">
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
        @if (isset($year))
            <div class="date_index">
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
                        <div class="carousel-item
                        @if ($currentMonth == $m['month'] && $currentYear == $m['year'])
                            active 
                        @endif
                        ">
                            
                            <h2 class="my">{{config('configurazione.mesi')[$m['month']]}} - {{$m['year']}}</h2>
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
                                        <form action="{{ route('admin.dates.showDay') }}" class="day {{ 'd' . $d['day_w']}} @if(!isset($d['time'])) day-off @endif @if($currentMonth == $m['month'] && $currentYear == $m['year'] && $currentDay == $d['day']) day-active @endif " style="grid-column-start:{{$d['day_w'] }}" method="get">
                                            @csrf
                                            <input type="hidden" name="date" value="{{$d['date']}}">
                                            @if(isset($d['asporto']) && $d['asporto'] !== 0 )
                                                <p class="pop1">
                                                    <span>{{$d['asporto']}}</span>
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-cloud-fog-fill" viewBox="0 0 16 16">
                                                        <path d="M3 13.5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m10.405-9.473a5.001 5.001 0 0 0-9.499-1.004A3.5 3.5 0 1 0 3.5 12H13a3 3 0 0 0 .405-5.973"/>
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
        @else 
        <div class="date-off">
            <a href="{{route('admin.dates.index')}}">Non sono ancora state impostate le disponibilita dei servizi, clicca QUI e impostale ora</a>
        </div>
        @endif
       </div>
        <div class="setting">
            <h2>Impostazioni</h2>
            <form action="{{ route('admin.settings.updateAll')}}" method="POST" class="top-set">
                @csrf
                <div class="set">
                    <h5>Asporto</h5>
                    <div class="radio-inputs">
                        <label class="radio">
                            <input type="radio" name="asporto_status"  @if($setting[1]['status'] == 0) checked  @endif value="0" >
                            <span class="name">Off</span>
                        </label>
                        <label class="radio">
                            <input type="radio" name="asporto_status"  @if($setting[1]['status'] == 1) checked  @endif value="1" >
                            <span class="name">Chiamate</span>
                        </label>
                        @if (config('configurazione.pack') == 3 || config('configurazione.pack') == 4)   
                        <label class="radio">
                            <input type="radio" name="asporto_status"  @if($setting[1]['status'] == 2) checked  @endif value="2" >
                            <span class="name">Web App</span>
                        </label>
                        @endif
                    </div>
                </div>
                <div class="set">
                    <h5>Tavoli</h5>
                    <div class="radio-inputs">
                        <label class="radio">
                            <input type="radio" name="tavoli_status"  @if($setting[0]['status'] == 0) checked  @endif value="0" >
                            <span class="name">Off</span>
                        </label>
                        <label class="radio">
                            <input type="radio" name="tavoli_status"  @if($setting[0]['status'] == 1) checked  @endif value="1" >
                            <span class="name">Chiamate</span>
                        </label>
                        @if (config('configurazione.pack') == 3 || config('configurazione.pack') == 4)   
                        <label class="radio">
                            <input type="radio" name="tavoli_status"  @if($setting[0]['status'] == 2) checked  @endif value="2" >
                            <span class="name">Web App</span>
                        </label>
                        @endif
                    </div>
                </div>
                @if (config('configurazione.pack') == 3 || config('configurazione.pack') == 4)
                <div class="set">
                    <h5>Domicilio</h5>
                    <div class="radio-inputs">
                        <label class="radio">
                            <input type="radio" name="domicilio_status"  @if($setting[6]['status'] == 0) checked  @endif value="0" >
                            <span class="name">off</span>
                        </label>
                        <label class="radio">
                            <input type="radio" name="domicilio_status"  @if($setting[6]['status'] == 1) checked  @endif value="1" >
                            <span class="name">On</span>
                        </label>
                    </div>
                </div>
                @endif
                @php
                    $setting[2]['property'] = json_decode($setting[2]['property'], true);
                @endphp
                <div class="set">
                    <h5>Ferie</h5>
                    <div class="sets">
                        <div class="radio-inputs">
                            <label class="radio">
                                <input type="radio" name="ferie_status"  @if($setting[6]['status'] == 0) checked  @endif value="0" >
                                <span class="name">A lavoro</span>
                            </label>
                            <label class="radio">
                                <input type="radio" name="ferie_status"  @if($setting[6]['status'] == 1) checked  @endif value="1" >
                                <span class="name">In ferie</span>
                            </label>
                        </div>
                        <div class="input-group flex-nowrap">
                            <label for="form" class="input-group-text" >Da</label>
                            <input name="from" id="form" type="date" class="form-control" placeholder="da" @if($setting[2]['property']['from'] !== '') value="{{$setting[2]['property']['from']}}"  @endif>
                            <label for="to" class="input-group-text" >A</label>
                            <input name="to" id="to" type="date" class="form-control" placeholder="da" @if($setting[2]['property']['to'] !== '') value="{{$setting[2]['property']['to']}}"  @endif>
                        </div>
                    </div>
                </div>
                <button type="submit" class="my_btn_1 my_btn_2 w-75 m-auto">Aggiorna</button>
                
            </form>
            <div class="bottom-set">
                <div class="accordion accordion-flush" id="accordionFlushExample">
                    <form action="{{ route('admin.settings.updateAll')}}" method="POST" class="accordion-item">
                        @csrf
                        <h4 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseOne" aria-expanded="false" aria-controls="flush-collapseOne">
                            Giorni e orari d'apertura
                            </button>
                        </h4>
                        <div id="flush-collapseOne" class="accordion-collapse collapse" data-bs-parent="#accordionFlushExample">
                            <div class="accordion-body">
                                @php 
                                    $property_orari = json_decode($setting[3]['property'], true);
                                    $property_posizione = json_decode($setting[4]['property'], true);
                                    $property_contatti = json_decode($setting[5]['property'], true);
                                @endphp
                                <section class="activity-day">
                                    <h3> Giorni di attività </h3>
                                    @foreach (array_slice(config('configurazione.days_name'), 1) as $giorno)
                                        <div class="input-group mb-3">
                                            <label for="{{$giorno}}" class="input-group-text" id="basic-addon2">{{ $giorno }}</label>
                                            <input id="{{$giorno}}" type="text" class="form-control" placeholder="19:30 - 23:30" @if($property_contatti) name="{{ $giorno }}" value="{{ $property_orari[$giorno] }}" @endif aria-label="{{ $giorno }}" aria-describedby="basic-addon2">
                                        </div>
                                    @endforeach
                                </section>
                                <button type="submit" class="my_btn_1 my_btn_2">Aggiorna</button>
                            </div>
                        </div>
                    </form>
                    <form action="{{ route('admin.settings.updateAll')}}" method="POST" class="accordion-item" enctype="multipart/form-data">
                        @csrf
                        <h4 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseTwo" aria-expanded="false" aria-controls="flush-collapseTwo">
                            Posizione del tuo locale
                            </button>
                        </h4>
                        <div id="flush-collapseTwo" class="accordion-collapse collapse" data-bs-parent="#accordionFlushExample">
                            <div class="accordion-body">
                                <section>
                                    <h3> Posizione </h3>
                                    @dump($property_posizione['foto_maps'])
                                    @if(isset($property_posizione["foto_maps"]) && $property_posizione['foto_maps'] !== "")
                                        <img src="{{ asset('public/storage/' . $property_posizione['foto_maps']) }}" alt="{{ $property_posizione['foto_maps'] }}">
                                        <div class="input-group mb-3">
                                            <input type="file" class="form-control" aria-describedby="basic-addon1" name="foto_maps">
                                        </div>
                                    @else
                                        <div class="input-group mb-3">
                                            
                                            <input type="file" class="form-control" aria-describedby="basic-addon1" name="foto_maps">
                                        </div>
                                    @endif
                                    <div class="input-group mb-3">
                                        <label class="input-group-text" id="basic-addon1">Link Google Maps</label>
                                        <input type="text" class="form-control" aria-describedby="basic-addon1" name="link_maps" @if($property_contatti) value="{{ $property_posizione['link_maps'] }}" @endif>
                                    </div>
                                    <div class="input-group mb-3">
                                        <label class="input-group-text" id="basic-addon1">Indirizzo</label>
                                        <input type="text" class="form-control" aria-describedby="basic-addon1" name="indirizzo" @if($property_contatti) value="{{ $property_posizione['indirizzo'] }}" @endif>
                                    </div>          
                                </section>
                                <button type="submit" class="my_btn_1 my_btn_2">Aggiorna</button>
                            </div>
                        </div>
                    </form>
                    <form action="{{ route('admin.settings.updateAll')}}" method="POST" class="accordion-item">
                        @csrf
                        <h4 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseThree" aria-expanded="false" aria-controls="flush-collapseThree">
                            Contatti
                            </button>
                        </h4>
                        <div id="flush-collapseThree" class="accordion-collapse collapse" data-bs-parent="#accordionFlushExample">
                            <div class="accordion-body">
                                <section>
                                    <h3> Contatti </h3>
                                    <div class="input-group mb-3">
                                        <span class="input-group-text" id="basic-addon1">Telefono</span>
                                        <input type="text" class="form-control" aria-describedby="basic-addon1" name="telefono" @if($property_contatti) value="{{ $property_contatti['telefono'] }}" @endif>
                                    </div>
                                    <div class="input-group mb-3">
                                        <span class="input-group-text" id="basic-addon1">Email</span>
                                        <input type="text" class="form-control" aria-describedby="basic-addon1" name="email" @if($property_contatti) value="{{ $property_contatti['email'] }}" @endif>
                                    </div>        
                                </section>
                                <button type="submit" class="my_btn_1 my_btn_2">Aggiorna</button>
                            </div>
                        </div>
                    </form>
                    @if (config('configurazione.pack') == 3 || config('configurazione.pack') == 4)
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
                                    if (is_string($setting[7]['property'])) {
                                        $setting[7]['property'] = json_decode($setting[7]['property'], true);
                                    } 
                                @endphp
                                <h2 >Gestione indirizzi di consegna</h2>
                                <div class="actions">
                                    <button type="button" class=" my_btn_1 " data-bs-toggle="modal" data-bs-target="#staticBackdrop">Crea nuovo </button>
                                    <button type="button" class="my_btn_1 trash" data-bs-toggle="modal" data-bs-target="#staticBackdrop1"> Modifica selezione </button>
                                </div>
                                
                                <div class="address"> 
                                    @foreach ($setting[7]['property'] as $i)
                                        <span class="">
                                            ({{$i['provincia']}})
                                            {{$i['comune']}}
                                        </span>    
                                    @endforeach
                                </div>                          
                            </div>
                        </div>
                    </div> 
                    @endif
                  </div>
            </div>

        </div>
    </div>



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
                    @foreach ($setting[7]['property'] as $i)
                        <input type="checkbox" class="btn-check" id="a{{ $i['comune'] }}" name="comuni[]" value="{{ $i['comune'] }}" >
                        <label class="btn btn-outline-danger" for="a{{ $i['comune'] }}">{{ $i['provincia'] }} - {{ $i['comune'] }}</label>
                    @endforeach
                </div>
                <div class="modal-footer">
                    <button type="button" class="my_btn d" data-bs-dismiss="modal">Annulla</button>
                    <button type="sumbit" class="my_btn add">Rimuovi comuni selezionati</button>
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
                </div>
                <div class="modal-footer">
                    <button type="button" class="my_btn d" data-bs-dismiss="modal">Annulla</button>
                    <button type="sumbit" class="my_btn add">Aggiungi nuovo comune</button>
                </div>
            </form>
        </div>
    </div>


</div>
@endsection

