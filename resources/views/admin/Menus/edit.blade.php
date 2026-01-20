@extends('layouts.base')

@section('contents')
    

 
<form class="creation"  action="{{ route('admin.menus.update', $menu) }}"  enctype="multipart/form-data"  method="POST">
    @csrf
    @method('PUT')
    <a class="my_btn_5 ml-auto" href="{{ route('admin.menus.index') }}">Torna ai Menu</a>
    
    <h1>Modifica il Menu</h1>
    
    <section class="base">
        <div class="split">
            <div>
                <label class="label_c" for="name"> 
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-type" viewBox="0 0 16 16">
                        <path d="m2.244 13.081.943-2.803H6.66l.944 2.803H8.86L5.54 3.75H4.322L1 13.081zm2.7-7.923L6.34 9.314H3.51l1.4-4.156zm9.146 7.027h.035v.896h1.128V8.125c0-1.51-1.114-2.345-2.646-2.345-1.736 0-2.59.916-2.666 2.174h1.108c.068-.718.595-1.19 1.517-1.19.971 0 1.518.52 1.518 1.464v.731H12.19c-1.647.007-2.522.8-2.522 2.058 0 1.319.957 2.18 2.345 2.18 1.06 0 1.716-.43 2.078-1.011zm-1.763.035c-.752 0-1.456-.397-1.456-1.244 0-.65.424-1.115 1.408-1.115h1.805v.834c0 .896-.752 1.525-1.757 1.525"/>
                      </svg>
                      Nome
                </label>
                <p><input value="{{ old('name', $menu->name) }}"  type="text" name="name" id="name" placeholder=" Inserisci il nome"></p>
                @error('name') <p class="error">{{ $message }}</p> @enderror
            </div>
            <div class="price_input">
                <label class="label_c" for="price">   
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-123" viewBox="0 0 16 16">
                    <path d="M2.873 11.297V4.142H1.699L0 5.379v1.137l1.64-1.18h.06v5.961zm3.213-5.09v-.063c0-.618.44-1.169 1.196-1.169.676 0 1.174.44 1.174 1.106 0 .624-.42 1.101-.807 1.526L4.99 10.553v.744h4.78v-.99H6.643v-.069L8.41 8.252c.65-.724 1.237-1.332 1.237-2.27C9.646 4.849 8.723 4 7.308 4c-1.573 0-2.36 1.064-2.36 2.15v.057zm6.559 1.883h.786c.823 0 1.374.481 1.379 1.179.01.707-.55 1.216-1.421 1.21-.77-.005-1.326-.419-1.379-.953h-1.095c.042 1.053.938 1.918 2.464 1.918 1.478 0 2.642-.839 2.62-2.144-.02-1.143-.922-1.651-1.551-1.714v-.063c.535-.09 1.347-.66 1.326-1.678-.026-1.053-.933-1.855-2.359-1.845-1.5.005-2.317.88-2.348 1.898h1.116c.032-.498.498-.944 1.206-.944.703 0 1.206.435 1.206 1.07.005.64-.504 1.106-1.2 1.106h-.75z"/>
                    </svg>
                    Prezzo</label>
                <p><input value="{{old('price', $menu->price) / 100 }}" step="0.01" type="number" name="price" id="price" placeholder=" Inserisci il prezzo "><span>€</span></p>
                @error('price') <p class="error">{{ $message }}</p> @enderror
            </div>
        </div>
        <div class="split">
            <div class="mr-auto">
                <label class="label_c" for="promo">Menu in evidenza</label>
                <label class="container_star">
                    <input name="promo" type="checkbox" @if (old('promo', $menu->promo))  checked  @endif>
                    <svg height="24px" id="promo" version="1.2" viewBox="0 0 24 24" width="24px" xml:space="preserve" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><g><g><path d="M9.362,9.158c0,0-3.16,0.35-5.268,0.584c-0.19,0.023-0.358,0.15-0.421,0.343s0,0.394,0.14,0.521    c1.566,1.429,3.919,3.569,3.919,3.569c-0.002,0-0.646,3.113-1.074,5.19c-0.036,0.188,0.032,0.387,0.196,0.506    c0.163,0.119,0.373,0.121,0.538,0.028c1.844-1.048,4.606-2.624,4.606-2.624s2.763,1.576,4.604,2.625    c0.168,0.092,0.378,0.09,0.541-0.029c0.164-0.119,0.232-0.318,0.195-0.505c-0.428-2.078-1.071-5.191-1.071-5.191    s2.353-2.14,3.919-3.566c0.14-0.131,0.202-0.332,0.14-0.524s-0.23-0.319-0.42-0.341c-2.108-0.236-5.269-0.586-5.269-0.586    s-1.31-2.898-2.183-4.83c-0.082-0.173-0.254-0.294-0.456-0.294s-0.375,0.122-0.453,0.294C10.671,6.26,9.362,9.158,9.362,9.158z"></path></g></g></svg>
                </label>
            </div>
            <div class="price_input">
                <label class="label_c" for="old_price">   
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-123" viewBox="0 0 16 16">
                    <path d="M2.873 11.297V4.142H1.699L0 5.379v1.137l1.64-1.18h.06v5.961zm3.213-5.09v-.063c0-.618.44-1.169 1.196-1.169.676 0 1.174.44 1.174 1.106 0 .624-.42 1.101-.807 1.526L4.99 10.553v.744h4.78v-.99H6.643v-.069L8.41 8.252c.65-.724 1.237-1.332 1.237-2.27C9.646 4.849 8.723 4 7.308 4c-1.573 0-2.36 1.064-2.36 2.15v.057zm6.559 1.883h.786c.823 0 1.374.481 1.379 1.179.01.707-.55 1.216-1.421 1.21-.77-.005-1.326-.419-1.379-.953h-1.095c.042 1.053.938 1.918 2.464 1.918 1.478 0 2.642-.839 2.62-2.144-.02-1.143-.922-1.651-1.551-1.714v-.063c.535-.09 1.347-.66 1.326-1.678-.026-1.053-.933-1.855-2.359-1.845-1.5.005-2.317.88-2.348 1.898h1.116c.032-.498.498-.944 1.206-.944.703 0 1.206.435 1.206 1.07.005.64-.504 1.106-1.2 1.106h-.75z"/>
                    </svg>
                    Prezzo Barrato</label>
                <p><span>€</span><input value="{{old('old_price', $menu->old_price / 100 )}}" type="number" name="old_price" id="old_price" step="0.01" placeholder=" Inserisci il prezzo "></p>
                @error('old_price') <p class="error">{{ $message }}</p> @enderror
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
                    @if (isset($menu->image))
                        <input type="checkbox" class="btn-check" id="b2" name="img_off">
                        <label class="btn btn-outline-danger" for="b2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash-fill" viewBox="0 0 16 16">
                                <path d="M2.5 1a1 1 0 0 0-1 1v1a1 1 0 0 0 1 1H3v9a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V4h.5a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H10a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1zm3 4a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 .5-.5M8 5a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7A.5.5 0 0 1 8 5m3 .5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 1 0"/>
                            </svg>
                        </label>
                        <img class="" src="{{ asset('public/storage/' . $menu->image) }}" alt="{{$menu->name }}">
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
                            <option @if( $category->id == old('category_id', $menu->category->id)) selected @endif value="{{ $category->id }}">{{ $category->name }}</option>
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
            <textarea name="description" id="description" cols="1" rows="3" >{{ old('description', $menu->description) }}  </textarea>
        </p>
    </section>

    <section>
        <h2>Tipo di Menu</h2>
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
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-ui-checks-grid" viewBox="0 0 16 16">
                <path d="M2 10h3a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1m9-9h3a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-3a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1m0 9a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h3a1 1 0 0 0 1-1v-3a1 1 0 0 0-1-1zm0-10a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h3a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2zM2 9a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h3a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2zm7 2a2 2 0 0 1 2-2h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2h-3a2 2 0 0 1-2-2zM0 2a2 2 0 0 1 2-2h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm5.354.854a.5.5 0 1 0-.708-.708L3 3.793l-.646-.647a.5.5 0 1 0-.708.708l1 1a.5.5 0 0 0 .708 0z"/>
            </svg>
            Abbina Prodotti</h2>
        <div class="check_c">
            
                @foreach($products as $c)
                    <div class="prod_cont">
                        <h3>{{$c->name}}</h3>
                        @foreach ($c->product as $p)
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
        <h2>Imposta le scelte che puo fare il cliente</h2>
        <button class="my_btn_3 m-auto" id="btn-aggiungi" type="button" onclick="aggiungiCampo()">Crea nuova scelta</button>
        <div id="campi-wrapper"></div>
    </section>
    <button class="my_btn_2 mb-5  w-75 m-auto" type="submit">Modifica Menu</button>

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
                <button type="button" class="btn btn-danger" onclick="this.closest('.campo-opzione').remove(); number_field--">Rimuovi</button>
            </div>
            <div class="check_c small"> 
                ${categories.map(cat => `
                    <h3>${cat.name}</h3>
                    <p class="prod_cont">
                    ${cat.product.map(prod => `
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