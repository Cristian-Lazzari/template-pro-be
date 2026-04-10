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
    

 

<h1>{{ __('admin.Crea_nuova_Categoria') }}</h1>

<a class="my_btn_5 ml-auto my-3" href="{{ route('admin.categories.index') }}">{{__('admin.Annulla')}}</a>

<form class="creation"  action="{{ route('admin.categories.store') }}"  enctype="multipart/form-data"  method="POST">
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
        </div>
        <p class="desc"> 
            <label class="label_c" for="description"> 
                <i class="bi bi-type" style="font-size: 16px"></i>
                  {{__('admin.Descrizione')}} 
            </label>
            <textarea name="description" id="description" cols="30" rows="10" >{{ old('description') }}</textarea>
            @error('description') <p class="error">{{ $message }}</p> @enderror
        </p>
       
    </section>

    <button class="my_btn_2 mb-5  w-75 m-auto" type="submit">{{ __('admin.Crea_Categoria') }}</button>

</form>



@endsection