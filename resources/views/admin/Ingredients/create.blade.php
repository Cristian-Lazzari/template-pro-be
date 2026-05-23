@extends('layouts.base')

@section('contents')
    
@php
  //se impostato a true gli ordini vengono presi in base ai pezzi altrimenti in base al numero di ordini
$domain = 'https://future-plus.it/allergens/';
$moneyInputStep = \App\Support\Currency::inputStep();
 
@endphp

 

<header class="menu-dashboard__hero order-detail__summary">
    <div class="order-detail__meta">
        <div class="order-detail__status">
            <span class="order-detail__status-icon order-detail__status-icon--active">
                <i class="bi bi-basket2-fill"></i>
            </span>
            <strong>{{ __('admin.Ingredienti') }}</strong>
        </div>
        <h1 class="menu-dashboard__title">{{ __('admin.Crea_nuovo_i') }}</h1>
    </div>
    <div class="menu-dashboard__hero-actions dashboard-home__hero-actions">
        <a href="{{ route('admin.ingredients.index') }}" class="order-detail__contact">
            <i class="bi bi-arrow-left"></i>
            <span>{{ __('admin.Annulla') }}</span>
        </a>
    </div>
</header>

<form class="creation"  action="{{ route('admin.ingredients.store') }}"  enctype="multipart/form-data"  method="POST">
    @csrf

    <section class="more_i">
        
        <div class="split">
            <div>
                <label class="label_c" for="name_ing"> 
                    <i class="bi bi-type"></i>
                      {{__('admin.Nome')}}
                </label>
                <p><input value="{{ old('name_ing') }}" type="text" name="name_ing" id="name_ing" placeholder="{{ __('admin.catalog.insert_name_placeholder') }}"></p>
                @error('name_ing') <p class="error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="label_c" for="price_ing">
                    <i class="bi bi-123"></i>
                    {{__('admin.Prezzo')}}</label>
                <p><span>{{ $appCurrency['symbol'] }}</span><input value="{{ old('price_ing') }}" type="number" name="price_ing" step="{{ $moneyInputStep }}" id="price_ing" placeholder="{{ __('admin.products.price_placeholder') }}"></p>
                @error('price_ing') <p class="error">{{ $message }}</p> @enderror
            </div>
        </div>
        <div class="check_c">
            <label class="label_c" for="type">
                <i class="bi bi-ui-checks-grid"></i>
                {{ __('admin.ingredients.linked_categories') }}</label>
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
                    <i class="bi bi-file-earmark-image"></i> 
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
                <i class="bi bi-ui-checks-grid"></i>
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
