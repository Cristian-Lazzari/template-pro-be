@extends('layouts.base')



@section('contents')

@if (session('success'))
    @php
        $data = session('success')
    @endphp
    <div class="alert alert-primary alert-dismissible fade show" role="alert">
        {{ $data }} 
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('admin.common.close') }}"></button>
    </div>
@endif
@if (session('delete_success'))
    @php
        $data = session('delete_success')
    @endphp
    <div class="alert alert-danger">
        {{ __('admin.deleted_successfully', ['name' => $data->name]) }}
    </div>
@endif
 
<div class="dash_page">

    <div id="productsAsyncAlerts"></div>

    <header class="menu-dashboard__hero order-detail__summary">
        <div class="order-detail__meta">
            <div class="order-detail__status">
                <span class="order-detail__status-icon order-detail__status-icon--active">
                    <i class="bi bi-fork-knife"></i>
                </span>
                <strong>{{ __('admin.Prodotti') }}</strong>
            </div>
            <h1 class="menu-dashboard__title">{{ __('admin.Prodotti') }}</h1>
        </div>
        <div class="menu-dashboard__hero-actions dashboard-home__hero-actions">
            <a href="{{ route('admin.products.archived') }}" class="order-detail__contact">
                <i class="bi bi-archive-fill"></i>
                <span>{{ __('admin.Archivio') }}</span>
            </a>
            <a href="{{ route('admin.products.create') }}" class="order-detail__contact">
                <i class="bi bi-cloud-plus-fill"></i>
                <span>{{ __('admin.Crea_nuovo') }}</span>
            </a>
        </div>
    </header>
    <div class="filters">
        <div class="bar">
            <input type="checkbox" class="check" id="f">
            <div class="box">
                <input type="text" id="searchInput" class="search" placeholder="{{__('admin.Cerca_prodotto')}}" >
                <select id="categorySelect" class="type">
                    <option value="all">{{__('admin.Tutti')}}</option>
                    @foreach ($categories as $category)
                        <option value="{{$category->id}}">{{$category->name}}</option>
                    @endforeach
                </select>
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
        <div id="productsList">
            @include('admin.Products.partials.index_cards', ['products' => $products])
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

        <div class="modal fade" id="productInfoModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body" id="productInfoModalBody">
                        <div class="text-center py-4">{{ __('admin.catalog.loading') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-center mt-3" id="productsPagination">
        {{ $products->links() }}
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('searchInput');
    const sortToggle = document.getElementById('sortToggle');
    const productContainer = document.getElementById('productsList');
    const pagination = document.getElementById('productsPagination');
    const categorySelect = document.getElementById('categorySelect');
    const dynamicPreviewImage = document.getElementById('dynamicPreviewImage');
    const infoModalBody = document.getElementById('productInfoModalBody');
    const productInfoModalElement = document.getElementById('productInfoModal');
    const asyncAlerts = document.getElementById('productsAsyncAlerts');
    const defaultListHtml = productContainer.innerHTML;
    const searchUrlBase = "{{ route('admin.products.search') }}";
    const i18n = {
        close: @json(__('admin.common.close')),
        loading: @json(__('admin.catalog.loading')),
        noProductsFound: @json(__('admin.catalog.no_products_found')),
        statusUpdated: @json(__('admin.catalog.status_updated')),
        statusUpdateError: @json(__('admin.products.status_update_error')),
        detailLoadError: @json(__('admin.catalog.detail_load_error')),
        productsLoadError: @json(__('admin.catalog.products_load_error')),
    };

    function showAsyncAlert(message, type = 'primary') {
        if (!asyncAlerts) {
            return;
        }

        asyncAlerts.innerHTML = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="${i18n.close}"></button>
            </div>
        `;
    }

    function ensureEmptyStateCard() {
        const cards = productContainer.querySelectorAll('.res-item.prod');
        if (cards.length === 0) {
            productContainer.innerHTML = `<div class="res-item prod"><div class="name_cat"><div class="name">${i18n.noProductsFound}</div></div></div>`;
        }
    }

    function bindModalStatusForms() {
        const forms = infoModalBody.querySelectorAll('.js-product-status-form');

        forms.forEach(form => {
            form.addEventListener('submit', async event => {
                event.preventDefault();

                const submitButton = form.querySelector('button[type="submit"]');
                if (submitButton) {
                    submitButton.disabled = true;
                }

                const formData = new FormData(form);
                const productId = String(formData.get('id') || '');

                try {
                    const response = await fetch(form.action, {
                        method: form.method || 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                    });

                    if (!response.ok) {
                        throw new Error('Request failed');
                    }

                    const data = await response.json();
                    const card = productContainer.querySelector(`.res-item.prod[data-product-id="${productId}"]`);

                    if (data.should_remove && card) {
                        card.remove();
                        ensureEmptyStateCard();
                    }

                    if (data.action === 'archived' && productInfoModalElement) {
                        if (window.bootstrap && window.bootstrap.Modal) {
                            const modal = window.bootstrap.Modal.getOrCreateInstance(productInfoModalElement);
                            modal.hide();
                        } else {
                            const closeBtn = infoModalBody.querySelector('.btn_close');
                            if (closeBtn) {
                                closeBtn.click();
                            }
                        }
                    }

                    if (card && data.product) {
                        card.classList.toggle('not_v', !data.product.visible);
                    }

                    const visibleToggleButton = infoModalBody.querySelector('.js-toggle-visible-btn');
                    if (visibleToggleButton && data.product) {
                        visibleToggleButton.classList.toggle('not', !data.product.visible);
                    }

                    showAsyncAlert(data.message || i18n.statusUpdated, 'primary');
                } catch (error) {
                    showAsyncAlert(i18n.statusUpdateError, 'danger');
                } finally {
                    if (submitButton) {
                        submitButton.disabled = false;
                    }
                }
            });
        });
    }

    // --- ORDINAMENTO ---
    let sortMode = 'recent';

    const iconRecent = `
        <i class="bi bi-sort-down-alt"></i>`;
    const iconAlpha = `
        <i class="bi bi-sort-alpha-down"></i>`;

    function bindDynamicEvents() {
        productContainer.querySelectorAll('.preview-image').forEach(button => {
            button.addEventListener('click', () => {
                dynamicPreviewImage.src = button.dataset.imageSrc;
                dynamicPreviewImage.alt = button.dataset.imageAlt || '';
            });
        });

        productContainer.querySelectorAll('.js-open-product-info').forEach(button => {
            button.addEventListener('click', async () => {
                const url = button.dataset.infoUrl;
                infoModalBody.innerHTML = `<div class="text-center py-4">${i18n.loading}</div>`;

                try {
                    const response = await fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    if (!response.ok) {
                        throw new Error('Request failed');
                    }

                    infoModalBody.innerHTML = await response.text();
                    bindModalStatusForms();
                } catch (error) {
                    infoModalBody.innerHTML = `<div class="text-center text-danger py-4">${i18n.detailLoadError}</div>`;
                }
            });
        });
    }

    async function filterAndRenderGlobal() {
        const searchValue = searchInput.value.trim();
        const selectedCategory = categorySelect.value;
        const mustQueryServer = searchValue !== '' || selectedCategory !== 'all' || sortMode === 'alpha';

        if (!mustQueryServer) {
            productContainer.innerHTML = defaultListHtml;
            pagination.classList.remove('d-none');
            bindDynamicEvents();
            return;
        }

        const params = new URLSearchParams({
            q: searchValue,
            category_id: selectedCategory,
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
            productContainer.innerHTML = data.html;
            pagination.classList.add('d-none');
            bindDynamicEvents();
        } catch (error) {
            productContainer.innerHTML = `<div class="res-item prod"><div class="name_cat"><div class="name">${i18n.productsLoadError}</div></div></div>`;
            pagination.classList.add('d-none');
        }
    }

    let searchDebounce;
    searchInput.addEventListener('input', () => {
        clearTimeout(searchDebounce);
        searchDebounce = setTimeout(filterAndRenderGlobal, 220);
    });
    categorySelect.addEventListener('change', filterAndRenderGlobal);
    sortToggle.addEventListener('click', () => {
        sortMode = sortMode === 'recent' ? 'alpha' : 'recent';
        sortToggle.innerHTML = sortMode === 'recent' ? iconRecent : iconAlpha;
        filterAndRenderGlobal();
    });

    sortToggle.innerHTML = iconRecent;
    bindDynamicEvents();
});

</script>



@endsection
