@forelse ($posts as $item)
    <div class="res-item @if(!$item->visible) not_v @endif prod">
        @if (isset($item->image))
            <button type="button" class="image_btn preview-image" data-bs-toggle="modal" data-bs-target="#imageModal" data-image-src="{{ asset('public/storage/' . $item->image) }}" data-image-alt="{{$item->title}}">
                <img src="{{ asset('public/storage/' . $item->image) }}" alt="{{$item->title}}" loading="lazy" decoding="async">
            </button>
        @else
            <div class="no_img">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-image-fill" viewBox="0 0 16 16">
                    <path d="M.002 3a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2h-12a2 2 0 0 1-2-2zm1 9v1a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V9.5l-3.777-1.947a.5.5 0 0 0-.577.093l-3.71 3.71-2.66-1.772a.5.5 0 0 0-.63.062zm5-6.5a1.5 1.5 0 1 0-3 0 1.5 1.5 0 0 0 3 0"/>
                </svg>
            </div>
        @endif

        <div class="name_cat">
            <div class="name">{{$item->title}}</div>
            <div class="cat" data-path="{{$item->path}}">{{$item->path}}</div>
        </div>

        <div class="price_btn">
            @if ($item->link)
                <a class="link" href="{{$item->link}}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-link-45deg" viewBox="0 0 16 16">
                        <path d="M4.715 6.542 3.343 7.914a3 3 0 1 0 4.243 4.243l1.828-1.829A3 3 0 0 0 8.586 5.5L8 6.086a1 1 0 0 0-.154.199 2 2 0 0 1 .861 3.337L6.88 11.45a2 2 0 1 1-2.83-2.83l.793-.792a4 4 0 0 1-.128-1.287z"/>
                        <path d="M6.586 4.672A3 3 0 0 0 7.414 9.5l.775-.776a2 2 0 0 1-.896-3.346L9.12 3.55a2 2 0 1 1 2.83 2.83l-.793.792c.112.42.155.855.128 1.287l1.372-1.372a3 3 0 1 0-4.243-4.243z"/>
                    </svg>
                    {{__('admin.Link')}}
                </a>
            @endif

            <button type="button" class="action_menu action_menu_info js-open-post-info" data-bs-toggle="modal" data-bs-target="#postInfoModal" data-info-url="{{ route('admin.posts.quick-view', $item->id) }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-info-circle-fill" viewBox="0 0 16 16">
                    <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16m.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.47l-.451-.081.082-.381 2.29-.287zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2"/>
                </svg>
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
