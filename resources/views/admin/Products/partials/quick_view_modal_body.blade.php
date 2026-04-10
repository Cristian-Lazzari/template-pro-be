<button type="button" class="btn_close" data-bs-dismiss="modal">
    <i class="bi bi-x-circle-fill" style="font-size: 20px"></i>
    {{__('admin.Chiudi')}}
</button>
<div class="action_top">
    <a href="{{ route('admin.products.edit', $product) }}" class="edit">
        <i style="vertical-align: sub; font-size: 20px" class="bi bi-pencil-square"></i>
    </a>

    <form action="{{ route('admin.products.status') }}" method="POST" class="js-product-status-form" data-status-action="visible">
        @csrf
        <input type="hidden" name="archive" value="0">
        <input type="hidden" name="v" value="1">
        <input type="hidden" name="a" value="0">
        <input type="hidden" name="id" value="{{$product->id}}">
        <button type="submit" class="edit @if(!$product->visible) not @endif visible js-toggle-visible-btn">
            <i class="bi bi-eye-fill" style="font-size: 20px"></i>
            <i class="bi bi-eye-slash-fill" style="font-size: 20px"></i>
        </button>
    </form>

    <form action="{{ route('admin.products.status') }}" method="POST" class="js-product-status-form" data-status-action="archived">
        @csrf
        <input type="hidden" name="archive" value="0">
        <input type="hidden" name="v" value="0">
        <input type="hidden" name="a" value="1">
        <input type="hidden" name="id" value="{{$product->id}}">
        <button class="edit" type="submit">
            <i class="bi bi-trash-fill" style="font-size: 20px"></i>
        </button>
    </form>
</div>

<div class="name_cat">
    <div class="name">{{$product->name}}</div>
    <div class="cat">{{$product->category->name ?? ''}}</div>
</div>

@if (count($product->ingredients))
    <section>
        <h4>
            <i class="bi bi-card-list" style="font-size: 20px"></i>
            {{__('admin.Ingredienti')}}
        </h4>
        <p>
            @foreach ($product->ingredients as $ingredient)
                {{ $ingredient->name }}{{ !$loop->last ? ', ' : '.' }}
            @endforeach
        </p>
    </section>
@endif

@if ($product->description)
    <section>
        <h4>
            <i class="bi bi-card-text" style="font-size: 20px"></i>
            {{__('admin.Descrizione')}}
        </h4>
        <p>{{$product->description}}</p>
    </section>
@endif

<div class="price">€{{$product->price / 100}}</div>

<div class="allergens">
    @foreach ($product->allergens as $i)
        <div class="al">
            <img src="{{$i->img}}" alt="" title="{{$i->name}}">
            {{$i->name}}
        </div>
    @endforeach
</div>
