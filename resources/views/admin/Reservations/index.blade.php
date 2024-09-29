@extends('layouts.base')



@section('contents')
@if (session('success'))
    @php
        $data = session('success')
    @endphp
    <div class="alert alert-primary">
        {{ $data }}
    </div>
@endif
@if (session('filter'))
    @php
        $data = session('filter');
        $filters = $data[0];
        $reservations = $data[1];
    @endphp
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        
      Filtri aggiornati
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
<button onclick="history.back()" class="btn btn-outline-light my-5">
    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-90deg-left" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M1.146 4.854a.5.5 0 0 1 0-.708l4-4a.5.5 0 1 1 .708.708L2.707 4H12.5A2.5 2.5 0 0 1 15 6.5v8a.5.5 0 0 1-1 0v-8A1.5 1.5 0 0 0 12.5 5H2.707l3.147 3.146a.5.5 0 1 1-.708.708l-4-4z"/></svg>
</button>
<h1>Prenotazioni Tavoli</h1>

<form class="top-bar-product" action="{{ route('admin.reservations.filter') }}" method="post">
    @csrf   
    <div class="bar">
        <div class="s-name">
            <label for="name" class="fw-semibold">Nome Cliente</label>
            <div>
                <input type="text" class="" id="name" name="name"
                    @if (isset($filters))
                        value="{{  $filters['name'] }}"  
                    @endif > 
                
            </div>
        </div>
        <div class="s-name">
            <label for="date" class="fw-semibold">Data</label>
            <div>
                <input type="date" class="" id="name" name="date"
                    @if (isset($filters))
                        value="{{  $filters['name'] }}"  
                    @endif > 
                
            </div>
        </div>


        <div>
            <label for="status" class="form-label fw-semibold">Status</label>
            <select class="" id="status" name="status" >
                <option @if (isset($filters) && $filters['status'] == '2') selected @endif value="2">In Elaborazione</option>
                <option @if (isset($filters) && $filters['status'] == '1') selected @endif value="1">Confermate</option>
                <option @if (isset($filters) && $filters['status'] == '0') selected @endif value="0">Annullate</option>
                <option @if (isset($filters) && $filters['status'] == '3') selected @endif value="3">Tutte</option>
            </select>
        </div>
        <div>
            <label for="order" class="form-label fw-semibold">Ordina</label>
            <select class="" id="order" name="order" >
                <option @if (isset($filters) && $filters['order'] == '0') selected @endif value="0">Data di prenotazione</option>
                <option @if (isset($filters) && $filters['order'] == '1') selected @endif value="1">Data di creazione</option>
            </select>
        </div>
        
       
        <div class="buttons">
         <button type="submit" class=" my_btn_3">
             <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-funnel-fill" viewBox="0 0 16 16">
                <path d="M1.5 1.5A.5.5 0 0 1 2 1h12a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.128.334L10 8.692V13.5a.5.5 0 0 1-.342.474l-3 1A.5.5 0 0 1 6 14.5V8.692L1.628 3.834A.5.5 0 0 1 1.5 3.5z"/>
             </svg>  Applica
         </button>
         <a class="my_btn_1 search" href="{{ route('admin.reservations.index')}}">
             <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-funnel" viewBox="0 0 16 16">
                 <path d="M1.5 1.5A.5.5 0 0 1 2 1h12a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.128.334L10 8.692V13.5a.5.5 0 0 1-.342.474l-3 1A.5.5 0 0 1 6 14.5V8.692L1.628 3.834A.5.5 0 0 1 1.5 3.5zm1 .5v1.308l4.372 4.858A.5.5 0 0 1 7 8.5v5.306l2-.666V8.5a.5.5 0 0 1 .128-.334L13.5 3.308V2z"/>
             </svg>  Rimuovi
         </a>   
        </div>
    </div>
    
</form> 



    {{-- Legenda  --}}


        
    @foreach ($reservations as $reservation)
        
        @php
         $parts = explode(" ", $reservation->date_slot);
         $date = $parts[0];
         $time = $parts[1];
        @endphp 
        @if ($reservation->status == 2)
        <div class="or-res my_2 my-4">
        @elseif ($reservation->status == 1)
        <div class="or-res my_1 my-4">
        @elseif ($reservation->status == 0)
        <div class="or-res my_0 my-4">
        @endif
            <section class="top">
                <div class="name">
                    <h4>{{$date}}</h4>
                    <h3>{{$reservation->surname}} {{$reservation->name}}</h3>
                </div>
                <div class="actions">
                    <a href="{{ route('admin.reservations.show', $reservation->id) }}" class="my_btn_5">Dettagli</a>
                    <div class="my_btn_5">Contatta</div>
                </div>
            </section>
            <section>
                <div class="name">
                    <h1 class="p">{{$time}}</h1>
                    @php $n_person = json_decode($reservation->n_person); @endphp
                    <h4>Ospiti:
                        @if ($n_person->adult > 0)
                            {{$n_person->adult }} {{$n_person->adult > 1 ? 'adulti' : 'adulto'}}
                        @endif
                        @if ($n_person->child > 0)
                            {{$n_person->child }} {{$n_person->child > 1 ? 'bambini' : 'bambino'}}
                        @endif
                        
                    </h4>
                </div>
                <div class="actions">
                    @if($reservation->status !== 1)
                    <div class="w-100">
                        <button type="button" data-bs-toggle="modal" data-bs-target="#staticBackdrop" class="w-100 my_btn_6">Conferma</button>
                    </div>
                    @endif
                    @if($reservation->status !== 0)
                    <div class="w-100">
                        <button type="button" data-bs-toggle="modal" data-bs-target="#staticBackdrop1" class="w-100 my_btn_6">Annulla</button>                   
                    </div>
                    @endif
                </div>
            </section>
        </div>
        <div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header c-1">
                        <h1 class="modal-title fs-5" id="staticBackdropLabel">Gestione notifica</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body c-1">
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
        <div class="modal fade" id="staticBackdrop1" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdrop1Label" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header c-1">
                        <h1 class="modal-title fs-5" id="staticBackdrop1Label">Gestione notifica</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body c-1">
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
    @endforeach
        
      

@endsection