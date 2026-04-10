@forelse ($products as $item)
    <div class="res-item @if(!$item->visible) not_v @endif prod" data-product-id="{{$item->id}}">
        @if (isset($item->image))
            <button type="button" class="image_btn preview-image" data-bs-toggle="modal" data-bs-target="#imageModal" data-image-src="{{ asset('public/storage/' . $item->image) }}" data-image-alt="{{$item->display_name}}">
                <img src="{{ asset('public/storage/' . $item->image) }}" alt="{{$item->display_name}}" loading="lazy" decoding="async">
            </button>
        @else
            <div class="no_img">
                <i class="bi bi-image-fill" style="font-size: 16px"></i>
            </div>
        @endif

        <div class="name_cat">
            <div class="name">{{$item->display_name}}</div>
            <div class="cat">{{$item->category_name}}</div>
        </div>

        <div class="price_btn">
            <div class="price">€{{$item->price / 100}}</div>
            <button type="button" class="action_menu action_menu_info js-open-product-info" data-bs-toggle="modal" data-bs-target="#productInfoModal" data-info-url="{{ route('admin.products.quick-view', $item->id) }}">
                <i class="bi bi-info-circle-fill" style="font-size: 20px"></i>{{ __('admin.Info') }}
            </button>
        </div>
    </div>
@empty
    <div class="res-item prod">
        <div class="name_cat">
            <div class="name">Nessun prodotto trovato</div>
        </div>
    </div>
@endforelse
