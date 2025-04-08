@extends('layouts.base')



@section('contents')

@if (session('success'))
    @php
        $data = session('success')
    @endphp
   <div class="alert alert-primary alert-dismissible fade show" role="alert">
    {{ $data }} 
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif
@if (session('delete_success'))
    @php
        $data = session('delete_success')
    @endphp
    <div class="alert alert-danger">
       " {{ $data->title }} " e stato eliminato correttamente
    </div>
@endif

 
<h1>Menu</h1>
 

<div class="action-page">
    <a class="my_btn_1 create m-1 w-auto" href="{{ route('admin.menus.create') }}">Crea un nuovo Menu</a>
</div>

<h2>Menu fissi</h2>
<div class="object-container post-container">
    @foreach ($fix as $item)

        <div class="menu  @if (!$item->visible) not_v @endif" onclick="window.location.href='{{ route('admin.posts.show', $item->id) }}">
            <div class="m_card">
                <div class="top">
                    <h2>{{$item->name}}</h2>
                    @if (isset($item->image))
                        <img class="img_m" src="{{ asset('public/storage/' . $item->image) }}" alt="{{$item->title}}">
                    @else
                        <img class="img_m" src="https://db.kojo-sushi.it/public/images/or.png" alt="{{$item->title }}">
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

                @if (isset($p->image))
                    <p class="desc">{{$item->description}}</p>
                @endif
                <div class="prices">
                    @if ($item->old_price)
                    <h5 class="old_price">€{{$item->old_price / 100}}</h5>
                    @endif
                    <h5 class="price">€{{$item->price / 100}}</h5>
                </div>
            </div>
            <div class="actions">
                <a class="my_btn_1 m" href="{{ route('admin.posts.edit', $item) }}">Modifica</a>
                <form action="{{ route('admin.posts.status') }}" method="POST">
                    @csrf
                    <input type="hidden" name="archive" value="0">
                    <input type="hidden" name="v" value="1">
                    <input type="hidden" name="a" value="0">
                    <input type="hidden" name="id" value="{{$item->id}}">
                    @if (!$item->visible)
                        <button class="my_btn_1 v op" type="submit">
                            <svg style="vertical-align: sub" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye-slash-fill" viewBox="0 0 16 16">
                                <path d="m10.79 12.912-1.614-1.615a3.5 3.5 0 0 1-4.474-4.474l-2.06-2.06C.938 6.278 0 8 0 8s3 5.5 8 5.5a7 7 0 0 0 2.79-.588M5.21 3.088A7 7 0 0 1 8 2.5c5 0 8 5.5 8 5.5s-.939 1.721-2.641 3.238l-2.062-2.062a3.5 3.5 0 0 0-4.474-4.474z"/>
                                <path d="M5.525 7.646a2.5 2.5 0 0 0 2.829 2.829zm4.95.708-2.829-2.83a2.5 2.5 0 0 1 2.829 2.829zm3.171 6-12-12 .708-.708 12 12z"/>
                            </svg>  
                        </button>
                    @else
                        <button class="my_btn_1 v" type="submit">
                            <svg style="vertical-align: sub" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye-fill" viewBox="0 0 16 16">
                                <path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0"/>
                                <path d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8m8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7"/>
                            </svg>    
                        </button>
                    @endif
                    
                </form>
            </div>

        </div>
    @endforeach
</div>
<h2>Combo prodotti</h2>
<div class="object-container post-container">
    @foreach ($combo as $item)

        <div class="menu  @if (!$item->visible) not_v @endif" onclick="window.location.href='{{ route('admin.posts.show', $item->id) }}">
            <div class="m_card">
                <div class="top">
                    <h2>{{$item->name}}</h2>
                    <p>{{$item->category->name}}</p>
                    @if (isset($item->image))
                        <img class="img_m" src="{{ asset('public/storage/' . $item->image) }}" alt="{{$item->title}}">
                    @else
                        <img class="img_m" src="https://db.kojo-sushi.it/public/images/or.png" alt="{{$item->title }}">
                    @endif
                </div>
                @if($item->fixed_menu == 1)
                ma comeee s
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
                        eheheheheheh
                        @foreach ($item->fixed_menu as $key => $c)
                            <div class="scelta">
                                <h5>{{$key}}</h5>
                                <div class="cont">
                                    @foreach ($c as $p)
                                        <div class="product">
                                            @if (isset($p->image))
                                                <img class="img_p" src="{{ asset('public/storage/' . $p->image) }}" alt="{{$item->title}}">
                                            @endif
                                            @if ($p['extra_price'] > 0)
                                            <span class="ext_p">+ € {{$p['extra_price'] / 100}}</span>
                                            @endif
                                            <span class="name">{{$p['name']}}</span>
                                            <span class="cat">{{$p['category']['name']}}</span>
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
            <div class="actions">
                <a class="my_btn_1 m" href="{{ route('admin.posts.edit', $item) }}">Modifica</a>
                <form action="{{ route('admin.posts.status') }}" method="POST">
                    @csrf
                    <input type="hidden" name="archive" value="0">
                    <input type="hidden" name="v" value="1">
                    <input type="hidden" name="a" value="0">
                    <input type="hidden" name="id" value="{{$item->id}}">
                    @if (!$item->visible)
                        <button class="my_btn_1 v op" type="submit">
                            <svg style="vertical-align: sub" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye-slash-fill" viewBox="0 0 16 16">
                                <path d="m10.79 12.912-1.614-1.615a3.5 3.5 0 0 1-4.474-4.474l-2.06-2.06C.938 6.278 0 8 0 8s3 5.5 8 5.5a7 7 0 0 0 2.79-.588M5.21 3.088A7 7 0 0 1 8 2.5c5 0 8 5.5 8 5.5s-.939 1.721-2.641 3.238l-2.062-2.062a3.5 3.5 0 0 0-4.474-4.474z"/>
                                <path d="M5.525 7.646a2.5 2.5 0 0 0 2.829 2.829zm4.95.708-2.829-2.83a2.5 2.5 0 0 1 2.829 2.829zm3.171 6-12-12 .708-.708 12 12z"/>
                            </svg>  
                        </button>
                    @else
                        <button class="my_btn_1 v" type="submit">
                            <svg style="vertical-align: sub" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye-fill" viewBox="0 0 16 16">
                                <path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0"/>
                                <path d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8m8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7"/>
                            </svg>    
                        </button>
                    @endif
                    
                </form>
            </div>

        </div>
    @endforeach
</div>


@endsection