@extends('layouts.base')

@section('contents')
    


<div class="dash_page">


    <h1>Modifica  {{$allergen->special == 0 ? 'l\'Allergene' : 'lo Special Flag'}} </h1>

    <a class="my_btn_5 ml-auto my-3" href="{{ route('admin.allergens.index') }}">{{__('admin.Annulla')}}</a>

    <form class="creation"  action="{{ route('admin.allergens.update', $allergen) }}"  enctype="multipart/form-data"  method="POST">
        @method('PUT')
        @csrf

        <section class="base">
            <div class="split">
                <div>
                    <label class="label_c" for="name"> 
                        <i class="bi bi-type" style="font-size: 16px"></i>
                        {{__('admin.Nome')}}
                    </label>
                    <p><input value="{{ old('name', $allergen->name) }}" type="text" name="name" id="name" placeholder=" Inserisci il nome"></p>
                    @error('name') <p class="error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="label_c" for="special">
                        <i class="bi bi-view-list" style="font-size: 16px"></i>
                        {{__('admin.Tipo')}}</label>
                    <p>
                        <select name="special" id="special">
                            @if ($allergen->id === 1)
                                <option selected value="4">{{ __('admin.Special_Flag_') }}</option>
                            @else
                                <option @if( $allergen->special == 0) selected @endif value="0">{{ __('admin.Allergene_standard') }}</option>
                                <option @if( $allergen->special > 0) selected @endif value="1">{{ __('admin.Special_Flag') }}</option>
                            @endif
                        </select>
                    </p>
                </div>
            </div>
            <div class="split">
                <div>
                    <label class="label_c" for="file-input">
                        <i class="bi bi-file-earmark-image" style="font-size: 16px"></i>
                    {{__('admin.Immagine')}}</label>
                    <p class="img-cont">
                        <input type="file" id="file-input" name="image" >
                        @if (isset($allergen->img))
                            <input type="checkbox" class="btn-check" id="b2" name="img_off">
                            <label class="btn btn-outline-danger" for="b2">
                                <i class="bi bi-trash-fill" style="font-size: 16px"></i>
                            </label>
                            <img class="" src="{{ asset('public/storage/' . $allergen->img) }}" alt="{{$allergen->name }}">
                        @endif 
                    </p>
                    @error('img') <p class="error">{{ $message }}</p> @enderror
                </div>
            </div>

        
        </section>

        <button class="my_btn_2 mb-5  w-75 m-auto" type="submit">{{ __('admin.Modifica_Allergene') }}</button>

    </form>

</div>

@endsection