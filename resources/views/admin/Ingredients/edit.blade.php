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
            <span class="order-detail__status-icon order-detail__status-icon--warning">
                <i class="bi bi-basket2-fill"></i>
            </span>
            <strong>{{ __('admin.Ingredienti') }}</strong>
        </div>
        <h1 class="menu-dashboard__title">{{ __('admin.Modifica_ingrediente') }}</h1>
    </div>
    <div class="menu-dashboard__hero-actions dashboard-home__hero-actions">
        <a href="{{ route('admin.ingredients.index') }}" class="order-detail__contact">
            <i class="bi bi-arrow-left"></i>
            <span>{{ __('admin.Annulla') }}</span>
        </a>
    </div>
</header>

<form class="creation"  action="{{ route('admin.ingredients.update' , $ingredient) }}"  enctype="multipart/form-data"  method="POST">
    @method('PUT')
    @csrf
@php
    $list = $languages['languages'];
    $dfl = $languages['default'];
    $list =array_diff($list, [$dfl]);
@endphp
    <section class="more_i">
        
        <div class="split">
            <div>
                <label class="label_c" for="name_ing"> 
                    <i class="bi bi-type"></i>
                      {{__('admin.Nome')}}
                </label>
                <p><input value="{{ old('name', $translations[$dfl]->name ?? '') }}" type="text" name="name_ing" id="name_ing" placeholder=" Inserisci il nome"></p>
                @error('name_ing') <p class="error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="label_c" for="price_ing">
                    <i class="bi bi-123"></i>
                    {{__('admin.Prezzo')}}</label>
                <p><span>{{ $appCurrency['symbol'] }}</span><input value="{{ old('price_ing', \App\Support\Currency::formatForInput($ingredient->price)) }}" type="number" name="price_ing" step="{{ $moneyInputStep }}" id="price_ing" placeholder=" Inserisci il prezzo "></p>
                @error('price_ing') <p class="error">{{ $message }}</p> @enderror
            </div>
        </div>
        <div class="check_c">
            <label class="label_c" for="type">
                <i class="bi bi-ui-checks-grid"></i>
                Categorie abbinate</label>
            <p>
                @php
                    if ($ingredient->type !== NULL && $ingredient->type !== '"[]"') {
                        $t = json_decode($ingredient->type, true);
                    }else{
                        $t = [];
                    }
                @endphp
                
                @foreach ($categories as $c)
                
                <input type="checkbox" class="btn-check" id="a{{ $c->id }}" name="type_ing[]" value="{{ $c->id }}" @if (in_array($c->id, $t)) checked @endif>
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
                <input type="checkbox" class="btn-check" id="option_ing" name="option_ing" value="1" @if (old('option_ing', $ingredient->option )) checked @endif>
                <label class="btn btn-outline-light" for="option_ing">{{ __('admin.questo_ingrediente__un_opzione') }}</label>
            </div>
        </div>
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
                                    description="Aggiorna il nome dell ingrediente nella lingua selezionata."
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

                                    <x-slot name="footer">
                                        <button class="my_btn_2" type="submit">{{ __('admin.Modifica_Traduzione') }}</button>
                                    </x-slot>
                                </x-dashboard.action-modal>
                            </div>
                        </div>
                    @endforeach
                </div>
        </section>
       
        <div class="check_c">
            <label class="label_c" for="type">
                <i class="bi bi-ui-checks-grid"></i>
                {{__('admin.Allergeni')}}</label>
            <p>
                @foreach( $allergens as $a)
                    <input type="checkbox" class="btn-check" id="b{{ $a->id }}" name="allergens_ing[]" value="{{ $a->id }}" @if (in_array($a->id, old('allergens_ing', $ingredient->allergens ? $ingredient->allergens->pluck('id')->toArray() : [], []))) checked @endif>
                    <label class="btn 
                        @if($a->special != 0)
                        btn-outline-dark btn_special
                        @else
                        btn-outline-light
                        @endif
                        " for="b{{ $a->id }}">{{ $a->name }}</label>
                @endforeach
            </p>
        </div>
            
    </section>
    <button class="my_btn_2 mb-5  w-75 m-auto" type="submit">{{ __('admin.Modifica_ingrediente') }}</button>

</form>



@endsection
