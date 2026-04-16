@extends('layouts.base')

@section('contents')
@php
    $formatCurrency = static fn ($value) => \App\Support\Currency::formatAmount($value);
    $initialOrderConfirmRate = $summary['order_count'] > 0
        ? round(($summary['confirmed_orders'] / $summary['order_count']) * 100)
        : 0;
    $initialReservationConfirmRate = $summary['reservation_count'] > 0
        ? round(($summary['confirmed_reservations'] / $summary['reservation_count']) * 100)
        : 0;
    $initialPeriodLabel = $activityRange['start'] && $activityRange['end']
        ? \Carbon\Carbon::parse($activityRange['start'])->translatedFormat('d M Y') . ' - ' . \Carbon\Carbon::parse($activityRange['end'])->translatedFormat('d M Y')
        : 'Nessun dato disponibile';
    $bestRevenueDay = $summary['best_revenue_day'] ?? [];
    $bestReservationDay = $summary['best_reservation_day'] ?? [];
@endphp

<div class="dash_page statistics-page">
    <header class="statistics-page__hero order-detail__summary">
        <div class="order-detail__meta">
            <div class="order-detail__status">
                <span class="order-detail__status-icon order-detail__status-icon--active">
                    <x-icon name="graph-up-arrow" />
                </span>
                <strong>Analisi operativa</strong>
            </div>

            <h1 class="menu-dashboard__title">

                Statistiche
            </h1>

            @if ($activityRange['end'])
                <p class="ml-auto">
                    <span class="settings-state settings-state--active " data-stat-field="lastActivityLabel">
                        Ultimo dato {{ \Carbon\Carbon::parse($activityRange['end'])->translatedFormat('d M Y') }}
                    </span>
                </p>
            @endif
        </div>
    </header>

    @if ($hasStatistics)
        <div class="statistics-page__floating-filter-shell">
            <div class="statistics-page__floating-filter">
                <label class="statistics-page__period-control " for="statisticsPeriod">
                    <span>Periodo analizzato</span>
                    <select id="statisticsPeriod" data-stat-period>
                        <option value="7">Ultimi 7 giorni</option>
                        <option value="30" selected>Ultimi 30 giorni</option>
                        <option value="90">Ultimi 90 giorni</option>
                        <option value="365">Ultimi 12 mesi</option>
                        <option value="all">Tutto lo storico</option>
                    </select>
                </label>

                <div class="statistics-page__hero-badges statistics-page__hero-badges--floating">
                    <span class="settings-state settings-state--neutral" data-stat-field="periodLabel">{{ $initialPeriodLabel }}</span>
                    
                </div>
            </div>
        </div>
    @endif

    @if (!$hasStatistics)
        <section class="order-detail__section">
            <div class="statistics-page__empty">
                <span class="statistics-page__empty-icon">
                    <x-icon name="bar-chart" />
                </span>
                <div>
                    <h2>Le statistiche compariranno qui appena arrivano i primi dati</h2>
                    <p>Quando il locale riceverà ordini o prenotazioni, questa pagina mostrerà andamento, prodotti più richiesti e insight sul periodo.</p>
                </div>
            </div>
        </section>
    @else
        <section class="order-detail__section">
            <div class="order-detail__section-head">
                <h3>
                    <span class="order-detail__section-icon">
                        <x-icon name="speedometer2" />
                    </span>
                    Panorama del periodo
                </h3>
            </div>

            <div class="statistics-page__metric-grid">
                <article class="statistics-page__metric-card">
                    <span class="menu-dashboard__stat-label">Ordini</span>
                    <strong data-stat-field="ordersCount">{{ $summary['order_count'] }}</strong>
                    <p data-stat-field="ordersMeta">{{ $summary['confirmed_orders'] }} confermati, {{ $summary['cancelled_orders'] }} annullati</p>
                </article>

                <article class="statistics-page__metric-card">
                    <span class="menu-dashboard__stat-label">Conferma ordini</span>
                    <strong data-stat-field="orderConfirmRate">{{ $initialOrderConfirmRate }}%</strong>
                    <p data-stat-field="orderConfirmMeta">{{ $summary['cancelled_orders'] }} annullati nel periodo</p>
                </article>

                <article class="statistics-page__metric-card">
                    <span class="menu-dashboard__stat-label">Ricavi confermati</span>
                    <strong data-stat-field="confirmedRevenue">{{ $formatCurrency($summary['confirmed_revenue']) }}</strong>
                    <p data-stat-field="revenueMeta">Ticket medio {{ $formatCurrency($summary['average_ticket']) }}</p>
                </article>

                <article class="statistics-page__metric-card">
                    <span class="menu-dashboard__stat-label">Prenotazioni</span>
                    <strong data-stat-field="reservationsCount">{{ $summary['reservation_count'] }}</strong>
                    <p data-stat-field="reservationsMeta">{{ $summary['confirmed_reservations'] }} confermate, {{ $summary['cancelled_reservations'] }} annullate</p>
                </article>

                <article class="statistics-page__metric-card">
                    <span class="menu-dashboard__stat-label">Coperti stimati</span>
                    <strong data-stat-field="guestsCount">{{ $summary['guests'] }}</strong>
                    <p data-stat-field="guestsMeta">Media {{ number_format((float) $summary['average_guests'], 1, ',', '.') }} persone per prenotazione</p>
                </article>

                <article class="statistics-page__metric-card">
                    <span class="menu-dashboard__stat-label">Prodotto guida</span>
                    <strong class="statistics-page__metric-card-title" data-stat-field="topProductName">{{ $summary['top_product'] ?? 'Nessun dato' }}</strong>
                    <p data-stat-field="topProductMeta">Il più richiesto nel periodo selezionato</p>
                </article>
            </div>
        </section>

        <section class="order-detail__section">
            <div class="order-detail__section-head">
                <h3>
                    <span class="order-detail__section-icon">
                        <x-icon name="stars" />
                    </span>
                    Approfondimenti rapidi
                </h3>
            </div>

            <div class="statistics-page__insight-grid">
                <article class="statistics-page__insight-card">
                    <span class="menu-dashboard__stat-label">Giorno migliore per ricavi</span>
                    <strong data-stat-field="bestRevenueValue">
                        @if (!empty($bestRevenueDay['date'] ?? null))
                            {{ \Carbon\Carbon::parse($bestRevenueDay['date'])->translatedFormat('d M Y') }}
                        @else
                            Nessun dato
                        @endif
                    </strong>
                    <p data-stat-field="bestRevenueMeta">
                        @if (!empty($bestRevenueDay['date'] ?? null))
                            {{ $formatCurrency($bestRevenueDay['confirmed_revenue_cents'] ?? 0) }} confermati
                        @else
                            Nessun ricavo registrato
                        @endif
                    </p>
                </article>

                <article class="statistics-page__insight-card">
                    <span class="menu-dashboard__stat-label">Giorno più richiesto</span>
                    <strong data-stat-field="bestReservationValue">
                        @if (!empty($bestReservationDay['date'] ?? null))
                            {{ \Carbon\Carbon::parse($bestReservationDay['date'])->translatedFormat('d M Y') }}
                        @else
                            Nessun dato
                        @endif
                    </strong>
                    <p data-stat-field="bestReservationMeta">
                        @if (!empty($bestReservationDay['date'] ?? null))
                            {{ $bestReservationDay['total_reservations'] ?? 0 }} prenotazioni
                        @else
                            Nessuna prenotazione registrata
                        @endif
                    </p>
                </article>

                <article class="statistics-page__insight-card">
                    <span class="menu-dashboard__stat-label">Presidio del periodo</span>
                    <strong data-stat-field="activeDaysValue">{{ $activityRange['days'] }}</strong>
                    <p data-stat-field="activeDaysMeta">
                        @if ($activityRange['days'])
                            Dati distribuiti su {{ $activityRange['days'] }} giorni
                        @else
                            Nessuna attività presente
                        @endif
                    </p>
                </article>

                <article class="statistics-page__insight-card">
                    <span class="menu-dashboard__stat-label">Qualità prenotazioni</span>
                    <strong data-stat-field="reservationConfirmRate">{{ $initialReservationConfirmRate }}%</strong>
                    <p data-stat-field="reservationConfirmMeta">{{ $summary['cancelled_reservations'] }} prenotazioni annullate</p>
                </article>
            </div>
        </section>

        <section class="statistics-page__panel-grid">
            <article class="order-detail__section statistics-page__panel-card">
                <div class="statistics-page__chart-head">
                    <div class="order-detail__section-head">
                        <h3>
                            <span class="order-detail__section-icon">
                                <x-icon name="cash-coin" />
                            </span>
                            Ricavi nel tempo
                        </h3>
                    </div>
                    <p data-stat-field="revenueChartMeta">Confronto fra ricavi confermati, online, alla consegna e annullati.</p>
                </div>
                <div class="statistics-page__chart-shell">
                    <canvas id="statisticsRevenueChart"></canvas>
                </div>
            </article>

            <article class="order-detail__section statistics-page__panel-card">
                <div class="statistics-page__chart-head">
                    <div class="order-detail__section-head">
                        <h3>
                            <span class="order-detail__section-icon">
                                <x-icon name="pie-chart-fill" />
                            </span>
                            Prodotti più richiesti
                        </h3>
                    </div>
                    <p data-stat-field="topProductsChartMeta">Top prodotti del periodo con peso relativo sul totale ordinato.</p>
                </div>

                <div class="statistics-page__top-products-layout">
                    <div class="statistics-page__donut-shell">
                        <canvas id="statisticsTopProductsChart"></canvas>
                    </div>

                    <div class="statistics-page__ranking-shell">
                        <table class="table mytable statistics-page__ranking-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Prodotto</th>
                                    <th>Qta</th>
                                    <th>Quota</th>
                                </tr>
                            </thead>
                            <tbody data-stat-products-table>
                                <tr>
                                    <td colspan="4" data-label="Stato">Caricamento dati...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </article>

            <article class="order-detail__section statistics-page__panel-card">
                <div class="statistics-page__chart-head">
                    <div class="order-detail__section-head">
                        <h3>
                            <span class="order-detail__section-icon">
                                <x-icon name="people-fill" />
                            </span>
                            Prenotazioni e coperti
                        </h3>
                    </div>
                    <p data-stat-field="reservationChartMeta">Andamento di adulti, bambini e numero prenotazioni nel periodo.</p>
                </div>
                <div class="statistics-page__chart-shell">
                    <canvas id="statisticsReservationsChart"></canvas>
                </div>
            </article>

            <article class="order-detail__section statistics-page__panel-card">
                <div class="statistics-page__chart-head">
                    <div class="order-detail__section-head">
                        <h3>
                            <span class="order-detail__section-icon">
                                <x-icon name="bar-chart-steps" />
                            </span>
                            Mix prodotti nel tempo
                        </h3>
                    </div>
                    <p data-stat-field="productMixMeta">I prodotti più forti del periodo distribuiti giorno per giorno.</p>
                </div>
                <div class="statistics-page__chart-shell">
                    <canvas id="statisticsProductMixChart"></canvas>
                </div>
            </article>
        </section>
    @endif
</div>
@endsection

@section('scripts')
    @if ($hasStatistics)
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const appCurrency = @json($appCurrency);
                const payload = @json($statisticsPayload);
                const periodSelect = document.querySelector('[data-stat-period]');
                const tableBody = document.querySelector('[data-stat-products-table]');

                if (!payload || !periodSelect) {
                    return;
                }

                const numberFormatter = new Intl.NumberFormat('it-IT');
                const percentFormatter = new Intl.NumberFormat('it-IT', {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 1,
                });
                const currencyFormatter = new Intl.NumberFormat('it-IT', {
                    style: 'currency',
                    currency: appCurrency.code,
                    minimumFractionDigits: Number(appCurrency.decimals ?? 2),
                    maximumFractionDigits: Number(appCurrency.decimals ?? 2),
                });
                const shortDateFormatter = new Intl.DateTimeFormat('it-IT', {
                    day: '2-digit',
                    month: 'short',
                });
                const longDateFormatter = new Intl.DateTimeFormat('it-IT', {
                    day: '2-digit',
                    month: 'long',
                    year: 'numeric',
                });
                const tones = ['settings-state--active', 'settings-state--warning', 'settings-state--off', 'settings-state--neutral'];
                const accentPalette = ['#8ef6db', '#7cc7ff', '#ffd37a', '#ff9f9f', '#c4a7ff', '#79e0a3', '#ffb067', '#84b9ff'];

                const parseDate = (value) => new Date(`${value}T00:00:00`);
                const dateKey = (value) => {
                    const year = value.getFullYear();
                    const month = `${value.getMonth() + 1}`.padStart(2, '0');
                    const day = `${value.getDate()}`.padStart(2, '0');

                    return `${year}-${month}-${day}`;
                };
                const formatShortDate = (value) => shortDateFormatter.format(parseDate(value));
                const formatLongDate = (value) => longDateFormatter.format(parseDate(value));
                const money = (value) => currencyFormatter.format(value || 0);
                const percent = (value) => `${percentFormatter.format(value || 0)}%`;
                const setField = (key, value) => {
                    document.querySelectorAll(`[data-stat-field="${key}"]`).forEach((element) => {
                        element.textContent = value;
                    });
                };
                const setTone = (key, tone) => {
                    document.querySelectorAll(`[data-stat-field="${key}"]`).forEach((element) => {
                        tones.forEach((className) => element.classList.remove(className));
                        element.classList.add(`settings-state--${tone}`);
                    });
                };
                const buildDateRange = (startKey, endKey) => {
                    const values = [];
                    let cursor = parseDate(startKey);
                    const limit = parseDate(endKey);

                    while (cursor <= limit) {
                        values.push(dateKey(cursor));
                        cursor.setDate(cursor.getDate() + 1);
                    }

                    return values;
                };
                const sum = (items, getter) => items.reduce((carry, item) => carry + getter(item), 0);
                const emptyRevenueRow = {
                    total_orders: 0,
                    confirmed_orders: 0,
                    cancelled_orders: 0,
                    total_revenue_cents: 0,
                    confirmed_revenue_cents: 0,
                    paid_revenue_cents: 0,
                    cod_revenue_cents: 0,
                    cancelled_revenue_cents: 0,
                };
                const emptyReservationRow = {
                    total_reservations: 0,
                    confirmed_reservations: 0,
                    cancelled_reservations: 0,
                    adults_total: 0,
                    children_total: 0,
                    adults_confirmed: 0,
                    children_confirmed: 0,
                    adults_cancelled: 0,
                    children_cancelled: 0,
                };

                const charts = {};

                const allOrders = payload.ordersDaily || [];
                const allReservations = payload.reservationsDaily || [];
                const allProducts = payload.productSeries || [];
                const activity = payload.activity || {};
                const activityStart = activity.start;
                const activityEnd = activity.end || payload.today;

                const createCharts = () => {
                    if (typeof Chart === 'undefined') {
                        return;
                    }

                    const commonScale = {
                        ticks: {
                            color: 'rgba(216, 221, 232, 0.72)',
                            maxTicksLimit: 8,
                        },
                        grid: {
                            color: 'rgba(216, 221, 232, 0.08)',
                        },
                    };

                    charts.revenue = new Chart(document.getElementById('statisticsRevenueChart'), {
                        type: 'line',
                        data: {
                            labels: [],
                            datasets: [],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            interaction: {
                                mode: 'index',
                                intersect: false,
                            },
                            plugins: {
                                legend: {
                                    labels: {
                                        color: 'rgba(216, 221, 232, 0.82)',
                                    },
                                },
                            },
                            scales: {
                                x: commonScale,
                                y: {
                                    ...commonScale,
                                    ticks: {
                                        color: 'rgba(216, 221, 232, 0.72)',
                                        callback: (value) => currencyFormatter.format(value),
                                    },
                                },
                            },
                        },
                    });

                    charts.topProducts = new Chart(document.getElementById('statisticsTopProductsChart'), {
                        type: 'doughnut',
                        data: {
                            labels: [],
                            datasets: [{
                                data: [],
                                backgroundColor: [],
                                borderWidth: 0,
                            }],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        color: 'rgba(216, 221, 232, 0.82)',
                                        padding: 16,
                                    },
                                },
                            },
                        },
                    });

                    charts.reservations = new Chart(document.getElementById('statisticsReservationsChart'), {
                        type: 'bar',
                        data: {
                            labels: [],
                            datasets: [],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            interaction: {
                                mode: 'index',
                                intersect: false,
                            },
                            plugins: {
                                legend: {
                                    labels: {
                                        color: 'rgba(216, 221, 232, 0.82)',
                                    },
                                },
                            },
                            scales: {
                                x: commonScale,
                                y: {
                                    ...commonScale,
                                    beginAtZero: true,
                                },
                            },
                        },
                    });

                    charts.productMix = new Chart(document.getElementById('statisticsProductMixChart'), {
                        type: 'bar',
                        data: {
                            labels: [],
                            datasets: [],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        color: 'rgba(216, 221, 232, 0.82)',
                                    },
                                },
                            },
                            scales: {
                                x: {
                                    ...commonScale,
                                    stacked: true,
                                },
                                y: {
                                    ...commonScale,
                                    beginAtZero: true,
                                    stacked: true,
                                },
                            },
                        },
                    });
                };

                const getPeriodBounds = (selectedPeriod) => {
                    if (!activityStart || !activityEnd) {
                        return null;
                    }

                    if (selectedPeriod === 'all') {
                        return {
                            start: activityStart,
                            end: activityEnd,
                            label: `${formatLongDate(activityStart)} - ${formatLongDate(activityEnd)}`,
                        };
                    }

                    const days = Number(selectedPeriod);
                    const endDate = parseDate(activityEnd);
                    const startDate = parseDate(activityEnd);
                    startDate.setDate(startDate.getDate() - (days - 1));

                    if (startDate < parseDate(activityStart)) {
                        return {
                            start: activityStart,
                            end: activityEnd,
                            label: `${formatLongDate(activityStart)} - ${formatLongDate(activityEnd)}`,
                        };
                    }

                    return {
                        start: dateKey(startDate),
                        end: dateKey(endDate),
                        label: `${formatLongDate(dateKey(startDate))} - ${formatLongDate(dateKey(endDate))}`,
                    };
                };

                const buildSnapshot = (selectedPeriod) => {
                    const bounds = getPeriodBounds(selectedPeriod);

                    if (!bounds) {
                        return null;
                    }

                    const dates = buildDateRange(bounds.start, bounds.end);
                    const orders = allOrders.filter((row) => row.date >= bounds.start && row.date <= bounds.end);
                    const reservations = allReservations.filter((row) => row.date >= bounds.start && row.date <= bounds.end);
                    const products = allProducts.filter((row) => row.date >= bounds.start && row.date <= bounds.end);
                    const ordersMap = new Map(orders.map((row) => [row.date, row]));
                    const reservationsMap = new Map(reservations.map((row) => [row.date, row]));
                    const productTotals = new Map();
                    const productByDate = new Map();

                    products.forEach((row) => {
                        productTotals.set(row.product, (productTotals.get(row.product) || 0) + row.quantity);

                        if (!productByDate.has(row.product)) {
                            productByDate.set(row.product, new Map());
                        }

                        productByDate.get(row.product).set(row.date, row.quantity);
                    });

                    const topProducts = Array.from(productTotals.entries())
                        .sort((left, right) => right[1] - left[1]);
                    const topProductsForMix = topProducts.slice(0, 6);
                    const ordersCount = sum(orders, (row) => row.total_orders);
                    const confirmedOrders = sum(orders, (row) => row.confirmed_orders);
                    const cancelledOrders = sum(orders, (row) => row.cancelled_orders);
                    const totalRevenueCents = sum(orders, (row) => row.total_revenue_cents);
                    const confirmedRevenueCents = sum(orders, (row) => row.confirmed_revenue_cents);
                    const paidRevenueCents = sum(orders, (row) => row.paid_revenue_cents);
                    const codRevenueCents = sum(orders, (row) => row.cod_revenue_cents);
                    const cancelledRevenueCents = sum(orders, (row) => row.cancelled_revenue_cents);
                    const reservationsCount = sum(reservations, (row) => row.total_reservations);
                    const confirmedReservations = sum(reservations, (row) => row.confirmed_reservations);
                    const cancelledReservations = sum(reservations, (row) => row.cancelled_reservations);
                    const guestsCount = sum(reservations, (row) => row.adults_total + row.children_total);
                    const orderConfirmRate = ordersCount ? (confirmedOrders / ordersCount) * 100 : 0;
                    const reservationConfirmRate = reservationsCount ? (confirmedReservations / reservationsCount) * 100 : 0;
                    const averageTicketCents = confirmedOrders ? confirmedRevenueCents / confirmedOrders : 0;
                    const averageGuests = reservationsCount ? guestsCount / reservationsCount : 0;
                    const activeDays = dates.filter((date) => {
                        const orderRow = ordersMap.get(date);
                        const reservationRow = reservationsMap.get(date);

                        return Boolean(
                            (orderRow && (orderRow.total_orders || orderRow.confirmed_revenue_cents)) ||
                            (reservationRow && (reservationRow.total_reservations || reservationRow.adults_total || reservationRow.children_total))
                        );
                    }).length;

                    const bestRevenueDay = dates.reduce((best, date) => {
                        const currentRow = ordersMap.get(date) || emptyRevenueRow;
                        const value = currentRow.confirmed_revenue_cents || 0;

                        if (!best || value > best.value) {
                            return { date, value };
                        }

                        return best;
                    }, null);
                    const bestReservationDay = dates.reduce((best, date) => {
                        const currentRow = reservationsMap.get(date) || emptyReservationRow;
                        const value = currentRow.total_reservations || 0;

                        if (!best || value > best.value) {
                            return { date, value };
                        }

                        return best;
                    }, null);

                    return {
                        bounds,
                        dates,
                        ordersMap,
                        reservationsMap,
                        productByDate,
                        topProducts,
                        topProductsForMix,
                        summary: {
                            ordersCount,
                            confirmedOrders,
                            cancelledOrders,
                            totalRevenueCents,
                            confirmedRevenueCents,
                            paidRevenueCents,
                            codRevenueCents,
                            cancelledRevenueCents,
                            reservationsCount,
                            confirmedReservations,
                            cancelledReservations,
                            guestsCount,
                            orderConfirmRate,
                            reservationConfirmRate,
                            averageTicketCents,
                            averageGuests,
                            activeDays,
                            bestRevenueDay,
                            bestReservationDay,
                            topProduct: topProducts[0] || null,
                            totalProductQuantity: sum(products, (row) => row.quantity),
                        },
                    };
                };

                const updateSummary = (snapshot) => {
                    const { summary, bounds, dates } = snapshot;
                    const topProductName = summary.topProduct ? summary.topProduct[0] : 'Nessun dato';
                    const topProductQty = summary.topProduct ? summary.topProduct[1] : 0;

                    setField('periodLabel', bounds.label);
                    setField('lastActivityLabel', activity.end ? `Ultimo dato ${formatLongDate(activity.end)}` : 'Nessun dato recente');
                    setField('ordersCount', numberFormatter.format(summary.ordersCount));
                    setField('ordersMeta', `${numberFormatter.format(summary.confirmedOrders)} confermati, ${numberFormatter.format(summary.cancelledOrders)} annullati`);
                    setField('orderConfirmRate', percent(summary.orderConfirmRate));
                    setField('orderConfirmMeta', `${numberFormatter.format(summary.cancelledOrders)} annullati nel periodo`);
                    setField('confirmedRevenue', money(summary.confirmedRevenueCents));
                    setField('revenueMeta', `Ticket medio ${currencyFormatter.format(summary.averageTicketCents || 0)}`);
                    setField('reservationsCount', numberFormatter.format(summary.reservationsCount));
                    setField('reservationsMeta', `${numberFormatter.format(summary.confirmedReservations)} confermate, ${numberFormatter.format(summary.cancelledReservations)} annullate`);
                    setField('guestsCount', numberFormatter.format(summary.guestsCount));
                    setField('guestsMeta', `Media ${percentFormatter.format(summary.averageGuests)} persone per prenotazione`);
                    setField('topProductName', topProductName);
                    setField('topProductMeta', topProductQty > 0 ? `${numberFormatter.format(topProductQty)} pezzi nel periodo` : 'Nessun ordine registrato');
                    setField('activeDaysValue', numberFormatter.format(summary.activeDays));
                    setField('activeDaysMeta', `${numberFormatter.format(summary.activeDays)} giorni attivi su ${numberFormatter.format(dates.length)}`);
                    setField('reservationConfirmRate', percent(summary.reservationConfirmRate));
                    setField('reservationConfirmMeta', `${numberFormatter.format(summary.cancelledReservations)} prenotazioni annullate`);
                    setField('bestRevenueValue', summary.bestRevenueDay && summary.bestRevenueDay.value > 0 ? formatLongDate(summary.bestRevenueDay.date) : 'Nessun dato');
                    setField('bestRevenueMeta', summary.bestRevenueDay && summary.bestRevenueDay.value > 0 ? `${money(summary.bestRevenueDay.value)} confermati` : 'Nessun ricavo registrato');
                    setField('bestReservationValue', summary.bestReservationDay && summary.bestReservationDay.value > 0 ? formatLongDate(summary.bestReservationDay.date) : 'Nessun dato');
                    setField('bestReservationMeta', summary.bestReservationDay && summary.bestReservationDay.value > 0 ? `${numberFormatter.format(summary.bestReservationDay.value)} prenotazioni` : 'Nessuna prenotazione registrata');
                    setField('revenueChartMeta', `Lettura del periodo ${bounds.label.toLowerCase()}.`);
                    setField('topProductsChartMeta', `Top prodotti per il periodo ${bounds.label.toLowerCase()}.`);
                    setField('reservationChartMeta', `Coperti e numero prenotazioni per il periodo ${bounds.label.toLowerCase()}.`);
                    setField('productMixMeta', `Distribuzione dei prodotti più forti nel periodo ${bounds.label.toLowerCase()}.`);
                    setTone('lastActivityLabel', 'active');
                    setTone('periodLabel', 'neutral');
                };

                const updateTopProductsTable = (snapshot) => {
                    const { summary, topProducts } = snapshot;

                    if (!tableBody) {
                        return;
                    }

                    if (!topProducts.length) {
                        tableBody.innerHTML = `
                            <tr>
                                <td colspan="4" data-label="Stato">Nessun prodotto ordinato nel periodo selezionato.</td>
                            </tr>
                        `;
                        return;
                    }

                    tableBody.innerHTML = topProducts.slice(0, 8).map((item, index) => {
                        const share = summary.totalProductQuantity > 0
                            ? (item[1] / summary.totalProductQuantity) * 100
                            : 0;

                        return `
                            <tr>
                                <td data-label="#">${index + 1}</td>
                                <td data-label="Prodotto">${item[0]}</td>
                                <td data-label="Qta"><strong>${numberFormatter.format(item[1])}</strong></td>
                                <td data-label="Quota">${percentFormatter.format(share)}%</td>
                            </tr>
                        `;
                    }).join('');
                };

                const updateCharts = (snapshot) => {
                    if (!charts.revenue) {
                        return;
                    }

                    const labels = snapshot.dates.map((date) => formatShortDate(date));
                    const revenueSeries = snapshot.dates.map((date) => snapshot.ordersMap.get(date) || emptyRevenueRow);
                    const reservationSeries = snapshot.dates.map((date) => snapshot.reservationsMap.get(date) || emptyReservationRow);
                    const topProducts = snapshot.topProducts.slice(0, 6);

                    charts.revenue.data.labels = labels;
                    charts.revenue.data.datasets = [
                        {
                            label: 'Ricavi confermati',
                            data: revenueSeries.map((row) => row.confirmed_revenue_cents || 0),
                            borderColor: '#8ef6db',
                            backgroundColor: 'rgba(142, 246, 219, 0.16)',
                            fill: true,
                            tension: 0.32,
                        },
                        {
                            label: 'Pagati online',
                            data: revenueSeries.map((row) => row.paid_revenue_cents || 0),
                            borderColor: '#7cc7ff',
                            backgroundColor: 'rgba(124, 199, 255, 0.12)',
                            fill: false,
                            tension: 0.32,
                        },
                        {
                            label: 'Alla consegna',
                            data: revenueSeries.map((row) => row.cod_revenue_cents || 0),
                            borderColor: '#ffd37a',
                            backgroundColor: 'rgba(255, 211, 122, 0.12)',
                            fill: false,
                            tension: 0.32,
                        },
                        {
                            label: 'Annullati',
                            data: revenueSeries.map((row) => row.cancelled_revenue_cents || 0),
                            borderColor: '#ff9f9f',
                            backgroundColor: 'rgba(255, 159, 159, 0.12)',
                            fill: false,
                            tension: 0.32,
                        },
                    ];
                    charts.revenue.update();

                    charts.topProducts.data.labels = topProducts.length ? topProducts.map((item) => item[0]) : ['Nessun dato'];
                    charts.topProducts.data.datasets[0].data = topProducts.length ? topProducts.map((item) => item[1]) : [1];
                    charts.topProducts.data.datasets[0].backgroundColor = topProducts.length
                        ? accentPalette.slice(0, topProducts.length)
                        : ['rgba(216, 221, 232, 0.2)'];
                    charts.topProducts.options.plugins.legend.display = topProducts.length > 0;
                    charts.topProducts.update();

                    charts.reservations.data.labels = labels;
                    charts.reservations.data.datasets = [
                        {
                            type: 'bar',
                            label: 'Adulti',
                            data: reservationSeries.map((row) => row.adults_total),
                            backgroundColor: 'rgba(124, 199, 255, 0.48)',
                            borderColor: '#7cc7ff',
                            borderWidth: 1,
                            stack: 'guests',
                        },
                        {
                            type: 'bar',
                            label: 'Bambini',
                            data: reservationSeries.map((row) => row.children_total),
                            backgroundColor: 'rgba(255, 159, 159, 0.48)',
                            borderColor: '#ff9f9f',
                            borderWidth: 1,
                            stack: 'guests',
                        },
                        {
                            type: 'line',
                            label: 'Prenotazioni',
                            data: reservationSeries.map((row) => row.total_reservations),
                            borderColor: '#8ef6db',
                            backgroundColor: 'rgba(142, 246, 219, 0.12)',
                            tension: 0.32,
                            yAxisID: 'y',
                        },
                    ];
                    charts.reservations.update();

                    charts.productMix.data.labels = labels;
                    charts.productMix.data.datasets = snapshot.topProductsForMix.map((item, index) => {
                        const valuesByDate = snapshot.productByDate.get(item[0]) || new Map();

                        return {
                            label: item[0],
                            data: snapshot.dates.map((date) => valuesByDate.get(date) || 0),
                            backgroundColor: accentPalette[index % accentPalette.length],
                            borderWidth: 0,
                        };
                    });
                    charts.productMix.update();
                };

                const applyPeriod = () => {
                    const snapshot = buildSnapshot(periodSelect.value);

                    if (!snapshot) {
                        return;
                    }

                    updateSummary(snapshot);
                    updateTopProductsTable(snapshot);
                    updateCharts(snapshot);
                };

                createCharts();
                applyPeriod();
                periodSelect.addEventListener('change', applyPeriod);
            });
        </script>
    @endif
@endsection
