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
        
      Filtri aggiornati
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="dash_page">
    <h1>
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-credit-card-2-front-fill" viewBox="0 0 16 16">
            <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm2.5 1a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h2a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5zm0 3a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1zm0 2a.5.5 0 0 0 0 1h1a.5.5 0 0 0 0-1zm3 0a.5.5 0 0 0 0 1h1a.5.5 0 0 0 0-1zm3 0a.5.5 0 0 0 0 1h1a.5.5 0 0 0 0-1zm3 0a.5.5 0 0 0 0 1h1a.5.5 0 0 0 0-1z"/>
        </svg>
        Lista prenotazioni
        <br>
        & ordini
    </h1>

    
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

    
    @php
        $old_date = '';
        $old_time = '';
        use Carbon\Carbon;
    @endphp 
    
    <div id="res-list" class="time-list res_index">
    
        @foreach ($reservations as $res)
            @php
             $parts = explode(" ", $res->date_slot);
             $date = $parts[0];
             $time = $parts[1];
            @endphp 
            @if (($time !== $old_time) || ($date !== $old_date) )
            <p class="date_time">
                @if ($time !== $old_time)
                <span class="time
                @if ($date == $old_date) op @endif
                "> 
                     {{$time}}
                </span>
                
                @endif
                @if ($date !== $old_date)
                <span class="line"></span>
                <span class="data"> 
                        {{
                            $formattedDate = Carbon::createFromFormat('d/m/Y', $date)
                            ->locale('it')
                            ->translatedFormat('l j F');
                        }}
                </span>
                @endif
            </p>
            @endif
            @php
             $parts = explode(" ", $res->date_slot);
             $old_date = $parts[0];
             $old_time = $parts[1];
            @endphp 
        
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
                <div class="name">{{$res->name. ' ' . $res->surname}} </div>
                @php $n_person = json_decode($res->n_person); @endphp
                @if (isset($res->n_person))
                    <div class="guest">
                    @if ($n_person->adult > 0)
                        {{$n_person->adult}}
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-standing" viewBox="0 0 16 16">
                            <path d="M8 3a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3M6 6.75v8.5a.75.75 0 0 0 1.5 0V10.5a.5.5 0 0 1 1 0v4.75a.75.75 0 0 0 1.5 0v-8.5a.25.25 0 1 1 .5 0v2.5a.75.75 0 0 0 1.5 0V6.5a3 3 0 0 0-3-3H7a3 3 0 0 0-3 3v2.75a.75.75 0 0 0 1.5 0v-2.5a.25.25 0 0 1 .5 0"/>
                        </svg>
                    @endif
                    @if ($n_person->child > 0)
                        {{$n_person->child}}
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-arms-up" viewBox="0 0 16 16">
                            <path d="M8 3a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3"/>
                            <path d="m5.93 6.704-.846 8.451a.768.768 0 0 0 1.523.203l.81-4.865a.59.59 0 0 1 1.165 0l.81 4.865a.768.768 0 0 0 1.523-.203l-.845-8.451A1.5 1.5 0 0 1 10.5 5.5L13 2.284a.796.796 0 0 0-1.239-.998L9.634 3.84a.7.7 0 0 1-.33.235c-.23.074-.665.176-1.304.176-.64 0-1.074-.102-1.305-.176a.7.7 0 0 1-.329-.235L4.239 1.286a.796.796 0 0 0-1.24.998l2.5 3.216c.317.316.475.758.43 1.204Z"/>
                        </svg>
                    @endif
                </div>
                @endif
                @if (in_array($res->status, [3,5,6]))
                    <div class="{{ $res->status == 6 ? 'refound' : 'paid' }} status">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-credit-card-2-back" viewBox="0 0 16 16">
                            <path d="M11 5.5a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1-.5-.5z"/>
                            <path d="M2 2a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2zm13 2v5H1V4a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1m-1 9H2a1 1 0 0 1-1-1v-1h14v1a1 1 0 0 1-1 1"/>
                        </svg>
                        {{ $res->status == 6 ? 'Rimborsato' : 'Pagato' }}
                    </div>
                @endif
                @if (isset($res->tot_price))
                    <div class="price">€{{$res->tot_price / 100}}</div>
                @endif

                
                <div class="btn-group dropup">
                    <button type="button" class="action_menu" data-bs-toggle="dropdown" aria-expanded="false">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-three-dots-vertical" viewBox="0 0 16 16">
                        <path d="M9.5 13a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0m0-5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0m0-5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0"/>
                        </svg>
                    </button>
                    <ul class="dropdown-menu">
                        <li>
                            <h4>#{{isset($res->n_person) ? 'R':'O'}}{{$res->id}}</h4>
                        </li>
                        <li>
                            <a href="{{ $res->n_person ? route('admin.reservations.show', $res->id) : route('admin.orders.show', $res->id)}}">Vedi</a>
                        </li>
                        <li>Chiama</li>
                        <li>Conferma</li>
                        <li>Annulla</li>
                    </ul>
                </div>

            </div>
            </div>
        
            {{-- Modale per conferma --}}
            <div class="modal fade" id="confirmModal{{$res->id}}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="confirmModalLabel{{$res->id}}" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header c-1">
                            <h1 class="modal-title fs-5" id="confirmModalLabel{{$res->id}}">Gestione notifica conferma</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body c-1">
                            Ordine di: {{$res->name}} 
                            per il: {{$res->date_slot}}
                            <p>Vuoi inviare un messaggio whatsapp?</p>
                        </div>
                        <div class="modal-footer">
                            <form action="{{ route('admin.reservations.status') }}" method="POST">
                                @csrf
                                <input value="1" type="hidden" name="wa">
                                <input value="1" type="hidden" name="c_a">
                                <input value="{{$res->id}}" type="hidden" name="id">
                                <button type="submit" class="w-100 my_btn_6">Si</button>
                            </form>
                            <form action="{{ route('admin.reservations.status') }}" method="POST">
                                @csrf
                                <input value="0" type="hidden" name="wa">
                                <input value="1" type="hidden" name="c_a">
                                <input value="{{$res->id}}" type="hidden" name="id">
                                <button type="submit" class="w-100 my_btn_6">NO</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        
            {{-- Modale per annullamento --}}
            <div class="modal fade" id="cancelModal{{$res->id}}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="cancelModalLabel{{$res->id}}" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header c-1">
                            <h1 class="modal-title fs-5" id="cancelModalLabel{{$res->id}}">Gestione notifica annullamento</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body c-1">
                            Ordine di: {{$res->name}} 
                            per il: {{$res->date_slot}}
                            <p>Vuoi inviare un messaggio whatsapp?</p>
                        </div>
                        <div class="modal-footer">
                            <form action="{{ route('admin.reservations.status') }}" method="POST">
                                @csrf
                                <input value="1" type="hidden" name="wa">
                                <input value="0" type="hidden" name="c_a">
                                <input value="{{$res->id}}" type="hidden" name="id">
                                <button type="submit" class="w-100 my_btn_6">Si</button>
                            </form>
                            <form action="{{ route('admin.reservations.status') }}" method="POST">
                                @csrf
                                <input value="0" type="hidden" name="wa">
                                <input value="0" type="hidden" name="c_a">
                                <input value="{{$res->id}}" type="hidden" name="id">
                                <button type="submit" class="w-100 my_btn_6">NO</button>
                            </form>
                        </div>
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

            let showTime = time !== oldTime;
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
            typeToggle.textContent = "Prenotazioni";
        } else if (currentType === "reservations") {
            currentType = "orders";
            typeToggle.textContent = "Ordini";
        } else {
            currentType = "all";
            typeToggle.textContent = "Tutti";
        }
        render();
    });

    down_svg = `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-sort-down-alt" viewBox="0 0 16 16">
            <path d="M3.5 3.5a.5.5 0 0 0-1 0v8.793l-1.146-1.147a.5.5 0 0 0-.708.708l2 1.999.007.007a.497.497 0 0 0 .7-.006l2-2a.5.5 0 0 0-.707-.708L3.5 12.293zm4 .5a.5.5 0 0 1 0-1h1a.5.5 0 0 1 0 1zm0 3a.5.5 0 0 1 0-1h3a.5.5 0 0 1 0 1zm0 3a.5.5 0 0 1 0-1h5a.5.5 0 0 1 0 1zM7 12.5a.5.5 0 0 0 .5.5h7a.5.5 0 0 0 0-1h-7a.5.5 0 0 0-.5.5"/>
        </svg>`;
    up_svg = `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-sort-up" viewBox="0 0 16 16">
            <path d="M3.5 12.5a.5.5 0 0 1-1 0V3.707L1.354 4.854a.5.5 0 1 1-.708-.708l2-1.999.007-.007a.5.5 0 0 1 .7.006l2 2a.5.5 0 1 1-.707.708L3.5 3.707zm3.5-9a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5M7.5 6a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1zm0 3a.5.5 0 0 0 0 1h3a.5.5 0 0 0 0-1zm0 3a.5.5 0 0 0 0 1h1a.5.5 0 0 0 0-1z"/>
        </svg>`;

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