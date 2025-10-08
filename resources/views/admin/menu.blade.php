@extends('layouts.base')

@section('contents')
<div class="dash_page">
 
    <h1>
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-fork-knife" viewBox="0 0 16 16">
            <path d="M13 .5c0-.276-.226-.506-.498-.465-1.703.257-2.94 2.012-3 8.462a.5.5 0 0 0 .498.5c.56.01 1 .13 1 1.003v5.5a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5zM4.25 0a.25.25 0 0 1 .25.25v5.122a.128.128 0 0 0 .256.006l.233-5.14A.25.25 0 0 1 5.24 0h.522a.25.25 0 0 1 .25.238l.233 5.14a.128.128 0 0 0 .256-.006V.25A.25.25 0 0 1 6.75 0h.29a.5.5 0 0 1 .498.458l.423 5.07a1.69 1.69 0 0 1-1.059 1.711l-.053.022a.92.92 0 0 0-.58.884L6.47 15a.971.971 0 1 1-1.942 0l.202-6.855a.92.92 0 0 0-.58-.884l-.053-.022a1.69 1.69 0 0 1-1.059-1.712L3.462.458A.5.5 0 0 1 3.96 0z"/>
        </svg>
        Prodotti e Menu
    </h1>
    <div class="top_action">
        <a href="{{ route('admin.products.index') }}" class="my_btn_3">Prodotti</a>
        <a href="{{ route('admin.ingredients.index') }}" class="my_btn_3">Ingredienti</a>
        <a href="{{ route('admin.categories.index') }}" class="my_btn_3">Categorie</a>
        <a href="{{ route('admin.menus.index') }}" class="my_btn_3">Menu</a>
    </div>
    @if (count($products)) 
    <div class="promo_cont">
        <h3>Prodotti in evidenza</h3>
        <div class="cont">
            @foreach ($products as $p)
                <div class="promo_item">
                    <div class="title">{{$p->name}}</div>
                    <div class="cat">{{$p->category->name}}</div>
                    @if (isset($p->image))
                        <img src="{{ asset('public/storage/' . $p->image) }}" alt="{{$p->title}}">
                    @else
                        <img src="https://future-plus.it/img/favicon.png" alt="{{$p->title }}">
                    @endif 
                    @if ($p->description)<h4>Descrizione:</h4> <div class="description">{{$p->description}}</div> @endif
                    
                    @if (count($p->ingredients)) 
                    <div class="ingredients">
                        <h4>Ingredienti</h4>
                        @foreach ($p->ingredients as $ingredient)     
                            {{ $ingredient->name }}{{ !$loop->last ? ', ' : '.' }}
                        @endforeach
                    </div> 
                    @endif
                    <div class="price">€{{$p->price / 100}}</div>
                </div>
            @endforeach
        </div>
    </div>
    @endif
    @if (count($menus)) 
    <div class="promo_cont">
        <h3>Menu in evidenza</h3>
        @foreach ($menus as $item)
            <div class="promo_item">
                <img src="" alt="">
                <div class="title">{{$item->name}}</div>
                @if ($item->description) <div class="description">{{$item->description}}</div> @endif
                @if ($item->ingredients) 
                    <div class="ingredients">
                         @foreach ($item->products as $prod)     
                            {{ $prod->name }}{{ !$loop->last ? ', ' : '.' }}
                        @endforeach
                    </div> 
                @endif
                <div class="price">€{{$item->price / 100}}</div>
            </div>
            <div class="menu">
                <div class="m_card">
                    <div class="title">{{$item->name}}</div>
                    <div class="cat">{{$item->category->name}}</div>
                    @if (isset($item->image))
                        <img class="img_m" src="{{ asset('public/storage/' . $item->image) }}" alt="{{$item->title}}">
                    @endif
                    @if (isset($p->description))
                        <p class="desc">{{$item->description}}</p>
                    @endif
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
                    
                    <div class="prices">
                        @if ($item->old_price)
                        <h5 class="old_price">€{{$item->old_price / 100}}</h5>
                        @endif
                        <h5 class="price">€{{$item->price / 100}}</h5>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    @endif
</div>


@endsection
