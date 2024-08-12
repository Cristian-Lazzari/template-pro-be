@extends('layouts.base')

@section('contents')
@php
  //se impostato a true gli ordini vengono presi in base ai pezzi altrimenti in base al numero di ordini
$domain = 'https://future-plus.it/allergiens/';
 
@endphp
<button onclick="history.back()" class="btn btn-outline-light my-5">
    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-90deg-left" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M1.146 4.854a.5.5 0 0 1 0-.708l4-4a.5.5 0 1 1 .708.708L2.707 4H12.5A2.5 2.5 0 0 1 15 6.5v8a.5.5 0 0 1-1 0v-8A1.5 1.5 0 0 0 12.5 5H2.707l3.147 3.146a.5.5 0 1 1-.708.708l-4-4z"/></svg>
</button>

<h1>Dettagli Ingrediente</h1>
<div class="show_p">
    <h2>{{$ingredient->name}}</h2>
    <div class="split_p">
        <div class="image_p">
            @if (isset($ingredient->icon))
                <img class="logo_" src="{{ asset('public/storage/' . $ingredient->icon) }}" alt="{{$ingredient->name }}">
            @else 
                <img class="logo_" src="https://db.kojo-sushi.it/public/images/or.png" alt="{{$ingredient->name }}">
            @endif 
            <div class="allergiens">
                @php $all = json_decode($ingredient->allergiens, true) @endphp
               
                @foreach ($all as $p)
                    <img src="{{config('configurazione.allergiens')[$p]['img']}}" alt="{{config('configurazione.allergiens')[$p]['name']}}" title="{{config('configurazione.allergiens')[$p]['name']}}">
                @endforeach
            </div>
            <div class="price">€{{$ingredient->price / 100}}</div>
        </div>
        <div class="info">
            <section>
               
                    @if ($ingredient->type !== '"[]"')
                    <h4>Categorie in cui e' possibile aggiungiere l'ingrediente:</h4>
                    <p>
                        @php $cat = json_decode($ingredient->type) @endphp
                        @foreach ($cat as $c)
                            @if (in_array($c, $categories->pluck('id')->all())){{ $categories->where('id', $c)->first()->name }}{{ !$loop->last ? ', ' : '.' }} @endif     
                        @endforeach
                    </p>
                    @else
                    <h4>
                        l'ingrediente non puo essere aggiunto a nessun prodotto 
                    </h4>
                    @endif
                
            </section>
            
        </div>
    </div>
    <div class="prod-spec">
        <div class="actions">
            <a class="my_btn_2" href="{{ route('admin.ingredients.edit', $ingredient) }}">Modifica</a>
            {{-- <form action="{{ route('admin.ingredients.status') }}" method="POST">
                @csrf
                <input type="hidden" name="f" value="1">
                <button class="my_btn_1 d" type="submit">Archivia</button>
            </form>
            <form action="{{ route('admin.ingredients.status') }}" method="POST">
                @csrf
                <input type="hidden" name="f" value="1">
                <button class="my_btn_1 v" type="submit">Visibilità -  @if ($ingredient->visible) on  @else off @endif</button>
            </form> --}}
        </div>
    </div>
    <p>Data creazione: {{$ingredient->created_at}}</p>
    <p>Ultima modifica: {{$ingredient->updated_at}}</p>
</div>

 

@endsection