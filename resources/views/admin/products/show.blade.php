@extends('layouts.base')

@section('contents')
@php
    $typeOfOrdering = true; //se impostato a true gli ordini vengono presi in base ai pezzi altrimenti in base al numero di ordini
    $domain = 'https://future-plus.it/allergiens/';
    $allergiens = [
        1 => ['img' => $domain . 'gluten.png', 'name' => 'glutine'] ,
        2 => ['img' => $domain . 'fish.png', 'name' => 'pesce'] ,
        3 => ['img' => $domain . 'crab.png', 'name' => 'crostacei'] ,
        4 => ['img' => $domain . 'dairy.png', 'name' => 'latticini'] ,
        5 => ['img' => $domain . 'fish.png', 'name' => 'pesce'] ,
        6 => ['img' => $domain . 'sesame.png', 'name' => 'sesamo'] ,
        7 => ['img' => $domain . 'peanut.png', 'name' => 'arachidi'] ,
        8 => ['img' => $domain . 'soy.png', 'name' => 'soia'] ,
        9 => ['img' => $domain . 'molluschi.png', 'name' => 'molluschi'] ,
        10 => ['img' => $domain . 'sedano.png', 'name' => 'sedano'] ,
        11 => ['img' => $domain . 'egg.png', 'name' => 'uova'] ,
    ];

@endphp
<a class="btn btn-outline-dark mb-5" href="{{ route('admin.products.index') }}">Indietro</a>

<h1>Dettagli prodotto</h1>
<div class="show_p">
    <h2>{{$product->name}}</h2>
    <div class="split_p">
        <div class="image_p">
            @if (isset($product->image))
                <img class="logo_" src="{{ asset('public/storage/' . $product->image) }}" alt="{{$product->name }}">
            @else 
                <img class="logo_" src="https://db.kojo-sushi.it/public/images/or.png" alt="{{$product->name }}">
            @endif 
            <div class="allergiens">
                @php $all = json_decode($product->allergiens) @endphp
                @foreach ($all as $p)
                    <img src="{{$allergiens[$p]['img']}}" alt="{{$allergiens[$p]['name']}}" title="{{$allergiens[$p]['name']}}">
                @endforeach
            </div>
        </div>
        <div class="info">
            <section>
                <h4>Ingredienti:</h4>
                <p>
                    @foreach ($product->ingredients as $ingredient)     
                    {{ $ingredient->name }}{{ !$loop->last ? ', ' : '.' }}
                    @endforeach
                </p>
            </section>
            <section>
                <h4>Descrizione:</h4>
                @if ($product->description)
                <p>{{$product->description}}</p> @else <p>(nessuna)</p> @endif
            </section>
            <section>
                <h4>Categoria:</h4>
                <p>{{$product->category->name}}</p>
            </section>
        </div>
    </div>
    <div class="price">€{{$product->price / 100}}</div>
    <div class="prod-spec">
        <div class="set_plate">
            <div class="title">
                <p>Slot piatto:</p>
                <p>Tipo piatto:</p>
                <p>Costum cliente:</p>
            </div>
            <div class="res">
                <p>{{$product->slot_plate}}</p>
                @if($product->type_plate == 0) <p>Altro</p> @elseif($product->type_plate == 1) <p>Cucina 1</p> @else <p>Cucina 2</p> @endif
                @if($product->tag_set == 0)
                <p> Nessuma </p>  @elseif($product->tag_set == 1)
                <p> Togliere </p> @elseif($product->tag_set == 2)
                <p> Aggiungiere </p> @else
                <p> Aggiungiere / Togliere@else</p>
                @endif
            </div>
        </div>
        <div class="actions">
            <a class="my_btn m" href="{{ route('admin.products.edit', $product) }}">Modifica</a>
            {{-- <form action="{{ route('admin.products.status') }}" method="POST">
                @csrf
                <input type="hidden" name="f" value="1">
                <button class="my_btn d" type="submit">Archivia</button>
            </form>
            <form action="{{ route('admin.products.status') }}" method="POST">
                @csrf
                <input type="hidden" name="f" value="1">
                <button class="my_btn v" type="submit">Visibilità -  @if ($product->visible) on  @else off @endif</button>
            </form> --}}
        </div>
    </div>
    <p>Data creazione: {{$product->created_at}}</p>
    <p>Ultima modifica: {{$product->updated_at}}</p>
</div>

 

@endsection