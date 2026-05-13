@extends('layouts.base')



@section('contents')

@if (session('success'))
    @php
        $data = session('success')
    @endphp
   <div class="alert alert-info alert-dismissible fade show" role="alert">
    {{ $data }} 
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
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
    <header class="menu-dashboard__hero order-detail__summary">
        <div class="order-detail__meta">
            <div class="order-detail__status">
                <span class="order-detail__status-icon order-detail__status-icon--active">
                    <i class="bi bi-card-checklist"></i>
                </span>
                <strong>{{ __('admin.Menu') }}</strong>
            </div>
            <h1 class="menu-dashboard__title">{{ __('admin.Menu') }}</h1>
        </div>
        <div class="menu-dashboard__hero-actions dashboard-home__hero-actions">
            <a href="{{ route('admin.menus.create') }}" class="order-detail__contact">
                <i class="bi bi-cloud-plus-fill"></i>
                <span>{{ __('admin.Crea_nuovo') }}</span>
            </a>
        </div>
    </header>
    <div class="menu-container">
        @if ($fix->count() == 0)
            <div class="alert alert-warning">
                {{__('admin.no_menuf')}}
            </div>
            
        @else
            <h2>{{ __('admin.Menu_fissi') }}</h2>
        @endif
        @foreach ($fix as $item)
    
            <div class="menu  @if (!$item->visible) not_v @endif" onclick="window.location.href='{{ route('admin.posts.show', $item->id) }}">
                <div class="m_card">
                    <div class="top">
                        <h2>{{$item->name}}</h2>
                        @if (isset($item->image))
                            <img class="img_m" src="{{ asset('public/storage/' . $item->image) }}" alt="{{$item->title}}">
                        @endif
                    </div>
                    <div class="products">
                        @foreach ($item->products as $p)
                            <div class="product">
                                @if (isset($p->image))
                                    <img class="img_p" src="{{ asset('public/storage/' . $p->image) }}" alt="{{$item->title}}">
                                @endif
                                <span>{{$p->name}}</span>
                                <span class="cat">{{$p->category->name}}</span>
                            </div>
                        @endforeach
                    </div>
    
                    @if (isset($p->description))
                        <p class="desc">{{$item->description}}</p>
                    @endif
                    <div class="prices">
                        @if ($item->old_price)
                        <h5 class="old_price">{{ \App\Support\Currency::formatCents($item->old_price) }}</h5>
                        @endif
                        <h5 class="price">{{ \App\Support\Currency::formatCents($item->price) }}</h5>
                    </div>
                </div>
                <div class="actions menus-actions">
                    <form action="{{ route('admin.menus.destroy', ['menu'=>$item]) }}" method="post" >
                        @method('delete')
                        @csrf
                        <input type="hidden" name="f" value="1">
                        <button class="my_btn_2 bg-danger" type="submit">
                            <i style="vertical-align: sub; font-size: var(--fs-400)" class="bi bi-x-circle"></i>
                        </button>
                    </form>
                    <a class="my_btn_1 m" href="{{ route('admin.menus.edit', $item) }}">
                        <i style="vertical-align: sub; font-size: var(--fs-400)" class="bi bi-pencil-square"></i>
                    </a>
                </div>
    
            </div>
        @endforeach
    </div>
    <div class="menu-container">
        @if ($combo->count() == 0)
            <div class="alert alert-warning">
                {{__('admin.no_menu')}}
            </div>
            
        @else
            <h2 class="my-3">{{ __('admin.Combo_prodotti') }}</h2>
        @endif
        @foreach ($combo as $item)
    
            <div class="menu  @if (!$item->visible) not_v @endif" onclick="window.location.href='{{ route('admin.posts.show', $item->id) }}">
                <div class="m_card">
                    <div class="top">
                        <h2>{{$item->name}}</h2>
                        <p>{{$item->category->name}}</p>
                        @if (isset($item->image))
                            <img class="img_m" src="{{ asset('public/storage/' . $item->image) }}" alt="{{$item->title}}">
                        @endif
                    </div>
                    @if($item->fixed_menu == 1)
                        <div class="products">
                            @foreach ($item->products as $p)
                                <div class="product">
                                    @if (isset($p->image))
                                        <img class="img_p" src="{{ asset('public/storage/' . $p->image) }}" alt="{{$item->title}}">
                                    @endif
                                    <span>{{$p->name}}</span>
                                    <span class="cat">{{$p->category->name}}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="scelte">
                            @foreach ($item->fixed_menu as $key => $c)
                                <div class="scelta">
                                    <h5>{{$key}}</h5>
                                    <div class="cont">
                                        @foreach ($c as $p)
                                            <div class="product">
                                                @if (isset($p->image))
                                                    <img class="img_p" src="{{ asset('public/storage/' . $p->image) }}" alt="{{$item->title}}">
                                                @endif
                                                <span class="name">{{$p->name}}</span>
                                                @if ($p->pivot->extra_price)
                                                    <span class="ext_p">+ {{ \App\Support\Currency::formatCents($p->pivot->extra_price) }}</span>
                                                @endif
                                                <span class="cat">{{$p->category->name}}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                    @if (isset($p->description))
                        <p class="desc">{{$item->description}}</p>
                    @endif
                    <div class="prices">
                        @if ($item->old_price)
                        <h5 class="old_price">{{ \App\Support\Currency::formatCents($item->old_price) }}</h5>
                        @endif
                        <h5 class="price">{{ \App\Support\Currency::formatCents($item->price) }}</h5>
                    </div>
                </div>
                <div class="actions menus-actions">
                    <form action="{{ route('admin.menus.destroy', ['menu'=>$item]) }}" method="post" >
                        @method('delete')
                        @csrf
                        <input type="hidden" name="f" value="1">
                        <button class="my_btn_2 bg-danger" type="submit">
                            <i style="vertical-align: sub; font-size: var(--fs-400)" class="bi bi-x-circle"></i>
                        </button>
                    </form>
                    <a class="my_btn_1 m" href="{{ route('admin.menus.edit', $item) }}">
                        <i style="vertical-align: sub; font-size: var(--fs-400)" class="bi bi-pencil-square"></i>
                    </a>
                </div>
    
            </div>
        @endforeach
    </div>
</div>


@endsection
