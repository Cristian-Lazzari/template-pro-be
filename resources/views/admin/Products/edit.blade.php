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
    

 


<form class="creation"  action="{{ route('admin.products.update', ['product' => $product]) }}"  enctype="multipart/form-data"  method="POST">
    <a class="my_btn_5 ml-auto" href="{{ route('admin.products.index') }}">Torna ai Prodotti</a>
    <h1 class="mb-5">Modifica prodotto</h1>
    @method('PUT')
    @csrf

    <section class="base">
        <div class="split">
            <div>
                <label class="label_c" for="name"> 
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-type" viewBox="0 0 16 16">
                        <path d="m2.244 13.081.943-2.803H6.66l.944 2.803H8.86L5.54 3.75H4.322L1 13.081zm2.7-7.923L6.34 9.314H3.51l1.4-4.156zm9.146 7.027h.035v.896h1.128V8.125c0-1.51-1.114-2.345-2.646-2.345-1.736 0-2.59.916-2.666 2.174h1.108c.068-.718.595-1.19 1.517-1.19.971 0 1.518.52 1.518 1.464v.731H12.19c-1.647.007-2.522.8-2.522 2.058 0 1.319.957 2.18 2.345 2.18 1.06 0 1.716-.43 2.078-1.011zm-1.763.035c-.752 0-1.456-.397-1.456-1.244 0-.65.424-1.115 1.408-1.115h1.805v.834c0 .896-.752 1.525-1.757 1.525"/>
                      </svg>
                      Nome
                </label>
                <p><input @if(!isset($data)) value="{{ old('name', $product->name) }}" @else value="{{ $data['name'] }}" @endif  type="text" name="name" id="name" placeholder=" Inserisci il nome"></p>
                @error('name') <p class="error">{{ $message }}</p> @enderror
            </div>
            <div class="price_input">
                <label class="label_c" for="price">   
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-123" viewBox="0 0 16 16">
                    <path d="M2.873 11.297V4.142H1.699L0 5.379v1.137l1.64-1.18h.06v5.961zm3.213-5.09v-.063c0-.618.44-1.169 1.196-1.169.676 0 1.174.44 1.174 1.106 0 .624-.42 1.101-.807 1.526L4.99 10.553v.744h4.78v-.99H6.643v-.069L8.41 8.252c.65-.724 1.237-1.332 1.237-2.27C9.646 4.849 8.723 4 7.308 4c-1.573 0-2.36 1.064-2.36 2.15v.057zm6.559 1.883h.786c.823 0 1.374.481 1.379 1.179.01.707-.55 1.216-1.421 1.21-.77-.005-1.326-.419-1.379-.953h-1.095c.042 1.053.938 1.918 2.464 1.918 1.478 0 2.642-.839 2.62-2.144-.02-1.143-.922-1.651-1.551-1.714v-.063c.535-.09 1.347-.66 1.326-1.678-.026-1.053-.933-1.855-2.359-1.845-1.5.005-2.317.88-2.348 1.898h1.116c.032-.498.498-.944 1.206-.944.703 0 1.206.435 1.206 1.07.005.64-.504 1.106-1.2 1.106h-.75z"/>
                    </svg>
                    Prezzo</label>
                <p><input @if(!isset($data)) value="{{  $product->price / 100 }}" @else value="{{ $data['price'] / 100}}" @endif  step="0.01" type="number" name="price" id="price" placeholder=" Inserisci il prezzo "><span>€</span></p>
                @error('price') <p class="error">{{ $message }}</p> @enderror
            </div>
        </div>
        
        <div class="split">
            
            <div>
                <label class="label_c" for="file-input">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-earmark-image" viewBox="0 0 16 16">
                        <path d="M6.502 7a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3"/>
                        <path d="M14 14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h5.5L14 4.5zM4 1a1 1 0 0 0-1 1v10l2.224-2.224a.5.5 0 0 1 .61-.075L8 11l2.157-3.02a.5.5 0 0 1 .76-.063L13 10V4.5h-2A1.5 1.5 0 0 1 9.5 3V1z"/>
                    </svg>
                Immagine</label>
                <p class="img-cont">
                    <input type="file" id="file-input" name="image" >
                    @if (isset($product->image))
                        <input type="checkbox" class="btn-check" id="b2" name="img_off">
                        <label class="btn btn-outline-danger" for="b2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash-fill" viewBox="0 0 16 16">
                                <path d="M2.5 1a1 1 0 0 0-1 1v1a1 1 0 0 0 1 1H3v9a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V4h.5a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H10a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1zm3 4a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 .5-.5M8 5a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7A.5.5 0 0 1 8 5m3 .5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 1 0"/>
                            </svg>
                        </label>
                        <img class="" src="{{ asset('public/storage/' . $product->image) }}" alt="{{$product->name }}">
                    @endif 
                </p>
                @error('image') <p class="error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="label_c" for="category_id">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-view-list" viewBox="0 0 16 16">
                        <path d="M3 4.5h10a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2m0 1a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1v-3a1 1 0 0 0-1-1zM1 2a.5.5 0 0 1 .5-.5h13a.5.5 0 0 1 0 1h-13A.5.5 0 0 1 1 2m0 12a.5.5 0 0 1 .5-.5h13a.5.5 0 0 1 0 1h-13A.5.5 0 0 1 1 14"/>
                    </svg>
                    Categoria</label>
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
            <label class="label_c" for="description">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-body-text" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M0 .5A.5.5 0 0 1 .5 0h4a.5.5 0 0 1 0 1h-4A.5.5 0 0 1 0 .5m0 2A.5.5 0 0 1 .5 2h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5m9 0a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m-9 2A.5.5 0 0 1 .5 4h3a.5.5 0 0 1 0 1h-3a.5.5 0 0 1-.5-.5m5 0a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m7 0a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 0 1h-3a.5.5 0 0 1-.5-.5m-12 2A.5.5 0 0 1 .5 6h6a.5.5 0 0 1 0 1h-6a.5.5 0 0 1-.5-.5m8 0a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m-8 2A.5.5 0 0 1 .5 8h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m7 0a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5m-7 2a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 0 1h-8a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 0 1h-4a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1h-2a.5.5 0 0 1-.5-.5"/>
                </svg> 
                Descrizione</label>   
            <textarea name="description" id="description" cols="1" rows="4" >{{ old('description', $product->description) }}</textarea>
        </p>
        <div class="split">
            <div>
                <label class="label_c" for="promotion">Prodotto in evidenza</label>
                <label class="container_star">
                    <input name="promotion" type="checkbox" @if (old('promotion', $product->promotion))  checked  @endif>
                    <svg height="24px" id="promotion" version="1.2" viewBox="0 0 24 24" width="24px" xml:space="preserve" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><g><g><path d="M9.362,9.158c0,0-3.16,0.35-5.268,0.584c-0.19,0.023-0.358,0.15-0.421,0.343s0,0.394,0.14,0.521    c1.566,1.429,3.919,3.569,3.919,3.569c-0.002,0-0.646,3.113-1.074,5.19c-0.036,0.188,0.032,0.387,0.196,0.506    c0.163,0.119,0.373,0.121,0.538,0.028c1.844-1.048,4.606-2.624,4.606-2.624s2.763,1.576,4.604,2.625    c0.168,0.092,0.378,0.09,0.541-0.029c0.164-0.119,0.232-0.318,0.195-0.505c-0.428-2.078-1.071-5.191-1.071-5.191    s2.353-2.14,3.919-3.566c0.14-0.131,0.202-0.332,0.14-0.524s-0.23-0.319-0.42-0.341c-2.108-0.236-5.269-0.586-5.269-0.586    s-1.31-2.898-2.183-4.83c-0.082-0.173-0.254-0.294-0.456-0.294s-0.375,0.122-0.453,0.294C10.671,6.26,9.362,9.158,9.362,9.158z"></path></g></g></svg>
                </label>
            </div>
            <div class="price_input">
                <label class="label_c" for="old_price">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-123" viewBox="0 0 16 16">
                        <path d="M2.873 11.297V4.142H1.699L0 5.379v1.137l1.64-1.18h.06v5.961zm3.213-5.09v-.063c0-.618.44-1.169 1.196-1.169.676 0 1.174.44 1.174 1.106 0 .624-.42 1.101-.807 1.526L4.99 10.553v.744h4.78v-.99H6.643v-.069L8.41 8.252c.65-.724 1.237-1.332 1.237-2.27C9.646 4.849 8.723 4 7.308 4c-1.573 0-2.36 1.064-2.36 2.15v.057zm6.559 1.883h.786c.823 0 1.374.481 1.379 1.179.01.707-.55 1.216-1.421 1.21-.77-.005-1.326-.419-1.379-.953h-1.095c.042 1.053.938 1.918 2.464 1.918 1.478 0 2.642-.839 2.62-2.144-.02-1.143-.922-1.651-1.551-1.714v-.063c.535-.09 1.347-.66 1.326-1.678-.026-1.053-.933-1.855-2.359-1.845-1.5.005-2.317.88-2.348 1.898h1.116c.032-.498.498-.944 1.206-.944.703 0 1.206.435 1.206 1.07.005.64-.504 1.106-1.2 1.106h-.75z"/>
                    </svg>
                    Prezzo barrato</label>
                <p><span>€</span><input @if($product->old_price) value="{{  $product->old_price / 100 }}" @else value="{{ old('old_price')}}" @endif  step="0.01" type="number" name="old_price" id="old_price" placeholder=" Inserisci il prezzo barrato"></p>
            </div>
        </div>
    </section>
    @if ($property_adv['services'] > 2)
    <section class="set" >
        <div class="split-3">
            
            @if ($property_adv['too'])
            <div>
                <label class="label_c" for="slot_plate">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-123" viewBox="0 0 16 16">
                    <path d="M2.873 11.297V4.142H1.699L0 5.379v1.137l1.64-1.18h.06v5.961zm3.213-5.09v-.063c0-.618.44-1.169 1.196-1.169.676 0 1.174.44 1.174 1.106 0 .624-.42 1.101-.807 1.526L4.99 10.553v.744h4.78v-.99H6.643v-.069L8.41 8.252c.65-.724 1.237-1.332 1.237-2.27C9.646 4.849 8.723 4 7.308 4c-1.573 0-2.36 1.064-2.36 2.15v.057zm6.559 1.883h.786c.823 0 1.374.481 1.379 1.179.01.707-.55 1.216-1.421 1.21-.77-.005-1.326-.419-1.379-.953h-1.095c.042 1.053.938 1.918 2.464 1.918 1.478 0 2.642-.839 2.62-2.144-.02-1.143-.922-1.651-1.551-1.714v-.063c.535-.09 1.347-.66 1.326-1.678-.026-1.053-.933-1.855-2.359-1.845-1.5.005-2.317.88-2.348 1.898h1.116c.032-.498.498-.944 1.206-.944.703 0 1.206.435 1.206 1.07.005.64-.504 1.106-1.2 1.106h-.75z"/>
                    </svg> Spazio occupato</label>
                <p><input @if(!isset($data)) value="{{ old('slot_plate', $product->slot_plate) }}" @else value="{{ $data['slot_plate'] }}" @endif  type="number" name="slot_plate" id="slot_plate" placeholder="Inserisci lo spazio  "></p>
                @error('slot_plate') <p class="error">{{ $message }}</p> @enderror
            </div>
           
            <div>
                <label class="label_c" for="type_plate">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-view-list" viewBox="0 0 16 16">
                    <path d="M3 4.5h10a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2m0 1a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1v-3a1 1 0 0 0-1-1zM1 2a.5.5 0 0 1 .5-.5h13a.5.5 0 0 1 0 1h-13A.5.5 0 0 1 1 2m0 12a.5.5 0 0 1 .5-.5h13a.5.5 0 0 1 0 1h-13A.5.5 0 0 1 1 14"/>
                    </svg>
                    Tipo di piatto</label>
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
                <label class="label_c" for="tag_set">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-view-list" viewBox="0 0 16 16">
                    <path d="M3 4.5h10a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2m0 1a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1v-3a1 1 0 0 0-1-1zM1 2a.5.5 0 0 1 .5-.5h13a.5.5 0 0 1 0 1h-13A.5.5 0 0 1 1 2m0 12a.5.5 0 0 1 .5-.5h13a.5.5 0 0 1 0 1h-13A.5.5 0 0 1 1 14"/>
                    </svg>
                    Custom Ingredienti</label>
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
        <div class="modal-content my_bg_6">
            <div class="modal-header">
                <h2>Crea e aggiungi Ingredienti mancanti</h2>
                <button type="button" class="btn-close my_btn_2" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <section class="more_i">  
                    <div class="split">
                        <div>
                            <label class="label_c" for="name_ing">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-type" viewBox="0 0 16 16">
                                    <path d="m2.244 13.081.943-2.803H6.66l.944 2.803H8.86L5.54 3.75H4.322L1 13.081zm2.7-7.923L6.34 9.314H3.51l1.4-4.156zm9.146 7.027h.035v.896h1.128V8.125c0-1.51-1.114-2.345-2.646-2.345-1.736 0-2.59.916-2.666 2.174h1.108c.068-.718.595-1.19 1.517-1.19.971 0 1.518.52 1.518 1.464v.731H12.19c-1.647.007-2.522.8-2.522 2.058 0 1.319.957 2.18 2.345 2.18 1.06 0 1.716-.43 2.078-1.011zm-1.763.035c-.752 0-1.456-.397-1.456-1.244 0-.65.424-1.115 1.408-1.115h1.805v.834c0 .896-.752 1.525-1.757 1.525"/>
                                </svg>
                                Nome</label>
                            <p><input value="{{ old('name_ing') }}" type="text" name="name_ing" id="name_ing" placeholder=" Inserisci il nome"></p>
                            @error('name_ing') <p class="error">{{ $message }}</p> @enderror
                        </div>
                        <div class="price_input">
                            <label class="label_c" for="price_ing">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-123" viewBox="0 0 16 16">
                                <path d="M2.873 11.297V4.142H1.699L0 5.379v1.137l1.64-1.18h.06v5.961zm3.213-5.09v-.063c0-.618.44-1.169 1.196-1.169.676 0 1.174.44 1.174 1.106 0 .624-.42 1.101-.807 1.526L4.99 10.553v.744h4.78v-.99H6.643v-.069L8.41 8.252c.65-.724 1.237-1.332 1.237-2.27C9.646 4.849 8.723 4 7.308 4c-1.573 0-2.36 1.064-2.36 2.15v.057zm6.559 1.883h.786c.823 0 1.374.481 1.379 1.179.01.707-.55 1.216-1.421 1.21-.77-.005-1.326-.419-1.379-.953h-1.095c.042 1.053.938 1.918 2.464 1.918 1.478 0 2.642-.839 2.62-2.144-.02-1.143-.922-1.651-1.551-1.714v-.063c.535-.09 1.347-.66 1.326-1.678-.026-1.053-.933-1.855-2.359-1.845-1.5.005-2.317.88-2.348 1.898h1.116c.032-.498.498-.944 1.206-.944.703 0 1.206.435 1.206 1.07.005.64-.504 1.106-1.2 1.106h-.75z"/>
                                </svg>
                                Prezzo</label>
                            <p><span>€</span><input value="{{ old('price_ing') }}" type="number" name="price_ing" id="price_ing"  step="0.01" placeholder=" Inserisci il prezzo "></p>
                            @error('price_ing') <p class="error">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    
                    <div>
                        <label class="label_c" for="file-input1"> 
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-earmark-image" viewBox="0 0 16 16">
                                <path d="M6.502 7a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3"/>
                                <path d="M14 14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h5.5L14 4.5zM4 1a1 1 0 0 0-1 1v10l2.224-2.224a.5.5 0 0 1 .61-.075L8 11l2.157-3.02a.5.5 0 0 1 .76-.063L13 10V4.5h-2A1.5 1.5 0 0 1 9.5 3V1z"/>
                            </svg> 
                            Immagine</label>
                        <p><input type="file" id="file-input" name="image_ing" ></p>
                        @error('image_ing') <p class="error">{{ $message }}</p> @enderror    
                    </div>          
                
                    <div class="check_c">
                        <label class="label_c" for="type">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-ui-checks-grid" viewBox="0 0 16 16">
                                <path d="M2 10h3a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1m9-9h3a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-3a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1m0 9a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h3a1 1 0 0 0 1-1v-3a1 1 0 0 0-1-1zm0-10a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h3a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2zM2 9a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h3a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2zm7 2a2 2 0 0 1 2-2h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2h-3a2 2 0 0 1-2-2zM0 2a2 2 0 0 1 2-2h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm5.354.854a.5.5 0 1 0-.708-.708L3 3.793l-.646-.647a.5.5 0 1 0-.708.708l1 1a.5.5 0 0 0 .708 0z"/>
                            </svg>
                            Categorie abbinate</label>
                        <p>
                            
                            @foreach ($categories as $c)
                            
                            <input type="checkbox" class="btn-check" id="a{{ $c->id }}" name="type_ing[]" value="{{ $c->id }}" @if (in_array($c->id, old('type_ing', []))) checked @endif>
                            <label class="btn btn-outline-light" for="a{{ $c->id }}">{{ $c['name'] }}</label>
                            @endforeach
                            
                        </p>
                        @error('type_ing') <p class="error">{{ $message }}</p> @enderror
                    </div>
                    
                    <div class="check_c">
                        <label class="label_c" for="type">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-ui-checks-grid" viewBox="0 0 16 16">
                            <path d="M2 10h3a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1m9-9h3a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-3a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1m0 9a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h3a1 1 0 0 0 1-1v-3a1 1 0 0 0-1-1zm0-10a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h3a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2zM2 9a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h3a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2zm7 2a2 2 0 0 1 2-2h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2h-3a2 2 0 0 1-2-2zM0 2a2 2 0 0 1 2-2h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm5.354.854a.5.5 0 1 0-.708-.708L3 3.793l-.646-.647a.5.5 0 1 0-.708.708l1 1a.5.5 0 0 0 .708 0z"/>
                            </svg>
                            Allergeni</label>
                        <p>
                            @foreach(  config('configurazione.allergens') as $a)
                                @php $i = $loop->iteration; @endphp
                                <input type="checkbox" class="btn-check" id="b{{ $i }}" name="allergens_ing[]" value="{{ $i }}" @if (in_array($i, old('allergens_ing', []))) checked @endif>
                                <label class="btn 
                                @if($a['special'])
                                btn-outline-dark btn_special
                                @else
                                btn-outline-light
                                @endif
                                " for="b{{ $i }}">{{ $a['name'] }}</label>
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
    {{-- ingedienti --}}
    <section class="cont_i">
        <h2>
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-ui-checks-grid" viewBox="0 0 16 16">
                <path d="M2 10h3a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1m9-9h3a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-3a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1m0 9a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h3a1 1 0 0 0 1-1v-3a1 1 0 0 0-1-1zm0-10a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h3a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2zM2 9a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h3a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2zm7 2a2 2 0 0 1 2-2h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2h-3a2 2 0 0 1-2-2zM0 2a2 2 0 0 1 2-2h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm5.354.854a.5.5 0 1 0-.708-.708L3 3.793l-.646-.647a.5.5 0 1 0-.708.708l1 1a.5.5 0 0 0 .708 0z"/>
            </svg>
            Abbina Ingredienti
        </h2>
        <div id="associated-ingredients" class="check_c">
            <h3>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check-square-fill" viewBox="0 0 16 16">
                    <path d="M2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2zm10.03 4.97a.75.75 0 0 1 .011 1.05l-3.992 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425a.75.75 0 0 1 1.08-.022z"/>
                </svg> Ingredienti abbinati
            </h3>
            <p id="associated-list">
                @if(isset($data['ingredients']))
                    @foreach($ingredients as $ingredient)
                        @php
                            $alreadyLinked = (isset($data['ingredients']) && in_array($ingredient->id, $data['ingredients']));
                        @endphp
            
                        @if($alreadyLinked)
                            <button type="button" class="available-ingredient" data-id="{{ $ingredient->id }}" data-name="{{ $ingredient->name }}">
                                {{ $ingredient->name }}
                                <span class="remove-ingredient">−</span>
                            </button>
                        @endif
                    @endforeach
                @else
                    
                    @foreach ($product->ingredients as $ingredient)
                        <button type="button" class="associated-ingredient" data-id="{{ $ingredient->id }}" data-name="{{ $ingredient->name }}">
                            {{ $ingredient->name }}
                            <span class="remove-ingredient">−</span>
                        </button>
                    @endforeach
                @endif
            </p>
        </div>

        <div class="check_c">
            <h3>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-square" viewBox="0 0 16 16">
                    <path d="M14 1a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1zM2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2z"/>
                </svg>
                Ingredienti disponibili
            </h3>
            <p id="available-ingredients">
                @foreach($ingredients as $ingredient)
                    @php
                        $alreadyLinked = (isset($data['ingredients']) && in_array($ingredient->id, $data['ingredients'])) 
                            || in_array($ingredient->id, old('ingredients', $product->ingredients->pluck('id')->all()));
                    @endphp

                    @if(!$alreadyLinked)
                        <button type="button" class="available-ingredient" data-id="{{ $ingredient->id }}" data-name="{{ $ingredient->name }}">
                            {{ $ingredient->name }}
                        </button>
                    @endif
                @endforeach

                @error('ingredients') 
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror  
            </p>
        </div>
        <button type="button" class="my_btn_5 ml-auto" data-bs-toggle="modal" data-bs-target="#staticBackdrop">
            Crea Ingredienti mancanti
        </button>

        <!-- Hidden container con input per submit -->
        <div id="selected-ingredients">
            @foreach ($product->ingredients as $ingredient)
                <input type="hidden" name="ingredients[]" value="{{ $ingredient->id }}" data-id="{{ $ingredient->id }}" >
            @endforeach
        </div>


        
    </section>
    <section >
        <label class="label_c" for="type">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-ui-checks-grid" viewBox="0 0 16 16">
            <path d="M2 10h3a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1m9-9h3a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-3a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1m0 9a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h3a1 1 0 0 0 1-1v-3a1 1 0 0 0-1-1zm0-10a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h3a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2zM2 9a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h3a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2zm7 2a2 2 0 0 1 2-2h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2h-3a2 2 0 0 1-2-2zM0 2a2 2 0 0 1 2-2h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm5.354.854a.5.5 0 1 0-.708-.708L3 3.793l-.646-.647a.5.5 0 1 0-.708.708l1 1a.5.5 0 0 0 .708 0z"/>
            </svg>
            Allergeni</label>
        <div class="check_c">
            <p>
                @foreach(  config('configurazione.allergens') as $a)
                    @php 
                        $i = $loop->iteration;
                        if ($product->allergens) {
                            $al = json_decode($product->allergens);
                        }else{
                            $al = [];
                        }
                    @endphp
                    <input type="checkbox" class="btn-check" id="ab{{ $i }}" name="allergens[]" value="{{ $i }}" 
                    @if (in_array($i, old('allergens', $al, []))) checked @endif>
                    <label class="btn 
                    @if($a['special'])
                    btn-outline-dark btn_special
                    @else
                    btn-outline-light
                    @endif
                    " for="ab{{ $i }}">{{ $a['name'] }}</label>
                @endforeach
            </p>
        </div>
    </section>
    <button class="my_btn_2 mb-5  w-75 m-auto" type="submit">Modifica Prodotto</button>

</form>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const associatedContainer = document.getElementById('associated-list');
    const availableContainer = document.getElementById('available-ingredients');
    const hiddenInputsContainer = document.getElementById('selected-ingredients');

    function createAvailableIngredientButton(id, name) {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'available-ingredient';
        button.dataset.id = id;
        button.dataset.name = name;
        button.textContent = name;

        button.addEventListener('click', () => {
            button.remove();
            addAssociatedIngredient(id, name);
        });

        return button;
    }

    function addAssociatedIngredient(id, name) {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'associated-ingredient';
        button.dataset.id = id;
        button.dataset.name = name;
        button.innerHTML = `${name} <span class="remove-ingredient">−</span>`;

        button.querySelector('.remove-ingredient').addEventListener('click', () => {
            button.remove();
            removeHiddenInput(id);
            const availableBtn = createAvailableIngredientButton(id, name);
            availableContainer.prepend(availableBtn);
        });

        associatedContainer.appendChild(button);
        addHiddenInput(id);
    }

    function addHiddenInput(id) {
        // Evita duplicati
        if (hiddenInputsContainer.querySelector(`input[data-id="${id}"]`)) return;

        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'ingredients[]';
        input.value = id;
        input.dataset.id = id;
        hiddenInputsContainer.appendChild(input);
    }

    function removeHiddenInput(id) {
        const input = hiddenInputsContainer.querySelector(`input[data-id="${id}"]`);
        if (input) {
            input.remove();
        } else {
            console.warn('Hidden input non trovato per ID:', id);
        }
    }

    // Associa eventi su bottoni già renderizzati
    availableContainer.querySelectorAll('.available-ingredient').forEach(button => {
        button.addEventListener('click', () => {
            const id = button.dataset.id;
            const name = button.dataset.name;
            button.remove();
            addAssociatedIngredient(id, name);
        });
    });

    associatedContainer.querySelectorAll('.associated-ingredient').forEach(button => {
        const id = button.dataset.id;
        const name = button.dataset.name;
        const removeBtn = button.querySelector('.remove-ingredient');

        removeBtn?.addEventListener('click', () => {
            button.remove();
            removeHiddenInput(id);
            const availableBtn = createAvailableIngredientButton(id, name);
            availableContainer.prepend(availableBtn);
        });

        // Aggiunge hidden anche per quelli già associati
        addHiddenInput(id);
    });
});

</script>






@endsection