@extends('layouts.base')

@section('contents')
@php
  //se impostato a true gli ordini vengono presi in base ai pezzi altrimenti in base al numero di ordini
$domain = 'https://future-plus.it/allergiens/';
 
@endphp
<a class="btn btn-outline-dark mb-5" href="{{ route('admin.ingredients.index') }}">Indietro</a>

<h1>Dettagli Ingrediente</h1>
<div class="show_p">
    <h2>{{$ingredient->name}}</h2>
    <div class="split_p">
        <div class="image_p">
            @if (isset($ingredient->image))
                <img class="logo_" src="{{ asset('public/storage/' . $ingredient->image) }}" alt="{{$ingredient->name }}">
            @else 
                <img class="logo_" src="https://db.kojo-sushi.it/public/images/or.png" alt="{{$ingredient->name }}">
            @endif 
            <div class="allergiens">
                @php $all = json_decode($ingredient->allergiens) @endphp
                @foreach ($all as $p)
                    <img src="{{$allergiens[$p]['img']}}" alt="{{$allergiens[$p]['name']}}" title="{{$allergiens[$p]['name']}}">
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
            <a class="my_btn m" href="{{ route('admin.ingredients.edit', $ingredient) }}">Modifica</a>
            {{-- <form action="{{ route('admin.ingredients.status') }}" method="POST">
                @csrf
                <input type="hidden" name="f" value="1">
                <button class="my_btn d" type="submit">Archivia</button>
            </form>
            <form action="{{ route('admin.ingredients.status') }}" method="POST">
                @csrf
                <input type="hidden" name="f" value="1">
                <button class="my_btn v" type="submit">Visibilità -  @if ($ingredient->visible) on  @else off @endif</button>
            </form> --}}
        </div>
    </div>
    <p>Data creazione: {{$ingredient->created_at}}</p>
    <p>Ultima modifica: {{$ingredient->updated_at}}</p>
</div>

 

@endsection