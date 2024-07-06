@extends('layouts.base')

@section('contents')
    
@php
  //se impostato a true gli ordini vengono presi in base ai pezzi altrimenti in base al numero di ordini
$domain = 'https://future-plus.it/allergiens/';
 
@endphp

<a class="btn btn-outline-light mb-5" href="{{ route('admin.ingredients.index') }}">Indietro - annulla</a>


<h1>Modifica ingrediente</h1>
<form class="creation"  action="{{ route('admin.ingredients.update' , $ingredient) }}"  enctype="multipart/form-data"  method="POST">
    @method('PUT')
    @csrf
    <section class="more_i">
        
        <div class="split">
            <div>
                <label class="label_c" for="name_ing">Nome</label>
                <p><input value="{{ old('name_ing', $ingredient->name) }}" type="text" name="name_ing" id="name_ing" placeholder=" inserisci il nome"></p>
                @error('name_ing') <p class="error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="label_c" for="price_ing">Prezzo</label>
                <p>€<input value="{{ old('price_ing', $ingredient->price) / 100 }}" type="number" name="price_ing" id="price_ing" placeholder=" inserisci il prezzo "></p>
                @error('price_ing') <p class="error">{{ $message }}</p> @enderror
            </div>
        </div>
        <div class="check_c">
            <label class="label_c" for="type">Categorie abbinate</label>
            <p>
                @php
                    if ($ingredient->type !== NULL && $ingredient->type !== '"[]"') {
                        $t = json_decode($ingredient->type, true);
                    }else{
                        $t = [];
                    }
                @endphp
                
                @foreach ($categories as $c)
                
                <input type="checkbox" class="btn-check" id="a{{ $c->id }}" name="type_ing[]" value="{{ $c->id }}" @if (in_array($c->id, $t)) checked @endif>
                <label class="btn btn-outline-light" for="a{{ $c->id }}">{{ $c['name'] }}</label>
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
                <input type="checkbox" class="btn-check" id="option_ing" name="option_ing" value="1" @if (old('option_ing', $ingredient->option )) checked @endif>
                <label class="btn btn-outline-light" for="option_ing">questo ingrediente è un opzione</label>
            </div>
        </div>
        
        <div class="check_c">
            <label class="label_c" for="type">Allergieni</label>
            <p>
                @foreach(  config('configurazione.allergiens') as $a)
                    @php 
                        $i = $loop->iteration;
                        if ($ingredient->allergiens) {
                            $al = json_decode($ingredient->allergiens);
                        }else{
                            $al = [];
                        }
                    @endphp
                    <input type="checkbox" class="btn-check" id="b{{ $i }}" name="allergiens_ing[]" value="{{ $i }}" @if (in_array($i, old('allergiens_ing', $al, []))) checked @endif>
                    <label class="btn btn-outline-light" for="b{{ $i }}">{{ $a['name'] }}</label>
                @endforeach
            </p>
        </div>
            
    </section>
    <button class="my_btn_1 mb-5  w-75 m-auto" type="submit">Modifica ingrediente</button>

</form>



@endsection