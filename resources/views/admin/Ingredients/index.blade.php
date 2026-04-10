@extends('layouts.base')



@section('contents')
@php
      //se impostato a true gli ordini vengono presi in base ai pezzi altrimenti in base al numero di ordini
    $domain = 'https://future-plus.it/allergens/';
@endphp
@if (session('ingredient_success'))
    @php
        $data = session('ingredient_success')
    @endphp
    <div class="alert alert-primary">
        {{ $data }}
    </div>
@endif
@if (session('delete_success'))
    @php
        $data = session('delete_success')
    @endphp
    <div class="alert alert-danger">
        {{ $data }}
    </div>
@endif


<div class="dash_page">
    <h1>{{__('admin.Ingredienti')}}</h1>
    <div class="action-page">
        <a class="my_btn_1 create w-auto" href="{{ route('admin.ingredients.create') }}">
            <i class="bi bi-cloud-plus-fill" style="font-size: 20px"></i>
            {{__('admin.Crea_nuovo')}}</a>
    </div>


    @if (count($options))
        <h2 class="my-4">{{ __('admin.Opzioni_extra_per_prodotti') }}</h2>
        <div class="slim_cont">
            @foreach ($options as $item)
        
                <div class="slim_ ">
                    <section class="s1">
            
                        <h3><a href="{{ route('admin.ingredients.show', $item) }}">{{$item->name}}</a></h3>     
                    </section>
                    <section>
                        <div class="price">€{{$item->price / 100}}</div>         
                        <div class="actions">
                            <a class="" href="{{ route('admin.ingredients.edit', $item) }}">
                                <i style="vertical-align: sub; font-size: 20px" class="bi bi-pencil-square"></i>
                            </a>
                            <form action="{{ route('admin.ingredients.destroy', ['ingredient'=>$item]) }}" method="post" >
                                @method('delete')
                                @csrf
                                <button class="s_d" type="submit">
                                    <i style="vertical-align: sub; font-size: 20px" class="bi bi-x-circle"></i>
                                </button>
                            </form>
                            
                        </div>
                    </section>
        
                </div>
            @endforeach
        </div>
    @endif
    <h2 class="my-4 mt-5">{{__('admin.Ingredienti')}}:</h2>
    @if (count($ingredients) == 0)
        <div class="alert alert-info">
            {{__('admin.no_ing')}}
        </div>
    @endif
    
    <div class="slim_cont">
        @foreach ($ingredients as $item)

            <div class="slim_ ">
                <section class="s1">

                    @if (isset($item->icon))
                        <img src="{{ asset('public/storage/' . $item->icon) }}" alt="{{$item->name}}">
                    @endif 
        
                    <h3><a href="{{ route('admin.ingredients.show', $item) }}">{{$item->name}}</a></h3>     
                </section>
                <section>
                    <div class="price">€{{$item->price / 100}}</div>         
                    <div class="actions">
                        <a class="" href="{{ route('admin.ingredients.edit', $item) }}">
                            <i style="vertical-align: sub; font-size: 19px" class="bi bi-pencil-square"></i>
                        </a>
                        <form action="{{ route('admin.ingredients.destroy', ['ingredient'=>$item]) }}" method="post" >
                            @method('delete')
                            @csrf
                            <button class="s_d" type="submit">
                                <i style="vertical-align: sub; font-size: 19px" class="bi bi-x-circle"></i>
                            </button>
                        </form>
                        
                    </div>
                </section>

            </div>
        @endforeach
    </div>
</div>
@endsection