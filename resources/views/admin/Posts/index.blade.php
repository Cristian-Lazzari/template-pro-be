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
<a class="btn btn-outline-light mb-5" href="{{ route('admin.dashboard') }}">Indietro</a>

<h1>Post</h1>
 
<form class="top-bar-product" action="{{ route('admin.posts.filter') }}" method="post">
    @csrf   
    <input type="hidden" name="archive" value="0">
    
    <div class="bar">


        {{-- NOME --}}
        <div class="s-name">
            <label for="name" class="fw-semibold">Titolo post</label>
            <div>
                <input type="text" class="" id="title" name="title"
                    @if (isset($filters))
                        value="{{  $filters['title'] }}"  
                    @endif > 
                
            </div>
        </div>

        
        {{-- VISIBILITà  --}}
        <div>
            <label for="visible" class="form-label fw-semibold">Visibilità</label>
            <select class="" id="visible" name="visible" >
                <option @if (isset($filters) && $filters['visible'] == '0') selected @endif value="0">Tutti</option>
                <option @if (isset($filters) && $filters['visible'] == '1') selected @endif value="1">Visibili</option>
                <option @if (isset($filters) && $filters['visible'] == '2') selected @endif value="2">Non visibili</option>
            </select>
        </div>
        <div>
            <label for="path" class="form-label fw-semibold">Pagina</label>
            <select class="" id="path" name="path" >
                <option @if (isset($filters) && $filters['path'] == '0') selected @endif value="0">Tutti</option>
                <option @if (isset($filters) && $filters['path'] == '1') selected @endif value="1">News</option>
                <option @if (isset($filters) && $filters['path'] == '2') selected @endif value="2">Storia</option>
            </select>
        </div>
        <div>
            <label for="order" class="form-label fw-semibold">Ordina per</label>
            <select class="" id="order" name="order" >
                <option @if (isset($filters) && $filters['order'] == '0') selected @endif value="0">Precedenza assegnata</option>
                <option @if (isset($filters) && $filters['order'] == '1') selected @endif value="1">Nome A-Z</option>
            </select>
        </div>
       
        <div class="buttons">
         <button type="submit" class=" my_btn_3">
             <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-funnel-fill" viewBox="0 0 16 16">
                 <path d="M1.5 1.5A.5.5 0 0 1 2 1h12a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.128.334L10 8.692V13.5a.5.5 0 0 1-.342.474l-3 1A.5.5 0 0 1 6 14.5V8.692L1.628 3.834A.5.5 0 0 1 1.5 3.5z"/>
             </svg>  Applica
         </button>
         <a class="my_btn_1 search" href="{{ route('admin.posts.index')}}">
             <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-funnel" viewBox="0 0 16 16">
                 <path d="M1.5 1.5A.5.5 0 0 1 2 1h12a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.128.334L10 8.692V13.5a.5.5 0 0 1-.342.474l-3 1A.5.5 0 0 1 6 14.5V8.692L1.628 3.834A.5.5 0 0 1 1.5 3.5zm1 .5v1.308l4.372 4.858A.5.5 0 0 1 7 8.5v5.306l2-.666V8.5a.5.5 0 0 1 .128-.334L13.5 3.308V2z"/>
             </svg> Rimuovi
         </a>   
        </div>
    </div>
    
</form> 
<div class="action-page">
    <a class="my_btn_1 create m-1 w-auto" href="{{ route('admin.posts.create') }}">Crea un nuovo post</a>
    <a class="my_btn_1 trash m-1 w-auto" href="{{ route('admin.posts.archived') }}">Archivio</a>
</div>

<div class="object-container">
    @foreach ($posts as $item)

        <div class="obj  @if (!$item->visible) not_v @endif" onclick="window.location.href='{{ route('admin.posts.show', $item->id) }}">
            <h3><a href="{{ route('admin.posts.show', $item) }}">{{$item->title}}</a></h3>     
            <div class="card_">
                @if (isset($item->image))
                    <img src="{{ asset('public/storage/' . $item->image) }}" alt="{{$item->title}}">
                @else
                    <img src="https://db.kojo-sushi.it/public/images/or.png" alt="{{$item->title }}">
                @endif 

                <div class="info">
                    <section>
                        <h4>Descrizione:</h4> 
                        <p>{{$item->description}}</p>       
                    </section>
                    <section>
                        <h4>Precedenza: <strong>{{$item->order}}</strong></h4>      
                        @if (isset($item->link)) 
                            <h4 class="ell-c">Link: <span class="ellips">{{$item->link}}</span></h4>
                        @else
                            <p>(nessun link impostato)</p>   
                        @endif  
                    </section>

                    <div class="split_i">
                        
                        <h4 class="ell-c">Pagina: <span class="ellips">{{$item->path == 1 ? 'News' : 'Story'}}</span></h4>
                        @if (isset($item->link))
                            <div class="price">{{$item->hashtag}}</div>
                        @else
                            <div class="price">(nessun hashtag impostato)</div>   
                        @endif 
                    </div>
                </div>
            </div>
            <div class="actions">
                <a class="my_btn_1 m" href="{{ route('admin.posts.edit', $item) }}">Modifica</a>
                <form action="{{ route('admin.posts.status') }}" method="POST">
                    @csrf
                    <input type="hidden" name="archive" value="0">
                    <input type="hidden" name="v" value="0">
                    <input type="hidden" name="a" value="1">
                    <input type="hidden" name="id" value="{{$item->id}}">
                    <button class="my_btn_1 d" type="submit">Archivia</button>
                </form>
                <form action="{{ route('admin.posts.status') }}" method="POST">
                    @csrf
                    <input type="hidden" name="archive" value="0">
                    <input type="hidden" name="v" value="1">
                    <input type="hidden" name="a" value="0">
                    <input type="hidden" name="id" value="{{$item->id}}">
                    @if (!$item->visible)
                        <button class="my_btn_5" type="submit">
                            PUBBLICA
                        </button>
                    @else
                        <button class="my_btn_5" type="submit">
                            Nascondi   
                        </button>
                    @endif
                    
                </form>
            </div>

        </div>
    @endforeach
</div>
@endsection