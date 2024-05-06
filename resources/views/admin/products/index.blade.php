@extends('layouts.base')

@section('contents')

<div class="categories">
    @foreach ($categories as $item)
        <a href="{{ route('') }}">{{$item->name}}</a>
    @endforeach
</div>
<div class="top-bar-product">
    <div class="bar">


        <section>
                 {{-- FILTRA  --}}
            <a class="btn btn-primary my-1 flex-grow-1" data-bs-toggle="collapse" href="#collapseExample" role="button" aria-expanded="false" aria-controls="collapseExample">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-funnel-fill" viewBox="0 0 16 16">
                    <path d="M1.5 1.5A.5.5 0 0 1 2 1h12a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.128.334L10 8.692V13.5a.5.5 0 0 1-.342.474l-3 1A.5.5 0 0 1 6 14.5V8.692L1.628 3.834A.5.5 0 0 1 1.5 3.5z"/>
                </svg>  FILTRA
            </a>

            {{-- RIMUOVI FILTRI  --}}
            <a class="btn btn-success my-1 flex-grow-1" href="{{ route('admin.projects.showCategory', $category_id)}}">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-funnel" viewBox="0 0 16 16">
                    <path d="M1.5 1.5A.5.5 0 0 1 2 1h12a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.128.334L10 8.692V13.5a.5.5 0 0 1-.342.474l-3 1A.5.5 0 0 1 6 14.5V8.692L1.628 3.834A.5.5 0 0 1 1.5 3.5zm1 .5v1.308l4.372 4.858A.5.5 0 0 1 7 8.5v5.306l2-.666V8.5a.5.5 0 0 1 .128-.334L13.5 3.308V2z"/>
                </svg> RIMUOVI FILTRI
            </a>   
        </section>
        <setion>
            <a class="btn my-btn btn-success m-1 w-auto" href="{{ route('admin.projects.create') }}"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-circle-fill" viewBox="0 0 16 16">
                <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M8.5 4.5a.5.5 0 0 0-1 0v3h-3a.5.5 0 0 0 0 1h3v3a.5.5 0 0 0 1 0v-3h3a.5.5 0 0 0 0-1h-3z"/>
              </svg></a>
            <a class="btn my-btn btn-danger m-1 w-auto" href="{{ route('admin.projects.archive') }}"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash-fill" viewBox="0 0 16 16">
                <path d="M2.5 1a1 1 0 0 0-1 1v1a1 1 0 0 0 1 1H3v9a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V4h.5a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H10a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1zm3 4a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 .5-.5M8 5a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7A.5.5 0 0 1 8 5m3 .5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 1 0"/>
            </svg></a>
        </setion>

       
    </div>

    {{-- INPUT FILTRI  --}}
    <div class="collapse" id="collapseExample">
        <div class="card card-body">
            <form action="{{ route('admin.projects.filter')}}" class="filter mb-2" method="GET">

                {{-- NOME --}}
                <div>
                    <label for="name" class="form-label fw-semibold">Nome Prodotto</label>
                    <input
                        type="text"
                        class="form-control"
                        name="name"
                        @if (isset($name))
                            value="{{ $name }}"  
                        @endif
                    >
                </div>

                {{-- VISIBILITà  --}}
                <div>
                    <label for="visible" class="">Visibilità</label>
                    <select name="visible">
                        <option 
                            @if (isset($visible) && $visible == '0')
                                selected
                            @endif
                            value="0"
                        >Tutti</option>
                        <option 
                            @if (isset($visible) && $visible == '1')
                                selected
                            @endif
                            value="1"
                        >Visibili</option>
                        <option 
                            @if (isset($visible) && $visible == '2')
                                selected
                            @endif
                            value="2"
                        >Non visibili</option>
                    </select>
                </div>

                <button class="" type="submit">APPLICA FILTRI</button>
            </form>
        </div>
    </div>
</div>
<div class="object-container">
    @foreach ($products as $item)
        <div class="obj">
            <h3>{{$item->name}}</h3>
            <div class="buttons">
                <a href="">Modifica</a>
                <a href="">Archivia</a>
                <a href="">Visibilità -  @if ($item->vibility) on  @else off @endif</a>
            </div>
            <div class="allergiens">
                @foreach ($item->allergiens as $item)
                    <img src="{{$item-}}" alt="">
                @endforeach
            </div>
            @if (isset($item->image))
                <img src="{{ asset('public/storage/' . $item->image) }}" alt="">
            @endif

        </div>
    @endforeach
</div>
@endsection