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

 
<a class="my_btn_5 ml-auto" href="{{ route('admin.products.index') }}">Torna ai Prodotti</a>

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
                <p>€<input @if(!isset($data)) value="{{ old('price') }}" @else value="{{ $data['price'] }}" @endif  type="number" name="price" id="price" step="0.01" placeholder=" inserisci il prezzo "></p>
                @error('price') <p class="error">{{ $message }}</p> @enderror
            </div>
        </div>
        <div class="split">
            
            <div>
                <label class="label_c" for="file-input">Immagine</label>
                <p><input type="file" id="file-input" name="image" ></p>
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
            <textarea name="description" id="description" cols="30" rows="10" >@if(!isset($data)) {{ old('description') }} @else {{ $data['description'] }} @endif </textarea>
        </p>
    </section>
    @error('slot_plate') <p class="error">{{ $message }}</p> @enderror
    @if (config('configurazione.pack') > 2)
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
    @endif

  
    <!-- Modal -->
    <div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog">
        <div class="modal-content my_bg_1">
            <div class="modal-header">
                <h2>Crea e aggiungi Ingredienti mancanti</h2>
                <button type="button" class="btn-close bg-light" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <section class="more_i">
                    
                    <div class="split">
                        <div>
                            <label class="label_c" for="name_ing">Nome</label>
                            <p><input value="{{ old('name_ing') }}" type="text" name="name_ing" id="name_ing" placeholder=" inserisci il nome"></p>
                            @error('name_ing') <p class="error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="label_c" for="price_ing">Prezzo</label>
                            <p>€<input value="{{ old('price_ing') }}" type="number" name="price_ing" step="0.01" id="price_ing" placeholder=" inserisci il prezzo "></p>
                            @error('price_ing') <p class="error">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div>
                        <label class="label_c" for="file-input1">Immagine</label>
                        <p><input type="file" id="file-input1" name="image_ing" ></p>
                        @error('image_ing') <p class="error">{{ $message }}</p> @enderror    
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
                        <label class="label_c" for="type">allergeni</label>
                        <p>
                            @foreach(  config('configurazione.allergens') as $a)
                                @php $i = $loop->iteration; @endphp
                                <input type="checkbox" class="btn-check" id="b{{ $i }}" name="allergens_ing[]" value="{{ $i }}" @if (in_array($i, old('allergens_ing', []))) checked @endif>
                                <label class="btn 
                                @if($a['special'])
                                btn-outline-info
                                @else
                                btn-outline-light
                                @endif
                                " for="b{{ $i }}">{{ $a['name'] }}</label>
                            @endforeach
                        </p>
                    </div>
                        <input type="submit" class="btn-check" id="newi" name="newi" value="1">
                        <label class="my_btn_2 m-auto" for="newi">Crea Ingrediente</label>
                </section>
            </div>
            
        </div>
        </div>
    </div>
    <section class="cont_i">
        <h2>Abbina Ingredienti</h2>
            <!-- Button trigger modal -->

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
        <h5 class="text-center" >Se non hai ancora creato tutti gli ingedienti per il tuo prodotto puoi farlo ora</h5>
        <button type="button" class="my_btn_4" data-bs-toggle="modal" data-bs-target="#staticBackdrop">
            Crea Ingredienti mancanti
        </button>
        

    </section>
    <section class="check_c">
        <label class="label_c" for="type">Allergeni</label>
        <p>
            @foreach(  config('configurazione.allergens') as $a)
                @php 
                    $i = $loop->iteration;
                    
                    $al = [];
                    
                @endphp
                <input type="checkbox" class="btn-check" id="ab{{ $i }}" name="allergens[]" value="{{ $i }}" 
                @if (in_array($i, old('allergens', $al, []))) checked @endif>
                <label class="btn 
                @if($a['special'])
                btn-outline-info
                @else
                btn-outline-light
                @endif
                " for="ab{{ $i }}">{{ $a['name'] }}</label>
            @endforeach
        </p>
    </section>
    <button class="my_btn_2 mb-5  w-75 m-auto" type="submit">Crea Prodotto</button>

</form>



@endsection