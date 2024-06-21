@extends('layouts.base')

@section('contents')


<div class="cont-dash">
    <div class="mycDash my-5 ">
        <section>
            <a class="small s1a" href="">
                <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="currentColor" class="bi bi-gear-wide-connected" viewBox="0 0 16 16">
                    <path d="M7.068.727c.243-.97 1.62-.97 1.864 0l.071.286a.96.96 0 0 0 1.622.434l.205-.211c.695-.719 1.888-.03 1.613.931l-.08.284a.96.96 0 0 0 1.187 1.187l.283-.081c.96-.275 1.65.918.931 1.613l-.211.205a.96.96 0 0 0 .434 1.622l.286.071c.97.243.97 1.62 0 1.864l-.286.071a.96.96 0 0 0-.434 1.622l.211.205c.719.695.03 1.888-.931 1.613l-.284-.08a.96.96 0 0 0-1.187 1.187l.081.283c.275.96-.918 1.65-1.613.931l-.205-.211a.96.96 0 0 0-1.622.434l-.071.286c-.243.97-1.62.97-1.864 0l-.071-.286a.96.96 0 0 0-1.622-.434l-.205.211c-.695.719-1.888.03-1.613-.931l.08-.284a.96.96 0 0 0-1.186-1.187l-.284.081c-.96.275-1.65-.918-.931-1.613l.211-.205a.96.96 0 0 0-.434-1.622l-.286-.071c-.97-.243-.97-1.62 0-1.864l.286-.071a.96.96 0 0 0 .434-1.622l-.211-.205c-.719-.695-.03-1.888.931-1.613l.284.08a.96.96 0 0 0 1.187-1.186l-.081-.284c-.275-.96.918-1.65 1.613-.931l.205.211a.96.96 0 0 0 1.622-.434zM12.973 8.5H8.25l-2.834 3.779A4.998 4.998 0 0 0 12.973 8.5m0-1a4.998 4.998 0 0 0-7.557-3.779l2.834 3.78zM5.048 3.967l-.087.065zm-.431.355A4.98 4.98 0 0 0 3.002 8c0 1.455.622 2.765 1.615 3.678L7.375 8zm.344 7.646.087.065z"/>
                </svg>
            </a>
        
            
            <a class=" s1b" href="{{ route('admin.products.index') }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="currentColor" class="bi bi-handbag" viewBox="0 0 16 16">
                    <path d="M8 1a2 2 0 0 1 2 2v2H6V3a2 2 0 0 1 2-2m3 4V3a3 3 0 1 0-6 0v2H3.36a1.5 1.5 0 0 0-1.483 1.277L.85 13.13A2.5 2.5 0 0 0 3.322 16h9.355a2.5 2.5 0 0 0 2.473-2.87l-1.028-6.853A1.5 1.5 0 0 0 12.64 5zm-1 1v1.5a.5.5 0 0 0 1 0V6h1.639a.5.5 0 0 1 .494.426l1.028 6.851A1.5 1.5 0 0 1 12.678 15H3.322a1.5 1.5 0 0 1-1.483-1.723l1.028-6.851A.5.5 0 0 1 3.36 6H5v1.5a.5.5 0 1 0 1 0V6z"/>
                  </svg>
                Prodotti
            </a>
        </section>
        <section>
            <a class="small s2a" href="{{ route('admin.dates.index') }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="currentColor" class="bi bi-calendar-week" viewBox="0 0 16 16">
                    <path d="M11 6.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm-3 0a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm-5 3a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm3 0a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5z"/>
                    <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4z"/>
                  </svg>
            </a>
            <a class=" s2b" href="{{ route('admin.categories.index') }}">
                Categorie
            </a>
        
        
            <a class="s2c" href="{{ route('admin.ingredients.index') }}">
                Ingredienti
            </a>
        </section>
        <section>
            <a class="s3b" href="{{ route('admin.posts.index') }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="currentColor" class="bi bi-hash" viewBox="0 0 16 16">
                    <path d="M8.39 12.648a1 1 0 0 0-.015.18c0 .305.21.508.5.508.266 0 .492-.172.555-.477l.554-2.703h1.204c.421 0 .617-.234.617-.547 0-.312-.188-.53-.617-.53h-.985l.516-2.524h1.265c.43 0 .618-.227.618-.547 0-.313-.188-.524-.618-.524h-1.046l.476-2.304a1 1 0 0 0 .016-.164.51.51 0 0 0-.516-.516.54.54 0 0 0-.539.43l-.523 2.554H7.617l.477-2.304c.008-.04.015-.118.015-.164a.51.51 0 0 0-.523-.516.54.54 0 0 0-.531.43L6.53 5.484H5.414c-.43 0-.617.22-.617.532s.187.539.617.539h.906l-.515 2.523H4.609c-.421 0-.609.219-.609.531s.188.547.61.547h.976l-.516 2.492c-.008.04-.015.125-.015.18 0 .305.21.508.5.508.265 0 .492-.172.554-.477l.555-2.703h2.242zm-1-6.109h2.266l-.515 2.563H6.859l.532-2.563z"/>
                  </svg> 
                  About us
            </a>
        </section>
        <section>
            <a class="s4a" href="{{ route('admin.reservations.index') }}">
                Prenotazioni Tavoli
            </a>
            <a class="small s4b" href="{{ route('admin.products.create') }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="currentColor" class="bi bi-calendar-plus" viewBox="0 0 16 16">
                    <path d="M8 7a.5.5 0 0 1 .5.5V9H10a.5.5 0 0 1 0 1H8.5v1.5a.5.5 0 0 1-1 0V10H6a.5.5 0 0 1 0-1h1.5V7.5A.5.5 0 0 1 8 7"/>
                    <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4z"/>
                </svg>
            </a>
        </section>
        <section>
            <a class="s5a" href="{{ route('admin.orders.index') }}">
                Ordinazioni
            </a>
            <a class="small s5b" href="{{ route('admin.orders.create') }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="currentColor" class="bi bi-cart-plus-fill" viewBox="0 0 16 16">
                    <path d="M.5 1a.5.5 0 0 0 0 1h1.11l.401 1.607 1.498 7.985A.5.5 0 0 0 4 12h1a2 2 0 1 0 0 4 2 2 0 0 0 0-4h7a2 2 0 1 0 0 4 2 2 0 0 0 0-4h1a.5.5 0 0 0 .491-.408l1.5-8A.5.5 0 0 0 14.5 3H2.89l-.405-1.621A.5.5 0 0 0 2 1zM6 14a1 1 0 1 1-2 0 1 1 0 0 1 2 0m7 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0M9 5.5V7h1.5a.5.5 0 0 1 0 1H9v1.5a.5.5 0 0 1-1 0V8H6.5a.5.5 0 0 1 0-1H8V5.5a.5.5 0 0 1 1 0"/>
                  </svg>
            </a>
        </section>
    
        </div>
        @if (isset($year))
            <div class="date_index my-5">
                <div id="carouselExampleIndicators" class="carousel slide">
                    <div class="carousel-indicators">
    
                        @php $i = 0; @endphp
                        @foreach ($year as $m)
                            <button type="button"  data-bs-target="#carouselExampleIndicators" data-bs-slide-to="{{$i}}"
                            @if ($i == 0)
                                class="active" aria-current="true" 
                            @endif
                            aria-label="{{ 'Slide ' . $i }}"></button>
                            @php $i ++ @endphp
                        @endforeach
                    </div>
                    <div class="carousel-inner">
                    @php $i = 0; @endphp
                    @foreach ($year as $m)
                        <div class="carousel-item @if ($i == 0) active @endif">
                            <h2 class="my">{{config('configurazione.mesi')[$m['month']]}} - {{$m['year']}}</h2>
                            <div class="calendar-c">
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
                                        <form action="{{ route('admin.dates.showDay') }}" class="day {{ 'd' . $d['day_w']}} @if(!isset($d['time'])) day-off @endif " style="grid-column-start:{{$d['day_w'] }}" method="get">
                                            @csrf
                                            <input type="hidden" name="date" value="{{$d['date']}}">
                                            @if(isset($d['asporto']))<p class="pop1"> <span>{{$d['asporto']}}</span> <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-cloud-fog-fill" viewBox="0 0 16 16">
                                                <path d="M3 13.5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m10.405-9.473a5.001 5.001 0 0 0-9.499-1.004A3.5 3.5 0 1 0 3.5 12H13a3 3 0 0 0 .405-5.973"/>
                                            </svg> </p>@endif
                                            @if(isset($d['domicilio']))<p class="pop2"><span>{{$d['domicilio']}}</span> <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-mailbox-flag" viewBox="0 0 16 16">
                                                <path d="M10.5 8.5V3.707l.854-.853A.5.5 0 0 0 11.5 2.5v-2A.5.5 0 0 0 11 0H9.5a.5.5 0 0 0-.5.5v8zM5 7c0 .334-.164.264-.415.157C4.42 7.087 4.218 7 4 7s-.42.086-.585.157C3.164 7.264 3 7.334 3 7a1 1 0 0 1 2 0"/>
                                                <path d="M4 3h4v1H6.646A4 4 0 0 1 8 7v6h7V7a3 3 0 0 0-3-3V3a4 4 0 0 1 4 4v6a1 1 0 0 1-1 1H1a1 1 0 0 1-1-1V7a4 4 0 0 1 4-4m0 1a3 3 0 0 0-3 3v6h6V7a3 3 0 0 0-3-3"/>
                                            </svg> </p>@endif
                                            @if(isset($d['table']))<p class="pop3"> <span>{{$d['table']}}</span> <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-lines-fill" viewBox="0 0 16 16">
                                                <path d="M6 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6m-5 6s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zM11 3.5a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 0 1h-4a.5.5 0 0 1-.5-.5m.5 2.5a.5.5 0 0 0 0 1h4a.5.5 0 0 0 0-1zm2 3a.5.5 0 0 0 0 1h2a.5.5 0 0 0 0-1zm0 3a.5.5 0 0 0 0 1h2a.5.5 0 0 0 0-1z"/>
                                            </svg></p>@endif
                                            <button class="b">{{$d['day']}}</button>
                                        </form>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @php $i ++ @endphp
                    @endforeach
    
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                    </button>
                </div>
            </div>
        @else <p style="text-align: center" class="m-5" >Non sono state impostate le disponibilità per i servizi</p>
        @endif
    
    <div id="setting">
        <form action="{{ route('admin.settings.updateAll')}}" class="setting" method="POST">
            @csrf
            
    
            <h1>Impostazioni</h1>
            
            <section>
                <h3> Prenotazioni Tavoli </h3>
                <div class="radio-inputs mb-3">
                    <label class="radio">
                        <input type="radio" name="tavoli_status" @if($setting[0]['status'] == 0) checked  @endif value="0" >
                        <span class="name">Non Visibile</span>
                    </label>
                    <label class="radio">
                        <input type="radio" name="tavoli_status"  @if($setting[0]['status'] == 1) checked  @endif value="1" >
                        <span class="name">Chiamate</span>
                    </label>
                        
                    <label class="radio">
                        <input type="radio" name="tavoli_status"  @if($setting[0]['status'] == 2) checked  @endif value="2" >
                        <span class="name">Web App</span>
                    </label>
                </div>
            </section>
            <section>
                <h3> Ordini d'Asporto </h3>
                <div class="radio-inputs mb-3">
                    <label class="radio">
                        <input type="radio" name="asporto_status"  @if($setting[1]['status'] == 0) checked  @endif value="0" >
                        <span class="name">Non Visibile</span>
                    </label>
                    <label class="radio">
                        <input type="radio" name="asporto_status"  @if($setting[1]['status'] == 1) checked  @endif value="1" >
                        <span class="name">Chiamate</span>
                    </label>
                        
                    <label class="radio">
                        <input type="radio" name="asporto_status"  @if($setting[1]['status'] == 2) checked  @endif value="2" >
                        <span class="name">Web App</span>
                    </label>
                </div>
            </section>   
            @if (config('configurazione.pack') == 3 || config('configurazione.pack') == 4) 
                <section>
                    <h3> Ordini a Domicilio </h3>
                    <div class="radio-inputs mb-3">
                        <label class="radio">
                            <input type="radio" name="domicilio_status"  @if($setting[6]['status'] == 0) checked  @endif value="0" >
                            <span class="name">Non Visibile</span>
                        </label>
                        <label class="radio">
                            <input type="radio" name="domicilio_status"  @if($setting[6]['status'] == 1) checked  @endif value="1" >
                            <span class="name">Chiamate</span>
                        </label>
                            
                        <label class="radio">
                            <input type="radio" name="domicilio_status"  @if($setting[6]['status'] == 2) checked  @endif value="2" >
                            <span class="name">Web App</span>
                        </label>
                    </div>
                </section>
            @endif
            @php
                $setting[2]['property'] = json_decode($setting[2]['property'], true);
            @endphp
            <section>
                <h3> Ferie </h3>
                <div class="radio-inputs">
                    <label class="radio">
                        <input type="radio" name="ferie_status"  @if($setting[2]['status'] == 0) checked  @endif value="0" >
                        <span class="name">Aperti</span>
                    </label>
                    <label class="radio">
                        <input type="radio" name="ferie_status"  @if($setting[2]['status'] == 1) checked  @endif value="1" >
                        <span class="name">In Ferie</span>
                    </label>
                </div>
                <h5 class="pt-4 ">Indica il periodo in cui sei in ferie</h5>
                <div class="input-group flex-nowrap py-2 w-auto mb-3">
                    <label for="form" class="input-group-text" >Da</label>
                    <input name="from" id="form" type="date" class="form-control" placeholder="da" @if($setting[2]['property']['from'] !== '') value="{{$setting[2]['property']['from']}}"  @endif>
                    <label for="to" class="input-group-text" >A</label>
                    <input name="to" id="to" type="date" class="form-control" placeholder="da" @if($setting[2]['property']['to'] !== '') value="{{$setting[2]['property']['to']}}"  @endif>
                </div>
            </section>

            <section>
                <h3> Giorni di attività </h3>
                @foreach (array_slice(config('configurazione.days_name'), 1) as $giorno)
                    <div class="input-group mb-3">
                        <span class="input-group-text" id="basic-addon2">{{ $giorno }}</span>
                        <input type="text" class="form-control" placeholder="19:30 - 23:30" name="{{ $giorno }}"  aria-label="{{ $giorno }}" aria-describedby="basic-addon2">
                    </div>
                @endforeach
            </section>

            <section>
                <h3> Posizione </h3>
                <div class="input-group mb-3">
                    <span class="input-group-text" id="basic-addon1">Foto Google Maps</span>
                    <input type="text" class="form-control" aria-describedby="basic-addon1" name="foto_maps">
                </div>
                <div class="input-group mb-3">
                    <span class="input-group-text" id="basic-addon1">Link Google Maps</span>
                    <input type="text" class="form-control" aria-describedby="basic-addon1" name="link_maps">
                </div>
                <div class="input-group mb-3">
                    <span class="input-group-text" id="basic-addon1">Indirizzo</span>
                    <input type="text" class="form-control" aria-describedby="basic-addon1" name="indirizzo">
                </div>          
            </section>

            <section>
                <h3> Contatti </h3>
                <div class="input-group mb-3">
                    <span class="input-group-text" id="basic-addon1">Telefono</span>
                    <input type="text" class="form-control" aria-describedby="basic-addon1" name="telefono">
                </div>
                <div class="input-group mb-3">
                    <span class="input-group-text" id="basic-addon1">Email</span>
                    <input type="text" class="form-control" aria-describedby="basic-addon1" name="email">
                </div>        
            </section>


    
            <button type="submit" class="my_btn mb-3">Modifica</button>
        </form>
       
        @if (config('configurazione.pack') == 3 || config('configurazione.pack') == 4)
            @php
            if (is_string($setting[7]['property'])) {
                $setting[7]['property'] = json_decode($setting[7]['property'], true);
            } 
            @endphp
            <h2 >Gestione indirizzi di consegna</h2>
            <button type="button" class="my_btn create mb-3" data-bs-toggle="modal" data-bs-target="#staticBackdrop">
                Crea nuovo
            </button>
              
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
            <button type="button" class="my_btn trash" data-bs-toggle="modal" data-bs-target="#staticBackdrop1">
                Modifica selezione
            </button>
              
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


            <div class="addres"> 
                @foreach ($setting[7]['property'] as $i)
                    <span class="my_btn">
                        {{$i['provincia']}}
                        {{$i['comune']}}
                    </span>    
                @endforeach
                </div>
        @endif
    </div>


</div>


@endsection
