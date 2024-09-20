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

<h1>Modifica il Post</h1>
<form class="creation"  action="{{ route('admin.posts.update' , $post) }}"  enctype="multipart/form-data"  method="POST">
    @csrf
    @method('PUT')

    <section class="base">
        <div class="m-auto w-auto">
            <label class="label_c" for="promo">Post in evidenza</label>
            <label class="container_star">
                <input name="promo" type="checkbox" @if (old('promo', $post->promo))  checked  @endif>
                <svg height="24px" id="promo" version="1.2" viewBox="0 0 24 24" width="24px" xml:space="preserve" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><g><g><path d="M9.362,9.158c0,0-3.16,0.35-5.268,0.584c-0.19,0.023-0.358,0.15-0.421,0.343s0,0.394,0.14,0.521    c1.566,1.429,3.919,3.569,3.919,3.569c-0.002,0-0.646,3.113-1.074,5.19c-0.036,0.188,0.032,0.387,0.196,0.506    c0.163,0.119,0.373,0.121,0.538,0.028c1.844-1.048,4.606-2.624,4.606-2.624s2.763,1.576,4.604,2.625    c0.168,0.092,0.378,0.09,0.541-0.029c0.164-0.119,0.232-0.318,0.195-0.505c-0.428-2.078-1.071-5.191-1.071-5.191    s2.353-2.14,3.919-3.566c0.14-0.131,0.202-0.332,0.14-0.524s-0.23-0.319-0.42-0.341c-2.108-0.236-5.269-0.586-5.269-0.586    s-1.31-2.898-2.183-4.83c-0.082-0.173-0.254-0.294-0.456-0.294s-0.375,0.122-0.453,0.294C10.671,6.26,9.362,9.158,9.362,9.158z"></path></g></g></svg>
            </label>
        </div>
        <div class="split">
            <div>
                <label class="label_c" for="title">Titolo</label>
                <p><input value="{{ old('title', $post->title) }}" type="text" name="title" id="title" placeholder=" inserisci il titolo"></p>
                @error('title') <p class="error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="label_c" for="order">Precedenza *1</label>
                <p><input value="{{ old('order', $post->order) }}" type="number" name="order" id="order" placeholder=" inserisci la precedenza "></p>
                @error('order') <p class="error">{{ $message }}</p> @enderror
            </div>
        </div>
        <div class="split">    
            <div>
                <label class="label_c" for="file-input">Immagine</label>
                <p><input   type="file" id="file-input" name="image" ></p>
                @error('image') <p class="error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="label_c" for="path">Pagina di destinazione</label>
                <p>
                    <select name="path" id="path">
                        <option @if( 1 == old('path', $post->path)) selected @endif value="1">News</option>
                        <option @if( 2 == old('path', $post->path)) selected @endif value="2">Storia</option>
                    </select>
                </p>
                @error('path') <p class="error">{{ $message }}</p> @enderror
            </div>       
        </div>
        <p class="desc"> 
            <label class="label_c" for="description">Descrizione *2</label>   
            <textarea name="description" id="description" cols="30" rows="10" >{{ old('description', $post->description) }}</textarea>
            @error('description') <p class="error">{{ $message }}</p> @enderror
        <div class="split">
            <div class="desc">
                <label class="label_c" for="hashtag">Hashtag *3</label>
                <textarea name="hashtag" id="hashtag" cols="1" rows="3"  >{{ old('hashtag', $post->hashtag) }}</textarea>
                   
            </div>
            <div>
                <label class="label_c" for="link">Link *3</label>
                <p><input value="{{ old('link', $post->link) }}" type="text" name="link" id="link" placeholder=" inserisci il link"></p>
                @error('link') <p class="error">{{ $message }}</p> @enderror
            </div>     
            </p>
        </div>
    </section>
    <p>*1 il post con la precedenza più alta verra visualizzato per primo</p>
    <p>*2 per andare a capo riportare i caratteri: <strong>/**/</strong> , per mettere in grassetto del testo invece basta racchiudere la porzione di testo che si vuole mettere in grassetto tra 3 asterischi in questo modo: <strong>***</strong> parola da mettere in grassetto <strong>***</strong> .  </p>
    <p>*3 campi facoltativi</p>
   
    <button class="my_btn_2 mb-5  w-75 m-auto" type="submit">Modificas Post</button>

</form>



@endsection