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

<h1>Modifica prodotto</h1>
<form class="creation"  action="{{ route('admin.products.update', ['product' => $product]) }}"  enctype="multipart/form-data"  method="POST">
    @method('PUT')
    @csrf

    <section class="base">
        <div class="split">
            <div>
                <label class="label_c" for="name">Nome</label>
                <p><input @if(!isset($data)) value="{{ old('name', $product->name) }}" @else value="{{ $data['name'] }}" @endif  type="text" name="name" id="name" placeholder=" inserisci il nome"></p>
                @error('name') <p class="error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="label_c" for="price">Prezzo</label>
                <p>€<input @if($product->price) value="{{  $product->price / 100 }}" @else value="{{ old('price')}}" @endif  step="0.01" type="number" name="price" id="price" placeholder=" inserisci il prezzo "></p>
                @error('price') <p class="error">{{ $message }}</p> @enderror
            </div>
        </div>
        <div class="split">
            <div>
                <label class="label_c" for="promotion">Prodotto in evidenza</label>
                <label class="container_star">
                    <input name="promotion" type="checkbox" @if (old('promotion', $product->promotion))  checked  @elseif(isset($data) && $data['promotion']) checked @endif>
                    <svg height="24px" id="promotion" version="1.2" viewBox="0 0 24 24" width="24px" xml:space="preserve" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><g><g><path d="M9.362,9.158c0,0-3.16,0.35-5.268,0.584c-0.19,0.023-0.358,0.15-0.421,0.343s0,0.394,0.14,0.521    c1.566,1.429,3.919,3.569,3.919,3.569c-0.002,0-0.646,3.113-1.074,5.19c-0.036,0.188,0.032,0.387,0.196,0.506    c0.163,0.119,0.373,0.121,0.538,0.028c1.844-1.048,4.606-2.624,4.606-2.624s2.763,1.576,4.604,2.625    c0.168,0.092,0.378,0.09,0.541-0.029c0.164-0.119,0.232-0.318,0.195-0.505c-0.428-2.078-1.071-5.191-1.071-5.191    s2.353-2.14,3.919-3.566c0.14-0.131,0.202-0.332,0.14-0.524s-0.23-0.319-0.42-0.341c-2.108-0.236-5.269-0.586-5.269-0.586    s-1.31-2.898-2.183-4.83c-0.082-0.173-0.254-0.294-0.456-0.294s-0.375,0.122-0.453,0.294C10.671,6.26,9.362,9.158,9.362,9.158z"></path></g></g></svg>
                </label>
            </div>
            <div>
                <label class="label_c" for="old_price">Prezzo barrato</label>
                <p>€<input @if($product->old_price) value="{{  $product->old_price / 100 }}" @else value="{{ old('old_price')}}" @endif  step="0.01" type="number" name="old_price" id="old_price" placeholder=" inserisci il prezzo barrato"></p>
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
                            <option @if( $category->id == old('category_id', $product->category_id)) selected  @elseif(isset($data) && $category->id == $data['category_id']) selected @endif value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </p>
            </div>
        </div>
        <p class="desc"> 
            <label class="label_c" for="description">Descrizione</label>   
            <textarea name="description" id="description" cols="30" rows="10" >{{ old('description', $product->description) }}</textarea>
        </p>
    </section>
    @if (config('configurazione.pack') > 2)
    <section class="set" >
        <div class="split-3">
            
            @if (config('configurazione.typeOfOrdering'))
            <div>
                <label class="label_c" for="slot_plate">Spazio occupato</label>
                <p><input @if(!isset($data)) value="{{ old('slot_plate', $product->slot_plate) }}" @else value="{{ $data['slot_plate'] }}" @endif  type="number" name="slot_plate" id="slot_plate" placeholder="inserisci lo spazio  "></p>
                @error('slot_plate') <p class="error">{{ $message }}</p> @enderror
            </div>
           
            <div>
                <label class="label_c" for="type_plate">Tipo di piatto</label>
                <p>
                    <select name="type_plate" id="type_plate">
                        <option @if( 0 == old('type_plate', $product->type_plate)) selected  @elseif(isset($data) && 0 == $data['type_plate']) selected @endif value="0">altro</option>
                        <option @if( 1 == old('type_plate', $product->type_plate)) selected  @elseif(isset($data) && 1 == $data['type_plate']) selected @endif value="1">tipo 1 (al taglio)</option>
                        <option @if( 2 == old('type_plate', $product->type_plate)) selected  @elseif(isset($data) && 2 == $data['type_plate']) selected @endif value="2">tipo 2 (al piatto)</option>
                    </select>
                </p>
                @error('type_plate') <p class="error">{{ $message }}</p> @enderror
            </div>
            @endif
            <div>
                <label class="label_c" for="tag_set">Custom Ingredienti</label>
                <p>
                    <select name="tag_set" id="tag_set">
                        <option @if( 0 == old('tag_set', $product->tag_set)) selected  @elseif(isset($data) && 0 == $data['tag_set']) selected @endif value="0">NON modificabile</option>
                        <option @if( 1 == old('tag_set', $product->tag_set)) selected  @elseif(isset($data) && 1 == $data['tag_set']) selected @endif value="1">togliere</option>
                        <option @if( 2 == old('tag_set', $product->tag_set)) selected  @elseif(isset($data) && 2 == $data['tag_set']) selected @endif value="2">aggiungiere</option>
                        <option @if( 3 == old('tag_set', $product->tag_set)) selected  @elseif(isset($data) && 3 == $data['tag_set']) selected @endif value="3">togliere e aggiungiere</option>
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
                            <p>€<input value="{{ old('price_ing') }}" type="number" name="price_ing" id="price_ing"  step="0.01" placeholder=" inserisci il prezzo "></p>
                            @error('price_ing') <p class="error">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    
                    <div>
                        <label class="label_c" for="file-input">Immagine</label>
                        <p><input type="file" id="file-input" name="image_ing" ></p>
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
                                <label class="btn btn-outline-light" for="b{{ $i }}">{{ $a['name'] }}</label>
                            @endforeach
                        </p>
                    </div>
                    <input type="submit" class="btn-check" id="newi" name="newi" value="1">
                    <label class="my_btn_2 w-50 m-auto" for="newi">Crea Ingrediente</label>
                </section>
            </div>
        </div>
        </div>
    </div>
    <section class="cont_i">
        <h2>Abbina Ingredienti</h2>
        <div class="check_c">
            <p>
                @foreach($ingredients as $ingredient)
                    <input type="checkbox" class="btn-check" id="ingredient{{ $ingredient->id }}" name="ingredients[]" 
                    value="{{ $ingredient->id }}"
                    
                    @if(isset($data['ingredients']) && in_array($ingredient->id, $data['ingredients']))   
                        checked 
                    @elseif(in_array($ingredient->id, old('ingredients', $product->ingredients->pluck('id')->all())))
                        checked
                    @endif
                    >

                    <label class="btn btn-outline-light shadow-sm" for="ingredient{{ $ingredient->id }}">{{ $ingredient->name }}</label>
                    @error('ingredients') 
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror      
                @endforeach
            </p>
        </div>
            <!-- Button trigger modal -->
        <h5 class="text-center" >Se non hai ancora creato tutti gli ingedienti per il tuo prodotto puoi farlo ora</h5>
        <button type="button" class="my_btn_4" data-bs-toggle="modal" data-bs-target="#staticBackdrop">
            Crea Ingredienti mancanti
        </button>
    </section>
    <button class="my_btn_2 mb-5  w-75 m-auto" type="submit">Modifica Prodotto</button>

</form>



@endsection