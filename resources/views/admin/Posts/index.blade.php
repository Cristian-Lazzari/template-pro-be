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
       " {{ $data->title }} " e stato eliminato correttamente
    </div>
@endif
@if (session('order_success'))
    @php
        $data = session('order_success')
    @endphp
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        {{ $data }} 
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
 
<div class="dash_page">
    <h1>
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-images" viewBox="0 0 16 16">
            <path d="M4.502 9a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3"/>
            <path d="M14.002 13a2 2 0 0 1-2 2h-10a2 2 0 0 1-2-2V5A2 2 0 0 1 2 3a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v8a2 2 0 0 1-1.998 2M14 2H4a1 1 0 0 0-1 1h9.002a2 2 0 0 1 2 2v7A1 1 0 0 0 15 11V3a1 1 0 0 0-1-1M2.002 4a1 1 0 0 0-1 1v8l2.646-2.354a.5.5 0 0 1 .63-.062l2.66 1.773 3.71-3.71a.5.5 0 0 1 .577-.094l1.777 1.947V5a1 1 0 0 0-1-1z"/>
        </svg>
        Contenuti multimediali 
    </h1>

    <div class="action-page">
        <a class="my_btn_1 create m-1 w-auto" href="{{ route('admin.posts.create') }}">Crea un nuovo post</a>
        <a class="my_btn_1 trash m-1 w-auto" href="{{ route('admin.posts.archived') }}">Archivio</a>
    </div>

        <div class="filters">
        <div class="bar">
            <input type="checkbox" class="check" id="f">
            <div class="box">
                <input type="text" id="searchInput" class="search" placeholder="Cerca prodotto..." >
                <button id="typeToggle" class="type">Tutti</button>
                <button id="sortToggle" class="order">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-sort-down-alt" viewBox="0 0 16 16">
                        <path d="M3.5 3.5a.5.5 0 0 0-1 0v8.793l-1.146-1.147a.5.5 0 0 0-.708.708l2 1.999.007.007a.497.497 0 0 0 .7-.006l2-2a.5.5 0 0 0-.707-.708L3.5 12.293zm4 .5a.5.5 0 0 1 0-1h1a.5.5 0 0 1 0 1zm0 3a.5.5 0 0 1 0-1h3a.5.5 0 0 1 0 1zm0 3a.5.5 0 0 1 0-1h5a.5.5 0 0 1 0 1zM7 12.5a.5.5 0 0 0 .5.5h7a.5.5 0 0 0 0-1h-7a.5.5 0 0 0-.5.5"/>
                    </svg>
                </button>
            </div>
            <label for="f">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-funnel-fill" viewBox="0 0 16 16">
                    <path d="M1.5 1.5A.5.5 0 0 1 2 1h12a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.128.334L10 8.692V13.5a.5.5 0 0 1-.342.474l-3 1A.5.5 0 0 1 6 14.5V8.692L1.628 3.834A.5.5 0 0 1 1.5 3.5z"/>
                </svg>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-funnel" viewBox="0 0 16 16">
                    <path d="M1.5 1.5A.5.5 0 0 1 2 1h12a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.128.334L10 8.692V13.5a.5.5 0 0 1-.342.474l-3 1A.5.5 0 0 1 6 14.5V8.692L1.628 3.834A.5.5 0 0 1 1.5 3.5zm1 .5v1.308l4.372 4.858A.5.5 0 0 1 7 8.5v5.306l2-.666V8.5a.5.5 0 0 1 .128-.334L13.5 3.308V2z"/>
                </svg>
            </label>
        </div>
    </div>
    
    <div class="time-list prod_index">
        @foreach ($posts as $item)

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
                    <div class="name">{{$item->title}}</div>
                    <div class="cat">{{$item->path}}</div>
                </div>
                <div class="price_btn"> 
                    @if ($item->link)
                        <a class="link" href="{{$item->link}}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-link-45deg" viewBox="0 0 16 16">
                        <path d="M4.715 6.542 3.343 7.914a3 3 0 1 0 4.243 4.243l1.828-1.829A3 3 0 0 0 8.586 5.5L8 6.086a1 1 0 0 0-.154.199 2 2 0 0 1 .861 3.337L6.88 11.45a2 2 0 1 1-2.83-2.83l.793-.792a4 4 0 0 1-.128-1.287z"/>
                        <path d="M6.586 4.672A3 3 0 0 0 7.414 9.5l.775-.776a2 2 0 0 1-.896-3.346L9.12 3.55a2 2 0 1 1 2.83 2.83l-.793.792c.112.42.155.855.128 1.287l1.372-1.372a3 3 0 1 0-4.243-4.243z"/>
                        </svg>
                        Link
                    </a>
                    @endif
                    <button type="button" class="action_menu action_menu_info" data-bs-toggle="modal" data-bs-target="#exampleModal{{$item->id}}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-info-circle-fill" viewBox="0 0 16 16">
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
                                        <a href="{{ route('admin.products.edit', $item) }}" class="edit">
                                            <svg style="vertical-align: sub" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-pencil-square" viewBox="0 0 16 16">
                                                <path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/>
                                                <path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z"/>
                                            </svg>
                                        </a>
                                        
                                        <form action="{{ route('admin.products.status') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="archive" value="0">
                                            <input type="hidden" name="v" value="1">
                                            <input type="hidden" name="a" value="0">
                                            <input type="hidden" name="id" value="{{$item->id}}">
                                            <button type="submit" class=" edit
                                                @if(!$item->visible) not @endif 
                                                visible">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-eye-fill" viewBox="0 0 16 16">
                                                    <path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0"/>
                                                    <path d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8m8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7"/>
                                                </svg>
                                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-eye-slash-fill" viewBox="0 0 16 16">
                                                    <path d="m10.79 12.912-1.614-1.615a3.5 3.5 0 0 1-4.474-4.474l-2.06-2.06C.938 6.278 0 8 0 8s3 5.5 8 5.5a7 7 0 0 0 2.79-.588M5.21 3.088A7 7 0 0 1 8 2.5c5 0 8 5.5 8 5.5s-.939 1.721-2.641 3.238l-2.062-2.062a3.5 3.5 0 0 0-4.474-4.474z"/>
                                                    <path d="M5.525 7.646a2.5 2.5 0 0 0 2.829 2.829zm4.95.708-2.829-2.83a2.5 2.5 0 0 1 2.829 2.829zm3.171 6-12-12 .708-.708 12 12z"/>
                                                </svg>
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.products.status') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="archive" value="0">
                                            <input type="hidden" name="v" value="0">
                                            <input type="hidden" name="a" value="1">
                                            <input type="hidden" name="id" value="{{$item->id}}">
                                            <button class="edit" type="submit">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-trash-fill" viewBox="0 0 16 16">
                                                    <path d="M2.5 1a1 1 0 0 0-1 1v1a1 1 0 0 0 1 1H3v9a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V4h.5a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H10a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1zm3 4a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 .5-.5M8 5a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7A.5.5 0 0 1 8 5m3 .5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 1 0"/>
                                                </svg>
                                            </button>
                                        </form>
                               
                                    </div>
                                    <div class="name_cat">
                                        <div class="name">{{$item->title}}</div>
                                        <div class="cat">{{$item->path}}</div>
                                    </div>
                                    @if ($item->description)
                                        <section>
                                            <h4>
                                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-card-text" viewBox="0 0 16 16">
                                                    <path d="M14.5 3a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-13a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5zm-13-1A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h13a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2z"/>
                                                    <path d="M3 5.5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5M3 8a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9A.5.5 0 0 1 3 8m0 2.5a.5.5 0 0 1 .5-.5h6a.5.5 0 0 1 0 1h-6a.5.5 0 0 1-.5-.5"/>
                                                </svg>
                                                Descrizione</h4>
                                            <p>{{$item->description}}</p>
                                        </section>
                                    @endif
                                    @if ($item->hashtags)
                                        <section>
                                            <h4>
                                                <strong>#</strong>
                                                hashtags</h4>
                                            <p>{{$item->hashtags}}</p>
                                        </section>
                                    @endif
                                    @if ($item->link)
                                        <section>
                                            <h4>
                                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-link-45deg" viewBox="0 0 16 16">
                                                    <path d="M4.715 6.542 3.343 7.914a3 3 0 1 0 4.243 4.243l1.828-1.829A3 3 0 0 0 8.586 5.5L8 6.086a1 1 0 0 0-.154.199 2 2 0 0 1 .861 3.337L6.88 11.45a2 2 0 1 1-2.83-2.83l.793-.792a4 4 0 0 1-.128-1.287z"/>
                                                    <path d="M6.586 4.672A3 3 0 0 0 7.414 9.5l.775-.776a2 2 0 0 1-.896-3.346L9.12 3.55a2 2 0 1 1 2.83 2.83l-.793.792c.112.42.155.855.128 1.287l1.372-1.372a3 3 0 1 0-4.243-4.243z"/>
                                                </svg>
                                                Link</h4>
                                            <a href="{{$item->link}}">{{$item->link}}</a>
                                        </section>
                                    @endif
        
        
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>

        @endforeach
    </div>


    <div class="object-container post-container">
        @foreach ($posts as $item)

            <div class="post  @if (!$item->visible) not_v @endif" onclick="window.location.href='{{ route('admin.posts.show', $item->id) }}">
                <div class="card_">
                    <h3>
                        @if ($item->promo)
                            <svg height="24px" class="promotion_on" version="1.2" viewBox="0 0 24 24" width="24px" xml:space="preserve" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><g><g><path d="M9.362,9.158c0,0-3.16,0.35-5.268,0.584c-0.19,0.023-0.358,0.15-0.421,0.343s0,0.394,0.14,0.521    c1.566,1.429,3.919,3.569,3.919,3.569c-0.002,0-0.646,3.113-1.074,5.19c-0.036,0.188,0.032,0.387,0.196,0.506    c0.163,0.119,0.373,0.121,0.538,0.028c1.844-1.048,4.606-2.624,4.606-2.624s2.763,1.576,4.604,2.625    c0.168,0.092,0.378,0.09,0.541-0.029c0.164-0.119,0.232-0.318,0.195-0.505c-0.428-2.078-1.071-5.191-1.071-5.191    s2.353-2.14,3.919-3.566c0.14-0.131,0.202-0.332,0.14-0.524s-0.23-0.319-0.42-0.341c-2.108-0.236-5.269-0.586-5.269-0.586    s-1.31-2.898-2.183-4.83c-0.082-0.173-0.254-0.294-0.456-0.294s-0.375,0.122-0.453,0.294C10.671,6.26,9.362,9.158,9.362,9.158z"></path></g></g></svg>
                        @endif
                        <a href="{{ route('admin.posts.show', $item) }}">{{$item->title}}</a>
                    </h3>     
                    @if (isset($item->image))
                        <img src="{{ asset('public/storage/' . $item->image) }}" alt="{{$item->title}}">
                    @else
                        <img src="https://db.kojo-sushi.it/public/images/or.png" alt="{{$item->title }}">
                    @endif 

                    @if (isset($item->hashtag))
                        <p class="hash">{{$item->hashtag}}</p>
                    @endif 
                    <h4 class="ell-c">Pagina: <span class="">{{$item->path == '1' ? 'News' : 'Story'}}</span></h4>
                    <div class="info">
                        <section>
                            {{-- <h4>Precedenza: <strong>{{$item->order}}</strong></h4>       --}}
                            @if (isset($item->link)) 
                                <h4 class="ell-c">Link: <a href="{{$item->link}}" class="ellips">{{$item->link}}</a></h4>
                            @endif  
                        </section>
                    </div>
                </div>
                <div class="actions">
                    <a class="my_btn_1 m" href="{{ route('admin.posts.edit', $item) }}">Modifica</a>
                    <form action="{{ route('admin.posts.status') }}" method="POST">
                        @csrf
                        <input type="hidden" name="archive" value="0">
                        <input type="hidden" name="v" value="0">
                        <input type="hidden" name="a" value="1">
                        <input type="hidden" name="id" value="{{$item->id}}">
                        <button class="my_btn_1 d" type="submit">Archivia</button>
                    </form>
                    <form action="{{ route('admin.posts.status') }}" method="POST">
                        @csrf
                        <input type="hidden" name="archive" value="0">
                        <input type="hidden" name="v" value="1">
                        <input type="hidden" name="a" value="0">
                        <input type="hidden" name="id" value="{{$item->id}}">
                        @if (!$item->visible)
                            <button class="my_btn_5" type="submit">
                                PUBBLICA
                            </button>
                        @else
                            <button class="my_btn_5" type="submit">
                                Nascondi   
                            </button>
                        @endif
                        
                    </form>
                </div>

            </div>
        @endforeach
    </div>
    <div class="action-page mt-5">
        <button type="button" class="my_btn_3" data-bs-toggle="modal" data-bs-target="#staticBackdropspecialStory">
            Modifica ordine Post in Storia
        </button>
        <button type="button" class="my_btn_2" data-bs-toggle="modal" data-bs-target="#staticBackdropspecialNews">
            Modifica ordine Post in News
        </button>
    </div>
</div>



    <!-- Modal ORDINA LE CATEGORIE -->
<div class="modal fade" id="staticBackdropspecialNews" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="#staticBackdropspecialNews" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered my_modal_dialog">
        <div class="modal-content">
            <div class="modal-header">
            <h1 class=" fs-5" id="staticBackdropspecialNews">Riordina i tuoi Post in News (Novità/eventi)</h1>
            <button type="button" class="btn-close" data-bs-target="#staticBackdropspecialNews" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body body">
                <form action="{{ route('admin.posts.neworder') }}" method="post" >
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <h4 class="text-center">Post in News</h4>
                            <ul id="newsA" class="list-group mylist">
                                @foreach($news as $oggetto)
                                    <li class="list-group-item list_n" data-id="{{ $oggetto['id'] }}">
                                        <input value="{{$oggetto->id}}" type="hidden" name="new_order[]"> {{ $oggetto->title }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    
                    
                        <div class="col-md-6">
                            <h4 class="text-center">Ordine corretto</h4>
                            <ul id="newsB" class="list-group mylist list_n">
                                <!-- Lista vuota inizialmente -->
                            </ul>
                        </div>
                    </div>
                    <button  id="btnConfermaN" class="d-none my_btn_5 w-100" type="submit">
                        Modifica
                    </button>
                </form>
            </div>
            
        </div>
    </div>
</div>
<div class="modal fade" id="staticBackdropspecialStory" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="#staticBackdropspecialStory" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered my_modal_dialog">
        <div class="modal-content">
            <div class="modal-header">
            <h1 class=" fs-5" id="staticBackdropspecialStory">Riordina i tuoi Post in Storia (Chi siamo)</h1>
            <button type="button" class="btn-close" data-bs-target="#staticBackdropspecial" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body body">
                <form action="{{ route('admin.categories.neworder') }}" method="post" >
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <h4 class="text-center">Post in story</h4>
                            <ul id="storyA" class="list-group mylist">
                                @foreach($story as $oggetto)
                                    <li class="list-group-item list_s" data-id="{{ $oggetto['id'] }}">
                                        <input value="{{$oggetto->id}}" type="hidden" name="new_order[]"> {{ $oggetto->title }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    
                    
                        <div class="col-md-6">
                            <h4 class="text-center">Ordine corretto</h4>
                            <ul id="storyB" class="list-group mylist list_s">
                                <!-- Lista vuota inizialmente -->
                            </ul>
                        </div>
                    </div>
                    <button  id="btnConfermaS" class="d-none my_btn_5 w-100" type="submit">
                        Modifica
                    </button>
                </form>
            </div>
            
        </div>
    </div>
</div>


<script defer>
document.addEventListener("DOMContentLoaded", function () {

    let storyA = document.getElementById("storyA");
    let storyB = document.getElementById("storyB");
    let newsA = document.getElementById("newsA");
    let newsB = document.getElementById("newsB");
    let btnConfermaS = document.getElementById("btnConfermaS");
    let btnConfermaN = document.getElementById("btnConfermaN");

    function spostaElemento(elemento, destinazione, list) {
        destinazione.appendChild(elemento);
        controllaBottone(list);
    }

    function controllaBottone(list) {
        // Mostra il bottone solo se listaB è vuota
        if(list == 'story'){
            if (storyA.children.length === 0) {
                btnConfermaS.classList.remove("d-none");
            } else {
                btnConfermaS.classList.add("d-none");
            }
        }else{
            if (newsA.children.length === 0) {
                btnConfermaN.classList.remove("d-none");
            } else {
                btnConfermaN.classList.add("d-none");
            }
        }
    }
    document.body.addEventListener("click", function (e) {
        if (e.target.classList.contains("list_s")) {
            const elemento = e.target;
            if (elemento.parentElement.id === "storyA") {
                spostaElemento(elemento, storyB, 'story');
            } else {
                spostaElemento(elemento, storyA, 'story');
            }
            
        }else if(e.target.classList.contains("list_n")){
            const elemento = e.target;
            if (elemento.parentElement.id === "newsA") {
                spostaElemento(elemento, newsB, 'news');
            } else {
                spostaElemento(elemento, newsA, 'news');
            }
        }
    });
});
</script>
@endsection