@props([
    'status' => 2,
    'reservationCode' => null,
    'time' => null,
    'dateLabel' => null,
    'customer' => null,
    'email' => null,
    'phone' => null,
    'adults' => 0,
    'children' => 0,
    'roomLabel' => null,
    'note' => null,
    'sentAt' => null,
    'marketing' => null,
    'promotions' => [],
])

@php
    $status = (int) $status;
    $adults = (int) $adults;
    $children = (int) $children;
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
@endphp

<article {{ $attributes->class(['reservation-detail', 'order-detail', 'order-detail--' . $statusData['tone']]) }}>
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
                @if ($reservationCode)
                    <p class="order-detail__code">#{{ $reservationCode }}</p>
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

        <section class="order-detail__section reservation-detail__guests">
            <div class="order-detail__section-head">
                <h3>
                    <span class="order-detail__section-icon">
                        <x-icon name="people-fill" />
                    </span>
                    {{ __('admin.components.reservation_detail.guests') }}
                </h3>
            </div>

            <div class="reservation-detail__guest-list">
                @if ($adults > 0)
                    <div class="reservation-detail__guest-chip">
                        <span class="reservation-detail__guest-icon">
                            <x-icon name="person-standing" />
                        </span>
                        <span class="reservation-detail__guest-copy">
                            <strong>{{ $adults }}</strong>
                            <small>{{ __('admin.components.reservation_detail.adults') }}</small>
                        </span>
                    </div>
                @endif

                @if ($children > 0)
                    <div class="reservation-detail__guest-chip">
                        <span class="reservation-detail__guest-icon">
                            <x-icon name="person-arms-up" />
                        </span>
                        <span class="reservation-detail__guest-copy">
                            <strong>{{ $children }}</strong>
                            <small>{{ __('admin.components.reservation_detail.children') }}</small>
                        </span>
                    </div>
                @endif

                @if ($roomLabel)
                    <div class="reservation-detail__room">
                        <span class="reservation-detail__room-label">{{ __('admin.components.reservation_detail.booked_room') }}</span>
                        <strong>{{ $roomLabel }}</strong>
                    </div>
                @endif
            </div>
        </section>

        @if ($promotions->isNotEmpty())
            <section class="order-detail__section">
                <div class="order-detail__section-head">
                    <h3>
                        <span class="order-detail__section-icon">
                            <i class="bi bi-gift-fill"></i>
                        </span>
                        {{ __('admin.components.reservation_detail.reservation_promotion') }}
                    </h3>
                </div>

                <div class="order-detail__items">
                    @foreach ($promotions as $promotion)
                        <div class="promo-card">
                            <div class="promo-card__header">
                                <div class="promo-card__title">
                                    <strong class="promo-card__name">{{ $promotion['name'] ?? __('admin.common.promotion') }}</strong>
                                    @if (!empty($promotion['type_label']))
                                        <span class="promo-card__type-badge">{{ $promotion['type_label'] }}</span>
                                    @endif
                                </div>
                                <x-dashboard.state-pill tone="active">{{ $promotion['status'] ?? 'n/d' }}</x-dashboard.state-pill>
                            </div>

                            @if (!empty($promotion['affected_items']))
                                <div class="promo-card__items">
                                    <p class="promo-card__items-title">{{ __('admin.components.reservation_detail.promotion_details') }}</p>
                                    <ul class="promo-card__items-list">
                                        @foreach ($promotion['affected_items'] as $affectedItem)
                                            <li class="promo-card__items-entry">
                                                <span>{{ $affectedItem['label'] ?? __('admin.common.element') }}</span>
                                                @if (!empty($affectedItem['details']))
                                                    <span class="promo-card__items-detail">{{ implode(' · ', $affectedItem['details']) }}</span>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($note)
            <section class="order-detail__section order-detail__note">
                <div class="order-detail__section-head">
                    <h3>
                        <span class="order-detail__section-icon">
                            <x-icon name="chat-left-text-fill" />
                        </span>
                        {{ __('admin.components.reservation_detail.customer_message') }}
                    </h3>
                </div>

                <p>{{ $note }}</p>
            </section>
        @endif

        @if ($sentAt || !is_null($marketing))
            <footer class="order-detail__footer">
                @if ($sentAt)
                    <div class="order-detail__footer-row">
                        <span>{{ __('admin.components.reservation_detail.sent_at') }}</span>
                        <strong>{{ $sentAt }}</strong>
                    </div>
                @endif

                @if (!is_null($marketing))
                    <div class="order-detail__footer-row">
                        <span>{{ __('admin.components.reservation_detail.contact_marketing') }}</span>
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
