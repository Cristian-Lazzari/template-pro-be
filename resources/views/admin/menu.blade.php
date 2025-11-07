@extends('layouts.base')

@section('contents')
<div class="dash_page">
 
    <h1>
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-fork-knife" viewBox="0 0 16 16">
            <path d="M13 .5c0-.276-.226-.506-.498-.465-1.703.257-2.94 2.012-3 8.462a.5.5 0 0 0 .498.5c.56.01 1 .13 1 1.003v5.5a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5zM4.25 0a.25.25 0 0 1 .25.25v5.122a.128.128 0 0 0 .256.006l.233-5.14A.25.25 0 0 1 5.24 0h.522a.25.25 0 0 1 .25.238l.233 5.14a.128.128 0 0 0 .256-.006V.25A.25.25 0 0 1 6.75 0h.29a.5.5 0 0 1 .498.458l.423 5.07a1.69 1.69 0 0 1-1.059 1.711l-.053.022a.92.92 0 0 0-.58.884L6.47 15a.971.971 0 1 1-1.942 0l.202-6.855a.92.92 0 0 0-.58-.884l-.053-.022a1.69 1.69 0 0 1-1.059-1.712L3.462.458A.5.5 0 0 1 3.96 0z"/>
        </svg>
        Prodotti e Menu
    </h1>
    <div class="stat_menu">
        <div class="top">
            <div class="count">{{$stat['products']['tot']}}</div>
            <div class="title">Prodotti</div>
            <a href="{{ route('admin.products.index') }}" class="my_btn_1">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-ui-checks" viewBox="0 0 16 16">
                <path d="M7 2.5a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-7a.5.5 0 0 1-.5-.5zM2 1a2 2 0 0 0-2 2v2a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2V3a2 2 0 0 0-2-2zm0 8a2 2 0 0 0-2 2v2a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2v-2a2 2 0 0 0-2-2zm.854-3.646a.5.5 0 0 1-.708 0l-1-1a.5.5 0 1 1 .708-.708l.646.647 1.646-1.647a.5.5 0 1 1 .708.708zm0 8a.5.5 0 0 1-.708 0l-1-1a.5.5 0 0 1 .708-.708l.646.647 1.646-1.647a.5.5 0 0 1 .708.708zM7 10.5a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-7a.5.5 0 0 1-.5-.5zm0-5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m0 8a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5"/>
                </svg>
                Vedi tutti
            </a>
        </div>
        <div class="body_stat">
            <div class="item">
                <div class="label">Disponibili per asporto</div>
                <div class="donut-wrapper" style="--percent: {{  $stat['products']['not_archived_visible'] / $stat['products']['tot'] * 100}}">
                    <p>
                        {{ $stat['products']['not_archived_visible'] }}
                    </p>
                </div>
            </div>
            <div class="item">
                <div class="label">Presenti nel menu</div>
                <div class="donut-wrapper" style="--percent: {{  $stat['products']['not_archived'] / $stat['products']['tot'] * 100}}">
                    <p>
                        {{ $stat['products']['not_archived'] }}
                    </p>
                </div>
            </div>
            <div class="item">
                <div class="label">Archiviati</div>
                <div class="donut-wrapper" style="--percent: {{  $stat['products']['archived'] / $stat['products']['tot'] * 100}}">
                    <p>
                        {{ $stat['products']['archived'] }}
                    </p>
                </div>
            </div>
        </div>


    </div>
    @if (count($products)) 
    <div class="promo_cont">
        <h3>
            <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" fill="currentColor" class="bi bi-bookmark-star-fill" viewBox="0 0 16 16">
            <path fill-rule="evenodd" d="M2 15.5V2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v13.5a.5.5 0 0 1-.74.439L8 13.069l-5.26 2.87A.5.5 0 0 1 2 15.5M8.16 4.1a.178.178 0 0 0-.32 0l-.634 1.285a.18.18 0 0 1-.134.098l-1.42.206a.178.178 0 0 0-.098.303L6.58 6.993c.042.041.061.1.051.158L6.39 8.565a.178.178 0 0 0 .258.187l1.27-.668a.18.18 0 0 1 .165 0l1.27.668a.178.178 0 0 0 .257-.187L9.368 7.15a.18.18 0 0 1 .05-.158l1.028-1.001a.178.178 0 0 0-.098-.303l-1.42-.206a.18.18 0 0 1-.134-.098z"/>
            </svg>
            Prodotti in evidenza</h3>
        <div class="cont">
            @foreach ($products as $p)
                <div class="promo_item">
                    @if (isset($p->image))
                        <img src="{{ asset('public/storage/' . $p->image) }}" alt="{{$p->title}}">
                    @else
                        <img src="https://future-plus.it/img/favicon.png" alt="{{$p->title }}">
                    @endif 
                    <div class="title">{{$p->name}}</div>
                    <div class="cat">{{$p->category->name}}</div>
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
    @else
        <p class="my-4 no_disp">"Nessun prodotto in evidenza"</p>
    @endif
        <div class="stat_menu">
        <div class="top">
            <div class="count">{{$stat['menus']['tot']}}</div>
            <div class="title">Menu</div>
            <a href="{{ route('admin.menus.index') }}" class="my_btn_1">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-ui-checks" viewBox="0 0 16 16">
                <path d="M7 2.5a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-7a.5.5 0 0 1-.5-.5zM2 1a2 2 0 0 0-2 2v2a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2V3a2 2 0 0 0-2-2zm0 8a2 2 0 0 0-2 2v2a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2v-2a2 2 0 0 0-2-2zm.854-3.646a.5.5 0 0 1-.708 0l-1-1a.5.5 0 1 1 .708-.708l.646.647 1.646-1.647a.5.5 0 1 1 .708.708zm0 8a.5.5 0 0 1-.708 0l-1-1a.5.5 0 0 1 .708-.708l.646.647 1.646-1.647a.5.5 0 0 1 .708.708zM7 10.5a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-7a.5.5 0 0 1-.5-.5zm0-5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m0 8a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5"/>
                </svg>
                Vedi tutti
            </a>
        </div>
    </div>

    @if (count($menus)) 
    <div class="promo_cont">
        <h3>
            <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" fill="currentColor" class="bi bi-bookmark-star-fill" viewBox="0 0 16 16">
            <path fill-rule="evenodd" d="M2 15.5V2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v13.5a.5.5 0 0 1-.74.439L8 13.069l-5.26 2.87A.5.5 0 0 1 2 15.5M8.16 4.1a.178.178 0 0 0-.32 0l-.634 1.285a.18.18 0 0 1-.134.098l-1.42.206a.178.178 0 0 0-.098.303L6.58 6.993c.042.041.061.1.051.158L6.39 8.565a.178.178 0 0 0 .258.187l1.27-.668a.18.18 0 0 1 .165 0l1.27.668a.178.178 0 0 0 .257-.187L9.368 7.15a.18.18 0 0 1 .05-.158l1.028-1.001a.178.178 0 0 0-.098-.303l-1.42-.206a.18.18 0 0 1-.134-.098z"/>
            </svg>
            Menu in evidenza</h3>
        <div class="cont">
            @foreach ($menus as $item)
            <div class="promo_item">
                @if (isset($p->image))
                    <img src="{{ asset('public/storage/' . $p->image) }}" alt="{{$p->title}}">
                @else
                    <img src="https://future-plus.it/img/favicon.png" alt="{{$p->title }}">
                @endif 
                <div class="title">{{$item->name}}</div>
                @if ($p->description)<h4>Descrizione:</h4> <div class="description">{{$item->description}}</div> @endif
                <div class="m_card menu_card">
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
                </div>
                <div class="price">
                    @if ($item->old_price)
                    <h5 class="old_price">€{{$item->old_price / 100}}</h5>
                    @endif
                    <h5 class="price">€{{$item->price / 100}}</h5>
                </div>
            </div>
            @endforeach
        </div>

    </div>
    @else
        <p class="my-4 no_disp">"Nessun menu in evidenza"</p>
    @endif
    <div class="stat_menu">
        <div class="top">
            <div class="count">{{$stat['categories']['tot']}}</div>
            <div class="title">Categorie</div>
            <a href="{{ route('admin.categories.index') }}" class="my_btn_1">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-ui-checks" viewBox="0 0 16 16">
                <path d="M7 2.5a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-7a.5.5 0 0 1-.5-.5zM2 1a2 2 0 0 0-2 2v2a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2V3a2 2 0 0 0-2-2zm0 8a2 2 0 0 0-2 2v2a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2v-2a2 2 0 0 0-2-2zm.854-3.646a.5.5 0 0 1-.708 0l-1-1a.5.5 0 1 1 .708-.708l.646.647 1.646-1.647a.5.5 0 1 1 .708.708zm0 8a.5.5 0 0 1-.708 0l-1-1a.5.5 0 0 1 .708-.708l.646.647 1.646-1.647a.5.5 0 0 1 .708.708zM7 10.5a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-7a.5.5 0 0 1-.5-.5zm0-5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m0 8a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5"/>
                </svg>
                Vedi tutti
            </a>
        </div>
    </div>
    <div class="stat_menu">
        <div class="top">
            <div class="count">{{$stat['ingredients']['tot']}}</div>
            <div class="title">Ingredienti</div>
            <a href="{{ route('admin.ingredients.index') }}" class="my_btn_1">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-ui-checks" viewBox="0 0 16 16">
                <path d="M7 2.5a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-7a.5.5 0 0 1-.5-.5zM2 1a2 2 0 0 0-2 2v2a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2V3a2 2 0 0 0-2-2zm0 8a2 2 0 0 0-2 2v2a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2v-2a2 2 0 0 0-2-2zm.854-3.646a.5.5 0 0 1-.708 0l-1-1a.5.5 0 1 1 .708-.708l.646.647 1.646-1.647a.5.5 0 1 1 .708.708zm0 8a.5.5 0 0 1-.708 0l-1-1a.5.5 0 0 1 .708-.708l.646.647 1.646-1.647a.5.5 0 0 1 .708.708zM7 10.5a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-7a.5.5 0 0 1-.5-.5zm0-5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m0 8a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5"/>
                </svg>
                Vedi tutti
            </a>
        </div>
    </div>
</div>


@endsection
