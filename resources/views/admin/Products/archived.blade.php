@extends('layouts.base')



@section('contents')
@php
  //se impostato a true gli ordini vengono presi in base ai pezzi altrimenti in base al numero di ordini
$domain = 'https://future-plus.it/allergens/';
 
@endphp
<style>
    body{
        background: #020222;      
    }
</style>

@if (session('success'))
    @php
        $data = session('success')
    @endphp
    <div class="alert alert-primary alert-dismissible fade show" role="alert">
        {{ $data }} 
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
 


<div class="dash_page">

    <h1>
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-fork-knife" viewBox="0 0 16 16">
            <path d="M13 .5c0-.276-.226-.506-.498-.465-1.703.257-2.94 2.012-3 8.462a.5.5 0 0 0 .498.5c.56.01 1 .13 1 1.003v5.5a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5zM4.25 0a.25.25 0 0 1 .25.25v5.122a.128.128 0 0 0 .256.006l.233-5.14A.25.25 0 0 1 5.24 0h.522a.25.25 0 0 1 .25.238l.233 5.14a.128.128 0 0 0 .256-.006V.25A.25.25 0 0 1 6.75 0h.29a.5.5 0 0 1 .498.458l.423 5.07a1.69 1.69 0 0 1-1.059 1.711l-.053.022a.92.92 0 0 0-.58.884L6.47 15a.971.971 0 1 1-1.942 0l.202-6.855a.92.92 0 0 0-.58-.884l-.053-.022a1.69 1.69 0 0 1-1.059-1.712L3.462.458A.5.5 0 0 1 3.96 0z"/>
        </svg>
        Prodotti</h1>
    
    <div class="action-page">
        <a class="my_btn_3 m-1 w-auto" href="{{ route('admin.products.index') }}">Torna ai prodotti</a>
    </div>

    <div class="time-list prod_index">
        @foreach ($products as $item)

            <div class="res-item
            @if(!$item->visible) not_v @endif
             prod">
                @if (isset($item->image))
                    <button type="button" class=" image_btn" data-bs-toggle="modal" data-bs-target="#img{{$item->id}}">
                        <img src="{{ asset('public/storage/' . $item->image) }}" alt="{{$item->name}}">
                    </button>
                    <!-- Modal -->
                    <div class="modal fade" id="img{{$item->id}}" tabindex="-1" aria-labelledby="img{{$item->id}}Label" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-body image_modal">
                                    <img src="{{ asset('public/storage/' . $item->image) }}" alt="{{$item->name}}">
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="no_img">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-image-fill" viewBox="0 0 16 16">
                        <path d="M.002 3a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2h-12a2 2 0 0 1-2-2zm1 9v1a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V9.5l-3.777-1.947a.5.5 0 0 0-.577.093l-3.71 3.71-2.66-1.772a.5.5 0 0 0-.63.062zm5-6.5a1.5 1.5 0 1 0-3 0 1.5 1.5 0 0 0 3 0"/>
                        </svg>
                    </div>
                @endif 
                <div class="name_cat">
                    <div class="name">{{$item->name}}</div>
                    <div class="cat">{{$item->category->name}}</div>
                </div>
                <div class="price_btn">
                    <div class="price">€{{$item->price / 100}}</div>
                    <button type="button" class="action_menu action_menu_info" data-bs-toggle="modal" data-bs-target="#exampleModal{{$item->id}}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-info-circle-fill" viewBox="0 0 16 16">
                            <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16m.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.47l-.451-.081.082-.381 2.29-.287zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2"/>
                        </svg>
                        Info
                    </button>
                    <!-- Modal -->
                    <div class="modal fade" id="exampleModal{{$item->id}}" tabindex="-1" aria-labelledby="exampleModal{{$item->id}}Label" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-body">
                                    <button type="button" class="btn_close" data-bs-dismiss="modal">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-x-circle-fill" viewBox="0 0 16 16">
                                        <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M5.354 4.646a.5.5 0 1 0-.708.708L7.293 8l-2.647 2.646a.5.5 0 0 0 .708.708L8 8.707l2.646 2.647a.5.5 0 0 0 .708-.708L8.707 8l2.647-2.646a.5.5 0 0 0-.708-.708L8 7.293z"/>
                                        </svg>
                                        Chiudi
                                    </button>
                                    <div class="action_top">
                                        <form action="{{ route('admin.products.status') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="archive" value="1">
                                            <input type="hidden" name="v" value="0">
                                            <input type="hidden" name="a" value="1">
                                            <input type="hidden" name="id" value="{{$item->id}}">
                                            <button class="edit" type="submit">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-cloud-plus-fill" viewBox="0 0 16 16">
                                                <path d="M8 2a5.53 5.53 0 0 0-3.594 1.342c-.766.66-1.321 1.52-1.464 2.383C1.266 6.095 0 7.555 0 9.318 0 11.366 1.708 13 3.781 13h8.906C14.502 13 16 11.57 16 9.773c0-1.636-1.242-2.969-2.834-3.194C12.923 3.999 10.69 2 8 2m.5 4v1.5H10a.5.5 0 0 1 0 1H8.5V10a.5.5 0 0 1-1 0V8.5H6a.5.5 0 0 1 0-1h1.5V6a.5.5 0 0 1 1 0"/>
                                                </svg>
                                                Ripristina
                                            </button>
                                        </form>                      

                                        <form action="{{ route('admin.products.destroy', $item) }}" method="POST">
                                            @method('DELETE')
                                            @csrf
                                            <button class="edit btn_delete" type="submit">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-trash-fill" viewBox="0 0 16 16">
                                                    <path d="M2.5 1a1 1 0 0 0-1 1v1a1 1 0 0 0 1 1H3v9a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V4h.5a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H10a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1zm3 4a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 .5-.5M8 5a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7A.5.5 0 0 1 8 5m3 .5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 1 0"/>
                                                </svg>
                                            </button>
                                        </form>
                               
                                    </div>
                                    <div class="name_cat">
                                        <div class="name">{{$item->name}}</div>
                                        <div class="cat">{{$item->category->name}}</div>
                                    </div>
                                    @if (count($item->ingredients))
                                        <section>
                                            <h4>
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-card-list" viewBox="0 0 16 16">
                                                    <path d="M14.5 3a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-13a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5zm-13-1A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h13a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2z"/>
                                                    <path d="M5 8a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7A.5.5 0 0 1 5 8m0-2.5a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5m0 5a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5m-1-5a.5.5 0 1 1-1 0 .5.5 0 0 1 1 0M4 8a.5.5 0 1 1-1 0 .5.5 0 0 1 1 0m0 2.5a.5.5 0 1 1-1 0 .5.5 0 0 1 1 0"/>
                                                </svg>
                                                Ingredienti</h4>
                                            <p>
                                                @foreach ($item->ingredients as $ingredient)     
                                                    {{ $ingredient->name }}{{ !$loop->last ? ', ' : '.' }}
                                                @endforeach
                                            </p>
                                        </section>
                                    @endif
                                    @if ($item->description)
                                        <section>
                                            <h4>
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-card-text" viewBox="0 0 16 16">
                                                    <path d="M14.5 3a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-13a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5zm-13-1A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h13a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2z"/>
                                                    <path d="M3 5.5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5M3 8a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9A.5.5 0 0 1 3 8m0 2.5a.5.5 0 0 1 .5-.5h6a.5.5 0 0 1 0 1h-6a.5.5 0 0 1-.5-.5"/>
                                                </svg>
                                                Descrizione</h4>
                                            <p>{{$item->description}}</p>
                                        </section>
                                    @endif
        
                                    <div class="price">€{{$item->price / 100}}</div>
        
                                    <div class="allergens">
                                        @php $all = json_decode($item->allergens) @endphp
                                        @foreach ($all as $i)
                                        <div class="al">
                                            <img src="{{config('configurazione.allergens')[$i]['img']}}" alt="" title="{{config('configurazione.allergens')[$i]['name']}}">
                                            {{config('configurazione.allergens')[$i]['name']}}
                                        </div>
                                        @endforeach
                                    </div>   
        
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>

        @endforeach
    </div>

</div>


@endsection