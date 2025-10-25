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

 
<div class="dash_page">
    <h1>
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-fork-knife" viewBox="0 0 16 16">
            <path d="M13 .5c0-.276-.226-.506-.498-.465-1.703.257-2.94 2.012-3 8.462a.5.5 0 0 0 .498.5c.56.01 1 .13 1 1.003v5.5a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5zM4.25 0a.25.25 0 0 1 .25.25v5.122a.128.128 0 0 0 .256.006l.233-5.14A.25.25 0 0 1 5.24 0h.522a.25.25 0 0 1 .25.238l.233 5.14a.128.128 0 0 0 .256-.006V.25A.25.25 0 0 1 6.75 0h.29a.5.5 0 0 1 .498.458l.423 5.07a1.69 1.69 0 0 1-1.059 1.711l-.053.022a.92.92 0 0 0-.58.884L6.47 15a.971.971 0 1 1-1.942 0l.202-6.855a.92.92 0 0 0-.58-.884l-.053-.022a1.69 1.69 0 0 1-1.059-1.712L3.462.458A.5.5 0 0 1 3.96 0z"/>
        </svg>
        Menu
    </h1>
    
    <div class="action-page">
        <a class="my_btn_1 create m-1 w-auto" href="{{ route('admin.menus.create') }}">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-cloud-plus-fill" viewBox="0 0 16 16">
                <path d="M8 2a5.53 5.53 0 0 0-3.594 1.342c-.766.66-1.321 1.52-1.464 2.383C1.266 6.095 0 7.555 0 9.318 0 11.366 1.708 13 3.781 13h8.906C14.502 13 16 11.57 16 9.773c0-1.636-1.242-2.969-2.834-3.194C12.923 3.999 10.69 2 8 2m.5 4v1.5H10a.5.5 0 0 1 0 1H8.5V10a.5.5 0 0 1-1 0V8.5H6a.5.5 0 0 1 0-1h1.5V6a.5.5 0 0 1 1 0"/>
                </svg>

            Crea nuovo
        </a>
    </div>
    <div class="menu-container">
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
    <div class="menu-container">
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