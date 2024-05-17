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
<a class="btn btn-outline-dark mb-5" href="{{ route('admin.posts.index') }}">Indietro</a>

<h1>Dettagli prodotto</h1>
<div class="show_p">
    <h2>{{$post->title}}</h2>
    <div class="split_p">
        <div class="image_p">
            @if (isset($post->image))
                <img class="logo_" src="{{ asset('public/storage/' . $post->image) }}" alt="{{$post->name }}">
            @else 
                <img class="logo_" src="https://db.kojo-sushi.it/public/images/or.png" alt="{{$post->name }}">
            @endif 
            
        </div>
        <div class="info">
            <section>
                <h4>Descrizione:</h4> 
                <p>{{$post->description}} Lorem ipsum dolor sit amet, consectetur adipisicing elit. Temporibus quisquam fuga voluptatem, aliquid culpa facere! Sunt, numquam. Aperiam eius doloribus officiis delectus officia aut fugiat sapiente quaerat, excepturi omnis quisquam?</p>       
            </section>
            <section>
                <h4>Precedenza: <strong>{{$post->order}}</strong></h4>      
                @if (isset($post->link)) 
                    <h4 class="">Link:</h4>
                    <p class="">{{$post->link}}</p>
                @else
                    <p>(nessun link impostato)</p>   
                @endif  
            </section>

            <section class="split_i">
                
                <h4 class="">Path: </h4>
                <p class="">{{$post->path}}</p>
                @if (isset($post->link))
                    <h4>Hashtag:</h4>
                    <p class="">{{$post->hashtag}}</p>
                @else
                    <p class="">(nessun hashtag impostato)</p>   
                @endif 
            </section>
        </div>
    </div>
    <div class="prod-spec">
        
        <div class="actions">
            <form action="{{ route('admin.posts.status') }}" method="POST">
                @csrf
                <input type="hidden" name="archive" value="0">
                <input type="hidden" name="v" value="0">
                <input type="hidden" name="a" value="1">
                <input type="hidden" name="id" value="{{$post->id}}">
                <button class="my_btn d" type="submit">Archivia</button>
            </form>
            <a class="my_btn m" href="{{ route('admin.posts.edit', $post) }}">Modifica</a>
            <form action="{{ route('admin.posts.destroy', ['post'=>$post]) }}" method="post" >
                @method('delete')
                @csrf
                <button class="my_btn bg-danger" type="submit">Elimina</button>
            </form>
             
        </div>
    </div>
    <p>Data creazione: {{$post->created_at}}</p>
    <p>Ultima modifica: {{$post->updated_at}}</p>
</div>

 

@endsection