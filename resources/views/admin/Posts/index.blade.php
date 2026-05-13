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
    <header class="menu-dashboard__hero order-detail__summary">
        <div class="order-detail__meta">
            <div class="order-detail__status">
                <span class="order-detail__status-icon order-detail__status-icon--active">
                    <i class="bi bi-images"></i>
                </span>
                <strong>Contenuti</strong>
            </div>
            <h1 class="menu-dashboard__title">Contenuti multimediali</h1>
        </div>
        <div class="menu-dashboard__hero-actions dashboard-home__hero-actions">
            <a href="{{ route('admin.posts.archived') }}" class="order-detail__contact">
                <i class="bi bi-archive-fill"></i>
                <span>{{ __('admin.Archivio') }}</span>
            </a>
            <a href="{{ route('admin.posts.create') }}" class="order-detail__contact">
                <i class="bi bi-cloud-plus-fill"></i>
                <span>{{ __('admin.Crea_nuovo') }}</span>
            </a>
        </div>
    </header>
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
<div class="modal fade" id="staticBackdropspecialNews" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropspecialNews" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered my_modal_dialog catalog-reorder-dialog">
        <form action="{{ route('admin.posts.neworder') }}" method="post" class="w-100">
            @csrf
            <x-dashboard.action-modal
                title-id="staticBackdropspecialNews"
                title="{{ __('admin.Riordina_i_tuoi_Post_in_News_Noviteventi') }}"
                eyebrow="News"
                tone="warning"
            >
                <x-dashboard.reorder-list
                    :items="$news"
                    input-name="new_order[]"
                    label-field="title"
                    item-label="post news"
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
<div class="modal fade" id="staticBackdropspecialStory" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropspecialStory" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered my_modal_dialog catalog-reorder-dialog">
        <form action="{{ route('admin.posts.neworder') }}" method="post" class="w-100">
            @csrf
            <x-dashboard.action-modal
                title-id="staticBackdropspecialStory"
                title="{{ __('admin.Riordina_i_tuoi_Post_in_Storia_Chi_siamo') }}"
                eyebrow="Story"
                tone="warning"
            >
                <x-dashboard.reorder-list
                    :items="$story"
                    input-name="new_order[]"
                    label-field="title"
                    item-label="post story"
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


<script defer>
document.addEventListener("DOMContentLoaded", function () {

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
