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
       {{ __('admin.deleted_successfully', ['name' => $data->title]) }}
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
        <a class="my_btn_1 create m-1 w-auto" href="{{ route('admin.posts.create') }}">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-cloud-plus-fill" viewBox="0 0 16 16">
                <path d="M8 2a5.53 5.53 0 0 0-3.594 1.342c-.766.66-1.321 1.52-1.464 2.383C1.266 6.095 0 7.555 0 9.318 0 11.366 1.708 13 3.781 13h8.906C14.502 13 16 11.57 16 9.773c0-1.636-1.242-2.969-2.834-3.194C12.923 3.999 10.69 2 8 2m.5 4v1.5H10a.5.5 0 0 1 0 1H8.5V10a.5.5 0 0 1-1 0V8.5H6a.5.5 0 0 1 0-1h1.5V6a.5.5 0 0 1 1 0"/>
                </svg>
            {{__('admin.Crea_nuovo')}}</a>
        <a class="my_btn_2 btn_delete trash m-1 w-auto" href="{{ route('admin.posts.archived') }}">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-trash-fill" viewBox="0 0 16 16">
                <path d="M2.5 1a1 1 0 0 0-1 1v1a1 1 0 0 0 1 1H3v9a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V4h.5a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H10a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1zm3 4a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 .5-.5M8 5a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7A.5.5 0 0 1 8 5m3 .5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 1 0"/>
                </svg>
            {{__('admin.Archivio')}}</a>
    </div>
    <div class="filters">
        <div class="bar">
            <input type="checkbox" class="check" id="f">
            <div class="box">
                <input type="text" id="searchInput" class="search" placeholder="Cerca prodotto..." >
                <button id="typeToggle" class="type">{{__('admin.Tutti')}}</button>
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
        <div id="postsList">
            @include('admin.Posts.partials.index_cards', ['posts' => $posts])
        </div>

        <div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body image_modal p-0">
                        <img id="dynamicPreviewImage" src="" alt="" loading="lazy" decoding="async">
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="postInfoModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body" id="postInfoModalBody">
                        <div class="text-center py-4">Caricamento...</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-center mt-3" id="postsPagination">
        {{ $posts->links() }}
    </div>


    <div class="action-page mt-5">
        <button type="button" class="my_btn_3" data-bs-toggle="modal" data-bs-target="#staticBackdropspecialStory">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-shuffle" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M0 3.5A.5.5 0 0 1 .5 3H1c2.202 0 3.827 1.24 4.874 2.418.49.552.865 1.102 1.126 1.532.26-.43.636-.98 1.126-1.532C9.173 4.24 10.798 3 13 3v1c-1.798 0-3.173 1.01-4.126 2.082A9.6 9.6 0 0 0 7.556 8a9.6 9.6 0 0 0 1.317 1.918C9.828 10.99 11.204 12 13 12v1c-2.202 0-3.827-1.24-4.874-2.418A10.6 10.6 0 0 1 7 9.05c-.26.43-.636.98-1.126 1.532C4.827 11.76 3.202 13 1 13H.5a.5.5 0 0 1 0-1H1c1.798 0 3.173-1.01 4.126-2.082A9.6 9.6 0 0 0 6.444 8a9.6 9.6 0 0 0-1.317-1.918C4.172 5.01 2.796 4 1 4H.5a.5.5 0 0 1-.5-.5"/>
                <path d="M13 5.466V1.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384l-2.36 1.966a.25.25 0 0 1-.41-.192m0 9v-3.932a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384l-2.36 1.966a.25.25 0 0 1-.41-.192"/>
            </svg>{{ __('admin.Ordina_Post_in_Storia') }}</button>
        <button type="button" class="my_btn_2" data-bs-toggle="modal" data-bs-target="#staticBackdropspecialNews">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-shuffle" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M0 3.5A.5.5 0 0 1 .5 3H1c2.202 0 3.827 1.24 4.874 2.418.49.552.865 1.102 1.126 1.532.26-.43.636-.98 1.126-1.532C9.173 4.24 10.798 3 13 3v1c-1.798 0-3.173 1.01-4.126 2.082A9.6 9.6 0 0 0 7.556 8a9.6 9.6 0 0 0 1.317 1.918C9.828 10.99 11.204 12 13 12v1c-2.202 0-3.827-1.24-4.874-2.418A10.6 10.6 0 0 1 7 9.05c-.26.43-.636.98-1.126 1.532C4.827 11.76 3.202 13 1 13H.5a.5.5 0 0 1 0-1H1c1.798 0 3.173-1.01 4.126-2.082A9.6 9.6 0 0 0 6.444 8a9.6 9.6 0 0 0-1.317-1.918C4.172 5.01 2.796 4 1 4H.5a.5.5 0 0 1-.5-.5"/>
                <path d="M13 5.466V1.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384l-2.36 1.966a.25.25 0 0 1-.41-.192m0 9v-3.932a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384l-2.36 1.966a.25.25 0 0 1-.41-.192"/>
            </svg>{{ __('admin.Ordina_Post_in_News') }}</button>
    </div>
</div>



    <!-- Modal ORDINA LE CATEGORIE -->
<div class="modal fade" id="staticBackdropspecialNews" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="#staticBackdropspecialNews" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered my_modal_dialog">
        <div class="modal-content">
            <div class="modal-header">
            <h1 class=" fs-5" id="staticBackdropspecialNews">{{ __('admin.Riordina_i_tuoi_Post_in_News_Noviteventi') }}</h1>
            <button type="button" class="btn-close" data-bs-target="#staticBackdropspecialNews" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body body">
                <form action="{{ route('admin.posts.neworder') }}" method="post" >
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <h4 class="text-center">{{ __('admin.Post_in_News') }}</h4>
                            <ul id="newsA" class="list-group mylist">
                                @foreach($news as $oggetto)
                                    <li class="list-group-item list_n" data-id="{{ $oggetto['id'] }}">
                                        <input value="{{$oggetto->id}}" type="hidden" name="new_order[]"> {{ $oggetto->title }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    
                    
                        <div class="col-md-6">
                            <h4 class="text-center">{{ __('admin.Ordine_corretto') }}</h4>
                            <ul id="newsB" class="list-group mylist list_n">
                                <!-- Lista vuota inizialmente -->
                            </ul>
                        </div>
                    </div>
                    <button  id="btnConfermaN" class="d-none my_btn_5 w-100" type="submit">{{ __('admin.Modifica') }}</button>
                </form>
            </div>
            
        </div>
    </div>
</div>
<div class="modal fade" id="staticBackdropspecialStory" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="#staticBackdropspecialStory" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered my_modal_dialog">
        <div class="modal-content">
            <div class="modal-header">
            <h1 class=" fs-5" id="staticBackdropspecialStory">{{ __('admin.Riordina_i_tuoi_Post_in_Storia_Chi_siamo') }}</h1>
            <button type="button" class="btn-close" data-bs-target="#staticBackdropspecial" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body body">
                <form action="{{ route('admin.categories.neworder') }}" method="post" >
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <h4 class="text-center">{{ __('admin.Post_in_story') }}</h4>
                            <ul id="storyA" class="list-group mylist">
                                @foreach($story as $oggetto)
                                    <li class="list-group-item list_s" data-id="{{ $oggetto['id'] }}">
                                        <input value="{{$oggetto->id}}" type="hidden" name="new_order[]"> {{ $oggetto->title }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    
                    
                        <div class="col-md-6">
                            <h4 class="text-center">{{ __('admin.Ordine_corretto') }}</h4>
                            <ul id="storyB" class="list-group mylist list_s">
                                <!-- Lista vuota inizialmente -->
                            </ul>
                        </div>
                    </div>
                    <button  id="btnConfermaS" class="d-none my_btn_5 w-100" type="submit">{{ __('admin.Modifica') }}</button>
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

    const searchInput = document.getElementById('searchInput');
    const typeToggle = document.getElementById('typeToggle');
    const sortToggle = document.getElementById('sortToggle');
    const postContainer = document.getElementById('postsList');
    const pagination = document.getElementById('postsPagination');
    const dynamicPreviewImage = document.getElementById('dynamicPreviewImage');
    const postInfoModalBody = document.getElementById('postInfoModalBody');
    const defaultListHtml = postContainer.innerHTML;
    const searchUrlBase = "{{ route('admin.posts.search') }}";

    let currentType = 'all';
    let sortMode = 'recent';

    const iconRecent = `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-sort-down-alt" viewBox="0 0 16 16"><path d="M3.5 3.5a.5.5 0 0 0-1 0v8.793l-1.146-1.147a.5.5 0 0 0-.708.708l2 1.999.007.007a.497.497 0 0 0 .7-.006l2-2a.5.5 0 0 0-.707-.708L3.5 12.293zm4 .5a.5.5 0 0 1 0-1h1a.5.5 0 0 1 0 1zm0 3a.5.5 0 0 1 0-1h3a.5.5 0 0 1 0 1zm0 3a.5.5 0 0 1 0-1h5a.5.5 0 0 1 0 1zM7 12.5a.5.5 0 0 0 .5.5h7a.5.5 0 0 0 0-1h-7a.5.5 0 0 0-.5.5"/></svg>`;
    const iconAlpha = `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-sort-alpha-down" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M10.082 5.629 9.664 7H8.598l1.789-5.332h1.234L13.402 7h-1.12l-.419-1.371zm1.57-.785L11 2.687h-.047l-.652 2.157z"/><path d="M12.96 14H9.028v-.691l2.579-3.72v-.054H9.098v-.867h3.785v.691l-2.567 3.72v.054h2.645zM4.5 2.5a.5.5 0 0 0-1 0v9.793l-1.146-1.147a.5.5 0 0 0-.708.708l2 1.999.007.007a.497.497 0 0 0 .7-.006l2-2a.5.5 0 0 0-.707-.708L4.5 12.293z"/></svg>`;

    function bindDynamicEvents() {
        postContainer.querySelectorAll('.preview-image').forEach(button => {
            button.addEventListener('click', () => {
                dynamicPreviewImage.src = button.dataset.imageSrc;
                dynamicPreviewImage.alt = button.dataset.imageAlt || '';
            });
        });

        postContainer.querySelectorAll('.js-open-post-info').forEach(button => {
            button.addEventListener('click', async () => {
                const url = button.dataset.infoUrl;
                postInfoModalBody.innerHTML = '<div class="text-center py-4">Caricamento...</div>';

                try {
                    const response = await fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    if (!response.ok) {
                        throw new Error('Request failed');
                    }

                    postInfoModalBody.innerHTML = await response.text();
                } catch (error) {
                    postInfoModalBody.innerHTML = '<div class="text-center text-danger py-4">Errore nel caricamento dei dettagli.</div>';
                }
            });
        });
    }

    async function applyFiltersGlobal() {
        const search = searchInput.value.trim();
        const mustQueryServer = search !== '' || currentType !== 'all' || sortMode === 'alpha';

        if (!mustQueryServer) {
            postContainer.innerHTML = defaultListHtml;
            pagination.classList.remove('d-none');
            bindDynamicEvents();
            return;
        }

        const params = new URLSearchParams({
            q: search,
            type: currentType,
            sort: sortMode,
        });

        try {
            const response = await fetch(`${searchUrlBase}?${params.toString()}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });

            if (!response.ok) {
                throw new Error('Request failed');
            }

            const data = await response.json();
            postContainer.innerHTML = data.html;
            pagination.classList.add('d-none');
            bindDynamicEvents();
        } catch (error) {
            postContainer.innerHTML = '<div class="res-item prod"><div class="name_cat"><div class="name">Errore nel caricamento post</div></div></div>';
            pagination.classList.add('d-none');
        }
    }

    typeToggle.addEventListener('click', () => {
        if (currentType === 'all') {
            currentType = 'story';
            typeToggle.textContent = 'Story';
        } else if (currentType === 'story') {
            currentType = 'news';
            typeToggle.textContent = 'News';
        } else {
            currentType = 'all';
            typeToggle.textContent = "{{__('admin.Tutti')}}";
        }
        applyFiltersGlobal();
    });

    let searchDebounce;
    searchInput.addEventListener('input', () => {
        clearTimeout(searchDebounce);
        searchDebounce = setTimeout(applyFiltersGlobal, 220);
    });

    sortToggle.addEventListener('click', () => {
        sortMode = sortMode === 'recent' ? 'alpha' : 'recent';
        sortToggle.innerHTML = sortMode === 'recent' ? iconRecent : iconAlpha;
        applyFiltersGlobal();
    });

    sortToggle.innerHTML = iconRecent;
    bindDynamicEvents();
    
});
</script>
@endsection