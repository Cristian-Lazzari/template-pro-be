@extends('layouts.base')

@section('contents')
    
@if (session('ingredient_success'))
    @php
        $data = session('ingredient_success')
    @endphp
    <div class="alert alert-success">
        "{{ $data['name_ing'] }}" è stato correttamente creato!
    </div>
@endif
    
@php
  //se impostato a true gli ordini vengono presi in base ai pezzi altrimenti in base al numero di ordini
$domain = 'https://future-plus.it/allergiens/';
 
@endphp

<a class="btn btn-outline-light mb-5" href="{{ route('admin.products.index') }}">Indietro</a>


<h1>Crea nuovo Prodotto</h1>
<form class="creation"  action="{{ route('admin.products.store') }}"  enctype="multipart/form-data"  method="POST">
    @csrf
    
    <section class="base">
        <div class="split">
            <div>
                <label class="label_c" for="name">Nome</label>
                <p><input @if(!isset($data)) value="{{ old('name') }}" @else value="{{ $data['name'] }}" @endif  type="text" name="name" id="name" placeholder=" inserisci il nome"></p>
                @error('name') <p class="error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="label_c" for="price">Prezzo</label>
                <p>€<input @if(!isset($data)) value="{{ old('price') }}" @else value="{{ $data['price'] }}" @endif  type="number" name="price" id="price" placeholder=" inserisci il prezzo "></p>
                @error('price') <p class="error">{{ $message }}</p> @enderror
            </div>
        </div>
        <div class="split">
            
            <div>
                <label class="label_c" for="image">Immagine</label>
                <p><input  class="form-control" type="file" id="image" name="image" ></p>
                @error('image') <p class="error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="label_c" for="category_id">Categoria</label>
                <p>
                    <select name="category_id" id="category_id">
                        
                        @foreach ($categories as $category)
                            <option @if( $category->id == old('category_id')) selected  @elseif(isset($data) && $category->id == $data['category_id']) selected @endif value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </p>
            </div>
        </div>
        <p class="desc"> 
            <label class="label_c" for="description">Descrizione</label>   
            <textarea name="description" id="description" cols="30" rows="10" >{{ old('description') }}</textarea>
        </p>
    </section>
    <section class="set" >
        <div class="split-3">
        
            @if (config('configurazione.typeOfOrdering'))
            <div>
                <label class="label_c" for="slot_plate">Spazio occupato</label>
                <p><input @if(!isset($data)) value="{{ old('slot_plate') }}" @else value="{{ $data['slot_plate'] }}" @endif  type="number" name="slot_plate" id="slot_plate" placeholder="inserisci lo spazio  "></p>
                @error('slot_plate') <p class="error">{{ $message }}</p> @enderror
            </div>
           
            <div>
                <label class="label_c" for="type_plate">Tipo di piatto</label>
                <p>
                    <select name="type_plate" id="type_plate">
                        <option @if( 0 == old('type_plate')) selected  @elseif(isset($data) && 0 == $data['type_plate']) selected @endif value="0">altro</option>
                        <option @if( 1 == old('type_plate')) selected  @elseif(isset($data) && 1 == $data['type_plate']) selected @endif value="1">tipo 1 (al taglio)</option>
                        <option @if( 2 == old('type_plate')) selected  @elseif(isset($data) && 2 == $data['type_plate']) selected @endif value="2">tipo 2 (al piatto)</option>
                    </select>
                </p>
                @error('type_plate') <p class="error">{{ $message }}</p> @enderror
            </div>
            @endif
            <div>
                <label class="label_c" for="tag_set">Custom Ingredienti</label>
                <p>
                    <select name="tag_set" id="tag_set">
                        <option @if( 0 == old('tag_set')) selected  @elseif(isset($data) && 0 == $data['tag_set']) selected @endif value="0">NON modificabile</option>
                        <option @if( 1 == old('tag_set')) selected  @elseif(isset($data) && 1 == $data['tag_set']) selected @endif value="1">togliere</option>
                        <option @if( 2 == old('tag_set')) selected  @elseif(isset($data) && 2 == $data['tag_set']) selected @endif value="2">aggiungiere</option>
                        <option @if( 3 == old('tag_set')) selected  @elseif(isset($data) && 3 == $data['tag_set']) selected @endif value="3">togliere e aggiungiere</option>
                    </select>
                </p>
                @error('tag_set') <p class="error">{{ $message }}</p> @enderror
            </div>
        </div>
    </section>
    <section class="more_i">
        <h2>Crea e aggiungi Ingredienti mancanti</h2>
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
        <div class="split"> 
            <div>
                <label class="label_c" for="image_ing">Immagine</label>
                <p><input  class="form-control" type="file" id="image_ing" name="image_ing" ></p>
                @error('image_ing') <p class="error">{{ $message }}</p> @enderror    
            </div> 
            <div class="m-auto">
                <input type="checkbox" class="btn-check" id="option_ing" name="option_ing" value="1" @if (old('option_ing', [])) checked @endif>
                <label class="btn btn-outline-light" for="option_ing">questo ingrediente è un opzione</label>
            </div>
        </div>
        <div class="check_c">
            <label class="label_c" for="type">Categorie abbinate</label>
            <p>
                
                @foreach ($categories as $c)
                
                <input type="checkbox" class="btn-check" id="a{{ $c->id }}" name="type_ing[]" value="{{ $c->id }}" @if (in_array($c->id, old('type_ing', []))) checked @endif>
                <label class="btn btn-outline-light" for="a{{ $c->id }}">{{ $c['name'] }}</label>
                @endforeach
                
            </p>
            @error('type_ing') <p class="error">{{ $message }}</p> @enderror
        </div>
        
        <div class="check_c">
            <label class="label_c" for="type">Allergieni</label>
            <p>
                @foreach(  config('configurazione.allergiens') as $a)
                    @php $i = $loop->iteration; @endphp
                    <input type="checkbox" class="btn-check" id="b{{ $i }}" name="allergiens_ing[]" value="{{ $i }}" @if (in_array($i, old('allergiens_ing', []))) checked @endif>
                    <label class="btn btn-outline-light" for="b{{ $i }}">{{ $a['name'] }}</label>
                @endforeach
            </p>
        </div>
            <input type="submit" class="btn-check" id="newi" name="newi" value="1">
            <label class="my_btn w-75 m-auto" for="newi">Crea Ingrediente</label>
    </section>
    <section class="cont_i">
        <h2>Abbina Ingredienti</h2>
        <div class="check_c">
            <p>
                @foreach($ingredients as $ingredient)
                    <input type="checkbox" class="btn-check" id="ingredient{{ $ingredient->id }}" name="ingredients[]" 
                    value="{{ $ingredient->id }}"
                    @if(isset($data['ingredients']) && in_array($ingredient->id, $data['ingredients']))   
                        checked 
                    @elseif(in_array($ingredient->id, old('ingredients', [])))
                        checked
                    @endif>

                    <label class="btn btn-outline-light shadow-sm" for="ingredient{{ $ingredient->id }}">{{ $ingredient->name }}</label>
                    @error('ingredients') 
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror      
                @endforeach
            </p>
        </div>
        

    </section>
    <button class="my_btn mb-5  w-75 m-auto" type="submit">Crea Prodotto</button>

</form>



@endsection