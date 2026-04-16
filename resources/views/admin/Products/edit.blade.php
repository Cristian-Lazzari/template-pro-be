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
    

 
<div class="dash_page">
    <a class="my_btn_5 ml-auto" href="{{ route('admin.products.index') }}">{{__('admin.Annulla')}}</a>
    <h1 class="mb-5">{{ __('admin.Modifica_prodotto') }}</h1>
@php
    $list = $languages['languages'];
    $dfl = $languages['default'];
    $list =array_diff($list, [$dfl]);
   
@endphp
    <form class="creation"  action="{{ route('admin.products.update', ['product' => $product]) }}"  enctype="multipart/form-data"  method="POST">
        @method('PUT')
        @csrf
 
        <section class="base">
            <div class="split">
                <div>
                    <label class="label_c" for="name"> 
                        <i class="bi bi-type"></i>
                        {{__('admin.Nome')}}
                    </label>
                    <p>
                        <input @if(!isset($data)) value="{{ old('name', $translations[$dfl]->name) }}" 
                            @else  value="{{ $data['name'] }}" @endif
                            type="text" name="name" id="name" placeholder=" Inserisci il nome">
                    </p>
                    @error('name') <p class="error">{{ $message }}</p> @enderror
                </div>
                <div class="price_input">
                    <label class="label_c" for="price">   
                        <i class="bi bi-123"></i>
                        {{__('admin.Prezzo')}}</label>
                    <p><input @if(!isset($data)) value="{{  $product->price / 100 }}" @else value="{{ $data['price'] / 100}}" @endif  step="0.01" type="number" name="price" id="price" placeholder=" Inserisci il prezzo "><span>€</span></p>
                    @error('price') <p class="error">{{ $message }}</p> @enderror
                </div>
            </div>
            
            <div class="split">
                
                <div>
                    <label class="label_c" for="file-input">
                        <i class="bi bi-file-earmark-image"></i>
                    {{__('admin.Immagine')}}</label>
                    <p class="img-cont">
                        <input type="file" id="file-input" name="image" >
                        @if (isset($product->image))
                            <input type="checkbox" class="btn-check" id="b2" name="img_off">
                            <label class="btn btn-outline-danger" for="b2">
                                <i class="bi bi-trash-fill"></i>
                            </label>
                            <img class="" src="{{ asset('public/storage/' . $product->image) }}" alt="{{$product->name }}">
                        @endif 
                    </p>
                    @error('image') <p class="error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="label_c" for="category_id">
                        <i class="bi bi-view-list"></i>
                        {{__('admin.Categoria')}}</label>
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
                    <i class="bi bi-body-text"></i> 
                    {{__('admin.Descrizione')}}</label>   
                <textarea name="description" id="description" cols="1" rows="4" >{{ old('description', $translations[$dfl]->description) }}</textarea>
            </p>
            {{-- multilingua --}}
            <section class="cont_i">
                <label class="label_c" for="l*">
                    <i class="bi bi-translate"></i>
                    {{__('admin.Multilingua')}}</label> 
                    
                    <div class="check_c"  style="border: none !important; padding: 0; border-radius: 0; width: fit-content">
                        @foreach ($list as $i)
                            <button type="button" style="text-transform: uppercase" id="l{{$i}}" class="btn btn-outline-light" data-bs-toggle="modal" data-bs-target="#lang{{$i}}">{{$i}}</button>
                            <!-- Modal -->
                            <div class="modal fade" id="lang{{$i}}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="lang{{$i}}Label" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <x-dashboard.action-modal
                                        title-id="lang{{$i}}Label"
                                        title="Personalizza traduzione"
                                        eyebrow="{{ strtoupper($i) }}"
                                        tone="mint"
                                        description="Aggiorna nome e descrizione del prodotto nella lingua selezionata."
                                    >
                                        <div class="check_c" style="border: none !important; padding: 0; border-radius: 0; width: fit-content">
                                            @foreach ($list as $e)
                                                <button type="button" style="text-transform: uppercase" class="btn {{$i != $e ? 'btn-outline-light' : 'btn-light'}}" data-bs-toggle="modal" data-bs-target="#lang{{$e}}">{{$e}}</button>
                                            @endforeach
                                        </div>

                                        <div class="dashboard-action-modal__field">
                                            <label class="label_c" for="translations[{{$i}}][name]">
                                                <i class="bi bi-type"></i>
                                                {{__('admin.Nome')}}
                                            </label>
                                            <input value="{{ $translations[$i]->name ?? '' }}" type="text" name="translations[{{$i}}][name]" id="translations[{{$i}}][name]" placeholder=" Inserisci il nome">
                                        </div>

                                        <div class="dashboard-action-modal__field">
                                            <label class="label_c" for="translations[{{$i}}][description]">
                                                <i class="bi bi-body-text"></i> 
                                                {{__('admin.Descrizione')}}
                                            </label>
                                            <textarea name="translations[{{$i}}][description]" id="translations[{{$i}}][description]" cols="1" rows="4">{{$translations[$i]->description ?? '' }}</textarea>
                                        </div>

                                        <x-slot name="footer">
                                            <button class="my_btn_2" type="submit">{{ __('admin.Modifica_Traduzione') }}</button>
                                        </x-slot>
                                    </x-dashboard.action-modal>
                                </div>
                            </div>
                        @endforeach
                    </div>
            </section>
            {{-- end multilingua --}}
            <div class="split">
                <div>
                    <label class="label_c" for="promotion">{{ __('admin.Prodotto_in_evidenza') }}</label>
                    <label class="container_star">
                        <input name="promotion" type="checkbox" @if (old('promotion', $product->promotion))  checked  @endif>
                        <svg height="24px" id="promotion" version="1.2" viewBox="0 0 24 24" width="24px" xml:space="preserve" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><g><g><path d="M9.362,9.158c0,0-3.16,0.35-5.268,0.584c-0.19,0.023-0.358,0.15-0.421,0.343s0,0.394,0.14,0.521    c1.566,1.429,3.919,3.569,3.919,3.569c-0.002,0-0.646,3.113-1.074,5.19c-0.036,0.188,0.032,0.387,0.196,0.506    c0.163,0.119,0.373,0.121,0.538,0.028c1.844-1.048,4.606-2.624,4.606-2.624s2.763,1.576,4.604,2.625    c0.168,0.092,0.378,0.09,0.541-0.029c0.164-0.119,0.232-0.318,0.195-0.505c-0.428-2.078-1.071-5.191-1.071-5.191    s2.353-2.14,3.919-3.566c0.14-0.131,0.202-0.332,0.14-0.524s-0.23-0.319-0.42-0.341c-2.108-0.236-5.269-0.586-5.269-0.586    s-1.31-2.898-2.183-4.83c-0.082-0.173-0.254-0.294-0.456-0.294s-0.375,0.122-0.453,0.294C10.671,6.26,9.362,9.158,9.362,9.158z"></path></g></g></svg>
                    </label>
                </div>
                <div class="price_input">
                    <label class="label_c" for="old_price">
                        <i class="bi bi-123"></i>
                        {{__('admin.Prezzo_barrato')}}</label>
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
                    <i class="bi bi-123"></i> Spazio occupato</label>
                    <p><input @if(!isset($data)) value="{{ old('slot_plate', $product->slot_plate) }}" @else value="{{ $data['slot_plate'] }}" @endif  type="number" name="slot_plate" id="slot_plate" placeholder="Inserisci lo spazio  "></p>
                    @error('slot_plate') <p class="error">{{ $message }}</p> @enderror
                </div>
            
                <div>
                    <label class="label_c" for="type_plate">
                        <i class="bi bi-view-list"></i>{{ __('admin.Tipo_di_piatto') }}</label>
                    <p>
                        <select name="type_plate" id="type_plate">
                            <option @if( 0 == old('type_plate', $product->type_plate)) selected  @elseif(isset($data) && 0 == $data['type_plate']) selected @endif value="0">{{ __('admin.altro') }}</option>
                            <option @if( 1 == old('type_plate', $product->type_plate)) selected  @elseif(isset($data) && 1 == $data['type_plate']) selected @endif value="1">{{ __('admin.tipo_1_al_taglio') }}</option>
                            <option @if( 2 == old('type_plate', $product->type_plate)) selected  @elseif(isset($data) && 2 == $data['type_plate']) selected @endif value="2">{{ __('admin.tipo_2_al_piatto') }}</option>
                        </select>
                    </p>
                    @error('type_plate') <p class="error">{{ $message }}</p> @enderror
                </div>
                @endif
                <div>
                    <label class="label_c" for="tag_set">
                        <i class="bi bi-view-list"></i>{{ __('admin.Custom_Ingredienti') }}</label>
                    <p>
                        <select name="tag_set" id="tag_set">
                            <option @if( 0 == old('tag_set', $product->tag_set)) selected  @elseif(isset($data) && 0 == $data['tag_set']) selected @endif value="0">{{ __('admin.NON_modificabile') }}</option>
                            <option @if( 1 == old('tag_set', $product->tag_set)) selected  @elseif(isset($data) && 1 == $data['tag_set']) selected @endif value="1">{{ __('admin.togliere') }}</option>
                            <option @if( 2 == old('tag_set', $product->tag_set)) selected  @elseif(isset($data) && 2 == $data['tag_set']) selected @endif value="2">{{ __('admin.aggiungiere') }}</option>
                            <option @if( 3 == old('tag_set', $product->tag_set)) selected  @elseif(isset($data) && 3 == $data['tag_set']) selected @endif value="3">{{ __('admin.togliere_e_aggiungiere') }}</option>
                        </select>
                    </p>
                    @error('tag_set') <p class="error">{{ $message }}</p> @enderror
                </div>
            </div>
        </section>
        @endif

        <!-- Modal -->
        <div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <x-dashboard.action-modal
                    title-id="staticBackdropLabel"
                    title="{{ __('admin.Crea_e_aggiungi_Ingredienti_mancanti') }}"
                    eyebrow="Ingrediente rapido"
                    tone="mint"
                    description="Aggiungi un ingrediente senza uscire dalla modifica prodotto e collegalo subito alla scheda corrente."
                >
                    <section class="more_i">  
                        <div class="split">
                            <div>
                                <label class="label_c" for="name_ing">
                                    <i class="bi bi-type"></i>
                                    {{__('admin.Nome')}}</label>
                                <p><input value="{{ old('name_ing') }}" type="text" name="name_ing" id="name_ing" placeholder=" Inserisci il nome"></p>
                                @error('name_ing') <p class="error">{{ $message }}</p> @enderror
                            </div>
                            <div class="price_input">
                                <label class="label_c" for="price_ing">
                                    <i class="bi bi-123"></i>
                                    {{__('admin.Prezzo')}}</label>
                                <p><span>€</span><input value="{{ old('price_ing') }}" type="number" name="price_ing" id="price_ing"  step="0.01" placeholder=" Inserisci il prezzo "></p>
                                @error('price_ing') <p class="error">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        
                        <div>
                            <label class="label_c" for="file-input1"> 
                                <i class="bi bi-file-earmark-image"></i> 
                                {{__('admin.Immagine')}}</label>
                            <p><input type="file" id="file-input1" name="image_ing" ></p>
                            @error('image_ing') <p class="error">{{ $message }}</p> @enderror    
                        </div>          
                    
                        <div class="check_c">
                            <label class="label_c" for="type">
                                <i class="bi bi-ui-checks-grid"></i>
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
                                <i class="bi bi-ui-checks-grid"></i>
                                {{__('admin.Allergeni')}}</label>
                            <p>
                                @foreach( $allergens as $a)
                                    <input type="checkbox" class="btn-check" id="b{{ $a->id }}" name="allergens_ing[]" value="{{ $a->id }}" @if (in_array($a->id, old('allergens_ing', [])))  checked @endif>
                                    <label class="btn 
                                    @if($a->special)
                                        btn-outline-dark btn_special
                                    @else
                                        btn-outline-light
                                    @endif
                                    " 
                                    for="b{{ $a->id }}">{{ $a->name }}</label>
                                @endforeach
                            </p>
                        </div>
                    </section>

                    <x-slot name="footer">
                        <button class="my_btn_2" type="submit" name="newi" value="1">{{ __('admin.Crea_Ingrediente') }}</button>
                    </x-slot>
                </x-dashboard.action-modal>
            </div>
        </div>
        {{-- ingedienti --}}
        <section class="cont_i">
            <h2>
                <i class="bi bi-ui-checks-grid"></i>
                Abbina Ingredienti
            </h2>
            <div id="associated-ingredients" class="check_c">
                <h3>
                    <i class="bi bi-check-square-fill"></i> Ingredienti abbinati
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
                                    <span class="remove-ingredient">-</span>
                                </button>
                            @endif
                        @endforeach
                    @else
                        
                        @foreach ($product->ingredients as $ingredient)
                            <button type="button" class="associated-ingredient" data-id="{{ $ingredient->id }}" data-name="{{ $ingredient->name }}">
                                {{ $ingredient->name }}
                                <span class="remove-ingredient">-</span>
                            </button>
                        @endforeach
                    @endif
                </p>
            </div>

            <div class="check_c">
                <h3>
                    <i class="bi bi-square"></i>
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
                <i class="bi bi-ui-checks-grid"></i>
                {{__('admin.Allergeni')}}</label>
            <div class="check_c">
                <p>
                    @foreach(  $allergens as $a)
                        <input type="checkbox" class="btn-check" id="ab{{ $a->id }}" name="allergens[]" value="{{ $a->id }}" 
                        @if (in_array($a->id, $product->allergens->pluck('id')->toArray()))checked @endif>
                        <label class="btn 
                        @if($a->special != 0)
                        btn-outline-dark btn_special
                        @else
                        btn-outline-light
                        @endif
                        " for="ab{{ $a->id }}">{{ $a['name'] }}</label>
                    @endforeach
                </p>
            </div>
        </section>
        <button class="my_btn_2 mb-5  w-75 m-auto" type="submit">{{ __('admin.Modifica_Prodotto') }}</button>

    </form>
</div>
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
        button.innerHTML = `${name} <span class="remove-ingredient">-</span>`;

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
