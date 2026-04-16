@extends('layouts.base')



@section('contents')

@if (session('category_success'))
    @php
        $data = session('category_success')
    @endphp
    <div class="alert alert-primary">
        {{ $data }}
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
    <h1><i class="bi bi-grid-1x2-fill"></i>{{__('admin.Categorie_prodotti')}}</h1>
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
                data-catalog-empty="#categoriesSearchEmpty"
            >
        </div>
        <a class="catalog-action-btn catalog-action-btn--primary catalog-action-btn--with-label" href="{{ route('admin.categories.create') }}">
            <i class="bi bi-cloud-plus-fill"></i>
            {{__('admin.Crea_nuova')}}
        </a>
        <button type="button" class="catalog-action-btn catalog-action-btn--accent catalog-action-btn--with-label" data-bs-toggle="modal" data-bs-target="#staticBackdropspecial">
            <i class="bi bi-shuffle"></i>
            {{__('admin.Modifica_ordine')}}
        </button>
    </div>
    <div id="categoriesSearchEmpty" class="catalog-search-empty d-none" role="status">
        {{ __('admin.Nessun_risultato_ricerca') }}
    </div>

    <div class="catalog-index-list">
        @foreach ($categories as $item)

            <div class="catalog-index-card {{ $item->id === 1 ? 'd-none' : '' }}" data-search-name="{{ mb_strtolower($item->name) }}">
                <div class="catalog-index-card__main">
                    <div class="catalog-index-card__media" aria-hidden="true">
                        <i class="bi bi-grid-1x2-fill"></i>
                    </div>
                    <div class="catalog-index-card__content">
                        <h3 class="catalog-index-card__title">{{$item->name}}</h3>
                    </div>
                </div>
            
                @if ($item->id !== 1)
                <div class="catalog-index-card__actions">
                    <a class="catalog-action-btn catalog-action-btn--primary" href="{{ route('admin.categories.edit', $item) }}" aria-label="{{ __('admin.Modifica') }} {{$item->name}}" title="{{ __('admin.Modifica') }}">
                        <i class="bi bi-pencil-square"></i>
                    </a> 
                    <button type="button" class="catalog-action-btn catalog-action-btn--accent" data-bs-toggle="modal" data-bs-target="#staticBackdropUp{{$item->id}}" aria-label="{{ __('admin.Modifica_ordine') }} {{$item->name}}" title="{{ __('admin.Modifica_ordine') }}">
                        <i class="bi bi-shuffle"></i>
                    </button>
                    <button type="button" class="catalog-action-btn catalog-action-btn--danger" data-bs-toggle="modal" data-bs-target="#staticBackdrop{{$item->id}}" aria-label="{{ __('admin.Elimina') }} {{$item->name}}" title="{{ __('admin.Elimina') }}">
                        <i class="bi bi-trash3"></i>
                    </button>
                    
                </div>
                @endif
                

            </div>
        <!-- Button trigger modal -->
        
            
            <!-- Modal ELINIMAZIONE -->
            <div class="modal fade" id="staticBackdrop{{$item->id}}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel{{$item->id}}" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered my_modal_dialog">
                    <form action="{{ route('admin.categories.destroy', ['category'=>$item]) }}" method="post" class="w-100">
                        @method('delete')
                        @csrf
                        <x-dashboard.action-modal
                            title-id="staticBackdropLabel{{$item->id}}"
                            title="{{ __('admin.Conferma_eliminazione') }}"
                            eyebrow="{{ __('admin.Elimina') }}"
                            tone="danger"
                            :subject="$item->name"
                        >
                            <p class="dashboard-action-modal__hint">{{ __('admin.Delete_categoria_info') }}</p>

                            <x-slot name="footer">
                                <button class="catalog-action-btn catalog-action-btn--danger catalog-action-btn--with-label" type="submit">
                                    <i class="bi bi-trash3"></i>
                                    {{__('admin.Elimina')}}
                                </button>
                            </x-slot>
                        </x-dashboard.action-modal>
                    </form>
                </div>
            </div>
            <!-- Modal ORDINA I PRODOTTI -->
            <div class="modal fade" id="staticBackdropUp{{$item->id}}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropUpLabel{{$item->id}}" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered my_modal_dialog catalog-reorder-dialog">
                    <form action="{{ route('admin.categories.new_order_products') }}" method="post" class="w-100">
                        @csrf
                        <x-dashboard.action-modal
                            title-id="staticBackdropUpLabel{{$item->id}}"
                            title="{{ __('admin.Riordina_prodotti_di') }}"
                            eyebrow="{{ __('admin.Modifica_ordine') }}"
                            tone="warning"
                            :subject="$item->name"
                        >
                            <x-dashboard.reorder-list
                                :items="$item->products"
                                input-name="new_order_p[]"
                                label-field="name"
                                item-label="prodotto"
                            />

                            <x-slot name="footer">
                                <button class="catalog-action-btn catalog-action-btn--primary catalog-action-btn--with-label" type="submit">
                                    <i class="bi bi-check2-circle"></i>
                                    {{ __('admin.Modifica_ordine') }}
                                </button>
                            </x-slot>
                        </x-dashboard.action-modal>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
        <!-- Modal ORDINA LE CATEGORIE -->
    <div class="modal fade" id="staticBackdropspecial" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropspecial" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered my_modal_dialog catalog-reorder-dialog">
            <form action="{{ route('admin.categories.neworder') }}" method="post" class="w-100">
                @csrf
                <x-dashboard.action-modal
                    title-id="staticBackdropspecial"
                    title="{{ __('admin.Riordina_categorie') }}"
                    eyebrow="{{ __('admin.Modifica_ordine') }}"
                    tone="warning"
                >
                    <x-dashboard.reorder-list
                        :items="$categories"
                        input-name="new_order[]"
                        label-field="name"
                        item-label="categoria"
                    />

                    <x-slot name="footer">
                        <button class="catalog-action-btn catalog-action-btn--primary catalog-action-btn--with-label" type="submit">
                            <i class="bi bi-check2-circle"></i>
                            {{ __('admin.Modifica_ordine') }}
                        </button>
                    </x-slot>
                </x-dashboard.action-modal>
            </form>
        </div>
    </div>
</div>

@include('admin.includes.catalog-search-script')

@endsection
