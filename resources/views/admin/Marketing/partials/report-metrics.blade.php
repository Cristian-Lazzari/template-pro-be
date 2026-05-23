@php
    $metrics = [
        ['label' => __('admin.marketing.customer_promotions_table.involved'), 'value' => $report['involved_count'] ?? 0],
        ['label' => __('admin.marketing.customer_promotions_table.sent'), 'value' => $report['sent_count'] ?? 0],
        ['label' => __('admin.marketing.customer_promotions_table.opens'), 'value' => $report['opened_count'] ?? 0],
        ['label' => __('admin.marketing.customer_promotions_table.clicks'), 'value' => $report['clicked_count'] ?? 0],
        ['label' => __('admin.marketing.customer_promotions_table.used'), 'value' => $report['used_count'] ?? 0],
        ['label' => __('admin.marketing.customer_promotions_table.orders'), 'value' => $report['order_conversion_count'] ?? 0],
        ['label' => __('admin.marketing.customer_promotions_table.reservations'), 'value' => $report['reservation_conversion_count'] ?? 0],
        ['label' => __('admin.marketing.customer_promotions_table.discount_label'), 'value' => number_format((float) ($report['discount_total'] ?? 0), 2, ',', '.')],
        ['label' => __('admin.marketing.customer_promotions_table.open_rate'), 'value' => number_format((float) ($report['open_rate'] ?? 0), 2, ',', '.') . '%'],
        ['label' => __('admin.marketing.customer_promotions_table.click_rate'), 'value' => number_format((float) ($report['click_rate'] ?? 0), 2, ',', '.') . '%'],
        ['label' => __('admin.marketing.customer_promotions_table.usage_rate'), 'value' => number_format((float) ($report['usage_rate'] ?? 0), 2, ',', '.') . '%'],
    ];
@endphp

<section class="order-detail__section">
    <div class="order-detail__section-head">
        <h3>
            <span class="order-detail__section-icon">
                <i class="bi bi-bar-chart-fill"></i>
            </span>
            {{ __('admin.marketing.customer_promotions_table.report_marketing') }}
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
