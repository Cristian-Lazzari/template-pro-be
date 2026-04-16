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
    

 

<h1>{{ __('admin.Modifica_la_Categoria') }}</h1>

<a class="my_btn_5 ml-auto my-3" href="{{ route('admin.categories.index') }}">{{__('admin.Annulla')}}</a>

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
                <p><input value="{{ old('name', $translations[$dfl]->name ?? '') }}" type="text" name="name" id="name" placeholder=" Inserisci il nome"></p>
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
                                    title="Personalizza traduzione"
                                    eyebrow="{{ strtoupper($i) }}"
                                    tone="mint"
                                    description="Aggiorna nome e descrizione della categoria nella lingua selezionata."
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
                                        <input value="{{ $translations[$i]->name ?? '' }}" type="text" name="translations[{{$i}}][name]" id="translations[{{$i}}][name]" placeholder=" Inserisci il nome">
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
