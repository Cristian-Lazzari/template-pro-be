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
 


<div class="dash_page">
    <h1>{{__('admin.Categorie_prodotti')}}</h1>
    <div class="action-page">
        <a class="my_btn_1 create w-auto" href="{{ route('admin.categories.create') }}">
            <i class="bi bi-cloud-plus-fill" style="font-size: 20px"></i>
            {{__('admin.Crea_nuova')}}</a>
    </div>

    <div class="slim_cont list-group">
        @foreach ($categories as $item)

            <div class="category {{ $item->id === 1 ? 'd-none' : 'list-group-item' }}">
                <h3><a>{{$item->name}}</a></h3>     
            
                @if ($item->id !== 1)
                <div class="actions">
                    <a class="my_btn_1" href="{{ route('admin.categories.edit', $item) }}">
                        <i style="vertical-align: sub; font-size: 21px" class="bi bi-pencil-square"></i>
                    </a> 
                    <button type="button" class="my_btn_4" data-bs-toggle="modal" data-bs-target="#staticBackdropUp{{$item->id}}">
                        <i class="bi bi-shuffle" style="font-size: 20px"></i>
                    </button>
                    <button type="button" class="my_btn_2" data-bs-toggle="modal" data-bs-target="#staticBackdrop{{$item->id}}">
                        <i style="vertical-align: sub; font-size: 21px" class="bi bi-x-circle"></i>
                    </button>
                    
                </div>
                @endif
                

            </div>
        <!-- Button trigger modal -->
        
            
            <!-- Modal ELINIMAZIONE -->
            <div class="modal fade" id="staticBackdrop{{$item->id}}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel{{$item->id}}" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered my_modal_dialog">
                <div class="modal-content">
                    <div class="modal-header">
                    <h1 class=" fs-5" id="staticBackdropLabel{{$item->id}}">{{__('admin.Conferma_eliminazione')}} "<strong>{{$item->name}}</strong>"?</h1>
                    <button data-bs-target="#staticBackdrop{{$item->id}}" type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body body">
                        <p>{{__('admin.Delete_categoria_info')}}</p>
                        <form action="{{ route('admin.categories.destroy', ['category'=>$item]) }}" method="post" >
                            @method('delete')
                            @csrf
                            <button class="my_btn_5 w-100"  type="submit">
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
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class=" fs-5" id="staticBackdropUpLabel{{$item->id}}">{{__('admin.Riordina_prodotti_di')}} "{{$item->name}}"</h1>
                            <button type="button" class="btn-close" data-bs-target="#staticBackdropUp{{$item->id}}" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body body cat_{{$item->id}}">
                            <form action="{{ route('admin.categories.new_order_products') }}" method="post" >
                                @csrf
                                <div class="row ">
                                    <div class="col-md-6 ">
                                        <h4 class="text-center">{{__('admin.Prodotti_da_ordinare')}}</h4>
                                        <ul id="lista_A{{$item->id}}" class="list-group mylist">
                                            @foreach($item->products as $oggetto)
                                                <li class="list-group-item list_p" cat_id="cat_{{$item->id}}" data-id="{{ $oggetto['id'] }}">
                                                    <input value="{{$oggetto->id}}" type="hidden" name="new_order_p[]"> {{ $oggetto->name }}
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                
                                
                                    <div class="col-md-6">
                                        <h4 class="text-center">{{__('admin.Ordine_corretto')}}</h4>
                                        <ul id="lista_B{{$item->id}}" class="list-group mylist">
                                            <!-- Lista vuota inizialmente -->
                                        </ul>
                                    </div>
                                </div>
                                <button  id="btnConferma_cat_{{$item->id}}" class="d-none my_btn_5 w-100" type="submit">
                                    {{__('admin.Modifica')}}
                                </button>
                            </form>
                        </div>
                        
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <button type="button" class="my_btn_3 m-auto mb-3" data-bs-toggle="modal" data-bs-target="#staticBackdropspecial">
        <i class="bi bi-shuffle" style="font-size: 16px"></i>
        {{__('admin.Modifica_ordine')}}
    </button>
        <!-- Modal ORDINA LE CATEGORIE -->
    <div class="modal fade" id="staticBackdropspecial" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="#staticBackdropspecial" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered my_modal_dialog">
            <div class="modal-content">
                <div class="modal-header">
                <h1 class=" fs-5" id="staticBackdropspecial">{{__('admin.Riordina_categorie')}}</h1>
                <button type="button" class="btn-close" data-bs-target="#staticBackdropspecial" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body body">
                    <form action="{{ route('admin.categories.neworder') }}" method="post" >
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <h4 class="text-center">{{__('admin.Categorie_da_ordinare')}}</h4>
                                <ul id="listaA" class="list-group mylist">
                                    @foreach($categories as $oggetto)
                                        <li class="list-group-item list_c" data-id="{{ $oggetto['id'] }}">
                                            <input value="{{$oggetto->id}}" type="hidden" name="new_order[]"> {{ $oggetto->name }}
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        
                        
                            <div class="col-md-6">
                                <h4 class="text-center">{{__('admin.Ordine_corretto')}}</h4>
                                <ul id="listaB" class="list-group mylist list_c">
                                    <!-- Lista vuota inizialmente -->
                                </ul>
                            </div>
                        </div>
                        <button  id="btnConferma" class="d-none my_btn_5 w-100" type="submit">
                            {{__('admin.Modifica')}}
                        </button>
                    </form>
                </div>
                
            </div>
        </div>
    </div>
</div>

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