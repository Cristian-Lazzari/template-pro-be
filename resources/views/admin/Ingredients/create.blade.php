@extends('layouts.base')

@section('contents')
    
@php
  //se impostato a true gli ordini vengono presi in base ai pezzi altrimenti in base al numero di ordini
$domain = 'https://future-plus.it/allergens/';
 
@endphp

 

<h1>{{__('admin.Crea_nuovo_i')}}</h1>
<form class="creation"  action="{{ route('admin.ingredients.store') }}"  enctype="multipart/form-data"  method="POST">
    @csrf

    <section class="more_i">
        
        <div class="split">
            <div>
                <label class="label_c" for="name_ing"> 
                    <i class="bi bi-type" style="font-size: 16px"></i>
                      {{__('admin.Nome')}}
                </label>
                <p><input value="{{ old('name_ing') }}" type="text" name="name_ing" id="name_ing" placeholder=" Inserisci il nome"></p>
                @error('name_ing') <p class="error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="label_c" for="price_ing">
                    <i class="bi bi-123" style="font-size: 16px"></i>
                    {{__('admin.Prezzo')}}</label>
                <p>€<input value="{{ old('price_ing') }}" type="number" name="price_ing" step="0.01" id="price_ing" placeholder=" Inserisci il prezzo "></p>
                @error('price_ing') <p class="error">{{ $message }}</p> @enderror
            </div>
        </div>
        <div class="check_c">
            <label class="label_c" for="type">
                <i class="bi bi-ui-checks-grid" style="font-size: 16px"></i>
                Categorie abbinate</label>
            <p>
                
                @foreach ($categories as $c)
                
                <input type="checkbox" class="btn-check" id="a{{ $c->id }}" name="type_ing[]" value="{{ $c->id }}" @if (in_array($c->id, old('type_ing', []))) checked @endif>
                <label class="btn btn-outline-light" for="a{{ $c->id }}">{{ $c['name'] }}</label>
                @endforeach
                
            </p>
            @error('type_ing') <p class="error">{{ $message }}</p> @enderror
        </div>
        <div class="split"> 
            <div>
                <label class="label_c" for="file-input"> 
                    <i class="bi bi-file-earmark-image" style="font-size: 16px"></i> 
                    {{__('admin.Immagine')}}</label>
                <p><input type="file" id="file-input" name="image_ing" ></p>
                @error('image_ing') <p class="error">{{ $message }}</p> @enderror    
            </div> 
            <div class="m-auto">
                <input type="checkbox" class="btn-check" id="option_ing" name="option_ing" value="1" @if (old('option_ing', [])) checked @endif>
                <label class="btn btn-outline-light" for="option_ing">{{ __('admin.questo_ingrediente__un_opzione') }}</label>
            </div>
        </div>
        
        <div class="check_c">
            <label class="label_c" for="type">
                <i class="bi bi-ui-checks-grid" style="font-size: 16px"></i>
                {{__('admin.Allergeni')}}</label>
            <p>
                @foreach( $allergens as $a)
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
            
    </section>
    <button class="my_btn_2 mb-5  w-75 m-auto" type="submit">{{ __('admin.Crea_Ingrediente') }}</button>

</form>



@endsection