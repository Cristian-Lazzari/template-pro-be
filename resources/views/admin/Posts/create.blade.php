@extends('layouts.base')

@section('contents')
    
    


 
<header class="menu-dashboard__hero order-detail__summary">
    <div class="order-detail__meta">
        <div class="order-detail__status">
                <span class="order-detail__status-icon order-detail__status-icon--active">
                    <i class="bi bi-images"></i>
                </span>
            <strong>{{ __('admin.content.contents') }}</strong>
        </div>
        <h1 class="menu-dashboard__title">{{ __('admin.Crea_nuovo_ps') }}</h1>
    </div>
    <div class="menu-dashboard__hero-actions dashboard-home__hero-actions">
        <a href="{{ route('admin.posts.index') }}" class="order-detail__contact">
            <i class="bi bi-arrow-left"></i>
            <span>{{ __('admin.Annulla') }}</span>
        </a>
    </div>
</header>

<form class="creation"  action="{{ route('admin.posts.store') }}"  enctype="multipart/form-data"  method="POST">
    @csrf
    <section class="base">

        <p class=" w-100">
            <label class="label_c" for="title">
                <i class="bi bi-type"></i>
                {{__('admin.Titolo')}}</label>
            <input class="w-100" value="{{ old('title') }}" type="text" name="title" id="title" placeholder="{{ __('admin.posts.title_placeholder') }}">
            @error('title') <p class="error">{{ $message }}</p> @enderror
        </p>

        <div class="split">    
            <div>
                <label class="label_c" for="file-input">
                    <i class="bi bi-file-earmark-image"></i>
                    {{__('admin.Immagine')}}</label>
                <p><input type="file" id="file-input" name="image" ></p>

            </div>
            <div>
                <label class="label_c" for="path">
                    <i class="bi bi-view-list"></i>
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

        <div class="w-100">
            <label class="label_c" for="images-input">
                <i class="bi bi-images"></i>
                {{__('admin.posts.gallery_label')}}</label>
            <p><input type="file" id="images-input" name="images[]" accept="image/*" multiple></p>
            <small>{{ __('admin.posts.gallery_hint') }}</small>
            @error('images.*') <p class="error">{{ $message }}</p> @enderror
        </div>

        <p class="desc">
            <label class="label_c" for="description">
                <i class="bi bi-body-text"></i>
                {{__('admin.Descrizione')}} 
                <button class="my_btn_4" type="button" data-bs-toggle="collapse" data-bs-target="#desc" aria-expanded="false" aria-controls="desc">
                    <i class="bi bi-info-circle-fill"></i>
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
                    <i class="bi bi-hash"></i>
                    {{ __('admin.Hashtag_') }}</label>
                <textarea name="hashtag" id="hashtag" cols="1" rows="2"  >{{ old('hashtag') }}</textarea>
                
            </div>
            <div>
                <label class="label_c" for="link">
                    <i class="bi bi-link-45deg"></i>
                    {{ __('admin.Link_IG_') }}</label>
                <p><input value="{{ old('link') }}" type="text" name="link" id="link" placeholder="{{ __('admin.posts.link_placeholder') }}"></p>
                @error('link') <p class="error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="label_c" for="link_label">
                    <i class="bi bi-tag"></i>
                    {{ __('admin.posts.link_label_label') }}</label>
                <p><input value="{{ old('link_label') }}" type="text" name="link_label" id="link_label" maxlength="60" placeholder="{{ __('admin.posts.link_label_placeholder') }}"></p>
                @error('link_label') <p class="error">{{ $message }}</p> @enderror
            </div>

        </div>
    </section>
    <p>* {{__('admin.Campi_facoltativi')}}</p>
    
    <button class="my_btn_2 mb-5  w-75 m-auto" type="submit">{{ __('admin.Crea_Post') }}</button>
    
</form>



@endsection
