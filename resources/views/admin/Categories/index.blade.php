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
                <div class="modal-content catalog-index-modal">
                    <div class="modal-header">
                    <h1 class="fs-5" id="staticBackdropLabel{{$item->id}}">{{__('admin.Conferma_eliminazione')}} "<strong>{{$item->name}}</strong>"?</h1>
                    <button data-bs-target="#staticBackdrop{{$item->id}}" type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body body catalog-index-modal__body">
                        <p>{{__('admin.Delete_categoria_info')}}</p>
                        <form action="{{ route('admin.categories.destroy', ['category'=>$item]) }}" method="post" >
                            @method('delete')
                            @csrf
                            <button class="catalog-action-btn catalog-action-btn--danger catalog-action-btn--with-label w-100" type="submit">
                                <i class="bi bi-trash3"></i>
                                {{__('admin.Elimina')}}
                            </button>
                        </form>
                    </div>
                    
                </div>
                </div>
            </div>
            <!-- Modal ORDINA I PRODOTTI -->
            <div class="modal fade" id="staticBackdropUp{{$item->id}}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropUpLabel{{$item->id}}" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered my_modal_dialog">
                    <div class="modal-content catalog-index-modal">
                        <div class="modal-header">
                            <h1 class="fs-5" id="staticBackdropUpLabel{{$item->id}}">{{__('admin.Riordina_prodotti_di')}} "{{$item->name}}"</h1>
                            <button type="button" class="btn-close" data-bs-target="#staticBackdropUp{{$item->id}}" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body body cat_{{$item->id}}">
                            <form action="{{ route('admin.categories.new_order_products') }}" method="post" >
                                @csrf
                                <div class="row ">
                                    <div class="col-md-6 ">
                                        <h4 class="text-center">{{__('admin.Prodotti_da_ordinare')}}</h4>
                                        <ul id="lista_A{{$item->id}}" class="list-group mylist catalog-sort-list">
                                            @foreach($item->products as $oggetto)
                                                <li class="list-group-item list_p catalog-sort-item" cat_id="cat_{{$item->id}}" data-id="{{ $oggetto['id'] }}">
                                                    <input value="{{$oggetto->id}}" type="hidden" name="new_order_p[]"> {{ $oggetto->name }}
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                
                                
                                    <div class="col-md-6">
                                        <h4 class="text-center">{{__('admin.Ordine_corretto')}}</h4>
                                        <ul id="lista_B{{$item->id}}" class="list-group mylist catalog-sort-list">
                                            <!-- Lista vuota inizialmente -->
                                        </ul>
                                    </div>
                                </div>
                                <button id="btnConferma_cat_{{$item->id}}" class="d-none catalog-action-btn catalog-action-btn--primary catalog-action-btn--with-label w-100" type="submit">
                                    <i class="bi bi-check2-circle"></i>
                                    {{__('admin.Modifica')}}
                                </button>
                            </form>
                        </div>
                        
                    </div>
                </div>
            </div>
        @endforeach
    </div>
        <!-- Modal ORDINA LE CATEGORIE -->
    <div class="modal fade" id="staticBackdropspecial" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropspecial" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered my_modal_dialog">
            <div class="modal-content catalog-index-modal">
                <div class="modal-header">
                <h1 class="fs-5" id="staticBackdropspecial">{{__('admin.Riordina_categorie')}}</h1>
                <button type="button" class="btn-close" data-bs-target="#staticBackdropspecial" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body body">
                    <form action="{{ route('admin.categories.neworder') }}" method="post" >
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <h4 class="text-center">{{__('admin.Categorie_da_ordinare')}}</h4>
                                <ul id="listaA" class="list-group mylist catalog-sort-list">
                                    @foreach($categories as $oggetto)
                                        <li class="list-group-item list_c catalog-sort-item" data-id="{{ $oggetto['id'] }}">
                                            <input value="{{$oggetto->id}}" type="hidden" name="new_order[]"> {{ $oggetto->name }}
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        
                        
                            <div class="col-md-6">
                                <h4 class="text-center">{{__('admin.Ordine_corretto')}}</h4>
                                <ul id="listaB" class="list-group mylist catalog-sort-list list_c">
                                    <!-- Lista vuota inizialmente -->
                                </ul>
                            </div>
                        </div>
                        <button id="btnConferma" class="d-none catalog-action-btn catalog-action-btn--primary catalog-action-btn--with-label w-100" type="submit">
                            <i class="bi bi-check2-circle"></i>
                            {{__('admin.Modifica')}}
                        </button>
                    </form>
                </div>
                
            </div>
        </div>
    </div>
</div>

@include('admin.includes.catalog-search-script')

<script defer>
        document.addEventListener("DOMContentLoaded", function () {

        let listaA = document.getElementById("listaA");
        let listaB = document.getElementById("listaB");
        let btnConferma = document.getElementById("btnConferma");

        function spostaElemento(elemento, destinazione) {
            destinazione.appendChild(elemento);
            controllaBottone();
        }

        function controllaBottone() {
            // Mostra il bottone solo se listaB è vuota
            if (listaA.children.length === 0) {
                btnConferma.classList.remove("d-none");
            } else {
                btnConferma.classList.add("d-none");
            }
        }
        function controllaBottoneProd(btn, list) {
            // Mostra il bottone solo se listaB è vuota
            if (list.children.length === 0) {
                btn.classList.remove("d-none");
            } else {
                btn.classList.add("d-none");
            }
        }

        document.body.addEventListener("click", function (e) {
            if (e.target.classList.contains("list_c")) {
                const elemento = e.target;
                if (elemento.parentElement.id === "listaA") {
                    spostaElemento(elemento, listaB);
                } else {
                    spostaElemento(elemento, listaA);
                }
            }
            if (e.target.classList.contains("list_p")) {
                console.log(e.target.classList)
                let elemento_prod = e.target;
                let cat_id = elemento_prod.getAttribute("cat_id");
                let listaA_prod = elemento_prod.closest("." + cat_id).querySelector("[id^='lista_A']");
                let listaB_prod = elemento_prod.closest("." + cat_id).querySelector("[id^='lista_B']");
                let btnConferma = document.getElementById("btnConferma_"+cat_id);
                console.log(cat_id)
                console.log(elemento_prod)
                console.log(listaA_prod)
                console.log(listaB_prod)
                
                if (elemento_prod.parentElement === listaA_prod) {
                    listaB_prod.appendChild(elemento_prod);
                } else {
                    listaA_prod.appendChild(elemento_prod);
                }
                controllaBottoneProd(btnConferma, listaA_prod);
            }
        });
        // Nasconde il bottone inizialmente
        controllaBottone();
    });
</script>

@endsection
