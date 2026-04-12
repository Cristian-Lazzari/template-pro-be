@forelse ($posts as $item)
    <div class="res-item @if(!$item->visible) not_v @endif prod">
        @if (isset($item->image))
            <button type="button" class="image_btn preview-image" data-bs-toggle="modal" data-bs-target="#imageModal" data-image-src="{{ asset('public/storage/' . $item->image) }}" data-image-alt="{{$item->title}}">
                <img src="{{ asset('public/storage/' . $item->image) }}" alt="{{$item->title}}" loading="lazy" decoding="async">
            </button>
        @else
            <div class="no_img">
                <i class="bi bi-image-fill"></i>
            </div>
        @endif

        <div class="name_cat">
            <div class="name">{{$item->title}}</div>
            <div class="cat" data-path="{{$item->path}}">{{$item->path}}</div>
        </div>

        <div class="price_btn">
            @if ($item->link)
                <a class="link" href="{{$item->link}}">
                    <i class="bi bi-link-45deg" style="font-size: 20px"></i>
                    {{__('admin.Link')}}
                </a>
            @endif

            <button type="button" class="action_menu action_menu_info js-open-post-info" data-bs-toggle="modal" data-bs-target="#postInfoModal" data-info-url="{{ route('admin.posts.quick-view', $item->id) }}">
                <i class="bi bi-info-circle-fill" style="font-size: 20px"></i>
                {{__('admin.Info')}}
            </button>
        </div>
    </div>
@empty
    <div class="res-item prod">
        <div class="name_cat">
            <div class="name">Nessun post trovato</div>
        </div>
    </div>
@endforelse
