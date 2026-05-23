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
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('admin.common.close') }}"></button>
</div>
@endif
 
<div class="dash_page">
    <header class="menu-dashboard__hero order-detail__summary">
        <div class="order-detail__meta">
            <div class="order-detail__status">
                <span class="order-detail__status-icon order-detail__status-icon--active">
                    <i class="bi bi-archive-fill"></i>
                </span>
                <strong>{{ __('admin.content.contents') }}</strong>
            </div>
            <h1 class="menu-dashboard__title">{{ __('admin.content.archived_title') }}</h1>
        </div>
        <div class="menu-dashboard__hero-actions dashboard-home__hero-actions">
            <a href="{{ route('admin.posts.index') }}" class="order-detail__contact">
                <i class="bi bi-arrow-left"></i>
                <span>{{ __('admin.content.contents') }}</span>
            </a>
        </div>
    </header>
    <div class="filters">
        <div class="bar">
            <input type="checkbox" class="check" id="f">
            <div class="box">
                <input type="text" id="searchInput" class="search" placeholder="{{ __('admin.posts.search_placeholder') }}" >
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
                        <i class="bi bi-image-fill"></i>
                    </div>
                @endif 
                <div class="name_cat">
                    <div class="name">{{$item->title}}</div>
                    <div class="cat">{{$item->path}}</div>
                </div>
                <div class="price_btn"> 
                    @if ($item->link)
                        <a class="link" href="{{$item->link}}">
                        <i class="bi bi-link-45deg" style="font-size: var(--fs-400)"></i>
                        {{ __('admin.Link') }}
                    </a>
                    @endif
                    <button type="button" class="action_menu action_menu_info" data-bs-toggle="modal" data-bs-target="#exampleModal{{$item->id}}">
                        <i class="bi bi-info-circle-fill" style="font-size: var(--fs-400)"></i>
                        {{ __('admin.Info') }}
                    </button>
                    <!-- Modal -->
                    <div class="modal fade" id="exampleModal{{$item->id}}" tabindex="-1" aria-labelledby="exampleModal{{$item->id}}Label" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-body">
                                    <button type="button" class="btn_close" data-bs-dismiss="modal">
                                        <i class="bi bi-x-circle-fill" style="font-size: var(--fs-400)"></i>
                                        {{__('admin.Chiudi')}}
                                    </button>
                                    <div class="action_top">
                                        <form action="{{ route('admin.posts.status') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="archive" value="1">
                                            <input type="hidden" name="v" value="0">
                                            <input type="hidden" name="a" value="1">
                                            <input type="hidden" name="id" value="{{$item->id}}">
                                            <button class="edit" type="submit">
                                                <i class="bi bi-cloud-plus-fill" style="font-size: var(--fs-400)"></i>
                                                {{ __('admin.Ripristina') }}
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.posts.destroy', $item) }}" method="POST">
                                            @method('DELETE')
                                            @csrf
                                            <button class="edit btn_delete" type="submit">
                                                <i class="bi bi-trash-fill" style="font-size: var(--fs-400)"></i>
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
                                                <i class="bi bi-card-text" style="font-size: var(--fs-400)"></i>
                                                {{__('admin.Descrizione')}}</h4>
                                            <p>{{$item->description}}</p>
                                        </section>
                                    @endif
                                    @if ($item->hashtag)
                                        <section>
                                            <h4>
                                                <strong>#</strong>
                                                {{ __('admin.posts.hashtags') }}</h4>
                                            <p>{{$item->hashtag}}</p>
                                        </section>
                                    @endif
                                    @if ($item->link)
                                        <section>
                                            <h4>
                                                <i class="bi bi-link-45deg" style="font-size: var(--fs-400)"></i>
                                                {{ __('admin.Link') }}</h4>
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
</div>

@endsection
