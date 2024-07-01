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
    


<a class="btn btn-outline-light mb-5" href="{{ route('admin.posts.index') }}">Indietro</a>


<h1>Crea nuovo Post</h1>
<form class="creation"  action="{{ route('admin.posts.store') }}"  enctype="multipart/form-data"  method="POST">
    @csrf

    <section class="base">
        <div class="split">
            <div>
                <label class="label_c" for="title">Titolo</label>
                <p><input value="{{ old('title') }}" type="text" name="title" id="title" placeholder=" inserisci il titolo"></p>
                @error('title') <p class="error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="label_c" for="order">Precedenza</label>
                <p><input value="{{ old('order') }}" type="number" name="order" id="order" placeholder=" inserisci la precedenza "></p>
                @error('order') <p class="error">{{ $message }}</p> @enderror
            </div>
        </div>
        <div class="split">    
            <div>
                <label class="label_c" for="image">Immagine</label>
                <p><input  class="form-control" type="file" id="image" name="image" ></p>
                @error('image') <p class="error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="label_c" for="path">Pagina di destinazione</label>
                <p>
                    <select name="path" id="path">
                        <option @if( 1 == old('path')) selected @endif value="1">News</option>
                        <option @if( 2 == old('path')) selected @endif value="2">Storia</option>
                    </select>
                </p>
                @error('path') <p class="error">{{ $message }}</p> @enderror
            </div>        
        </div>
        <div class="split">
            <div class="desc">
                <label class="label_c" for="hashtag">Hashtag</label>
                <textarea name="hashtag" id="hashtag" cols="1" rows="3"  >{{ old('hashtag') }}</textarea>
                   
            </div>
            <div>
                <label class="label_c" for="link">Link</label>
                <p><input value="{{ old('link') }}" type="text" name="link" id="link" placeholder=" inserisci il link"></p>
                @error('link') <p class="error">{{ $message }}</p> @enderror
                <p>* se il post non ha link lascia il campo vuoto</p>
            </div>
            
        </div>
        <p class="desc"> 
            <label class="label_c" for="description">Descrizione</label>   
            <textarea name="description" id="description" cols="30" rows="10" >{{ old('description') }}</textarea>
            <p>per andare a capo riportare i caratteri: <strong>/**/</strong> , per mettere in grassetto del testo invece basta racchiudere la porzione di testo che si vuole mettere in grassetto con questi caratteri: <strong>***</strong> parola da mettere in grassetto <strong>***</strong> .  </p>
            @error('description') <p class="error">{{ $message }}</p> @enderror
        </p>
    </section>
   
    <button class="my_btn_1 mb-5  w-75 m-auto" type="submit">Crea Post</button>

</form>



@endsection