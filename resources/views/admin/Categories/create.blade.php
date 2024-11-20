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
    

 

<h1>Crea nuova Categoria</h1>

<a class="my_btn_5 ml-auto my-3" href="{{ route('admin.categories.index') }}">Torna alle categorie</a>

<form class="creation"  action="{{ route('admin.categories.store') }}"  enctype="multipart/form-data"  method="POST">
    @csrf

    <section class="base">
        <div class="split">
            <div>
                <label class="label_c" for="name">Nome</label>
                <p> <input value="{{ old('name') }}" type="text" name="name" id="name" placeholder=" inserisci il nome"> </p>
                @error('name') <p class="error">{{ $message }}</p> @enderror
            </div>
        </div>
       
    </section>

    <button class="my_btn_2 mb-5  w-75 m-auto" type="submit">Crea Categoria</button>

</form>



@endsection