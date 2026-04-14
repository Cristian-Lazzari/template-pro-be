<button type="button" class="btn_close" data-bs-dismiss="modal">
    <i class="bi bi-x-circle-fill" style="font-size: var(--fs-400)"></i>
    {{__('admin.Chiudi')}}
</button>

<div class="action_top">
    <a href="{{ route('admin.posts.edit', $post) }}" class="edit">
        <i style="vertical-align: sub; font-size: var(--fs-400)" class="bi bi-pencil-square"></i>
    </a>

    <form action="{{ route('admin.posts.status') }}" method="POST">
        @csrf
        <input type="hidden" name="archive" value="0">
        <input type="hidden" name="v" value="1">
        <input type="hidden" name="a" value="0">
        <input type="hidden" name="id" value="{{$post->id}}">
        <button type="submit" class="edit @if(!$post->visible) not @endif visible">
            <i class="bi bi-eye-fill" style="font-size: var(--fs-400)"></i>
            <i class="bi bi-eye-slash-fill" style="font-size: var(--fs-400)"></i>
        </button>
    </form>

    <form action="{{ route('admin.posts.status') }}" method="POST">
        @csrf
        <input type="hidden" name="archive" value="0">
        <input type="hidden" name="v" value="0">
        <input type="hidden" name="a" value="1">
        <input type="hidden" name="id" value="{{$post->id}}">
        <button class="edit" type="submit">
            <i class="bi bi-trash-fill" style="font-size: var(--fs-400)"></i>
        </button>
    </form>
</div>

<div class="name_cat">
    <div class="name">{{$post->title}}</div>
    <div class="cat">{{$post->path}}</div>
</div>

@if ($post->description)
    <section>
        <h4>
            <i class="bi bi-card-text" style="font-size: var(--fs-400)"></i>
            {{__('admin.Descrizione')}}
        </h4>
        <p>{{$post->description}}</p>
    </section>
@endif

@if ($post->hashtag)
    <section>
        <h4><strong>#</strong> Hashtags</h4>
        <p>{{$post->hashtag}}</p>
    </section>
@endif

@if ($post->link)
    <section>
        <h4>
            <i class="bi bi-link-45deg" style="font-size: var(--fs-400)"></i>
            {{__('admin.Link')}}
        </h4>
        <a href="{{$post->link}}">{{$post->link}}</a>
    </section>
@endif
