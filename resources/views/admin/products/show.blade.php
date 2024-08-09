@extends('layouts.base')

@section('contents')
@php
  //se impostato a true gli ordini vengono presi in base ai pezzi altrimenti in base al numero di ordini
$domain = 'https://future-plus.it/allergiens/';
 
@endphp
<button onclick="history.back()" class="btn btn-outline-light my-5">
    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-90deg-left" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M1.146 4.854a.5.5 0 0 1 0-.708l4-4a.5.5 0 1 1 .708.708L2.707 4H12.5A2.5 2.5 0 0 1 15 6.5v8a.5.5 0 0 1-1 0v-8A1.5 1.5 0 0 0 12.5 5H2.707l3.147 3.146a.5.5 0 1 1-.708.708l-4-4z"/></svg>
</button>
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
                @if ($product->allergiens !== [])
                    @php $all = json_decode($product->allergiens) @endphp
                    @foreach ($all as $p)
                        <img src="{{config('configurazione.allergiens')[$p]['img']}}" alt="{{config('configurazione.allergiens')[$p]['name']}}" title="{{config('configurazione.allergiens')[$p]['name']}}">
                    @endforeach
                @endif
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
                @if (config('configurazione.typeOfOrdering') && config('configurazione.pack') > 2)
                <p>Slot piatto:</p>
                <p>Tipo piatto:</p>
                @endif
                <p>Costum cliente:</p>
            </div>
            <div class="res">
                @if (config('configurazione.typeOfOrdering') && config('configurazione.pack') > 2)
                    <p>{{$product->slot_plate}}</p>
                    @if($product->type_plate == 0) <p>Altro</p> @elseif($product->type_plate == 1) <p>Cucina 1</p> @else <p>Cucina 2</p> @endif
                @endif
                @if($product->tag_set == 0)
                <p> Nessuma </p>  @elseif($product->tag_set == 1)
                <p> Togliere </p> @elseif($product->tag_set == 2)
                <p> Aggiungiere </p>
                @else
                <p> Aggiungiere / Togliere</p>
                @endif
            </div>
        </div>
       
        <div class="actions">
            <form action="{{ route('admin.products.status') }}" method="POST">
                @csrf
                <input type="hidden" name="archive" value="0">
                <input type="hidden" name="v" value="1">
                <input type="hidden" name="a" value="0">
                <input type="hidden" name="id" value="{{$product->id}}">
                @if (!$product->visible)
                    <button class="my_btn_1 v" type="submit">
                        <svg style="vertical-align: sub" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye-slash-fill" viewBox="0 0 16 16">
                            <path d="m10.79 12.912-1.614-1.615a3.5 3.5 0 0 1-4.474-4.474l-2.06-2.06C.938 6.278 0 8 0 8s3 5.5 8 5.5a7 7 0 0 0 2.79-.588M5.21 3.088A7 7 0 0 1 8 2.5c5 0 8 5.5 8 5.5s-.939 1.721-2.641 3.238l-2.062-2.062a3.5 3.5 0 0 0-4.474-4.474z"/>
                            <path d="M5.525 7.646a2.5 2.5 0 0 0 2.829 2.829zm4.95.708-2.829-2.83a2.5 2.5 0 0 1 2.829 2.829zm3.171 6-12-12 .708-.708 12 12z"/>
                        </svg>  
                    </button>
                @else
                    <button class="my_btn_1 v" type="submit">
                        <svg style="vertical-align: sub" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye-fill" viewBox="0 0 16 16">
                            <path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0"/>
                            <path d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8m8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7"/>
                        </svg>    
                    </button>
                @endif
                
            </form>
            <a class="my_btn_1 " href="{{ route('admin.products.edit', $product) }}">Modifica</a>      
            <form action="{{ route('admin.products.status') }}" method="POST">
                @csrf
                <input type="hidden" name="archive" value="0">
                <input type="hidden" name="v" value="0">
                <input type="hidden" name="a" value="1">
                <input type="hidden" name="id" value="{{$product->id}}">
                <button class="my_btn_2 my_btn_1 d" type="submit">{{$product->arcived ? 'Ripristina': 'Archivia'}}</button>
            </form>
            
            <form action="{{ route('admin.products.destroy', ['product'=>$product]) }}" method="post" >
                @method('delete')
                @csrf
                <input type="hidden" name="f" value="1">
                <button class="my_btn_2 bg-danger" type="submit">Elimina</button>
            </form>
           
        </div>
    </div>
    <p>Data creazione: {{$product->created_at}}</p>
    <p>Ultima modifica: {{$product->updated_at}}</p>
</div>

 

@endsection