@extends('layouts.base')



@section('contents')
@if (session('success'))
    @php
        $data = session('success')
    @endphp
    <div class="alert alert-primary">
        {{ $data }}
    </div>
@endif
@if (session('filter'))
    @php
        $data = session('filter');
        $filters = $data[0];
        $ress = $data[1];
    @endphp
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        
      {{__('admin.Filtri_aggiornati')}}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="dash_page">
    <h1>
        <i class="bi bi-credit-card-2-front-fill"></i>
        {{__('admin.Lista_prenotazioni')}}
        <br>
        & {{__('admin.Ordini')}}
    </h1>

    
    <div class="filters">
        <div class="bar">
            <input type="checkbox" class="check" id="f">
            <div class="box">
                <input type="text" id="searchInput" class="search" placeholder="{{__('admin.Cerca_cliente')}}" >
                <button id="typeToggle" class="type">{{__('admin.Tutti')}}</button>
                <button id="sortToggle" class="order">
                    <i class="bi bi-sort-down-alt"></i>
                </button>
            </div>
            <label for="f">
                <i class="bi bi-funnel-fill"></i>
                <i class="bi bi-funnel"></i>
            </label>
        </div>
    </div>

    
    @php
        $old_date = '';
        $old_time = '';
        use Carbon\Carbon;
    @endphp 
    
    <div id="res-list" class="time-list res_index">
    
        @foreach ($reservations as $res)
        
            <div class="res-item
                @if(in_array($res->status, [0, 6])) 
                    null
                @elseif(in_array($res->status, [2, 3])) 
                    to_see
                @elseif(in_array($res->status, [1, 5])) 
                    okk
                @endif"
                data-date-slot="{{ $res->date_slot }}"
                data-type="{{ isset($res->n_person) ? 'reservation' : 'order' }}"
            >
                <div class="top">         
                    @if(in_array($res->status, [0, 6])) 
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi null bi-x-circle" viewBox="0 0 16 16">
                            <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
                            <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708"/>
                        </svg>
                    @elseif(in_array($res->status, [2, 3])) 
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi to_see bi-exclamation-circle-fill" viewBox="0 0 16 16">
                            <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M8 4a.905.905 0 0 0-.9.995l.35 3.507a.552.552 0 0 0 1.1 0l.35-3.507A.905.905 0 0 0 8 4m.002 6a1 1 0 1 0 0 2 1 1 0 0 0 0-2"/>
                        </svg>
                    @elseif(in_array($res->status, [1, 5])) 
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi okk bi-check-circle" viewBox="0 0 16 16">
                            <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
                            <path d="m10.97 4.97-.02.022-3.473 4.425-2.093-2.094a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05"/>
                        </svg>
                    @endif
                    <div class="name">{{$res->name}} {{$res->surname}} </div>
                    @php $n_person = json_decode($res->n_person); @endphp
                    @if (isset($res->n_person))
                        <div class="guest">
                        @if ($n_person->adult > 0)
                            {{$n_person->adult}}
                            <i class="bi bi-person-standing"></i>
                        @endif
                        @if ($n_person->child > 0)
                            {{$n_person->child}}
                            <i class="bi bi-person-arms-up"></i>
                        @endif
                    </div>
                    @endif
                    @if (in_array($res->status, [3,5,6]))
                        <div class="{{ $res->status == 6 ? 'refound' : 'paid' }} status">
                            <i class="bi bi-credit-card-2-back"></i>
                            {{ $res->status == 6 ? __('admin.Rimborsato') : __('admin.Pagato') }}
                        </div>
                    @endif
                    @if (isset($res->tot_price))
                        <div class="price">€{{$res->tot_price / 100}}</div>
                    @endif

                    
                    <div class="btn-group dropup">
                        <button type="button" class="action_menu" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-three-dots-vertical"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <li>
                                <h4>#{{isset($res->n_person) ? 'R':'O'}}{{$res->id}}</h4>
                            </li>
                            <li>
                                <a href="{{ $res->n_person ? route('admin.reservations.show', $res->id) : route('admin.orders.show', $res->id)}}">{{__('admin.Vedi')}}</a>
                            </li>
                            <li>
                                <a href="{{ 'tel:' . $res->phone}}">{{__('admin.Chiama')}}</a>
                            </li>
                            {{-- <li>{{ __('admin.Conferma') }}</li>
                            <li>{{ __('admin.Annulla') }}</li> --}}
                        </ul>
                    </div>

                </div>
            </div>
        @endforeach
    </div>
            
</div>

        
<script>
document.addEventListener("DOMContentLoaded", function() {
    const list = document.getElementById("res-list");
    const items = Array.from(list.querySelectorAll(".res-item"));

    // Filtri
    const searchInput = document.getElementById("searchInput");
    const typeToggle = document.getElementById("typeToggle");
    const sortToggle = document.getElementById("sortToggle");

    let currentType = "all"; // all | reservations | orders
    let sortDir = "desc"; // asc | desc

    function parseDateSlot(slot) {
        // formato d/m/Y H:i
        if (!slot) return new Date(0);
        const [date, time] = slot.split(" ");
        const [d, m, y] = date.split("/");
        const [h, min] = time.split(":");
        return new Date(y, m - 1, d, h, min);
    }

    // RENDER FUNZIONE PRINCIPALE
    function render() {
        // filtro
        let filtered = items.filter(i => {
            const type = i.dataset.type;
            const text = i.textContent.toLowerCase();
            const search = searchInput.value.toLowerCase();
            let visible = true;

            if (currentType === "reservations" && type !== "reservation") visible = false;
            if (currentType === "orders" && type !== "order") visible = false;
            if (search && !text.includes(search)) visible = false;

            return visible;
        });

        // sort
        filtered.sort((a, b) => {
            const da = parseDateSlot(a.dataset.dateSlot);
            const db = parseDateSlot(b.dataset.dateSlot);
            return sortDir === "asc" ? da - db : db - da;
        });

        // ricostruzione DOM
        list.innerHTML = "";
        let oldDate = "";
        let oldTime = "";

        filtered.forEach(item => {
            const [date, time] = item.dataset.dateSlot.split(" ");
            const parts = date.split("/");
            const formattedDate = new Date(parts[2], parts[1]-1, parts[0]).toLocaleDateString("it-IT", {
                weekday: "long",
                day: "numeric",
                month: "long"
            });

            let showTime = !(time == oldTime && date == oldDate)
            let showDate = date !== oldDate;

            if (showTime || showDate) {
                const p = document.createElement("p");
                p.className = "date_time";

                if (showTime) {
                    const spanTime = document.createElement("span");
                    spanTime.className = "time" + (date === oldDate ? " op" : "");
                    spanTime.textContent = time;
                    p.appendChild(spanTime);
                }

                if (showDate) {
                    const line = document.createElement("span");
                    line.className = "line";
                    const spanDate = document.createElement("span");
                    spanDate.className = "data";
                    spanDate.textContent = formattedDate;

                    p.appendChild(line);
                    p.appendChild(spanDate);
                }

                list.appendChild(p);
            }

            list.appendChild(item);

            oldDate = date;
            oldTime = time;
        });
    }

    // eventi
    searchInput?.addEventListener("input", render);

    typeToggle?.addEventListener("click", () => {
        if (currentType === "all") {
            currentType = "reservations";
            typeToggle.textContent = "{{__('admin.Prenotazioni')}}";
        } else if (currentType === "reservations") {
            currentType = "orders";
            typeToggle.textContent = "{{__('admin.Ordini')}}";
        } else {
            currentType = "all";
            typeToggle.textContent = "{{__('admin.Tutti')}}";
        }
        render();
    });

    down_svg = `<i class="bi bi-sort-down-alt"></i>`;
    up_svg = `<i class="bi bi-sort-up"></i>`;

    sortToggle?.addEventListener("click", () => {
        sortDir = sortDir === "desc" ? "asc" : "desc";
        sortToggle.innerHTML = sortDir === "desc" ? down_svg : up_svg;
        render();
    });

    // primo render
    render();
});
</script>


@endsection