@php
    $metrics = [
        ['label' => 'Coinvolti', 'value' => $report['involved_count'] ?? 0],
        ['label' => 'Inviate', 'value' => $report['sent_count'] ?? 0],
        ['label' => 'Aperture', 'value' => $report['opened_count'] ?? 0],
        ['label' => 'Click', 'value' => $report['clicked_count'] ?? 0],
        ['label' => 'Usate', 'value' => $report['used_count'] ?? 0],
        ['label' => 'Ordini', 'value' => $report['order_conversion_count'] ?? 0],
        ['label' => 'Prenotazioni', 'value' => $report['reservation_conversion_count'] ?? 0],
        ['label' => 'Discount', 'value' => number_format((float) ($report['discount_total'] ?? 0), 2, ',', '.')],
        ['label' => 'Open rate', 'value' => number_format((float) ($report['open_rate'] ?? 0), 2, ',', '.') . '%'],
        ['label' => 'Click rate', 'value' => number_format((float) ($report['click_rate'] ?? 0), 2, ',', '.') . '%'],
        ['label' => 'Usage rate', 'value' => number_format((float) ($report['usage_rate'] ?? 0), 2, ',', '.') . '%'],
    ];
@endphp

<section class="order-detail__section">
    <div class="order-detail__section-head">
        <h3>
            <span class="order-detail__section-icon">
                <i class="bi bi-bar-chart-fill"></i>
            </span>
            Report marketing
        </h3>
    </div>

    <div class="marketing-detail__metrics">
        @foreach ($metrics as $metric)
            <article class="marketing-detail__metric">
                <span>{{ $metric['label'] }}</span>
                <strong>{{ $metric['value'] }}</strong>
            </article>
        @endforeach
    </div>
</section>
