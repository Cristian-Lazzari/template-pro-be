@extends('layouts.base')

@section('contents')
@if (session('success'))
    @php $data = session('success') @endphp
    <div class="alert alert-primary">{{ $data }}</div>
@endif
@if (session('filter'))
    @php
        $data = session('filter');
        $filters = $data[0];
        $ress = $data[1];
    @endphp
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ __('admin.Filtri_aggiornati') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('admin.common.close') }}"></button>
    </div>
@endif

<style>
    .res-page {
        display: grid;
        gap: var(--admin-page-section-gap, 22px);
        width: 100%;
    }

    /* ── Toolbar shell ───────────────────────────────────── */
    .res-toolbar {
        position: sticky;
        top: 18px;
        z-index: 140;
        width: fit-content;
        margin-left: auto;
        transition: width .32s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .res-toolbar[data-open="true"] {
        width: 100%;
    }

    .res-toolbar__inner {
        display: flex;
        align-items: center;
        gap: 8px;
        /* border-radius: 24px;
        border: 1px solid rgba(216, 221, 232, 0.14);
        padding: 10px 16px;
        background:
            radial-gradient(circle at top left, rgba(14, 183, 146, 0.18), transparent 24%),
            radial-gradient(circle at 85% 20%, rgba(216, 221, 232, 0.12), transparent 22%),
            linear-gradient(145deg, rgba(216, 221, 232, 0.12), rgba(216, 221, 232, 0.04)),
            rgba(9, 3, 51, 0.82);
        backdrop-filter: blur(18px); */
        /* box-shadow: 0 18px 40px rgba(0, 0, 0, 0.18); */
        justify-content: flex-end;
    }

    /* ── Controls (collapsible) ──────────────────────────── */
    .res-toolbar__controls {
        display: flex;
        align-items: center;
        gap: 8px;
        flex: 1 1 auto;
        min-width: 0;
        overflow: hidden;
        max-width: 0;
        opacity: 0;
        pointer-events: none;
        transition: max-width .32s cubic-bezier(0.4, 0, 0.2, 1), opacity .22s ease;
    }

    .res-toolbar[data-open="true"] .res-toolbar__inner {
        justify-content: space-between;
    }

    .res-toolbar[data-open="true"] .res-toolbar__controls {
        max-width: 900px;
        opacity: 1;
        pointer-events: auto;
    }

    .res-toolbar__filter {
        display: flex;
        align-items: center;
        gap: 8px;
        flex: 1 1 auto;
        min-width: 0;
        min-height: 38px;
        padding: 0 12px;
        border-radius: 14px;
        border: 1px solid rgba(216, 221, 232, 0.62);
        background: rgba(9, 3, 51, 0.387);
        cursor: text;
        backdrop-filter: blur(12px);
    }

    .res-toolbar__filter-icon {
        flex: 0 0 auto;
        color: rgba(216, 221, 232, 0.46);
        font-size: 14px;
        line-height: 1;
    }

    .res-toolbar__filter input {
        flex: 1 1 auto;
        min-width: 80px;
        border: 0;
        background: transparent;
        color: var(--c3);
        font-size: var(--fs-100);
        outline: none;
    }

    .res-toolbar__filter input::placeholder {
        color: rgba(216, 221, 232, 0.36);
    }

    .res-toolbar__btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        min-height: 38px;
        padding: 0 14px;
        border-radius: 14px;
        border: 1px solid rgba(216, 221, 232, 0.62);
        background: rgba(9, 3, 51, 0.387);
        color: var(--c3);
        font-size: var(--fs-100);
        font-weight: 700;
        cursor: pointer;
        white-space: nowrap;
        flex: 0 0 auto;
        backdrop-filter: blur(12px);
        transition: background .18s ease, border-color .18s ease;
    }

    .res-toolbar__btn:hover {
        background: rgba(14, 183, 146, 0.12);
        border-color: rgba(14, 183, 146, 0.28);
    }

    .res-toolbar__btn--icon {
        min-width: 38px;
        padding: 0;
    }

    /* ── Toggle funnel button ────────────────────────────── */
    .res-toolbar__toggle {
        flex: 0 0 auto;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 38px;
        height: 38px;
        border-radius: 14px;
        border: 1px solid rgba(216, 221, 232, 0.16);
        background: rgba(9, 3, 51, 0.387);
        color: var(--c3);
        cursor: pointer;
        backdrop-filter: blur(12px);
        transition: background .18s ease, border-color .18s ease;
    }

    .res-toolbar__toggle:hover {
        background: rgba(14, 183, 146, 0.12);
        border-color: rgba(14, 183, 146, 0.28);
    }

    .res-toolbar[data-open="true"] .res-toolbar__toggle {
        background: rgba(14, 183, 146, 0.38);
        border-color: rgba(14, 183, 146, 0.36);
        color: #8ef6db;
    }

    .res-toolbar__toggle .bi-funnel { display: block; }
    .res-toolbar__toggle .bi-funnel-fill { display: none; }
    .res-toolbar[data-open="true"] .res-toolbar__toggle .bi-funnel { display: none; }
    .res-toolbar[data-open="true"] .res-toolbar__toggle .bi-funnel-fill { display: block; }

    /* ── Date/time separators ────────────────────────────── */
    #res-list.res_index .date_time .time {
        font-size: var(--fs-200);
        font-weight: 900;
        color: var(--c3);
        flex: 0 0 auto;
    }

    #res-list.res_index .date_time .line {
        flex: 1;
        border-bottom: 1px dashed rgba(216, 221, 232, 0.18);
        height: 1px;
    }

    #res-list.res_index .date_time .data {
        flex: 0 0 auto;
        font-size: var(--fs-100);
        color: rgba(216, 221, 232, 0.44);
        font-family: monospace;
    }

    /* ── List items ──────────────────────────────────────── */
    #res-list.res_index {
        gap: .4rem !important;
    }

    #res-list.res_index .res-item {
        border-radius: 16px;
        border: 1px solid rgba(216, 221, 232, 0.1);
    }

    #res-list.res_index .res-item .top {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 11px 14px;
        flex-wrap: wrap;
    }

    #res-list.res_index .res-item .name {
        font-weight: 700;
        flex: 1 1 140px;
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    #res-list.res_index .res-item .guest {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: var(--fs-100);
        flex: 0 0 auto;
        color: rgba(216, 221, 232, 0.82);
    }

    #res-list.res_index .res-item .price {
        font-weight: 800;
        font-family: monospace;
        font-size: var(--fs-100);
        flex: 0 0 auto;
    }

    #res-list.res_index .res-item .status {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 3px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        flex: 0 0 auto;
        border: 1px solid transparent;
    }

    #res-list.res_index a.res-item {
        text-decoration: none;
        display: block;
        cursor: pointer;
        transition: opacity .15s ease, transform .15s ease;
    }

    #res-list.res_index a.res-item:hover {
        opacity: .88;
        transform: translateY(-1px);
    }

    @media (max-width: 640px) {
        /* Sempre full-width su mobile: elimina fit-content che sfora il viewport */
        .res-toolbar {
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
        }

        /* Chiusa: toggle allineato a destra, nessun background pieno */
        .res-toolbar:not([data-open="true"]) .res-toolbar__inner {
            justify-content: flex-end;
            background: transparent;
            border-color: transparent;
            box-shadow: none;
            backdrop-filter: none;
            padding: 0;
        }
        .res-toolbar[data-open="true"] .res-toolbar__inner {
            flex-direction: column !important;
            align-items: flex-end
        }
        #typeToggle{
            flex: 1;
        }
        
        /* Aperta: i controlli possono andare a capo */
        .res-toolbar[data-open="true"] .res-toolbar__controls {
            flex-wrap: wrap;
        }

        .res-toolbar__filter {
            flex: 1 1 100%;
            min-width: 0;
        }
    }
</style>

<div class="dash_page res-page">
    <header class="menu-dashboard__hero order-detail__summary">
        <div class="order-detail__meta">
            <div class="order-detail__status">
                <span class="order-detail__status-icon order-detail__status-icon--active">
                    <i class="bi bi-credit-card-2-front-fill"></i>
                </span>
                <strong>{{ __('admin.Prenotazioni') }}</strong>
            </div>
            <h1 class="menu-dashboard__title">{{ __('admin.Lista_prenotazioni') }} & {{ __('admin.Ordini') }}</h1>
        </div>
        <div class="menu-dashboard__hero-actions dashboard-home__hero-actions">
            <a href="{{ route('admin.dashboard') }}" class="order-detail__contact">
                <i class="bi bi-grid-1x2-fill"></i>
                <span>{{ __('admin.common.dashboard') }}</span>
            </a>
        </div>
    </header>

    <div class="res-toolbar" id="resToolbar" data-open="false">
        <div class="res-toolbar__inner">
            <div class="res-toolbar__controls">
                <label class="res-toolbar__filter">
                    <span class="res-toolbar__filter-icon" aria-hidden="true">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" id="searchInput" placeholder="{{ __('admin.Cerca_cliente') }}">
                </label>
                <button id="typeToggle" class="res-toolbar__btn">{{ __('admin.Tutti') }}</button>
                <button id="sortToggle" class="res-toolbar__btn res-toolbar__btn--icon" title="Ordina">
                    <i class="bi bi-sort-down-alt"></i>
                </button>
            </div>
            <button id="filterToggle" class="res-toolbar__toggle" aria-expanded="false" aria-label="{{ __('admin.Filtri') }}">
                <i class="bi bi-funnel"></i>
                <i class="bi bi-funnel-fill"></i>
            </button>
        </div>
    </div>

    @php
        $old_date = '';
        $old_time = '';
        use Carbon\Carbon;
    @endphp

    <div id="res-list" class="time-list res_index">

        @foreach ($reservations as $res)
            @php $promotionSummary = $res->promotion_summary ?? ['has_promotion' => false]; @endphp

            <a href="{{ $res->n_person ? route('admin.reservations.show', $res->id) : route('admin.orders.show', $res->id) }}"
                class="res-item
                @if(in_array($res->status, [0, 6])) null
                @elseif(in_array($res->status, [2, 3])) to_see
                @elseif(in_array($res->status, [1, 5])) okk
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

                    <div class="name">{{ $res->name }} {{ $res->surname }}</div>

                    @php $n_person = json_decode($res->n_person); @endphp
                    @if (isset($res->n_person))
                        <div class="guest">
                            @if ($n_person->adult > 0)
                                {{ $n_person->adult }}<i class="bi bi-person-standing"></i>
                            @endif
                            @if ($n_person->child > 0)
                                {{ $n_person->child }}<i class="bi bi-person-arms-up"></i>
                            @endif
                        </div>
                    @endif

                    @if (in_array($res->status, [3, 5, 6]))
                        <div class="{{ $res->status == 6 ? 'refound' : 'paid' }} status">
                            <i class="bi bi-credit-card-2-back"></i>
                            {{ $res->status == 6 ? __('admin.Rimborsato') : __('admin.Pagato') }}
                        </div>
                    @endif

                    @if (isset($res->tot_price))
                        <div class="price">{{ \App\Support\Currency::formatCents($res->tot_price) }}</div>
                    @endif

                    @if ($promotionSummary['has_promotion'] ?? false)
                        <div class="status promo-badge" title="{{ $promotionSummary['name'] ?? __('admin.dashboard.promotion_applied') }}">
                            <i class="bi bi-gift-fill"></i>
                            {{ $promotionSummary['badge_label'] ?? __('admin.common.promo') }}
                        </div>
                    @endif

                    <i class="bi bi-chevron-right" style="margin-left:auto;opacity:.35;flex:0 0 auto;font-size:13px;"></i>
                </div>
            </a>
        @endforeach
    </div>
</div>


<script>
document.addEventListener("DOMContentLoaded", function() {
    const list = document.getElementById("res-list");
    const items = Array.from(list.querySelectorAll(".res-item"));

    const searchInput = document.getElementById("searchInput");
    const typeToggle = document.getElementById("typeToggle");
    const sortToggle = document.getElementById("sortToggle");
    const filterToggle = document.getElementById("filterToggle");
    const resToolbar = document.getElementById("resToolbar");

    filterToggle?.addEventListener("click", () => {
        const isOpen = resToolbar.dataset.open === "true";
        resToolbar.dataset.open = isOpen ? "false" : "true";
        filterToggle.setAttribute("aria-expanded", isOpen ? "false" : "true");
        if (isOpen) {
            searchInput.value = "";
            render();
        } else {
            requestAnimationFrame(() => searchInput.focus());
        }
    });

    let currentType = "all";
    let sortDir = "desc";

    function parseDateSlot(slot) {
        if (!slot) return new Date(0);
        const [date, time] = slot.split(" ");
        const [d, m, y] = date.split("/");
        const [h, min] = time.split(":");
        return new Date(y, m - 1, d, h, min);
    }

    function render() {
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

        filtered.sort((a, b) => {
            const da = parseDateSlot(a.dataset.dateSlot);
            const db = parseDateSlot(b.dataset.dateSlot);
            return sortDir === "asc" ? da - db : db - da;
        });

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

            let showTime = !(time == oldTime && date == oldDate);
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

    const down_svg = `<i class="bi bi-sort-down-alt"></i>`;
    const up_svg = `<i class="bi bi-sort-up"></i>`;

    sortToggle?.addEventListener("click", () => {
        sortDir = sortDir === "desc" ? "asc" : "desc";
        sortToggle.innerHTML = sortDir === "desc" ? down_svg : up_svg;
        render();
    });

    render();
});
</script>

@endsection
