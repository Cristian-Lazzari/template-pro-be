@extends('layouts.base')

@section('contents')

<button onclick="history.back()" class="btn btn-outline-light my-5">
    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-90deg-left" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M1.146 4.854a.5.5 0 0 1 0-.708l4-4a.5.5 0 1 1 .708.708L2.707 4H12.5A2.5 2.5 0 0 1 15 6.5v8a.5.5 0 0 1-1 0v-8A1.5 1.5 0 0 0 12.5 5H2.707l3.147 3.146a.5.5 0 1 1-.708.708l-4-4z"/></svg>
</button>

<h1>Dettagli prodotto</h1>
<div class="show_p">
    <h2>{{$post->title}}</h2>
    <div class="split_p">
        <div class="image_p">
            @if (isset($post->image))
                <img class="logo_" src="{{ asset('public/storage/' . $post->image) }}" alt="{{$post->name }}">
            @else 
                <img class="logo_" src="https://db.kojo-sushi.it/public/images/or.png" alt="{{$post->name }}">
            @endif 
            
        </div>
        <div class="info">
            <section>
                <h4>Descrizione:</h4> 
                <p>{{$post->description}} </p>       
            </section>
            <section>
                <h4>Precedenza: <strong>{{$post->order}}</strong></h4>      
                @if (isset($post->link)) 
                    <h4 class="">Link:</h4>
                    <p class="">{{$post->link}}</p>
                @else
                    <p>(nessun link impostato)</p>   
                @endif  
            </section>

            <section class="split_i">
                
                <h4 class="">Pagina: </h4>
                <p class="">{{$post->path == '1' ? 'News' : 'Story'}}</p>
                @if (isset($post->link))
                    <h4>Hashtag:</h4>
                    <p class="">{{$post->hashtag}}</p>
                @else
                    <p class="">(nessun hashtag impostato)</p>   
                @endif 
            </section>
        </div>
    </div>
    <div class="prod-spec">
        
        <div class="actions">
            <form action="{{ route('admin.posts.status') }}" method="POST">
                @csrf
                <input type="hidden" name="archive" value="0">
                <input type="hidden" name="v" value="1">
                <input type="hidden" name="a" value="0">
                <input type="hidden" name="id" value="{{$post->id}}">
                @if (!$post->visible)
                    <button class="my_btn_4" type="submit">
                       PUBBLICA
                    </button>
                @else
                    <button class="my_btn_4" type="submit">
                        Nascondi   
                    </button>
                @endif
                
            </form>
            <a class="my_btn_1" href="{{ route('admin.posts.edit', $post) }}">Modifica</a>
            <form action="{{ route('admin.posts.status') }}" method="POST">
                @csrf
                <input type="hidden" name="archive" value="0">
                <input type="hidden" name="v" value="0">
                <input type="hidden" name="a" value="1">
                <input type="hidden" name="id" value="{{$post->id}}">
                <button class="my_btn_2" type="submit">Archivia</button>
            </form>
            <form action="{{ route('admin.posts.destroy', ['post'=>$post]) }}" method="post" >
                @method('delete')
                @csrf
                <button class="my_btn_1 bg-danger" type="submit">Elimina</button>
            </form>
             
        </div>
    </div>
    <p></p>
    <p>Data creazione: {{$post->created_at}}, Ultima modifica: {{$post->updated_at}}.</p>
</div>

 

@endsection