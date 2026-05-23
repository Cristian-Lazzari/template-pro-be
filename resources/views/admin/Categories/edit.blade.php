@extends('layouts.base')

@section('contents')
    
@if (session('ingredient_success'))
    @php
        $data = session('ingredient_success')
    @endphp
    <div class="alert alert-success">
        {{ __('admin.catalog.created_flash', ['name' => $data['name_ing']]) }}
    </div>
@endif
    

 

<header class="menu-dashboard__hero order-detail__summary">
    <div class="order-detail__meta">
        <div class="order-detail__status">
            <span class="order-detail__status-icon order-detail__status-icon--warning">
                <i class="bi bi-grid-1x2-fill"></i>
            </span>
            <strong>{{ __('admin.Categorie') }}</strong>
        </div>
        <h1 class="menu-dashboard__title">{{ __('admin.Modifica_la_Categoria') }}</h1>
    </div>
    <div class="menu-dashboard__hero-actions dashboard-home__hero-actions">
        <a href="{{ route('admin.categories.index') }}" class="order-detail__contact">
            <i class="bi bi-arrow-left"></i>
            <span>{{ __('admin.Annulla') }}</span>
        </a>
    </div>
</header>

<form class="creation"  action="{{ route('admin.categories.update', $category) }}"  enctype="multipart/form-data"  method="POST">
    @method('PUT')
    @csrf
@php
    $list = $languages['languages'];
    $dfl = $languages['default'];
    $list =array_diff($list, [$dfl]);
@endphp
    <section class="base">
        <div class="split">
            <div>
                <label class="label_c" for="name"> 
                    <i class="bi bi-type"></i>
                      {{__('admin.Nome')}}
                </label>
                <p><input value="{{ old('name', $translations[$dfl]->name ?? '') }}" type="text" name="name" id="name" placeholder="{{ __('admin.catalog.insert_name_placeholder') }}"></p>
                @error('name') <p class="error">{{ $message }}</p> @enderror
            </div>
            
        </div>
        <p class="desc">
            <label class="label_c" for="description"> 
                <i class="bi bi-type"></i>
                  {{__('admin.Descrizione')}} 
            </label>
            <textarea name="description" id="description" cols="30" rows="10" >{{ old('description', $translations[$dfl]->description ?? '') }}</textarea>
            @error('description') <p class="error">{{ $message }}</p> @enderror
        </p>

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
                                    title="{{ __('admin.catalog.customize_translation') }}"
                                    eyebrow="{{ strtoupper($i) }}"
                                    tone="mint"
                                    description="{{ __('admin.categories.translation_description') }}"
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
                                        <input value="{{ $translations[$i]->name ?? '' }}" type="text" name="translations[{{$i}}][name]" id="translations[{{$i}}][name]" placeholder="{{ __('admin.catalog.insert_name_placeholder') }}">
                                    </div>

                                    <div class="dashboard-action-modal__field">
                                        <label class="label_c" for="translations[{{$i}}][description]">
                                            <i class="bi bi-body-text"></i> 
                                            {{__('admin.Descrizione')}}
                                        </label>
                                        <textarea name="translations[{{$i}}][description]" id="translations[{{$i}}][description]" cols="1" rows="4">{{$translations[$i]->description ?? '' }}</textarea>
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
       
    </section>

    <button class="my_btn_2 mb-5  w-75 m-auto" type="submit">{{ __('admin.Modifica_Categoria') }}</button>

</form>



@endsection
