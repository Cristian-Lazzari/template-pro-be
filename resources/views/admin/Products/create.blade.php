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

 
<form class="creation"  action="{{ route('admin.products.store') }}"  enctype="multipart/form-data"  method="POST">
    @csrf
    <a class="my_btn_5 ml-auto" href="{{ route('admin.products.index') }}">{{__('admin.Annulla')}}</a>
    
    <h1>{{__('admin.Crea_nuovo_p')}}</h1>
    
    <section class="base">
        <div class="split">
            <div>
                <label class="label_c" for="name"> 
                    <i class="bi bi-type"></i>
                      {{__('admin.Nome')}}
                </label>
                <p><input @if(!isset($data)) value="{{ old('name') }}" @else value="{{ $data['name'] }}" @endif  type="text" name="name" id="name" placeholder=" Inserisci il nome"></p>
                @error('name') <p class="error">{{ $message }}</p> @enderror
            </div>
            <div class="price_input">
                <label class="label_c" for="price">   
                    <i class="bi bi-123"></i>
                    {{__('admin.Prezzo')}}</label>
                <p><input @if(!isset($data)) value="{{ old('price') }}" @else value="{{ $data['price'] }}" @endif  type="number" name="price" id="price" step="0.01" placeholder=" Inserisci il prezzo "><span>€</span></p> 
                @error('price') <p class="error">{{ $message }}</p> @enderror
            </div>
        </div>
        <div class="split">
            
            <div>
                <label class="label_c" for="file-input">
                    <i class="bi bi-file-earmark-image"></i>
                    {{__('admin.Immagine')}}</label>
                <p><input type="file" id="file-input" name="image" ></p>
                @error('image') <p class="error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="label_c" for="category_id">
                    <i class="bi bi-view-list"></i>
                    {{__('admin.Categoria')}}</label>
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
            <label class="label_c" for="description">
            <i class="bi bi-body-text"></i> 
            {{__('admin.Descrizione')}}</label>   
            <textarea name="description" id="description" cols="30" rows="10" >@if(!isset($data)) {{ old('description') }} @else {{ $data['description'] }} @endif </textarea>
        </p>
    </section>
    @error('slot_plate') <p class="error">{{ $message }}</p> @enderror
    @if ($property_adv['services'] > 2)
    <section class="set" >
        <div class="split-3">
        
            @if ($property_adv['too'])
            <div>
                <label class="label_c" for="slot_plate">
                <i class="bi bi-123"></i>{{ __('admin.Spazio_occupato') }}</label>
                <p><input @if(!isset($data)) value="{{ old('slot_plate', 0) }}" @else value="{{ $data['slot_plate'] }}" @endif  type="number" name="slot_plate" id="slot_plate" placeholder="Inserisci lo spazio  "></p>
                @error('slot_plate') <p class="error">{{ $message }}</p> @enderror
            </div>
        
            <div>
                
                <label class="label_c" for="type_plate">
                    <i class="bi bi-view-list"></i>{{ __('admin.Tipo_di_piatto') }}</label>
                <p>
                    <select name="type_plate" id="type_plate">
                        <option @if( 0 == old('type_plate')) selected  @elseif(isset($data) && 0 == $data['type_plate']) selected @endif value="0">{{ __('admin.altro') }}</option>
                        <option @if( 1 == old('type_plate')) selected  @elseif(isset($data) && 1 == $data['type_plate']) selected @endif value="1">{{ __('admin.tipo_1_al_taglio') }}</option>
                        <option @if( 2 == old('type_plate')) selected  @elseif(isset($data) && 2 == $data['type_plate']) selected @endif value="2">{{ __('admin.tipo_2_al_piatto') }}</option>
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
                        <option @if( 0 == old('tag_set')) selected  @elseif(isset($data) && 0 == $data['tag_set']) selected @endif value="0">{{ __('admin.NON_modificabile') }}</option>
                        <option @if( 1 == old('tag_set')) selected  @elseif(isset($data) && 1 == $data['tag_set']) selected @endif value="1">{{ __('admin.togliere') }}</option>
                        <option @if( 2 == old('tag_set')) selected  @elseif(isset($data) && 2 == $data['tag_set']) selected @endif value="2">{{ __('admin.aggiungiere') }}</option>
                        <option @if( 3 == old('tag_set')) selected  @elseif(isset($data) && 3 == $data['tag_set']) selected @endif value="3">{{ __('admin.togliere_e_aggiungiere') }}</option>
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
                <h2>{{ __('admin.Crea_e_aggiungi_Ingredienti_mancanti') }}</h2>
                <button type="button" class="btn-close my_btn_2" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
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
                            <p><span>€</span><input value="{{ old('price_ing') }}" type="number" name="price_ing" step="0.01" id="price_ing" placeholder=" Inserisci il prezzo "></p>
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
                            <i class="bi bi-ui-checks-grid"></i>{{ __('admin.Categorie_abbinate') }}</label>
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
                                <input type="checkbox" class="btn-check" id="b{{ $a->id }}" name="allergens_ing[]" value="{{ $a->id }}" 
                                @if (in_array($a->id, old('allergens_ing', [])))  checked @endif
                                >
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
                        <input type="submit" class="btn-check" id="newi" name="newi" value="1">
                        <label class="my_btn_2 m-auto" for="newi">{{ __('admin.Crea_Ingrediente') }}</label>
                </section>
            </div>
            
        </div>
        </div>
    </div>
    <section class="cont_i">
        <h2>
            <i class="bi bi-ui-checks-grid"></i>{{ __('admin.Abbina_Ingredienti') }}</h2>
            <!-- Button trigger modal -->

        <div id="associated-ingredients" class="check_c">
            <h3>
                <i class="bi bi-check-square-fill"></i>{{ __('admin.Ingredienti_abbinati') }}</h3>
            <p id="associated-list">
                @if(isset($data['ingredients']))
                    @foreach($ingredients as $ingredient)
                        @php
                            $alreadyLinked = (isset($data['ingredients']) && in_array($ingredient->id, $data['ingredients']));
                        @endphp
            
                        @if($alreadyLinked)
                            <button type="button" class="associated-ingredient" data-id="{{ $ingredient->id }}">
                                {{ $ingredient->name }}
                                <span class="remove-ingredient">-</span>
                            </button>
                        @endif
                    @endforeach
                @endif
            </p>
        </div>

        <div class="check_c">
            <h3>
                <i class="bi bi-square"></i>{{ __('admin.Ingredienti_disponibili') }}</h3>
            <p id="available-ingredients">
                @foreach($ingredients as $ingredient)
                    @php
                        $alreadyLinked = (isset($data['ingredients']) && in_array($ingredient->id, $data['ingredients'])) 
                            || in_array($ingredient->id, old('ingredients', []));
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

        <!-- Hidden container con input per submit -->
        <div id="selected-ingredients">
            @if(isset($data['ingredients']))
                @foreach($ingredients as $ingredient)
                    @php
                        $alreadyLinked = (isset($data['ingredients']) && in_array($ingredient->id, $data['ingredients']));
                    @endphp
                    @if($alreadyLinked)
                        <input type="hidden" name="ingredients[]" value="{{ $ingredient->id }}" data-id="{{ $ingredient->id }}" >
                    @endif
                @endforeach
            @endif
        </div>
        <button type="button" class="my_btn_5 m-auto" data-bs-toggle="modal" data-bs-target="#staticBackdrop">{{ __('admin.Crea_Ingredienti_mancanti') }}</button>
        

    </section>
    <section class="cont_i">
        <h2 for="type">
            <i class="bi bi-ui-checks-grid"></i>
            {{__('admin.Allergeni')}}
        <h2>
        <div class="check_c">
            <p>
                @foreach( $allergens as $a)
                    <input type="checkbox" class="btn-check" id="all{{ $a->id }}" name="allergens[]" value="{{ $a->id }}" 
                    @if (in_array($a->id, old('allergens', [])))  checked @endif
                    >
                    <label class="btn 
                    @if($a->special)
                        btn-outline-dark btn_special
                    @else
                        btn-outline-light
                    @endif
                    " 
                    for="all{{ $a->id }}">{{ $a->name }}</label>
                @endforeach
            </p>
        </div>

    </section>
    <button class="my_btn_2 mb-5  w-75 m-auto" type="submit">{{ __('admin.Crea_Prodotto') }}</button>

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