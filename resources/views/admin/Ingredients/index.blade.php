@extends('layouts.base')



@section('contents')
@php
      //se impostato a true gli ordini vengono presi in base ai pezzi altrimenti in base al numero di ordini
    $domain = 'https://future-plus.it/allergens/';
@endphp
@if (session('ingredient_success'))
    @php
        $data = session('ingredient_success')
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
    <h1><i class="bi bi-basket2-fill"></i>{{__('admin.Ingredienti')}}</h1>
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
                data-catalog-empty="#ingredientsSearchEmpty"
            >
        </div>
        <a class="catalog-action-btn catalog-action-btn--primary catalog-action-btn--with-label" href="{{ route('admin.ingredients.create') }}">
            <i class="bi bi-cloud-plus-fill"></i>
            {{__('admin.Crea_nuovo')}}
        </a>
    </div>
    <div id="ingredientsSearchEmpty" class="catalog-search-empty d-none" role="status">
        {{ __('admin.Nessun_risultato_ricerca') }}
    </div>

    @if (count($options))
        <div class="catalog-index-section">
            <h2 class="catalog-index-section__title">{{ __('admin.Opzioni_extra_per_prodotti') }}</h2>
            <div class="catalog-index-list">
                @foreach ($options as $item)
                    <div class="catalog-index-card" data-search-name="{{ mb_strtolower($item->name) }}">
                        <div class="catalog-index-card__main">
                            <div class="catalog-index-card__media" aria-hidden="true">
                                <i class="bi bi-plus-circle-fill"></i>
                            </div>
                            <div class="catalog-index-card__content">
                                <h3 class="catalog-index-card__title">
                                    <a href="{{ route('admin.ingredients.show', $item) }}">{{$item->name}}</a>
                                </h3>
                            </div>
                        </div>
                        <div class="catalog-index-card__actions">
                            <span class="catalog-index-price">{{ \App\Support\Currency::formatCents($item->price) }}</span>
                            <a class="catalog-action-btn catalog-action-btn--neutral" href="{{ route('admin.ingredients.show', $item) }}" aria-label="{{ __('admin.Vedi') }} {{$item->name}}" title="{{ __('admin.Vedi') }}">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a class="catalog-action-btn catalog-action-btn--primary" href="{{ route('admin.ingredients.edit', $item) }}" aria-label="{{ __('admin.Modifica') }} {{$item->name}}" title="{{ __('admin.Modifica') }}">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                            <button type="button" class="catalog-action-btn catalog-action-btn--danger" data-bs-toggle="modal" data-bs-target="#ingredientDelete{{$item->id}}" aria-label="{{ __('admin.Elimina') }} {{$item->name}}" title="{{ __('admin.Elimina') }}">
                                <i class="bi bi-trash3"></i>
                            </button>
                        </div>
                    </div>

                    <div class="modal fade" id="ingredientDelete{{$item->id}}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="ingredientDeleteLabel{{$item->id}}" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered my_modal_dialog">
                            <form action="{{ route('admin.ingredients.destroy', ['ingredient'=>$item]) }}" method="post" class="w-100">
                                @method('delete')
                                @csrf
                                <x-dashboard.action-modal
                                    title-id="ingredientDeleteLabel{{$item->id}}"
                                    title="{{ __('admin.Conferma_eliminazione') }}"
                                    eyebrow="{{ __('admin.Elimina') }}"
                                    tone="danger"
                                    :subject="$item->name"
                                    description="{{ __('admin.Delete_ingredient_info') }}"
                                >
                                    <p class="dashboard-action-modal__hint">{{ __('admin.Delete_ingredient_info') }}</p>

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
    @endif

    <div class="catalog-index-section">
        <h2 class="catalog-index-section__title">{{__('admin.Ingredienti')}}:</h2>
        @if (count($ingredients) == 0)
            <div class="alert alert-info catalog-index-empty">
                {{__('admin.no_ing')}}
            </div>
        @endif
        
        <div class="catalog-index-list">
            @foreach ($ingredients as $item)
                <div class="catalog-index-card" data-search-name="{{ mb_strtolower($item->name) }}">
                    <div class="catalog-index-card__main">
                        <div class="catalog-index-card__media" aria-hidden="true">
                            @if (isset($item->icon))
                                <img src="{{ asset('public/storage/' . $item->icon) }}" alt="{{$item->name}}">
                            @else
                                <i class="bi bi-basket2-fill"></i>
                            @endif
                        </div>
                        <div class="catalog-index-card__content">
                            <h3 class="catalog-index-card__title">
                                <a href="{{ route('admin.ingredients.show', $item) }}">{{$item->name}}</a>
                            </h3>
                        </div>
                    </div>
                    <div class="catalog-index-card__actions">
                        <span class="catalog-index-price">{{ \App\Support\Currency::formatCents($item->price) }}</span>
                        <a class="catalog-action-btn catalog-action-btn--neutral" href="{{ route('admin.ingredients.show', $item) }}" aria-label="{{ __('admin.Vedi') }} {{$item->name}}" title="{{ __('admin.Vedi') }}">
                            <i class="bi bi-eye"></i>
                        </a>
                        <a class="catalog-action-btn catalog-action-btn--primary" href="{{ route('admin.ingredients.edit', $item) }}" aria-label="{{ __('admin.Modifica') }} {{$item->name}}" title="{{ __('admin.Modifica') }}">
                            <i class="bi bi-pencil-square"></i>
                        </a>
                        <button type="button" class="catalog-action-btn catalog-action-btn--danger" data-bs-toggle="modal" data-bs-target="#ingredientDeleteBase{{$item->id}}" aria-label="{{ __('admin.Elimina') }} {{$item->name}}" title="{{ __('admin.Elimina') }}">
                            <i class="bi bi-trash3"></i>
                        </button>
                    </div>
                </div>

                <div class="modal fade" id="ingredientDeleteBase{{$item->id}}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="ingredientDeleteBaseLabel{{$item->id}}" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered my_modal_dialog">
                        <form action="{{ route('admin.ingredients.destroy', ['ingredient'=>$item]) }}" method="post" class="w-100">
                            @method('delete')
                            @csrf
                            <x-dashboard.action-modal
                                title-id="ingredientDeleteBaseLabel{{$item->id}}"
                                title="{{ __('admin.Conferma_eliminazione') }}"
                                eyebrow="{{ __('admin.Elimina') }}"
                                tone="danger"
                                :subject="$item->name"
                                description="{{ __('admin.Delete_ingredient_info') }}"
                            >
                                <p class="dashboard-action-modal__hint">{{ __('admin.Delete_ingredient_info') }}</p>

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
</div>

@include('admin.includes.catalog-search-script')

@endsection
