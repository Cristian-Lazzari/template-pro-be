@extends('layouts.base')

@section('contents')
    

    

 

<h1>{{__('admin.Crea_nuovo_a')}}</h1>

<a class="my_btn_5 ml-auto my-3" href="{{ route('admin.allergens.index') }}">{{__('admin.Annulla')}}</a>

<form class="creation"  action="{{ route('admin.allergens.store') }}"  enctype="multipart/form-data"  method="POST">
    @csrf

    <section class="base">
        <div class="split">
            <div>
                <label class="label_c" for="name"> 
                    <i class="bi bi-type" style="font-size: 16px"></i>
                      {{__('admin.Nome')}}
                </label>
                <p> <input value="{{ old('name') }}" type="text" name="name" id="name" placeholder="Inserisci il nome"> </p>
                @error('name') <p class="error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="label_c" for="special">
                    <i class="bi bi-view-list" style="font-size: 16px"></i>
                    {{__('admin.Tipo')}}</label>
                <p>
                    <select name="special" id="special">
                        <option value="0">{{ __('admin.Allergene_standard') }}</option>
                        <option value="1">{{ __('admin.Special_Flag') }}</option>
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

    <button class="my_btn_2 mb-5  w-75 m-auto" type="submit">{{ __('admin.Crea_Allergene') }}</button>

</form>



@endsection