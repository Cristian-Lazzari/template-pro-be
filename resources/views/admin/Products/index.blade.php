@extends('layouts.base')



@section('contents')

@if (session('success'))
    @php
        $data = session('success')
    @endphp
    <div class="alert alert-primary alert-dismissible fade show" role="alert">
        {{ $data }} 
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
@if (session('delete_success'))
    @php
        $data = session('delete_success')
    @endphp
    <div class="alert alert-danger">
       " {{ $data->name }} " e stato eliminato correttamente
    </div>
@endif
 
<div class="dash_page">

    <h1>
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-fork-knife" viewBox="0 0 16 16">
            <path d="M13 .5c0-.276-.226-.506-.498-.465-1.703.257-2.94 2.012-3 8.462a.5.5 0 0 0 .498.5c.56.01 1 .13 1 1.003v5.5a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5zM4.25 0a.25.25 0 0 1 .25.25v5.122a.128.128 0 0 0 .256.006l.233-5.14A.25.25 0 0 1 5.24 0h.522a.25.25 0 0 1 .25.238l.233 5.14a.128.128 0 0 0 .256-.006V.25A.25.25 0 0 1 6.75 0h.29a.5.5 0 0 1 .498.458l.423 5.07a1.69 1.69 0 0 1-1.059 1.711l-.053.022a.92.92 0 0 0-.58.884L6.47 15a.971.971 0 1 1-1.942 0l.202-6.855a.92.92 0 0 0-.58-.884l-.053-.022a1.69 1.69 0 0 1-1.059-1.712L3.462.458A.5.5 0 0 1 3.96 0z"/>
        </svg>
        Prodotti</h1>
    
    <div class="action-page">
        <a class="my_btn_1 create m-1 w-auto" href="{{ route('admin.products.create') }}">Crea un nuovo prodotto</a>
        <a class="my_btn_1 trash m-1 w-auto" href="{{ route('admin.products.archived') }}">Archivio</a>
    </div>
    
    <div class="time-list prod_index">
        @foreach ($products as $item)

            <div class="res-item prod">
                @if (isset($item->image))
                    <img src="{{ asset('public/storage/' . $item->image) }}" alt="{{$item->name}}">
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
                </div>
                <!-- Modal -->
                <div class="modal fade" id="exampleModal{{$item->id}}" tabindex="-1" aria-labelledby="exampleModal{{$item->id}}Label" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-body">
                                <button type="button" class="btn_close" data-bs-dismiss="modal">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x-circle-fill" viewBox="0 0 16 16">
                                    <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M5.354 4.646a.5.5 0 1 0-.708.708L7.293 8l-2.647 2.646a.5.5 0 0 0 .708.708L8 8.707l2.646 2.647a.5.5 0 0 0 .708-.708L8.707 8l2.647-2.646a.5.5 0 0 0-.708-.708L8 7.293z"/>
                                    </svg>
                                    Chiudi
                                </button>
                                <div class="name_cat">
                                    <div class="name">{{$item->name}}</div>
                                    <div class="cat">{{$item->category->name}}</div>
                                </div>
                                @if (count($item->ingredients))
                                    <section>
                                        <h4>Ingredienti</h4>
                                        <p>
                                            @foreach ($item->ingredients as $ingredient)     
                                                {{ $ingredient->name }}{{ !$loop->last ? ', ' : '.' }}
                                            @endforeach
                                        </p>
                                    </section>
                                @endif
                                @if ($item->description)
                                    <section>
                                        <h4>Descrizione</h4>
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
{{--     
            <div class="obj  @if (!$item->visible) not_v @endif" onclick="window.location.href='{{ route('admin.products.show', $item->id) }}">
                <h3>
                    @if ($item->promotion)
                    <svg height="24px" class="promotion_on" version="1.2" viewBox="0 0 24 24" width="24px" xml:space="preserve" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><g><g><path d="M9.362,9.158c0,0-3.16,0.35-5.268,0.584c-0.19,0.023-0.358,0.15-0.421,0.343s0,0.394,0.14,0.521    c1.566,1.429,3.919,3.569,3.919,3.569c-0.002,0-0.646,3.113-1.074,5.19c-0.036,0.188,0.032,0.387,0.196,0.506    c0.163,0.119,0.373,0.121,0.538,0.028c1.844-1.048,4.606-2.624,4.606-2.624s2.763,1.576,4.604,2.625    c0.168,0.092,0.378,0.09,0.541-0.029c0.164-0.119,0.232-0.318,0.195-0.505c-0.428-2.078-1.071-5.191-1.071-5.191    s2.353-2.14,3.919-3.566c0.14-0.131,0.202-0.332,0.14-0.524s-0.23-0.319-0.42-0.341c-2.108-0.236-5.269-0.586-5.269-0.586    s-1.31-2.898-2.183-4.83c-0.082-0.173-0.254-0.294-0.456-0.294s-0.375,0.122-0.453,0.294C10.671,6.26,9.362,9.158,9.362,9.158z"></path></g></g></svg>
                    @endif
                    <a href="{{ route('admin.products.show', $item) }}">{{$item->name}}</a>
                </h3>     
                <div class="card_">
                    
    
                    <div class="info">
                        @if (count($item->ingredients))
                            <section>
                                <h4>Ingredienti:</h4>
                                <p>
                                    @foreach ($item->ingredients as $ingredient)     
                                        {{ $ingredient->name }}{{ !$loop->last ? ', ' : '.' }}
                                    @endforeach
                                </p>
                            </section>
                        @endif
                        @if ($item->description)
                            <section>
                                <h4>Descrizione:</h4>
                                <p>{{$item->description}}</p>
                            </section>
                        @endif
                        <div class="split_i">
                            <h4>{{$item->category->name}}</h4>
                            <div class="price">€{{$item->price / 100}}</div>
                        </div>
                    </div>
                </div>
                <div class="allergens">
                    
                    @php $all = json_decode($item->allergens) @endphp
                    @foreach ($all as $i)
                        <img src="{{config('configurazione.allergens')[$i]['img']}}" alt="" title="{{config('configurazione.allergens')[$i]['name']}}">
                    @endforeach
                </div>
                <div class="actions">
                    <a class="my_btn_1 m" href="{{ route('admin.products.edit', $item) }}">Modifica</a>
                    <form action="{{ route('admin.products.status') }}" method="POST">
                        @csrf
                        <input type="hidden" name="archive" value="0">
                        <input type="hidden" name="v" value="0">
                        <input type="hidden" name="a" value="1">
                        <input type="hidden" name="id" value="{{$item->id}}">
                        <button class="my_btn_1 d" type="submit">Archivia</button>
                    </form>
                    <form action="{{ route('admin.products.status') }}" method="POST">
                        @csrf
                        <input type="hidden" name="archive" value="0">
                        <input type="hidden" name="v" value="1">
                        <input type="hidden" name="a" value="0">
                        <input type="hidden" name="id" value="{{$item->id}}">
                        @if (!$item->visible)
                            <button class="my_btn_1 v op" type="submit">
                                <svg style="vertical-align: sub" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye-slash-fill" viewBox="0 0 16 16">
                                    <path d="m10.79 12.912-1.614-1.615a3.5 3.5 0 0 1-4.474-4.474l-2.06-2.06C.938 6.278 0 8 0 8s3 5.5 8 5.5a7 7 0 0 0 2.79-.588M5.21 3.088A7 7 0 0 1 8 2.5c5 0 8 5.5 8 5.5s-.939 1.721-2.641 3.238l-2.062-2.062a3.5 3.5 0 0 0-4.474-4.474z"/>
                                    <path d="M5.525 7.646a2.5 2.5 0 0 0 2.829 2.829zm4.95.708-2.829-2.83a2.5 2.5 0 0 1 2.829 2.829zm3.171 6-12-12 .708-.708 12 12z"/>
                                </svg>  
                            </button>
                        @else
                            <button class="my_btn_1 v" type="submit">
                                <svg style="vertical-align: sub" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye-fill" viewBox="0 0 16 16">
                                    <path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0"/>
                                    <path d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8m8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7"/>
                                </svg>    
                            </button>
                        @endif
                        
                    </form>
                </div>
    
            </div> --}}
        @endforeach
    </div>
</div>

@endsection