@php
    $metrics = [
        ['label' => 'Utenti coinvolti', 'value' => $report['involved_count'] ?? 0, 'meta' => 'Assegnazioni create', 'icon' => 'bi-people-fill'],
        ['label' => 'Email inviate', 'value' => $report['sent_count'] ?? 0, 'meta' => 'Invii tracciati', 'icon' => 'bi-envelope-check-fill'],
        ['label' => 'Aperture', 'value' => $report['opened_count'] ?? 0, 'meta' => 'Open tracking', 'icon' => 'bi-eye-fill'],
        ['label' => 'Click', 'value' => $report['clicked_count'] ?? 0, 'meta' => 'CTA tracciate', 'icon' => 'bi-cursor-fill'],
        ['label' => 'Promo usate', 'value' => $report['used_count'] ?? 0, 'meta' => 'Redemption', 'icon' => 'bi-bag-check-fill'],
        ['label' => 'Conversioni ordini', 'value' => $report['order_conversion_count'] ?? 0, 'meta' => 'Ordini collegati', 'icon' => 'bi-receipt'],
        ['label' => 'Conversioni prenotazioni', 'value' => $report['reservation_conversion_count'] ?? 0, 'meta' => 'Prenotazioni collegate', 'icon' => 'bi-calendar2-check-fill'],
        ['label' => 'Discount totale', 'value' => number_format((float) ($report['discount_total'] ?? 0), 2, ',', '.'), 'meta' => 'Valore sconti', 'icon' => 'bi-cash-coin'],
        ['label' => 'Open rate', 'value' => number_format((float) ($report['open_rate'] ?? 0), 2, ',', '.') . '%', 'meta' => 'Aperture / invii', 'icon' => 'bi-graph-up'],
        ['label' => 'Click rate', 'value' => number_format((float) ($report['click_rate'] ?? 0), 2, ',', '.') . '%', 'meta' => 'Click / invii', 'icon' => 'bi-mouse-fill'],
        ['label' => 'Usage rate', 'value' => number_format((float) ($report['usage_rate'] ?? 0), 2, ',', '.') . '%', 'meta' => 'Utilizzi / invii', 'icon' => 'bi-stars'],
    ];
@endphp

<section class="order-detail__section mt-4">
    <div class="order-detail__section-head">
        <h3>
            <span class="order-detail__section-icon">
                <i class="bi bi-bar-chart-fill"></i>
            </span>
            Report marketing
        </h3>
    </div>

    <div class="statistics-page__metric-grid">
        @foreach ($metrics as $metric)
            <article class="statistics-page__metric-card">
                <span class="menu-dashboard__stat-label">
                    <i class="bi {{ $metric['icon'] }}"></i>
                    {{ $metric['label'] }}
                </span>
                <strong>{{ $metric['value'] }}</strong>
                <p>{{ $metric['meta'] }}</p>
            </article>
        @endforeach
    </div>
</section>
