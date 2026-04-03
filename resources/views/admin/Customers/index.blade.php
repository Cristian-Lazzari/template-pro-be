@extends('layouts.base')

@section('contents')
<style>
    .customer-page .customer-summary {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: .8rem;
        margin-bottom: 1rem;
    }

    .customer-page .summary-card,
    .customer-page .customer-card,
    .customer-page .customer-empty {
        background: var(--c3);
        color: var(--c1);
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, .16);
    }

    .customer-page .summary-card {
        padding: .9rem 1rem;
    }

    .customer-page .summary-card span {
        display: block;
        font-size: .85rem;
        opacity: .75;
    }

    .customer-page .summary-card strong {
        display: block;
        font-size: 1.5rem;
        line-height: 1.2;
    }

    .customer-page .customer-toolbar {
        flex-direction: row;
        display: flex;
        gap: .7rem;
        flex-wrap: wrap;
        margin-bottom: 1rem;
        position: sticky;
        top: 25px;
        z-index: 50;
    }

    .customer-page .customer-toolbar input,
    .customer-page .customer-toolbar select {
        min-height: 44px;
        border-radius: 12px;
        border: 1px solid var(--c3_op_3);
        background: var(--c1);
        color: var(--c3);
        padding: .75rem .9rem;
        font-weight: 700;
    }

    .customer-page .customer-toolbar input {
        flex: 1 1 280px !important;
    }
    .customer-page .customer-toolbar input::placeholder{
        color: var(--c3_op_5);
    }


    .customer-page .customer-toolbar select {
        flex: 0 0 220px !important;
        max-width: 100%;
    }

    .customer-page .customer-lead {
        text-align: center;
        margin: -1.5rem auto 1.4rem;
        max-width: 640px;
        opacity: .78;
    }

    .customer-page .customer-list {
        display: flex;
        flex-direction: column;
        gap: .9rem;
    }

    .customer-page .customer-card {
        padding: 1rem;
        display: flex;
        flex-direction: column;
        gap: .9rem;
    }

    .customer-page .customer-card__top,
    .customer-page .customer-card__bottom,
    .customer-page .customer-card__contacts,
    .customer-page .customer-card__stats {
        display: flex;
        gap: .7rem;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
    }

    .customer-page .customer-card__name {
        display: flex;
        flex-direction: column;
        gap: .15rem;
    }

    .customer-page .customer-card__name h2 {
        margin: 0;
        font-size: 1rem;
        text-transform: uppercase;
        font-weight: 900;
    }

    .customer-page .customer-card__name p {
        margin: 0;
        opacity: .72;
    }

    .customer-page .customer-card__contacts a {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        color: inherit;
        text-decoration: none;
        font-weight: 700;
        background: rgba(30, 45, 100, .08);
        border-radius: 999px;
        padding: .45rem .75rem;
    }

    .customer-page .customer-card__contacts svg,
    .customer-page .customer-card__button svg {
        width: 16px;
        height: 16px;
    }

    .customer-page .customer-chip {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        padding: .45rem .7rem;
        border-radius: 999px;
        background: var(--c1);
        color: var(--c3);
        font-size: .84rem;
        font-weight: 900;
    }

    .customer-page .customer-card__button {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        text-decoration: none;
        background: var(--c2);
        color: var(--c3);
        padding: .65rem .9rem;
        border-radius: 12px;
        font-weight: 900;
    }

    .customer-page .customer-empty {
        padding: 1rem;
        text-align: center;
    }

    @media (max-width: 640px) {
        .customer-page .customer-card__top,
        .customer-page .customer-card__bottom {
            align-items: flex-start;
            flex-direction: column;
        }

        .customer-page .customer-toolbar {
            top: 10px;
        }
    }
</style>

<div class="dash_page customer-page">
    <h1>
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-people-fill" viewBox="0 0 16 16">
            <path d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1zm4-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6m-5.784 6A2.24 2.24 0 0 1 5 13c0-1.355.68-2.75 1.936-3.72A6.3 6.3 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1zM4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5"/>
        </svg>
        {{ __('admin.Clienti') }}
    </h1>

    <p class="customer-lead">
        {{ __('admin.Clienti_descrizione') }}
    </p>

    <div class="customer-summary">
        <div class="summary-card">
            <span>{{ __('admin.Clienti') }}</span>
            <strong>{{ $stats['total'] }}</strong>
        </div>
        <div class="summary-card">
            <span>{{ __('admin.Con_ordini') }}</span>
            <strong>{{ $stats['with_orders'] }}</strong>
        </div>
        <div class="summary-card">
            <span>{{ __('admin.Con_prenotazioni') }}</span>
            <strong>{{ $stats['with_reservations'] }}</strong>
        </div>
        <div class="summary-card">
            <span>{{ __('admin.Ordini_e_prenotazioni') }}</span>
            <strong>{{ $stats['with_both'] }}</strong>
        </div>
    </div>

    <section class="customer-toolbar">
        <input id="customerSearch" type="text" placeholder="{{ __('admin.Cerca_cliente') }}">
        <select id="customerType">
            <option value="all">{{ __('admin.Tutti') }}</option>
            <option value="orders">{{ __('admin.Con_ordini') }}</option>
            <option value="reservations">{{ __('admin.Con_prenotazioni') }}</option>
            <option value="both">{{ __('admin.Ordini_e_prenotazioni') }}</option>
        </select>
    </section>

    <div id="customerList" class="customer-list">
        @foreach ($customers as $customer)
            <article
                class="customer-card"
                data-customer-card
                data-search="{{ $customer->search_text }}"
                data-has-orders="{{ $customer->orders_count > 0 ? 1 : 0 }}"
                data-has-reservations="{{ $customer->reservations_count > 0 ? 1 : 0 }}"
            >
                <div class="customer-card__top">
                    <div class="customer-card__name">
                        <h2>{{ trim($customer->name . ' ' . $customer->surname) ?: $customer->email }}</h2>
                        <p>
                            {{ __('admin.Ultima_attivita') }}:
                            {{ $customer->last_activity_at ? $customer->last_activity_at->format('d/m/Y H:i') : '-' }}
                        </p>
                    </div>
                    <div class="customer-card__contacts">
                        <a href="mailto:{{ $customer->email }}">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi bi-envelope-fill" viewBox="0 0 16 16">
                                <path d="M.05 3.555A2 2 0 0 1 2 2h12a2 2 0 0 1 1.95 1.555L8 8.414z"/>
                                <path d="M0 4.697v7.104l5.803-3.558z"/>
                                <path d="M6.761 8.83 0 12.803A2 2 0 0 0 2 14h12a2 2 0 0 0 2-1.197L9.239 8.83l-1.239.757z"/>
                                <path d="M16 4.697v7.104l-5.803-3.558z"/>
                            </svg>
                            {{ $customer->email }}
                        </a>
                        @if ($customer->phone)
                            <a href="tel:{{ $customer->phone }}">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi bi-telephone-fill" viewBox="0 0 16 16">
                                    <path fill-rule="evenodd" d="M1.885.511a1.745 1.745 0 0 1 2.61.163L6.29 2.98c.329.423.445.974.315 1.494l-.547 2.19a.68.68 0 0 0 .178.643l2.457 2.457a.68.68 0 0 0 .644.178l2.189-.547a1.75 1.75 0 0 1 1.494.315l2.306 1.794c.829.645.905 1.87.163 2.611l-1.034 1.034c-.74.74-1.846 1.065-2.877.702a18.6 18.6 0 0 1-7.01-4.42 18.6 18.6 0 0 1-4.42-7.009c-.362-1.03-.037-2.137.703-2.877z"/>
                                </svg>
                                {{ $customer->phone }}
                            </a>
                        @endif
                    </div>
                </div>

                <div class="customer-card__bottom">
                    <div class="customer-card__stats">
                        <span class="customer-chip">{{ __('admin.Ordini') }}: {{ $customer->orders_count }}</span>
                        <span class="customer-chip">{{ __('admin.Prenotazioni') }}: {{ $customer->reservations_count }}</span>
                        <span class="customer-chip">{{ __('admin.Totale_interazioni') }}: {{ $customer->interactions_count }}</span>
                    </div>

                    @if ($customer->detail_url)
                        <a class="customer-card__button" href="{{ $customer->detail_url }}">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi bi-arrow-up-right-circle-fill" viewBox="0 0 16 16">
                                <path d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8m5.5-1a.5.5 0 0 0 0 1h3.793L4.146 13.146a.5.5 0 0 0 .708.708L10 8.707V12.5a.5.5 0 0 0 1 0v-5A.5.5 0 0 0 10.5 7z"/>
                            </svg>
                            {{ __('admin.Dettagli') }}
                        </a>
                    @endif
                </div>
            </article>
        @endforeach
    </div>

    <div id="customerEmpty" class="customer-empty" @if($customers->isNotEmpty()) hidden @endif>
        {{ __('admin.Nessun_cliente_trovato') }}
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('customerSearch');
        const typeSelect = document.getElementById('customerType');
        const cards = Array.from(document.querySelectorAll('[data-customer-card]'));
        const emptyState = document.getElementById('customerEmpty');

        function renderCustomers() {
            const search = (searchInput.value || '').trim().toLowerCase();
            const type = typeSelect.value;
            let visibleCount = 0;

            cards.forEach((card) => {
                const text = card.dataset.search || '';
                const hasOrders = card.dataset.hasOrders === '1';
                const hasReservations = card.dataset.hasReservations === '1';

                let typeMatch = true;
                if (type === 'orders') {
                    typeMatch = hasOrders;
                } else if (type === 'reservations') {
                    typeMatch = hasReservations;
                } else if (type === 'both') {
                    typeMatch = hasOrders && hasReservations;
                }

                const searchMatch = search === '' || text.includes(search);
                const visible = typeMatch && searchMatch;

                card.hidden = !visible;
                if (visible) {
                    visibleCount++;
                }
            });

            emptyState.hidden = visibleCount > 0;
        }

        searchInput.addEventListener('input', renderCustomers);
        typeSelect.addEventListener('change', renderCustomers);
        renderCustomers();
    });
</script>
@endsection
