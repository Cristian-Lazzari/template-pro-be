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
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
 


<div class="dash_page">

    <h1>
        <i class="bi bi-fork-knife"></i>{{ __('admin.Archivio_Prodotti') }}</h1>
    
    <div class="action-page">
        <a class="my_btn_3 m-1 w-auto" href="{{ route('admin.products.index') }}">{{__('admin.Esci')}}</a>
    </div>

    <div class="time-list prod_index">
        @foreach ($products as $item)

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
                    <div class="name">{{$item->name}}</div>
                    <div class="cat">{{$item->category->name}}</div>
                </div>
                <div class="price_btn">
                    <div class="price">€{{$item->price / 100}}</div>
                    <button type="button" class="action_menu action_menu_info" data-bs-toggle="modal" data-bs-target="#exampleModal{{$item->id}}">
                        <i class="bi bi-info-circle-fill"></i>{{ __('admin.Info') }}</button>
                    <!-- Modal -->
                    <div class="modal fade" id="exampleModal{{$item->id}}" tabindex="-1" aria-labelledby="exampleModal{{$item->id}}Label" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-body">
                                    <button type="button" class="btn_close" data-bs-dismiss="modal">
                                        <i class="bi bi-x-circle-fill" style="font-size: 20px"></i>
                                        {{__('admin.Chiudi')}}
                                    </button>
                                    <div class="action_top">
                                        <form action="{{ route('admin.products.status') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="archive" value="1">
                                            <input type="hidden" name="v" value="0">
                                            <input type="hidden" name="a" value="1">
                                            <input type="hidden" name="id" value="{{$item->id}}">
                                            <button class="edit" type="submit">
                                                <i class="bi bi-cloud-plus-fill" style="font-size: 20px"></i>{{ __('admin.Ripristina') }}</button>
                                        </form>                      

                                        <form action="{{ route('admin.products.destroy', $item) }}" method="POST">
                                            @method('DELETE')
                                            @csrf
                                            <button class="edit btn_delete" type="submit">
                                                <i class="bi bi-trash-fill" style="font-size: 20px"></i>
                                            </button>
                                        </form>
                               
                                    </div>
                                    <div class="name_cat">
                                        <div class="name">{{$item->name}}</div>
                                        <div class="cat">{{$item->category->name}}</div>
                                    </div>
                                    @if (count($item->ingredients))
                                        <section>
                                            <h4>
                                                <i class="bi bi-card-list"></i>
                                                {{__('admin.Ingredienti')}}</h4>
                                            <p>
                                                @foreach ($item->ingredients as $ingredient)     
                                                    {{ $ingredient->name }}{{ !$loop->last ? ', ' : '.' }}
                                                @endforeach
                                            </p>
                                        </section>
                                    @endif
                                    @if ($item->description)
                                        <section>
                                            <h4>
                                                <i class="bi bi-card-text"></i>
                                                {{__('admin.Descrizione')}}</h4>
                                            <p>{{$item->description}}</p>
                                        </section>
                                    @endif
        
                                    <div class="price">€{{$item->price / 100}}</div>
        
                                     <div class="allergens">
                                     
                                        @foreach ($item->allergens as $i)
                                        <div class="al">
                                            <img src="{{$i->img}}" alt="" title="{{$i->name}}">
                                            {{$i->name}}
                                        </div>
                                        @endforeach
                                    </div>   
        
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