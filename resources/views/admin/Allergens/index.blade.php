@extends('layouts.base')



@section('contents')


@if (session('created'))
    @php
        $data = session('created')
    @endphp
    <div class="alert alert-success">
        "{{ $data['name_ing'] }}" è stato correttamente creato!
    </div>
@endif
@if (session('edited'))
    @php
        $data = session('edited')
    @endphp
    <div class="alert alert-success">
        "{{ $data['name_ing'] }}" è stato modificato creato!
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
    <h1>{{__('admin.Allergeni')}}</h1>
    <div class="action-page">
        <a class="my_btn_1 create w-auto" href="{{ route('admin.allergens.create') }}">
            <i class="bi bi-cloud-plus-fill" style="font-size: 20px"></i>
            {{__('admin.Crea_nuovo')}}</a>
    </div>

    <div class="slim_cont list-group">
        @foreach ($allergens as $item)

            <div class="category list-group-item ">
                @if (str_starts_with($item->img, 'http'))
                    <img src="{{$item->img}}" alt="{{$item->name}}">
                @else
                    <img src="{{ asset('public/storage/' . $item->img) }}" alt="{{$item->name}}">
                @endif

                <h3><a>{{$item->name}}</a></h3>     
            
                <div class="actions">
                    <a class="my_btn_1" href="{{ route('admin.allergens.edit', $item) }}">
                        <i style="vertical-align: sub; font-size: 21px" class="bi bi-pencil-square"></i>
                    </a> 
                    <button type="button" class="my_btn_2" data-bs-toggle="modal" data-bs-target="#staticBackdrop{{$item->id}}">
                        <i style="vertical-align: sub; font-size: 21px" class="bi bi-x-circle"></i>
                    </button>              
                </div>
            </div>

            <!-- Modal ELINIMAZIONE -->
            <div class="modal fade" id="staticBackdrop{{$item->id}}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel{{$item->id}}" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered my_modal_dialog">
                <div class="modal-content">
                    <div class="modal-header">
                    <h1 class=" fs-5" id="staticBackdropLabel{{$item->id}}">{{ __('admin.Confermi_di_voler_eliminare_') }}<strong>{{$item->name}}</strong>"?</h1>
                    <button data-bs-target="#staticBackdrop{{$item->id}}" type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body body">
                        <p>{{ __('admin.Eiminando_questo_allergene_i_prodotti_ad_esso_abbianati_non_lo_mostreranno_pi') }}</p>
                        <form action="{{ route('admin.allergens.destroy', ['allergen'=>$item]) }}" method="post" >
                            @method('delete')
                            @csrf
                            <button class="my_btn_5 w-100"  type="submit">
                                Elimina
                            </button>
                        </form>
                    </div>
                    
                </div>
                </div>
            </div>

        @endforeach
    </div>

</div>

@endsection