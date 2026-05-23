@props([
    'status' => 2,
    'orderCode' => null,
    'time' => null,
    'dateLabel' => null,
    'customer' => null,
    'email' => null,
    'phone' => null,
    'items' => [],
    'deliveryCost' => null,
    'total' => null,
    'fulfillmentTitle' => null,
    'fulfillmentValue' => null,
    'fulfillmentType' => 'delivery',
    'note' => null,
    'sentAt' => null,
    'marketing' => null,
    'promotions' => [],
])

@php
    $status = (int) $status;
    $promotions = collect($promotions)->filter();

    $statusMap = [
        0 => ['label' => __('admin.components.statuses.cancelled'), 'tone' => 'off'],
        1 => ['label' => __('admin.components.statuses.confirmed'), 'tone' => 'active'],
        2 => ['label' => __('admin.components.statuses.pending'), 'tone' => 'warning'],
        3 => ['label' => __('admin.components.statuses.paid_pending'), 'tone' => 'warning'],
        5 => ['label' => __('admin.components.statuses.confirmed_paid'), 'tone' => 'active'],
        6 => ['label' => __('admin.components.statuses.refunded'), 'tone' => 'off'],
    ];

    $statusData = $statusMap[$status] ?? $statusMap[2];

    $statusIcon = match ($statusData['tone']) {
        'off' => 'x-circle',
        'active' => 'check-circle',
        default => 'exclamation-circle-fill',
    };

    $fulfillmentIcon = $fulfillmentType === 'takeaway' ? 'bag-fill' : 'truck';
@endphp

<article {{ $attributes->class(['order-detail', 'order-detail--' . $statusData['tone']]) }}>
    <header class="order-detail__header">
        <div class="order-detail__status">
            <span class="order-detail__status-icon order-detail__status-icon--{{ $statusData['tone'] }}">
                @if ($statusIcon === 'x-circle')
                    <x-icon name="x-circle" />
                @elseif ($statusIcon === 'check-circle')
                    <x-icon name="check-circle" />
                @else
                    <x-icon name="exclamation-circle-fill" />
                @endif
            </span>

            <strong>{{ $statusData['label'] }}</strong>
        </div>

        <div class="order-detail__contacts">
            @if ($email)
                <a href="{{ 'mailto:' . $email }}" class="order-detail__contact">
                    <x-icon name="envelope-arrow-up-fill" />
                    <span>{{ $email }}</span>
                </a>
            @endif

            @if ($phone)
                <a href="{{ 'tel:' . preg_replace('/\s+/', '', $phone) }}" class="order-detail__contact">
                    <x-icon name="telephone-outbound-fill" />
                    <span>{{ $phone }}</span>
                </a>
            @endif
        </div>
    </header>

    <div class="order-detail__body">
        <section class="order-detail__summary">
            <div class="order-detail__meta">
                @if ($orderCode)
                    <p class="order-detail__code">#{{ $orderCode }}</p>
                @endif

                @if ($time)
                    <p class="order-detail__time">{{ $time }}</p>
                @endif

                @if ($dateLabel)
                    <p class="order-detail__date">{{ $dateLabel }}</p>
                @endif
            </div>

            @if ($customer)
                <div class="order-detail__customer">{{ $customer }}</div>
            @endif
        </section>

        <section class="order-detail__section">
            <div class="order-detail__section-head">
                <h3>
                    <span class="order-detail__section-icon">
                        <x-icon name="card-checklist" />
                    </span>
                    {{ __('admin.components.order_detail.ordered_products') }}
                </h3>
            </div>

            <div class="order-detail__items">
                @foreach ($items as $item)
                    <article class="order-detail__item">
                        <div class="order-detail__item-top">
                            <span class="order-detail__qty">x{{ $item['quantity'] ?? 1 }}</span>
                            <strong class="order-detail__item-name">{{ $item['name'] ?? '' }}</strong>
                        </div>

                        @if (!empty($item['details']))
                            <div class="order-detail__detail-groups">
                                @foreach ($item['details'] as $detail)
                                    @if (!empty($detail['values']))
                                        <div class="order-detail__detail-group">
                                            <span>{{ $detail['label'] }}</span>
                                            <div class="order-detail__detail-values">
                                                @foreach ($detail['values'] as $value)
                                                    <small>{{ $value }}</small>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                    </article>
                @endforeach
            </div>

            @if ($deliveryCost || $total)
                <div class="order-detail__totals">
                    @if ($deliveryCost)
                        <div class="order-detail__total-row">
                            <span>{{ __('admin.components.order_detail.delivery_cost') }}</span>
                            <strong>{{ $deliveryCost }}</strong>
                        </div>
                    @endif

                    @if ($total)
                        <div class="order-detail__total-row order-detail__total-row--grand">
                            <span>{{ __('admin.common.total') }}</span>
                            <strong>{{ $total }}</strong>
                        </div>
                    @endif
                </div>
            @endif
        </section>

        @if ($promotions->isNotEmpty())
            <section class="order-detail__section">
                <div class="order-detail__section-head">
                    <h3>
                        <span class="order-detail__section-icon">
                            <i class="bi bi-gift-fill"></i>
                        </span>
                        {{ __('admin.components.order_detail.promotion_applied') }}
                    </h3>
                </div>

                <div class="order-detail__items">
                    @foreach ($promotions as $promotion)
                        <article class="order-detail__item">
                            <div class="order-detail__item-top">
                                <span class="order-detail__qty">{{ __('admin.common.promo') }}</span>
                                <strong class="order-detail__item-name">{{ $promotion['name'] ?? __('admin.common.promotion') }}</strong>
                                <x-dashboard.state-pill tone="active">{{ $promotion['status'] ?? 'n/d' }}</x-dashboard.state-pill>
                            </div>

                            <div class="order-detail__detail-groups">
                                @foreach ([
                                    ['label' => __('admin.components.order_detail.discount_type'), 'value' => $promotion['type_label'] ?? null],
                                    ['label' => __('admin.components.order_detail.discount'), 'value' => $promotion['discount_amount_label'] ?? null],
                                    ['label' => __('admin.components.order_detail.subtotal_before_discount'), 'value' => $promotion['subtotal_before_discount_label'] ?? null],
                                    ['label' => __('admin.components.order_detail.discounted_subtotal'), 'value' => $promotion['subtotal_after_discount_label'] ?? null],
                                    ['label' => __('admin.components.order_detail.total_after_discount'), 'value' => $promotion['total_after_discount_label'] ?? null],
                                    ['label' => __('admin.components.order_detail.customer_promotion'), 'value' => isset($promotion['customer_promotion_id']) ? '#' . $promotion['customer_promotion_id'] : null],
                                ] as $row)
                                    @if (!blank($row['value']))
                                        <div class="order-detail__detail-group">
                                            <span>{{ $row['label'] }}</span>
                                            <div class="order-detail__detail-values">
                                                <small>{{ $row['value'] }}</small>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach

                                @if (!empty($promotion['affected_items']))
                                    <div class="order-detail__detail-group">
                                        <span>{{ __('admin.components.order_detail.affected_rows') }}</span>
                                        <div class="order-detail__detail-values">
                                            @foreach ($promotion['affected_items'] as $affectedItem)
                                                <small>
                                                    {{ $affectedItem['label'] ?? __('admin.common.element') }}
                                                    @if (!empty($affectedItem['details']))
                                                        · {{ implode(' · ', $affectedItem['details']) }}
                                                    @endif
                                                </small>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($fulfillmentTitle)
            <section class="order-detail__section order-detail__service">
                <div class="order-detail__section-head">
                    <h3>
                        <span class="order-detail__section-icon">
                        @if ($fulfillmentIcon === 'bag-fill')
                            <x-icon name="bag-fill" />
                        @else
                            <x-icon name="truck" />
                        @endif
                        </span>
                        {{ $fulfillmentTitle }}
                    </h3>
                </div>

                @if ($fulfillmentValue)
                    <p>{{ $fulfillmentValue }}</p>
                @endif
            </section>
        @endif

        @if ($note)
            <section class="order-detail__section order-detail__note">
                <div class="order-detail__section-head">
                    <h3>
                        <span class="order-detail__section-icon">
                            <x-icon name="chat-left-text-fill" />
                        </span>
                        {{ __('admin.components.order_detail.customer_message') }}
                    </h3>
                </div>

                <p>{{ $note }}</p>
            </section>
        @endif

        @if ($sentAt || !is_null($marketing))
            <footer class="order-detail__footer">
                @if ($sentAt)
                    <div class="order-detail__footer-row">
                        <span>{{ __('admin.components.order_detail.sent_at') }}</span>
                        <strong>{{ $sentAt }}</strong>
                    </div>
                @endif

                @if (!is_null($marketing))
                    <div class="order-detail__footer-row">
                        <span>{{ __('admin.components.order_detail.contact_marketing') }}</span>
                        <strong>{{ $marketing }}</strong>
                    </div>
                @endif
            </footer>
        @endif

        @if (trim((string) $slot) !== '')
            <div class="order-detail__actions">
                {{ $slot }}
            </div>
        @endif
    </div>
</article>
