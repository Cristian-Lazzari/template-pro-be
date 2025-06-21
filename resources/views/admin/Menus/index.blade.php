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
       " {{ $data->name }} " e stato eliminato correttamente
    </div>
@endif

 
<h1>Menu</h1>
 

<div class="action-page">
    <a class="my_btn_1 create m-1 w-auto" href="{{ route('admin.menus.create') }}">Crea un nuovo Menu</a>
</div>
<div class="menu-containers">
    <div class="object-container menu-container">
        @if ($fix->count() == 0)
            <div class="alert alert-warning">
                Non sono presenti menu fissi, creane uno per iniziare
            </div>
            
        @else
            <h2>Menu fissi</h2>
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
                            <svg style="vertical-align: sub" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-x-circle" viewBox="0 0 16 16">
                                <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
                                <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708"/>
                            </svg>
                        </button>
                    </form>
                    <a class="my_btn_1 m" href="{{ route('admin.menus.edit', $item) }}">
                        <svg style="vertical-align: sub" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-pencil-square" viewBox="0 0 16 16">
                            <path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/>
                            <path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z"/>
                        </svg>
                    </a>
                </div>
    
            </div>
        @endforeach
    </div>
    <div class="object-container menu-container">
        @if ($combo->count() == 0)
            <div class="alert alert-warning">
                Non sono presenti menu combo o combo custom, creane uno per iniziare
            </div>
            
        @else
            <h2 class="my-3">Combo prodotti</h2>
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
                            <svg style="vertical-align: sub" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-x-circle" viewBox="0 0 16 16">
                                <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
                                <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708"/>
                            </svg>
                        </button>
                    </form>
                    <a class="my_btn_1 m" href="{{ route('admin.menus.edit', $item) }}">
                        <svg style="vertical-align: sub" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-pencil-square" viewBox="0 0 16 16">
                            <path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/>
                            <path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z"/>
                        </svg>
                    </a>
                </div>
    
            </div>
        @endforeach
    </div>
</div>


@endsection