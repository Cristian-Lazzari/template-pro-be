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
                    <a href="{{ route('admin.reservations.show', $reservation->id) }}" class="my_btn_1">Dettagli</a>
                    <div class="my_btn_4">Contatta</div>
                </div>
            </section>
            <section>
                <div class="name">
                    <h1 class="p">{{$time}}</h1>
                    <h4>Ospiti: {{$reservation->n_person}}</h4>
                </div>
                <div class="actions">
                    @if($reservation->status !== 1)
                    <form class="w-100" action="{{ route('admin.reservations.status') }}" method="POST">
                        @csrf
                        <input value="1" type="hidden" name="c_a">
                        <input value="{{$reservation->id}}" type="hidden" name="id">
                        
                        <button type="submit" class="w-100 my_btn_3">Conferma</button>
                    </form>
                    @endif
                    @if(!$reservation->status !== 0)
                    <form class="w-100" action="{{ route('admin.reservations.status') }}" method="POST">
                        @csrf
                        <input value="0" type="hidden" name="c_a">
                        <input value="{{$reservation->id}}" type="hidden" name="id">

                        <button type="submit" class="w-100 my_btn_2">Annulla</button>
                    </form>
                    @endif
                </div>
            </section>
        </div>
    @endforeach
        
      

@endsection