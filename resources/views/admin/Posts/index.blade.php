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
        <i class="bi bi-images"></i>
        Contenuti multimediali 
    </h1>

    <div class="action-page">
        <a class="my_btn_1 create m-1 w-auto" href="{{ route('admin.posts.create') }}">
            <i class="bi bi-cloud-plus-fill" style="font-size: var(--fs-400)"></i>
            {{__('admin.Crea_nuovo')}}</a>
        <a class="my_btn_2 btn_delete trash m-1 w-auto" href="{{ route('admin.posts.archived') }}">
            <i class="bi bi-trash-fill" style="font-size: var(--fs-400)"></i>
            {{__('admin.Archivio')}}</a>
    </div>
    <div class="filters">
        <div class="bar">
            <input type="checkbox" class="check" id="f">
            <div class="box">
                <input type="text" id="searchInput" class="search" placeholder="Cerca prodotto..." >
                <button id="typeToggle" class="type">{{__('admin.Tutti')}}</button>
                <button id="sortToggle" class="order">
                    <i class="bi bi-sort-down-alt"></i>
                </button>
            </div>
            <label for="f">
                <i class="bi bi-funnel-fill"></i>
                <i class="bi bi-funnel"></i>
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
            <i class="bi bi-shuffle" style="font-size: var(--fs-400)"></i>{{ __('admin.Ordina_Post_in_Storia') }}</button>
        <button type="button" class="my_btn_2" data-bs-toggle="modal" data-bs-target="#staticBackdropspecialNews">
            <i class="bi bi-shuffle" style="font-size: var(--fs-400)"></i>{{ __('admin.Ordina_Post_in_News') }}</button>
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

    const iconRecent = `<i class="bi bi-sort-down-alt"></i>`;
    const iconAlpha = `<i class="bi bi-sort-alpha-down"></i>`;

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