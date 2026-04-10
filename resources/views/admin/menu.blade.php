@extends('layouts.base')

@section('contents')
<div class="dash_page">
 
    <h1>
        <i class="bi bi-fork-knife" style="font-size: 16px"></i>
        {{__('admin.t_menu')}}
    </h1>
    <div class="stat_menu">
        <div class="top">
            <div class="count">{{$stat['products']['tot']}}</div>
            <div class="title">{{__('admin.Prodotti')}}</div>
            <a href="{{ route('admin.products.index') }}" class="my_btn_1">
                <i class="bi bi-ui-checks" style="font-size: 16px"></i>
               {{__('admin.Vedi_tutti')}}
            </a>
        </div>
        <div class="body_stat">
            <div class="item">
                <div class="label">{{__('admin.stat_1_menu')}}</div>
                <div class="donut-wrapper" style="--percent: {{  $stat['products']['not_archived_visible'] / $stat['products']['tot'] * 100}}">
                    <p>
                        {{ $stat['products']['not_archived_visible'] }}
                    </p>
                </div>
            </div>
            <div class="item">
                <div class="label">{{__('admin.stat_2_menu')}}</div>
                <div class="donut-wrapper" style="--percent: {{  $stat['products']['not_archived'] / $stat['products']['tot'] * 100}}">
                    <p>
                        {{ $stat['products']['not_archived'] }}
                    </p>
                </div>
            </div>
            <div class="item">
                <div class="label">{{__('admin.stat_3_menu')}}</div>
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
            <i class="bi bi-bookmark-star-fill" style="font-size: 25px"></i>
            {{__('admin.promo_p')}}</h3>
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
                    @if ($p->description)<h4>{{__('admin.Descrizione')}}:</h4> <div class="description">{{$p->description}}</div> @endif
                    
                    @if (count($p->ingredients)) 
                    <div class="ingredients">
                        <h4>{{__('admin.Ingredienti')}}</h4>
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
        <p class="my-4 no_disp">"{{__('admin.no_promo_p')}}"</p>
    @endif
        <div class="stat_menu">
        <div class="top">
            <div class="count">{{$stat['menus']['tot']}}</div>
            <div class="title">{{__('admin.Menu')}}</div>
            <a href="{{ route('admin.menus.index') }}" class="my_btn_1">
                <i class="bi bi-ui-checks" style="font-size: 16px"></i>
               {{__('admin.Vedi_tutti')}}
            </a>
        </div>
    </div>

    @if (count($menus)) 
    <div class="promo_cont">
        <h3>
            <i class="bi bi-bookmark-star-fill" style="font-size: 25px"></i>
            {{__('admin.promo_m')}}</h3>
        <div class="cont">
            @foreach ($menus as $item)
            <div class="promo_item">
                @if (isset($p->image))
                    <img src="{{ asset('public/storage/' . $p->image) }}" alt="{{$p->title}}">
                @else
                    <img src="https://future-plus.it/img/favicon.png" alt="{{$p->title }}">
                @endif 
                <div class="title">{{$item->name}}</div>
                @if ($p->description)<h4>{{__('admin.Descrizione')}}:</h4> <div class="description">{{$item->description}}</div> @endif
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
        <p class="my-4 no_disp">"{{__('admin.no_promo_p')}}"</p>
    @endif
    <div class="stat_menu">
        <div class="top">
            <div class="count">{{$stat['categories']['tot']}}</div>
            <div class="title">{{__('admin.Categorie')}}</div>
            <a href="{{ route('admin.categories.index') }}" class="my_btn_1">
                <i class="bi bi-ui-checks" style="font-size: 16px"></i>
               {{__('admin.Vedi_tutti')}}
            </a>
        </div>
    </div>
    <div class="stat_menu">
        <div class="top">
            <div class="count">{{$stat['ingredients']['tot']}}</div>
            <div class="title">{{__('admin.Ingredienti')}}</div>
            <a href="{{ route('admin.ingredients.index') }}" class="my_btn_1">
                <i class="bi bi-ui-checks" style="font-size: 16px"></i>
               {{__('admin.Vedi_tutti')}}
            </a>
        </div>
    </div>
    <div class="stat_menu">
        <div class="top">
            <div class="count">{{$stat['allergens']['tot']}}</div>
            <div class="title">{{__('admin.Allergeni')}}</div>
            <a href="{{ route('admin.allergens.index') }}" class="my_btn_1">
                <i class="bi bi-ui-checks" style="font-size: 16px"></i>
               {{__('admin.Vedi_tutti')}}
            </a>
        </div>
    </div>
</div>


@endsection
