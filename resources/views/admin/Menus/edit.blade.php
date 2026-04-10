@extends('layouts.base')

@section('contents')
    

 
<form class="creation"  action="{{ route('admin.menus.update', $menu) }}"  enctype="multipart/form-data"  method="POST">
    @csrf
    @method('PUT')
    <a class="my_btn_5 ml-auto" href="{{ route('admin.menus.index') }}">{{__('admin.Annulla')}}</a>
    
    <h1>{{ __('admin.Modifica_il_Menu') }}</h1>
    
    <section class="base">
        <div class="split">
            <div>
                <label class="label_c" for="name"> 
                    <i class="bi bi-type" style="font-size: 16px"></i>
                      {{__('admin.Nome')}}
                </label>
                <p><input value="{{ old('name', $menu->name) }}"  type="text" name="name" id="name" placeholder=" Inserisci il nome"></p>
                @error('name') <p class="error">{{ $message }}</p> @enderror
            </div>
            <div class="price_input">
                <label class="label_c" for="price">   
                    <i class="bi bi-123" style="font-size: 16px"></i>
                    {{__('admin.Prezzo')}}</label>
                <p><input value="{{old('price', $menu->price) / 100 }}" step="0.01" type="number" name="price" id="price" placeholder=" Inserisci il prezzo "><span>€</span></p>
                @error('price') <p class="error">{{ $message }}</p> @enderror
            </div>
        </div>
        <div class="split">
            <div class="mr-auto">
                <label class="label_c" for="promo">{{ __('admin.Menu_in_evidenza') }}</label>
                <label class="container_star">
                    <input value="1" name="promo" type="checkbox" @if (old('promo', $menu->promo))  checked  @endif>
                    <svg height="24px" id="promo" version="1.2" viewBox="0 0 24 24" width="24px" xml:space="preserve" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><g><g><path d="M9.362,9.158c0,0-3.16,0.35-5.268,0.584c-0.19,0.023-0.358,0.15-0.421,0.343s0,0.394,0.14,0.521    c1.566,1.429,3.919,3.569,3.919,3.569c-0.002,0-0.646,3.113-1.074,5.19c-0.036,0.188,0.032,0.387,0.196,0.506    c0.163,0.119,0.373,0.121,0.538,0.028c1.844-1.048,4.606-2.624,4.606-2.624s2.763,1.576,4.604,2.625    c0.168,0.092,0.378,0.09,0.541-0.029c0.164-0.119,0.232-0.318,0.195-0.505c-0.428-2.078-1.071-5.191-1.071-5.191    s2.353-2.14,3.919-3.566c0.14-0.131,0.202-0.332,0.14-0.524s-0.23-0.319-0.42-0.341c-2.108-0.236-5.269-0.586-5.269-0.586    s-1.31-2.898-2.183-4.83c-0.082-0.173-0.254-0.294-0.456-0.294s-0.375,0.122-0.453,0.294C10.671,6.26,9.362,9.158,9.362,9.158z"></path></g></g></svg>
                </label>
            </div>
            <div class="price_input">
                <label class="label_c" for="old_price">   
                    <i class="bi bi-123" style="font-size: 16px"></i>
                    {{__('admin.Prezzo_barrato')}}</label>
                <p><span>€</span><input value="{{old('old_price', $menu->old_price / 100 )}}" type="number" name="old_price" id="old_price" step="0.01" placeholder=" Inserisci il prezzo "></p>
                @error('old_price') <p class="error">{{ $message }}</p> @enderror
            </div>
        </div>
        <div class="split">
            <div>
                <label class="label_c" for="file-input">
                    <i class="bi bi-file-earmark-image" style="font-size: 16px"></i>
                {{__('admin.Immagine')}}</label>
                <p class="img-cont">
                    <input type="file" id="file-input" name="image" >
                    @if (isset($menu->image))
                        <input type="checkbox" class="btn-check" id="b2" name="img_off">
                        <label class="btn btn-outline-danger" for="b2">
                            <i class="bi bi-trash-fill" style="font-size: 16px"></i>
                        </label>
                        <img class="" src="{{ asset('public/storage/' . $menu->image) }}" alt="{{$menu->name }}">
                    @endif 
                </p>
                @error('image') <p class="error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="label_c" for="category_id">
                    <i class="bi bi-view-list" style="font-size: 16px"></i>
                    {{__('admin.Categoria')}}</label>
                <p>
                    <select name="category_id" id="category_id">
                        
                        @foreach ($categories as $category)
                            <option @if( $category->id == old('category_id', $menu->category->id)) selected @endif value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </p>
            </div>
        </div>
        <p class="desc"> 
            <label class="label_c" for="description">
            <i class="bi bi-body-text" style="font-size: 16px"></i> 
            {{__('admin.Descrizione')}}</label>   
            <textarea name="description" id="description" cols="1" rows="3" >{{ old('description', $menu->description) }}  </textarea>
        </p>
    </section>

    <section>
        <h2>{{ __('admin.Tipo_di_Menu') }}</h2>
        @php
            $types= [
                0 => 'Menu fisso',
                1 => 'Combo statico',
                2 => 'Menu custom'
            ];
        @endphp
        <div class="radio-inputs">
            <label class="radio">
                <span class="name">{{$types[$menu->fixed_menu]}}</span>
            </label>
        </div>
    </section>

    <section id="section_fix" class="cont_i d-none">
        <h2>
            <i class="bi bi-ui-checks-grid" style="font-size: 16px"></i>
            Abbina Prodotti</h2>
        <div class="check_c">
            
                @foreach($products as $c)
                    <div class="prod_cont">
                        <h3>{{$c->name}}</h3>
                        @foreach ($c->products as $p)
                            <input type="checkbox" style="visibility: hidden; position: absolute;" class="btn-check" id="product{{ $p->id }}{{$c->name}}" name="products[]" 
                            value="{{ $p->id }}"
                            @if(in_array($p->id, old('products', [])))
                                checked
                            @endif>
        
                            <label class="btn btn-outline-light shadow-sm" for="product{{ $p->id }}{{$c->name}}">{{ $p->name }}</label> 
                        @endforeach
                    </div>
                   
                @endforeach
        
            @error('products') 
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror 
        </div>
    </section>
    <section id="section_combo" class="cont_i d-none">
        <h2>{{ __('admin.Imposta_le_scelte_che_puo_fare_il_cliente') }}</h2>
        <button class="my_btn_3 m-auto" id="btn-aggiungi" type="button" onclick="aggiungiCampo()">{{__('admin.Crea_nuova_scelta')}}</button>
        <div id="campi-wrapper"></div>
    </section>
    <button class="my_btn_2 mb-5  w-75 m-auto" type="submit">{{ __('admin.Modifica_Menu') }}</button>

</form>


<div id="campi-wrapper"></div>


<script defer>
    //document.addEventListener("DOMContentLoaded", function () {
    const categories = @json($products);
    let number_field = 0

    function aggiungiCampo() {
        const wrapper = document.getElementById('campi-wrapper');
        const btn = document.getElementById('btn-aggiungi');

        const index = Date.now();
        number_field++;
        console.log(number_field);
        
        const div = document.createElement('div');
        div.classList.add('campo-opzione');
        div.innerHTML = `
            <div class="mb-2 d-flex justify-content-between gap-2">
                <input type="text" name="choice[${number_field}][label]" placeholder="Nome del campo">
                <button type="button" class="btn btn-danger" onclick="this.closest('.campo-opzione').remove(); number_field--">{{ __('admin.Rimuovi') }}</button>
            </div>
            <div class="check_c small"> 
                ${categories.map(cat => `
                    <h3>${cat.name}</h3>
                    <p class="prod_cont">
                    ${cat.products.map(prod => `
                            <input type="checkbox" style="visibility: hidden; position: absolute;"  id="${number_field}${prod.id}" class="btn-check" onchange="toggleExtra(this, ${number_field}, ${prod.id})"
                                name="choice[${number_field}][products][${prod.id}][id]"
                                value="${prod.id}">
                            <label class="btn btn-outline-light shadow-sm" for="${number_field}${prod.id}">
                                ${prod.name}
                            </label>
                            
                           
                    `).join('')}
                    </p>
                `).join('')}
            </div>
        `;

        btn.insertAdjacentElement('afterend', div);
    }
    function toggleExtra(checkbox, fieldIndex, productId) {
        const productLabel = checkbox.closest('p'); // Il <label> che contiene il checkbox
            console.log(productLabel);

        if (checkbox.checked) {
            // Crea il campo extra_price solo se non esiste già
            if (!productLabel.querySelector(`input[name="choice[${fieldIndex}][products][${productId}][extra_price]"]`)) {
                const priceInput = document.createElement("input");
                priceInput.type = "number";
                priceInput.step = "0.01";
                priceInput.placeholder = "+ €";
                priceInput.name = `choice[${fieldIndex}][products][${productId}][extra_price]`;
                priceInput.classList.add("extra-price");
                priceInput.style.marginLeft = "10px"; // Spaziatura per leggibilità

                checkbox.nextElementSibling.appendChild(priceInput); // Lo inserisce direttamente nel <p>
            }
        } else {
            // Rimuove il campo extra_price se il checkbox viene deselezionato
            const priceInput = productLabel.querySelector(`input[name="choice[${fieldIndex}][products][${productId}][extra_price]"]`);
            if (priceInput) {
                priceInput.remove();
            }
        }
    }

const switch_btn = document.querySelectorAll('.radio-choice');
switch_btn.forEach((btn) => {
    btn.addEventListener('click', function() {
        const value = this.value;
        const section_fix = document.querySelector('#section_fix');
        const section_combo = document.querySelector('#section_combo');
        switch (value) {
            case '0':
                section_fix.classList.remove('d-none');
                section_combo.classList.add('d-none');
                break;
            case '1':
                section_fix.classList.remove('d-none');
                section_combo.classList.add('d-none');
                break;
            case '2':
                section_fix.classList.add('d-none');
                section_combo.classList.remove('d-none');
                break;
        }
    });
});
//});
</script>


@endsection