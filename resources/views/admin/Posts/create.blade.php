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
    


<button onclick="history.back()" class="btn btn-outline-light my-5">
    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-90deg-left" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M1.146 4.854a.5.5 0 0 1 0-.708l4-4a.5.5 0 1 1 .708.708L2.707 4H12.5A2.5 2.5 0 0 1 15 6.5v8a.5.5 0 0 1-1 0v-8A1.5 1.5 0 0 0 12.5 5H2.707l3.147 3.146a.5.5 0 1 1-.708.708l-4-4z"/></svg>
</button>
<a class="my_btn_5 ml-auto" href="{{ route('admin.posts.index') }}">Torna ai Post</a>

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
                <label class="label_c" for="order">Precedenza *1</label>
                <p><input value="{{ old('order') }}" type="number" name="order" id="order" placeholder=" inserisci la precedenza "></p>
                @error('order') <p class="error">{{ $message }}</p> @enderror
            </div>
        </div>
        <div class="split">    
            <div>
                <label class="label_c" for="file-input">Immagine</label>
                <p><input type="file" id="file-input" name="image" ></p>
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
        <p class="desc"> 
            <label class="label_c" for="description">Descrizione *2</label>   
            <textarea name="description" id="description" cols="30" rows="10" >{{ old('description') }}</textarea>
            @error('description') <p class="error">{{ $message }}</p> @enderror
        </p>
        <div class="split">
            <div class="desc">
                <label class="label_c" for="hashtag">Hashtag *3</label>
                <textarea name="hashtag" id="hashtag" cols="1" rows="3"  >{{ old('hashtag') }}</textarea>
                
            </div>
            <div>
                <label class="label_c" for="link">Link *3</label>
                <p><input value="{{ old('link') }}" type="text" name="link" id="link" placeholder=" inserisci il link"></p>
                @error('link') <p class="error">{{ $message }}</p> @enderror
            </div>
            
        </div>
    </section>
    <p>*1 il post con la precedenza più alta verra visualizzato per primo</p>
    <p>*2 per andare a capo riportare i caratteri: <strong>/**/</strong> , per mettere in grassetto del testo invece basta racchiudere la porzione di testo che si vuole mettere in grassetto tra 3 asterischi in questo modo: <strong>***</strong> parola da mettere in grassetto <strong>***</strong> .  </p>
    <p>*3 campi facoltativi</p>
    
    <button class="my_btn_2 mb-5  w-75 m-auto" type="submit">Crea Post</button>
    
</form>



@endsection