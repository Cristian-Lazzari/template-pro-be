@extends('layouts.base')



@section('contents')


@if (session('created'))
    @php
        $data = session('created')
    @endphp
    <div class="alert alert-success">
        "{{ $data['name_ing'] }}" è stato correttamente creato!
    </div>
@endif
@if (session('edited'))
    @php
        $data = session('edited')
    @endphp
    <div class="alert alert-success">
        "{{ $data['name_ing'] }}" è stato modificato creato!
    </div>
@endif
@if (session('delete_success'))
    @php
        $data = session('delete_success')
    @endphp
    <div class="alert alert-danger">
        {{ $data }}
    </div>
@endif
 


<div class="dash_page catalog-index-page">
    <h1><i class="bi bi-exclamation-diamond-fill"></i>{{__('admin.Allergeni')}}</h1>
    <div class="action-page catalog-index-toolbar">
        <div class="catalog-toolbar-search">
            <i class="bi bi-search catalog-toolbar-search__icon"></i>
            <input
                class="catalog-toolbar-search__input"
                type="search"
                placeholder="{{ __('admin.Cerca_per_nome') }}"
                aria-label="{{ __('admin.Cerca_per_nome') }}"
                autocomplete="off"
                data-catalog-search
                data-catalog-empty="#allergensSearchEmpty"
            >
        </div>
        <a class="catalog-action-btn catalog-action-btn--primary catalog-action-btn--with-label" href="{{ route('admin.allergens.create') }}">
            <i class="bi bi-cloud-plus-fill"></i>
            {{__('admin.Crea_nuovo')}}
        </a>
    </div>
    <div id="allergensSearchEmpty" class="catalog-search-empty d-none" role="status">
        {{ __('admin.Nessun_risultato_ricerca') }}
    </div>

    <div class="catalog-index-list">
        @foreach ($allergens as $item)

            <div class="catalog-index-card" data-search-name="{{ mb_strtolower($item->name) }}">
                <div class="catalog-index-card__main">
                    <div class="catalog-index-card__media" aria-hidden="true">
                        @if ($item->img && str_starts_with($item->img, 'http'))
                            <img src="{{$item->img}}" alt="{{$item->name}}">
                        @elseif ($item->img)
                            <img src="{{ asset('public/storage/' . $item->img) }}" alt="{{$item->name}}">
                        @else
                            <i class="bi bi-exclamation-diamond-fill"></i>
                        @endif
                    </div>
                    <div class="catalog-index-card__content">
                        <h3 class="catalog-index-card__title">{{$item->name}}</h3>
                        @if ($item->special > 0)
                            <div class="catalog-index-card__meta">
                                <span class="catalog-index-badge">{{ __('admin.Special_Flag') }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            
                <div class="catalog-index-card__actions">
                    <a class="catalog-action-btn catalog-action-btn--primary" href="{{ route('admin.allergens.edit', $item) }}" aria-label="{{ __('admin.Modifica') }} {{$item->name}}" title="{{ __('admin.Modifica') }}">
                        <i class="bi bi-pencil-square"></i>
                    </a> 
                    <button type="button" class="catalog-action-btn catalog-action-btn--danger" data-bs-toggle="modal" data-bs-target="#staticBackdrop{{$item->id}}" aria-label="{{ __('admin.Elimina') }} {{$item->name}}" title="{{ __('admin.Elimina') }}">
                        <i class="bi bi-trash3"></i>
                    </button>              
                </div>
            </div>

            <!-- Modal ELINIMAZIONE -->
            <div class="modal fade" id="staticBackdrop{{$item->id}}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel{{$item->id}}" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered my_modal_dialog">
                    <form action="{{ route('admin.allergens.destroy', ['allergen'=>$item]) }}" method="post" class="w-100">
                        @method('delete')
                        @csrf
                        <x-dashboard.action-modal
                            title-id="staticBackdropLabel{{$item->id}}"
                            title="{{ __('admin.Conferma_eliminazione') }}"
                            eyebrow="{{ __('admin.Elimina') }}"
                            tone="danger"
                            :subject="$item->name"
                            description="{{ __('admin.Eiminando_questo_allergene_i_prodotti_ad_esso_abbianati_non_lo_mostreranno_pi') }}"
                        >
                            <p class="dashboard-action-modal__hint">{{ __('admin.Eiminando_questo_allergene_i_prodotti_ad_esso_abbianati_non_lo_mostreranno_pi') }}</p>

                            <x-slot name="footer">
                                <button class="catalog-action-btn catalog-action-btn--danger catalog-action-btn--with-label" type="submit">
                                    <i class="bi bi-trash3"></i>
                                    {{ __('admin.Elimina') }}
                                </button>
                            </x-slot>
                        </x-dashboard.action-modal>
                    </form>
                </div>
            </div>

        @endforeach
    </div>

</div>

@include('admin.includes.catalog-search-script')

@endsection
