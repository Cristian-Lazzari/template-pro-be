@extends('layouts.base')



@section('contents')
@php
      //se impostato a true gli ordini vengono presi in base ai pezzi altrimenti in base al numero di ordini
    $domain = 'https://future-plus.it/allergens/';
@endphp
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
 

<h1>Categorie prodotti</h1>

<div class="action-page">
    <a class="my_btn_2 m-1 w-auto" href="{{ route('admin.categories.create') }}">Crea una nuova categoria</a>
</div>

<div class="slim_cont">
    @foreach ($categories as $item)

    @if ($item->id !== 1)
        <div class="category ">
    @else
        <div class="category op ">
    @endif
            <h3><a>{{$item->name}}</a></h3>     
        
            @if ($item->id !== 1)
            <div class="actions">
                <a class="my_btn_1" href="{{ route('admin.categories.edit', $item) }}">
                    <svg style="vertical-align: sub" xmlns="http://www.w3.org/2000/svg" width="21" height="21" fill="currentColor" class="bi bi-pencil-square" viewBox="0 0 16 16">
                        <path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/>
                        <path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z"/>
                    </svg>
                </a>
                <button type="button" class="my_btn_2" data-bs-toggle="modal" data-bs-target="#staticBackdrop{{$item->id}}">
                    <svg style="vertical-align: sub" xmlns="http://www.w3.org/2000/svg" width="21" height="21" fill="currentColor" class="bi bi-x-circle" viewBox="0 0 16 16">
                        <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
                        <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708"/>
                    </svg>
                </button>
                
            </div>
            @endif
            

        </div>
       <!-- Button trigger modal -->
       
        
        <!-- Modal -->
        <div class="modal fade" id="staticBackdrop{{$item->id}}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel{{$item->id}}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered my_modal_dialog">
            <div class="modal-content">
                <div class="modal-header">
                <h1 class=" fs-5" id="staticBackdropLabel{{$item->id}}">Confermi di voler eliminare "<strong>{{$item->name}}</strong>"?</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body body">
                    <p>Eiminando questa categoria, se ci sono prodotti ad essa abbinati, verrano abbinati alla categoria "Non categorizzati"</p>
                    <form action="{{ route('admin.categories.destroy', ['category'=>$item]) }}" method="post" >
                        @method('delete')
                        @csrf
                        <button class="my_btn_5 w-100" type="submit">
                            Elimina
                        </button>
                    </form>
                </div>
                
            </div>
            </div>
        </div>
    @endforeach
</div>
<h3 class="c-title" >Ordine di visualizzazione nel sito</h3>
<button type="button" class="my_btn_3 m-auto mb-3" data-bs-toggle="modal" data-bs-target="#staticBackdropspecial">
    Modifica ordine
</button>
<ul class="list-group mylist">
    @php $c = -1
    @endphp
    @foreach ($order as $item)
    @php $c ++
    @endphp
    @if ($item->name == 'Non categorizzati')
     <li class="list-group-item op">{{$item->name}}</li>
        
    @else
        
    <li class="list-group-item">
        <strong>#{{$c}}</strong>
        <span>{{$item->name}}</span>
    </li>
    @endif
    @endforeach
</ul>

<div class="modal fade" id="staticBackdropspecial" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabelspecial" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered my_modal_dialog">
    <div class="modal-content">
        <div class="modal-header">
        <h1 class=" fs-5" id="staticBackdropLabelspecial">Riordina le tue categorie</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body body">
            <form action="{{ route('admin.categories.neworder') }}" method="post" >
                @csrf
                <div class="row">
                    <div class="col-md-6">
                        <h4 class="text-center">Lista Categeorie da ordinare</h4>
                        <ul id="listaA" class="list-group mylist">
                            @foreach($order as $oggetto)
                                <li class="list-group-item" data-id="{{ $oggetto['id'] }}">
                                    <input value="{{$oggetto->id}}" type="hidden" name="new_order[]"> {{ $oggetto->name }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                
                
                    <div class="col-md-6">
                        <h4 class="text-center">Ordine corretto</h4>
                        <ul id="listaB" class="list-group mylist">
                            <!-- Lista vuota inizialmente -->
                        </ul>
                    </div>
                </div>
                <button  id="btnConferma" class="d-none my_btn_5 w-100" type="submit">
                    Modifica
                </button>
            </form>
        </div>
        
    </div>
    </div>
</div>

<script defer>
    document.addEventListener("DOMContentLoaded", function () {
        const listaA = document.getElementById("listaA");
        const listaB = document.getElementById("listaB");
        const btnConferma = document.getElementById("btnConferma");

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

        document.body.addEventListener("click", function (e) {
            if (e.target.classList.contains("list-group-item")) {
                const elemento = e.target;
                if (elemento.parentElement.id === "listaA") {
                    spostaElemento(elemento, listaB);
                } else {
                    spostaElemento(elemento, listaA);
                }
            }
        });

        // Nasconde il bottone inizialmente
        controllaBottone();
    });
</script>

@endsection