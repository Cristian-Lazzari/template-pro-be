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
    <h1>
        <i class="bi bi-fork-knife" style="font-size: var(--fs-500)"></i>
        Menu
    </h1>
    
    <div class="action-page">
        <a class="my_btn_1 create m-1 w-auto" href="{{ route('admin.menus.create') }}">
            <i class="bi bi-cloud-plus-fill" style="font-size: var(--fs-400)"></i>

            {{__('admin.Crea_nuovo')}}
        </a>
    </div>
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
                        <h5 class="old_price">€{{$item->old_price / 100}}</h5>
                        @endif
                        <h5 class="price">€{{$item->price / 100}}</h5>
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
                                                    <span class="ext_p">+ € {{$p->pivot->extra_price / 100}}</span>
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
                        <h5 class="old_price">€{{$item->old_price / 100}}</h5>
                        @endif
                        <h5 class="price">€{{$item->price / 100}}</h5>
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