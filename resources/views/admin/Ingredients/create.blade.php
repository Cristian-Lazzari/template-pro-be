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
        5 => ['img' => $domain . 'sesame.png', 'name' => 'sesamo'] ,
        6 => ['img' => $domain . 'peanut.png', 'name' => 'arachidi'] ,
        7 => ['img' => $domain . 'soy.png', 'name' => 'soia'] ,
        8 => ['img' => $domain . 'molluschi.png', 'name' => 'molluschi'] ,
        9 => ['img' => $domain . 'sedano.png', 'name' => 'sedano'] ,
        10 => ['img' => $domain . 'senape.png', 'name' => 'senape'] ,
        11 => ['img' => $domain . 'egg.png', 'name' => 'uova'] ,
        12 => ['img' => $domain . 'gluten-free.png', 'name' => 'senza glutine']
];
@endphp

<a class="btn btn-outline-dark mb-5" href="{{ route('admin.ingredients.index') }}">Indietro</a>


<h1>Crea nuovo Ingrediente</h1>
<form class="creation"  action="{{ route('admin.ingredients.store') }}"  enctype="multipart/form-data"  method="POST">
    @csrf

    <section class="more_i">
        
        <div class="split">
            <div>
                <label class="label_c" for="name_ing">Nome</label>
                <p><input value="{{ old('name_ing') }}" type="text" name="name_ing" id="name_ing" placeholder=" inserisci il nome"></p>
                @error('name_ing') <p class="error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="label_c" for="price_ing">Prezzo</label>
                <p>€<input value="{{ old('price_ing') }}" type="number" name="price_ing" id="price_ing" placeholder=" inserisci il prezzo "></p>
                @error('price_ing') <p class="error">{{ $message }}</p> @enderror
            </div>
        </div>
        <div class="check_c">
            <label class="label_c" for="type">Categorie abbinate</label>
            <p>
                
                @foreach ($categories as $c)
                
                <input type="checkbox" class="btn-check" id="a{{ $c->id }}" name="type_ing[]" value="{{ $c->id }}" @if (in_array($c->id, old('type_ing', []))) checked @endif>
                <label class="btn btn-outline-dark" for="a{{ $c->id }}">{{ $c['name'] }}</label>
                @endforeach
                
            </p>
            @error('type_ing') <p class="error">{{ $message }}</p> @enderror
        </div>
        <div class="split"> 
            <div>
                <label class="label_c" for="image_ing">Immagine</label>
                <p><input  class="form-control" type="file" id="image_ing" name="image_ing" ></p>
                @error('image_ing') <p class="error">{{ $message }}</p> @enderror    
            </div> 
            <div class="m-auto">
                <input type="checkbox" class="btn-check" id="option_ing" name="option_ing" value="1" @if (old('option_ing', [])) checked @endif>
                <label class="btn btn-outline-dark" for="option_ing">questo ingrediente è un opzione</label>
            </div>
        </div>
        
        <div class="check_c">
            <label class="label_c" for="type">Allergieni</label>
            <p>
                @foreach($allergiens as $a)
                    @php $i = $loop->iteration; @endphp
                    <input type="checkbox" class="btn-check" id="b{{ $i }}" name="allergiens_ing[]" value="{{ $i }}" @if (in_array($i, old('allergiens_ing', []))) checked @endif>
                    <label class="btn btn-outline-dark" for="b{{ $i }}">{{ $a['name'] }}</label>
                @endforeach
            </p>
        </div>
            
    </section>
    <button class="btn btn-primary mb-5  w-75 m-auto" type="submit">Crea Ingrediente</button>

</form>



@endsection