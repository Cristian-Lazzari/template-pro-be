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
       " {{ $data->name }} " e stato eliminato correttamente
    </div>
@endif
 
<div class="dash_page">

    <h1>
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-fork-knife" viewBox="0 0 16 16">
            <path d="M13 .5c0-.276-.226-.506-.498-.465-1.703.257-2.94 2.012-3 8.462a.5.5 0 0 0 .498.5c.56.01 1 .13 1 1.003v5.5a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5zM4.25 0a.25.25 0 0 1 .25.25v5.122a.128.128 0 0 0 .256.006l.233-5.14A.25.25 0 0 1 5.24 0h.522a.25.25 0 0 1 .25.238l.233 5.14a.128.128 0 0 0 .256-.006V.25A.25.25 0 0 1 6.75 0h.29a.5.5 0 0 1 .498.458l.423 5.07a1.69 1.69 0 0 1-1.059 1.711l-.053.022a.92.92 0 0 0-.58.884L6.47 15a.971.971 0 1 1-1.942 0l.202-6.855a.92.92 0 0 0-.58-.884l-.053-.022a1.69 1.69 0 0 1-1.059-1.712L3.462.458A.5.5 0 0 1 3.96 0z"/>
        </svg>
        Prodotti</h1>
    
    <div class="action-page">
        <a class="my_btn_1 create m-1 w-auto" href="{{ route('admin.products.create') }}">Crea un nuovo prodotto</a>
        <a class="my_btn_1 trash m-1 w-auto" href="{{ route('admin.products.archived') }}">Archivio</a>
    </div>
    <div class="filters">
        <div class="bar">
            <input type="checkbox" class="check" id="f">
            <div class="box">
                <input type="text" id="searchInput" class="search" placeholder="Cerca cliente..." >
                <button id="typeToggle" class="type">Tutti</button>
                <button id="sortToggle" class="order">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-sort-down-alt" viewBox="0 0 16 16">
                        <path d="M3.5 3.5a.5.5 0 0 0-1 0v8.793l-1.146-1.147a.5.5 0 0 0-.708.708l2 1.999.007.007a.497.497 0 0 0 .7-.006l2-2a.5.5 0 0 0-.707-.708L3.5 12.293zm4 .5a.5.5 0 0 1 0-1h1a.5.5 0 0 1 0 1zm0 3a.5.5 0 0 1 0-1h3a.5.5 0 0 1 0 1zm0 3a.5.5 0 0 1 0-1h5a.5.5 0 0 1 0 1zM7 12.5a.5.5 0 0 0 .5.5h7a.5.5 0 0 0 0-1h-7a.5.5 0 0 0-.5.5"/>
                    </svg>
                </button>
            </div>
            <label for="f">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-funnel-fill" viewBox="0 0 16 16">
                    <path d="M1.5 1.5A.5.5 0 0 1 2 1h12a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.128.334L10 8.692V13.5a.5.5 0 0 1-.342.474l-3 1A.5.5 0 0 1 6 14.5V8.692L1.628 3.834A.5.5 0 0 1 1.5 3.5z"/>
                </svg>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-funnel" viewBox="0 0 16 16">
                    <path d="M1.5 1.5A.5.5 0 0 1 2 1h12a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.128.334L10 8.692V13.5a.5.5 0 0 1-.342.474l-3 1A.5.5 0 0 1 6 14.5V8.692L1.628 3.834A.5.5 0 0 1 1.5 3.5zm1 .5v1.308l4.372 4.858A.5.5 0 0 1 7 8.5v5.306l2-.666V8.5a.5.5 0 0 1 .128-.334L13.5 3.308V2z"/>
                </svg>
            </label>
        </div>
    </div>
    
    <div class="time-list prod_index">
        @foreach ($products as $item)

            <div class="res-item prod">
                @if (isset($item->image))
                    <img src="{{ asset('public/storage/' . $item->image) }}" alt="{{$item->name}}">
                @else
                    <div class="no_img">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-image-fill" viewBox="0 0 16 16">
                        <path d="M.002 3a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2h-12a2 2 0 0 1-2-2zm1 9v1a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V9.5l-3.777-1.947a.5.5 0 0 0-.577.093l-3.71 3.71-2.66-1.772a.5.5 0 0 0-.63.062zm5-6.5a1.5 1.5 0 1 0-3 0 1.5 1.5 0 0 0 3 0"/>
                        </svg>
                    </div>
                @endif 
                <div class="name_cat">
                    <div class="name">{{$item->name}}</div>
                    <div class="cat">{{$item->category->name}}</div>
                </div>
                <div class="price_btn">
                    <div class="price">€{{$item->price / 100}}</div>
                    <button type="button" class="action_menu action_menu_info" data-bs-toggle="modal" data-bs-target="#exampleModal{{$item->id}}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-info-circle-fill" viewBox="0 0 16 16">
                            <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16m.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.47l-.451-.081.082-.381 2.29-.287zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2"/>
                        </svg>
                        Info
                    </button>
                </div>
                
            </div>
            <!-- Modal -->
            <div class="modal fade" id="exampleModal{{$item->id}}" tabindex="-1" aria-labelledby="exampleModal{{$item->id}}Label" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-body">
                            <button type="button" class="btn_close" data-bs-dismiss="modal">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-x-circle-fill" viewBox="0 0 16 16">
                                <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M5.354 4.646a.5.5 0 1 0-.708.708L7.293 8l-2.647 2.646a.5.5 0 0 0 .708.708L8 8.707l2.646 2.647a.5.5 0 0 0 .708-.708L8.707 8l2.647-2.646a.5.5 0 0 0-.708-.708L8 7.293z"/>
                                </svg>
                                Chiudi
                            </button>
                            <div class="action_top">
                                <a href="{{ route('admin.products.edit', $item) }}" class="edit">
                                    <svg style="vertical-align: sub" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-pencil-square" viewBox="0 0 16 16">
                                        <path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/>
                                        <path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z"/>
                                    </svg>
                                </a>
                                
                                <form action="{{ route('admin.products.status') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="archive" value="0">
                                    <input type="hidden" name="v" value="1">
                                    <input type="hidden" name="a" value="0">
                                    <input type="hidden" name="id" value="{{$item->id}}">
                                    <button type="submit" class=" edit
                                        @if(!$item->visible) not @endif 
                                        visible">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-eye-fill" viewBox="0 0 16 16">
                                            <path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0"/>
                                            <path d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8m8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7"/>
                                        </svg>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-eye-slash-fill" viewBox="0 0 16 16">
                                            <path d="m10.79 12.912-1.614-1.615a3.5 3.5 0 0 1-4.474-4.474l-2.06-2.06C.938 6.278 0 8 0 8s3 5.5 8 5.5a7 7 0 0 0 2.79-.588M5.21 3.088A7 7 0 0 1 8 2.5c5 0 8 5.5 8 5.5s-.939 1.721-2.641 3.238l-2.062-2.062a3.5 3.5 0 0 0-4.474-4.474z"/>
                                            <path d="M5.525 7.646a2.5 2.5 0 0 0 2.829 2.829zm4.95.708-2.829-2.83a2.5 2.5 0 0 1 2.829 2.829zm3.171 6-12-12 .708-.708 12 12z"/>
                                        </svg>
                                    </button>
                                </form>
                                <form action="{{ route('admin.products.status') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="archive" value="0">
                                    <input type="hidden" name="v" value="0">
                                    <input type="hidden" name="a" value="1">
                                    <input type="hidden" name="id" value="{{$item->id}}">
                                    <button class="edit" type="submit">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-trash-fill" viewBox="0 0 16 16">
                                            <path d="M2.5 1a1 1 0 0 0-1 1v1a1 1 0 0 0 1 1H3v9a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V4h.5a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H10a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1zm3 4a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 .5-.5M8 5a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7A.5.5 0 0 1 8 5m3 .5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 1 0"/>
                                        </svg>
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
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-card-list" viewBox="0 0 16 16">
                                            <path d="M14.5 3a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-13a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5zm-13-1A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h13a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2z"/>
                                            <path d="M5 8a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7A.5.5 0 0 1 5 8m0-2.5a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5m0 5a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5m-1-5a.5.5 0 1 1-1 0 .5.5 0 0 1 1 0M4 8a.5.5 0 1 1-1 0 .5.5 0 0 1 1 0m0 2.5a.5.5 0 1 1-1 0 .5.5 0 0 1 1 0"/>
                                        </svg>
                                        Ingredienti</h4>
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
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-card-text" viewBox="0 0 16 16">
                                            <path d="M14.5 3a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-13a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5zm-13-1A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h13a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2z"/>
                                            <path d="M3 5.5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5M3 8a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9A.5.5 0 0 1 3 8m0 2.5a.5.5 0 0 1 .5-.5h6a.5.5 0 0 1 0 1h-6a.5.5 0 0 1-.5-.5"/>
                                        </svg>
                                        Descrizione</h4>
                                    <p>{{$item->description}}</p>
                                </section>
                            @endif

                            <div class="price">€{{$item->price / 100}}</div>

                            <div class="allergens">
                                @php $all = json_decode($item->allergens) @endphp
                                @foreach ($all as $i)
                                <div class="al">
                                    <img src="{{config('configurazione.allergens')[$i]['img']}}" alt="" title="{{config('configurazione.allergens')[$i]['name']}}">
                                    {{config('configurazione.allergens')[$i]['name']}}
                                </div>
                                @endforeach
                            </div>   

                        </div>
                    </div>
                </div>
            </div>

        @endforeach
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('searchInput');
    const typeToggle = document.getElementById('typeToggle');
    const sortToggle = document.getElementById('sortToggle');
    const productContainer = document.querySelector('.time-list');
    let products = Array.from(productContainer.querySelectorAll('.res-item'));
    
    // --- CATEGORIE ---
    const categories = ['Tutti', ...new Set(products.map(p => p.querySelector('.cat').textContent.trim()))];
    let currentCategoryIndex = 0;

    // --- ORDINAMENTO ---
    let sortMode = 'recent'; // recent | alpha
    const originalOrder = [...products];

    // --- CREA NUOVA ICONA PER ORDINAMENTO ---
    const iconRecent = `
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-sort-down-alt" viewBox="0 0 16 16">
            <path d="M3.5 3.5a.5.5 0 0 0-1 0v8.793l-1.146-1.147a.5.5 0 0 0-.708.708l2 1.999.007.007a.497.497 0 0 0 .7-.006l2-2a.5.5 0 0 0-.707-.708L3.5 12.293zm4 .5a.5.5 0 0 1 0-1h1a.5.5 0 0 1 0 1zm0 3a.5.5 0 0 1 0-1h3a.5.5 0 0 1 0 1zm0 3a.5.5 0 0 1 0-1h5a.5.5 0 0 1 0 1zM7 12.5a.5.5 0 0 0 .5.5h7a.5.5 0 0 0 0-1h-7a.5.5 0 0 0-.5.5"/>
        </svg>`;
    const iconAlpha = `
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-sort-alpha-down" viewBox="0 0 16 16">
            <path d="M10.082 12.59h3.718l-.85-2.406h-2.02l-.848 2.406zm-1.007 1.32 2.833-7.5h1.274l2.832 7.5H14.09l-.566-1.5h-3.398l-.566 1.5zM12.5 4.5V13h-1V4.5H8v-1h4.5v1zM3.5 3.5a.5.5 0 0 1 1 0v8.793l1.146-1.147a.5.5 0 0 1 .708.708l-2 2-.007.007a.497.497 0 0 1-.7-.006l-2-2a.5.5 0 0 1 .707-.708L3.5 12.293V3.5z"/>
        </svg>`;

    // --- FUNZIONI ---
    function filterAndRender() {
        const searchValue = searchInput.value.toLowerCase().trim();
        const selectedCategory = categories[currentCategoryIndex];
        let filtered = [...products];

        // Filtro per nome
        filtered = filtered.filter(p => p.querySelector('.name').textContent.toLowerCase().includes(searchValue));

        // Filtro per categoria
        if (selectedCategory !== 'Tutti') {
            filtered = filtered.filter(p => p.querySelector('.cat').textContent.trim() === selectedCategory);
        }

        // Ordinamento
        if (sortMode === 'alpha') {
            filtered.sort((a, b) => a.querySelector('.name').textContent.localeCompare(b.querySelector('.name').textContent));
        } else {
            filtered = originalOrder.filter(p => filtered.includes(p));
        }

        // Render
        productContainer.innerHTML = '';
        filtered.forEach(p => productContainer.appendChild(p));
    }

    // --- EVENTI ---
    searchInput.addEventListener('input', filterAndRender);

    typeToggle.addEventListener('click', () => {
        currentCategoryIndex = (currentCategoryIndex + 1) % categories.length;
        typeToggle.textContent = categories[currentCategoryIndex];
        filterAndRender();
    });

    sortToggle.addEventListener('click', () => {
        sortMode = sortMode === 'recent' ? 'alpha' : 'recent';
        sortToggle.innerHTML = sortMode === 'recent' ? iconRecent : iconAlpha;
        filterAndRender();
    });

    // Avvio iniziale
    sortToggle.innerHTML = iconRecent;
    filterAndRender();
});
</script>



@endsection