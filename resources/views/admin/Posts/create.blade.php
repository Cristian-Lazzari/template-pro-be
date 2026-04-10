@extends('layouts.base')

@section('contents')
    
    


 
<form class="creation"  action="{{ route('admin.posts.store') }}"  enctype="multipart/form-data"  method="POST">
    @csrf
    <a class="my_btn_5 ml-auto" href="{{ route('admin.posts.index') }}">{{__('admin.Annulla')}}</a>
    
    <h1 class="mb-5">{{__('admin.Crea_nuovo_ps')}}</h1>

    <section class="base">

        <p class=" w-100">
            <label class="label_c" for="title">
                <i class="bi bi-type" style="font-size: 16px"></i>
                {{__('admin.Titolo')}}</label>
            <input class="w-100" value="{{ old('title') }}" type="text" name="title" id="title" placeholder=" Inserisci il titolo">
            @error('title') <p class="error">{{ $message }}</p> @enderror
        </p>

        <div class="split">    
            <div>
                <label class="label_c" for="file-input">
                    <i class="bi bi-file-earmark-image" style="font-size: 16px"></i>
                    {{__('admin.Immagine')}}</label>
                <p><input type="file" id="file-input" name="image" ></p>
             
            </div>
            <div>
                <label class="label_c" for="path">
                    <i class="bi bi-view-list" style="font-size: 16px"></i>
                    {{__('admin.Pagina_di_destinazione')}}</label>
                <p>
                    <select name="path" id="path">
                        <option @if( 1 == old('path')) selected @endif value="1">{{ __('admin.News') }}</option>
                        <option @if( 2 == old('path')) selected @endif value="2">{{ __('admin.Storia') }}</option>
                    </select>
                </p>
                @error('path') <p class="error">{{ $message }}</p> @enderror
            </div>        
        </div>
           @error('image') <p class="error">{{ $message }}</p> @enderror
        <p class="desc"> 
            <label class="label_c" for="description">
                <i class="bi bi-body-text" style="font-size: 16px"></i>
                {{__('admin.Descrizione')}} 
                <button class="my_btn_4" type="button" data-bs-toggle="collapse" data-bs-target="#desc" aria-expanded="false" aria-controls="desc">
                    <i class="bi bi-info-circle-fill" style="font-size: 16px"></i>
                </button>
            </label>
            <div class="collapse" id="desc">
                <p>
                    {{__('admin.post_info')}}
                </p>
            </div>
            <textarea name="description" id="description" cols="30" rows="10" >{{ old('description') }}</textarea>
            @error('description') <p class="error">{{ $message }}</p> @enderror
        </p>
        <div class="split">
            <div class="desc">
                <label class="label_c" for="hashtag">
                    <i class="bi bi-hash" style="font-size: 16px"></i>
                    Hashtag *</label>
                <textarea name="hashtag" id="hashtag" cols="1" rows="2"  >{{ old('hashtag') }}</textarea>
                
            </div>
            <div>
                <label class="label_c" for="link">
                    <i class="bi bi-link-45deg" style="font-size: 16px"></i>                      
                    Link IG *</label>
                <p><input value="{{ old('link') }}" type="text" name="link" id="link" placeholder=" Inserisci il link"></p>
                @error('link') <p class="error">{{ $message }}</p> @enderror
            </div>
            
        </div>
    </section>
    <p>* {{__('admin.Campi_facoltativi')}}</p>
    
    <button class="my_btn_2 mb-5  w-75 m-auto" type="submit">{{ __('admin.Crea_Post') }}</button>
    
</form>



@endsection