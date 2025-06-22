<nav class="navbar navbar-expand-lg nav">
    <div class="container-fluid">

        <div class="d-flex">
            <a class="{{ request()->routeIs('admin.dashboard') ? 'my_btn_1 mylinknavs active_link' : 'my_btn_1 mylinknavs' }}" href="{{ route('admin.dashboard') }}">Dashboard</a>
            {{-- <div class="notifications_container h-100 ms-2">
                <button class="my_btn_1 search" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasExample" aria-controls="offcanvasExample">
                    <svg  xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="white" class="bi bi-bell-fill" viewBox="0 0 16 16">
                        <path d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2m.995-14.901a1 1 0 1 0-1.99 0A5 5 0 0 0 3 6c0 1.098-.5 6-2 7h14c-1.5-1-2-5.902-2-7 0-2.42-1.72-4.44-4.005-4.901"/>
                    </svg>
                </button>
                <div class="notifications_count">{{ count($notifications)}}</div>
            </div> --}}
        </div>

        <button class="navbar-toggler myitem" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse flex-grow-0 " id="navbarNavDropdown">

            <ul class="navbar-nav me-auto mb-2 mb-lg-0 gap-1">
                <li class="nav-item mx-auto dropdown">
                    <a class="{{ request()->routeIs('admin.products.index') ? 'nav-link mylinknav dropdown-toggle active_link' : 'nav-link mylinknav dropdown-toggle' }}" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Menu
                    </a>
                    <ul class="dropdown-menu">
                        <li><h5 class="dropdown-header">Prodotti</h5></li>
                        <li><a class="dropdown-item" href="{{ route('admin.products.index') }}">Mostra tutti</a></li>
                        <li><a class="dropdown-item" href="{{ route('admin.products.create') }}">Aggiungi</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><h5 class="dropdown-header">Categorie</h5></li>
                        <li><a class="dropdown-item" href="{{ route('admin.categories.index') }}">Mostra tutti</a></li>
                        <li><a class="dropdown-item" href="{{ route('admin.categories.create') }}">Aggiungi</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><h5 class="dropdown-header">Ingredienti</h5></li>
                        <li><a class="dropdown-item" href="{{ route('admin.ingredients.index') }}">Mostra tutti</a></li>
                        <li><a class="dropdown-item" href="{{ route('admin.ingredients.create') }}">Aggiungi</a></li>
                    </ul>
                </li>
                
                <li class="nav-item mx-auto dropdown">
                    <a class="{{ request()->routeIs('admin.posts.index') ? 'nav-link mylinknav dropdown-toggle active_link' : 'nav-link mylinknav dropdown-toggle' }}" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Post
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('admin.posts.index') }}">Mostra tutti</a></li>
                        <li><a class="dropdown-item" href="{{ route('admin.posts.create') }}">Aggiungi</a></li>
                    </ul>
                </li>
                @if (config('configurazione.subscription') > 1)    
                <li class="nav-item mx-auto dropdown">
                    <a class="{{ request()->routeIs('admin.reservations.index') ? 'nav-link mylinknav dropdown-toggle active_link' : 'nav-link mylinknav dropdown-toggle' }}" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Prenotazioni & Ordini
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('admin.reservations.index') }}">Prenotazioni tavoli</a></li>
                        <li><a class="dropdown-item" href="{{ config('configurazione.domain') . '/check-out'}}">Crea Prenotazione</a></li>
                        <li><a class="dropdown-item" href="{{ route('admin.orders.index') }}">Asporto & Delivery</a></li>
                        <li><a class="dropdown-item" href="{{ config('configurazione.domain') . '/ordina'}}">Crea Ordine</a></li>
                    </ul>
                </li>
                @endif
                
                @if (config('configurazione.subscription') > 1)  
                <li class="nav-item mx-auto ">
                    <a class="{{ request()->routeIs('admin.dates.index') ? 'nav-link mylinknav active_link' : 'nav-link mylinknav' }}" href="{{ route('admin.dates.index') }}">
                        Gestione date
                    </a>
                </li>      
                @endif

                @if (config('configurazione.subscription') > 2)  
                <li class="nav-item mx-auto ">
                    <a class="{{ request()->routeIs('admin.statistics') ? 'nav-link mylinknav active_link' : 'nav-link mylinknav' }}" href="{{ route('admin.statistics') }}">
                        Statistiche
                    </a>
                </li>      
                @endif

                <button id="theme-toggle" class="my_btn_1 mx-auto">
                    <svg id="dark" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-moon-fill" viewBox="0 0 16 16">
                        <path d="M6 .278a.77.77 0 0 1 .08.858 7.2 7.2 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277q.792-.001 1.533-.16a.79.79 0 0 1 .81.316.73.73 0 0 1-.031.893A8.35 8.35 0 0 1 8.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.75.75 0 0 1 6 .278"/>
                    </svg>

                    <svg id="light" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-sun-fill" viewBox="0 0 16 16">
                        <path d="M8 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8M8 0a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 0m0 13a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 13m8-5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2a.5.5 0 0 1 .5.5M3 8a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2A.5.5 0 0 1 3 8m10.657-5.657a.5.5 0 0 1 0 .707l-1.414 1.415a.5.5 0 1 1-.707-.708l1.414-1.414a.5.5 0 0 1 .707 0m-9.193 9.193a.5.5 0 0 1 0 .707L3.05 13.657a.5.5 0 0 1-.707-.707l1.414-1.414a.5.5 0 0 1 .707 0m9.193 2.121a.5.5 0 0 1-.707 0l-1.414-1.414a.5.5 0 0 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .707M4.464 4.465a.5.5 0 0 1-.707 0L2.343 3.05a.5.5 0 1 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .708"/>
                    </svg>
                </button>
                     
                
            </ul>   
        </div>
    </div>
  </nav>



  