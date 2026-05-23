@extends('layouts.base')

@section('contents')

@php
    $customerDisplayName = trim(($customer->name ?? '') . ' ' . ($customer->surname ?? '')) ?: $customer->email;
    $customerReference = $customer->id ? '#C' . $customer->id : __('admin.customers.profile_aggregate');
    $locale = app()->getLocale() ?: 'it';

    $accountLabels = [
        'guest' => __('admin.customers.guest'),
        'registered' => __('admin.customers.registered'),
    ];

    $orderStatuses = [
        0 => ['label' => __('admin.customers.status_cancelled_m'), 'tone' => 'off'],
        1 => ['label' => __('admin.customers.status_confirmed_m'), 'tone' => 'active'],
        2 => ['label' => __('admin.customers.status_pending'), 'tone' => 'warning'],
        3 => ['label' => __('admin.customers.status_paid_pending_m'), 'tone' => 'warning'],
        5 => ['label' => __('admin.customers.status_confirmed_paid_m'), 'tone' => 'active'],
        6 => ['label' => __('admin.customers.status_refunded_m'), 'tone' => 'off'],
    ];

    $reservationStatuses = [
        0 => ['label' => __('admin.customers.status_cancelled_f'), 'tone' => 'off'],
        1 => ['label' => __('admin.customers.status_confirmed_f'), 'tone' => 'active'],
        2 => ['label' => __('admin.customers.status_pending'), 'tone' => 'warning'],
        3 => ['label' => __('admin.customers.status_paid_pending_f'), 'tone' => 'warning'],
        5 => ['label' => __('admin.customers.status_confirmed_paid_f'), 'tone' => 'active'],
        6 => ['label' => __('admin.customers.status_refunded_f'), 'tone' => 'off'],
    ];

    $accountTone = match ($accountState) {
        'registered' => 'active',
        default => 'warning',
    };

    $pageTone = $hasCustomerRecord ? $accountTone : 'warning';

    $toCarbon = static function ($value): ?\Carbon\CarbonInterface {
        if (!$value) {
            return null;
        }

        return $value instanceof \Carbon\CarbonInterface
            ? $value
            : \Carbon\Carbon::parse($value);
    };

    $formatHumanDate = static function ($value, string $fallback = '-') use ($toCarbon, $locale): string {
        $carbon = $toCarbon($value);

        if (!$carbon) {
            return $fallback;
        }

        return mb_strtolower($carbon->locale($locale)->translatedFormat('l j F y'));
    };

    $formatHumanDateTime = static function ($value, string $fallback = '-') use ($toCarbon, $formatHumanDate): string {
        $carbon = $toCarbon($value);

        if (!$carbon) {
            return $fallback;
        }

        return __('admin.customers.datetime_at', [
            'date' => $formatHumanDate($carbon),
            'time' => $carbon->format('H:i'),
        ]);
    };

    $latestActivity = $toCarbon($stats['latest_activity_at'] ?? null);
    $latestActivityTime = $latestActivity?->format('H:i') ?? '--:--';
    $latestActivityDate = $latestActivity
        ? $formatHumanDate($latestActivity)
        : __('admin.customers.no_recent_activity');

    $statItems = [
        [
            'label' => __('admin.customers.orders'),
            'value' => $stats['orders_count'],
            'helper' => __('admin.customers.linked_to_profile'),
        ],
        [
            'label' => __('admin.customers.reservations'),
            'value' => $stats['reservations_count'],
            'helper' => __('admin.customers.tracked_in_customer_history'),
        ],
        [
            'label' => __('admin.customers.interactions'),
            'value' => $stats['interactions_count'],
            'helper' => __('admin.customers.orders_and_reservations_sum'),
        ],
        [
            'label' => __('admin.customers.online_spend'),
            'value' => \App\Support\Currency::formatCents($stats['total_spent_cents'] ?? 0),
            'helper' => __('admin.customers.non_cancelled_orders_total'),
        ],
    ];

    $profileItems = [
        ['label' => __('admin.common.name'), 'value' => $customer->name ?: '-'],
        ['label' => __('admin.customers.surname'), 'value' => $customer->surname ?: '-'],
        ['label' => __('admin.common.email'), 'value' => $customer->email ?: '-'],
        ['label' => __('admin.common.phone'), 'value' => $customer->phone ?: '-'],
        ['label' => __('admin.customers.gender'), 'value' => $customer->gender ?: '-'],
        ['label' => __('admin.customers.birth_date'), 'value' => $customer->birthday ? $formatHumanDate($customer->birthday) : '-'],
    ];

    $consentStatusItem = static function (
        string $label,
        bool $enabled,
        string $activeBadge,
        string $inactiveBadge,
        $date = null,
        array $details = [],
        string $activeTone = 'active',
        string $inactiveTone = 'off'
    ) use ($formatHumanDateTime): array {
        $itemDetails = [];

        if (!empty($date)) {
            $itemDetails[] = __('admin.customers.from_date', ['date' => $formatHumanDateTime($date)]);
        }

        foreach ($details as $detail) {
            $detail = trim((string) $detail);

            if ($detail !== '') {
                $itemDetails[] = $detail;
            }
        }

        return [
            'label' => $label,
            'tone' => $enabled ? $activeTone : $inactiveTone,
            'badge' => $enabled ? $activeBadge : $inactiveBadge,
            'details' => $itemDetails,
        ];
    };

    $legacyConsentItem = static function (
        string $label,
        string $badge,
        string $tone,
        $date = null,
        array $details = []
    ) use ($formatHumanDateTime): array {
        $itemDetails = [];

        foreach ($details as $detail) {
            $detail = trim((string) $detail);

            if ($detail !== '') {
                $itemDetails[] = $detail;
            }
        }

        if (!empty($date)) {
            $itemDetails[] = __('admin.customers.from_date', ['date' => $formatHumanDateTime($date)]);
        }

        return [
            'label' => $label,
            'tone' => $tone,
            'badge' => $badge,
            'details' => $itemDetails,
        ];
    };

    $softEmailMarketingItem = static function ($unsubscribedAt = null) use ($formatHumanDateTime): array {
        $enabled = empty($unsubscribedAt);

        return [
            'label' => __('admin.customers.soft_email_marketing'),
            'tone' => $enabled ? 'active' : 'off',
            'badge' => $enabled ? __('admin.common.active') : __('admin.common.deactivated'),
            'details' => [
                $enabled
                    ? __('admin.customers.available_until_unsubscribe')
                    : __('admin.customers.cancelled_on', ['date' => $formatHumanDateTime($unsubscribedAt)]),
            ],
        ];
    };

    $privacyVersion = trim((string) ($customer->privacy_accepted_version ?? ''));
    $softEmailMarketingUnsubscribedAt = $customer->soft_email_marketing_unsubscribed_at ?? null;

    $consentItems = [
        $consentStatusItem(
            __('admin.customers.privacy'),
            !empty($customer->privacy_accepted_at),
            __('admin.customers.accepted'),
            __('admin.customers.not_present'),
            $customer->privacy_accepted_at ?? null,
        ),
        $consentStatusItem(
            __('admin.customers.email_marketing'),
            !empty($customer->email_marketing_consent_at),
            __('admin.common.active'),
            __('admin.common.inactive'),
            $customer->email_marketing_consent_at ?? null
        ),
        $softEmailMarketingItem($softEmailMarketingUnsubscribedAt),
        $consentStatusItem(
            __('admin.customers.whatsapp_marketing'),
            !empty($customer->whatsapp_marketing_consent_at),
            __('admin.common.active'),
            __('admin.common.inactive'),
            $customer->whatsapp_marketing_consent_at ?? null
        ),
        $consentStatusItem(
            __('admin.customers.profiling'),
            !empty($customer->profiling_consent_at),
            __('admin.customers.active_female'),
            __('admin.customers.inactive_female'),
            $customer->profiling_consent_at ?? null
        ),
        $consentStatusItem(
            __('admin.customers.tracking'),
            !empty($customer->tracking_consent_at),
            __('admin.common.active'),
            __('admin.common.inactive'),
            $customer->tracking_consent_at ?? null
        ),
    ];

    $timelineItems = [
        [
            'label' => __('admin.customers.profile_created'),
            'value' => $formatHumanDateTime($customer->created_at, __('admin.customers.data_unavailable')),
        ],
        [
            'label' => __('admin.customers.account_registered'),
            'value' => $formatHumanDateTime($customer->registered_at, __('admin.customers.not_registered')),
        ],
        [
            'label' => __('admin.customers.email_verified'),
            'value' => $formatHumanDateTime($customer->email_verified_at, __('admin.customers.not_verified')),
        ],
    ];

    $profileNotice = null;
    if (!$hasCustomerRecord) {
        $profileNotice = __('admin.customers.aggregate_profile_notice');
    } elseif ($accountState === 'guest') {
        $profileNotice = __('admin.customers.guest_profile_notice');
    }
@endphp

<style>
    .customer-detail-page {
        width: 100%;
        display: grid;
        gap: 22px;
    }

    .customer-detail__grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 22px;
    }

    .customer-detail__hero-copy,
    .customer-detail__hero-title,
    .customer-detail__info-meta,
    .customer-detail__answer,
    .customer-detail__activity-copy,
    .customer-detail__activity-item {
        display: grid;
        gap: 10px;
    }

    .customer-detail__hero-title p,
    .customer-detail__notice p,
    .customer-detail__section-copy,
    .customer-detail__stat-card small,
    .customer-detail__info-help,
    .customer-detail__empty,
    .customer-detail__activity-footer p {
        margin: 0;
        color: rgba(216, 221, 232, 0.84);
        line-height: 1.7;
    }

    .customer-detail__notice {
        padding: 14px 16px;
        border-radius: 18px;
        border: 1px solid rgba(255, 211, 122, 0.24);
        background: rgba(255, 211, 122, 0.08);
    }

    .customer-detail__notice strong {
        color: #ffd37a;
    }

    .customer-detail__section-copy {
        margin: 0 0 14px;
    }

    .customer-detail__stat-grid,
    .customer-detail__info-grid,
    .customer-detail__question-list,
    .customer-detail__activity-list {
        display: grid;
        gap: 0;
    }

    .customer-detail__stat-grid {
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 18px;
    }

    .customer-detail__info-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        column-gap: 18px;
    }

    .customer-detail__stat-item {
        display: grid;
        gap: 8px;
        min-width: 0;
    }

    .customer-detail__info-item {
        display: flex;
        flex-direction: column;
        
        gap: 10px;
        min-width: 0;
    }

    .customer-detail__stat-item span,
    .customer-detail__info-label,
    .customer-detail__answer-label,
    .customer-detail__activity-label {
        color: rgba(216, 221, 232, 0.72);
        font-size: var(--fs-200);
        letter-spacing: .06em;
        text-transform: uppercase;
    }

    .customer-detail__stat-item strong,
    .customer-detail__info-value,
    .customer-detail__answer-value,
    .customer-detail__activity-copy strong {
        color: var(--c3);
    }

    .customer-detail__stat-item strong {
        font-size: var(--fs-500);
        line-height: 1.05;
    }

    .customer-detail__info-item,
    .customer-detail__question-item,
    .customer-detail__activity-item {
        padding: 14px 0;
        border-top: 1px solid rgba(216, 221, 232, 0.1);
    }

    .customer-detail__info-item:nth-child(-n + 2),
    .customer-detail__question-item:first-child,
    .customer-detail__activity-item:first-child {
        padding-top: 0;
        border-top: 0;
    }

    .customer-detail__info-value {
        font-size: var(--fs-200);
        line-height: 1.45;
        overflow-wrap: anywhere;
        word-break: break-word;
    }

    .customer-detail-page .order-detail__contact {
        align-items: flex-start;
    }

    .customer-detail-page .order-detail__contact span {
        white-space: normal;
        overflow: visible;
        text-overflow: initial;
        overflow-wrap: anywhere;
        word-break: break-word;
    }

    .customer-detail__activity-head,
    .customer-detail__activity-footer {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
    }

    .customer-detail__activity-footer .order-detail__contact {
        flex: 0 0 auto;
    }

    @media (max-width: 1040px) {
        .customer-detail__grid {
            grid-template-columns: 1fr;
        }

        .customer-detail__stat-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 720px) {
        .customer-detail__stat-grid,
        .customer-detail__info-grid {
            grid-template-columns: 1fr;
        }

        .customer-detail__info-item:nth-child(-n + 2) {
            padding-top: 14px;
            border-top: 1px solid rgba(216, 221, 232, 0.1);
        }

        .customer-detail__info-item:first-child {
            padding-top: 0;
            border-top: 0;
        }

        .customer-detail__activity-head,
        .customer-detail__activity-footer,
        .order-detail__contacts {
            flex-direction: column;
            align-items: stretch;
        }

        .customer-detail__activity-footer .order-detail__contact,
        .order-detail__contacts .order-detail__contact {
            width: 100%;
        }
    }
</style>

@include('admin.Marketing.partials.breadcrumbs', [
    'items' => [
        ['label' => __('admin.nav.dashboard'), 'url' => route('admin.dashboard')],
        ['label' => __('admin.nav.customers'), 'url' => route('admin.customers.index')],
        ['label' => $customerDisplayName],
    ],
])

<a onclick="history.back()" class="btn btn-outline-light my-4">
    <x-icon name="arrow-90deg-left" />
</a>

<div class="customer-detail-page">
    <article class="order-detail order-detail--{{ $pageTone }}">
        <header class="order-detail__header">
            <div class="customer-detail__hero-copy">
                <div class="order-detail__status">
                    <span class="order-detail__status-icon order-detail__status-icon--{{ $accountTone }}">
                        @if ($accountState === 'registered')
                            <x-icon name="person-check-fill" />
                        @else
                            <x-icon name="person-fill" />
                        @endif
                    </span>
                    <strong>{{ $accountLabels[$accountState] ?? ucfirst($accountState) }}</strong>
                </div>

                @if ($profileNotice)
                    <div class="customer-detail__notice">
                        <strong>{{ __('admin.customers.profile_attention') }}</strong>
                        <p>{{ $profileNotice }}</p>
                    </div>
                @endif
            </div>

            <div class="order-detail__contacts">
                @if (!empty($customer->email))
                    <a href="mailto:{{ $customer->email }}" class="order-detail__contact">
                        <x-icon name="envelope-arrow-up-fill" />
                        <span>{{ $customer->email }}</span>
                    </a>
                @endif

                @if (!empty($customer->phone))
                    <a href="tel:{{ preg_replace('/\s+/', '', $customer->phone) }}" class="order-detail__contact">
                        <x-icon name="telephone-outbound-fill" />
                        <span>{{ $customer->phone }}</span>
                    </a>
                @endif

                <a href="{{ route('admin.marketing') }}" class="order-detail__contact">
                    <x-icon name="grid-1x2-fill" />
                    <span>{{ __('admin.customers.marketing_area') }}</span>
                </a>

                <a href="{{ route('admin.campaigns.index') }}" class="order-detail__contact">
                    <x-icon name="envelope-paper-fill" />
                    <span>{{ __('admin.customers.campaigns_marketing') }}</span>
                </a>

                <a href="{{ route('admin.automations.index') }}" class="order-detail__contact">
                    <x-icon name="lightning-charge-fill" />
                    <span>{{ __('admin.customers.automations_marketing') }}</span>
                </a>

                <a href="{{ route('admin.promotions.index') }}" class="order-detail__contact">
                    <x-icon name="megaphone-fill" />
                    <span>{{ __('admin.marketing.area_links.promotions') }}</span>
                </a>
            </div>
        </header>

        <div class="order-detail__body">
            <section class="order-detail__summary">
                <div class="order-detail__meta">
                    <p class="order-detail__code">{{ $customerReference }}</p>
                    <p class="order-detail__time">{{ $latestActivityTime }}</p>
                    <p class="order-detail__date">{{ $latestActivityDate }}</p>
                </div>

                <div class="order-detail__customer">{{ $customerDisplayName }}</div>
            </section>

            <section class="order-detail__section">
                <div class="order-detail__section-head">
                    <h3>
                            <span class="order-detail__section-icon">
                                <x-icon name="card-checklist" />
                            </span>
                            {{ __('admin.customers.quick_summary') }}
                        </h3>
                    </div>

                <div class="customer-detail__stat-grid">
                    @foreach ($statItems as $item)
                        <div class="customer-detail__stat-item">
                            <span>{{ $item['label'] }}</span>
                            <strong>{{ $item['value'] }}</strong>
                            <small>{{ $item['helper'] }}</small>
                        </div>
                    @endforeach
                </div>
            </section>

            <footer class="order-detail__footer">
                @foreach ($timelineItems as $item)
                    <div class="order-detail__footer-row">
                        <span>{{ $item['label'] }}</span>
                        <strong>{{ $item['value'] }}</strong>
                    </div>
                @endforeach
            </footer>
        </div>
    </article>

    <div class="customer-detail__grid">
        <article class="order-detail">
            <div class="order-detail__body">
                <section class="order-detail__section">
                    <div class="order-detail__section-head">
                        <h3>
                            <span class="order-detail__section-icon">
                                <x-icon name="person-vcard-fill" />
                            </span>
                            {{ __('admin.customers.customer_data') }}
                        </h3>
                    </div>

                    <div class="customer-detail__info-grid">
                        @foreach ($profileItems as $item)
                            <div class="customer-detail__info-item">
                                <span class="customer-detail__info-label">{{ $item['label'] }}</span>
                                <strong class="customer-detail__info-value">{{ $item['value'] }}</strong>
                            </div>
                        @endforeach
                    </div>
                </section>
            </div>
        </article>


        <article class="order-detail">
            <div class="order-detail__body">
                <section class="order-detail__section">
                    <div class="order-detail__section-head">
                        <h3>
                            <span class="order-detail__section-icon">
                                <x-icon name="person-check-fill" />
                            </span>
                            {{ __('admin.customers.consents_preferences') }}
                        </h3>
                    </div>

                    <p class="customer-detail__section-copy">{{ __('admin.customers.consent_preferences_description') }}</p>

                    <div class="customer-detail__info-grid">
                        @foreach ($consentItems as $item)
                            <div class="customer-detail__info-item">
                                <span class="customer-detail__info-label">{{ $item['label'] }}</span>
                                <div class="customer-detail__info-meta">
                                    <x-dashboard.state-pill :tone="$item['tone']">{{ $item['badge'] }}</x-dashboard.state-pill>
                                    @foreach ($item['details'] as $detail)
                                        <small class="customer-detail__info-help">{{ $detail }}</small>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            </div>
        </article>

    </div>

    @if (!empty($questionAnswers))
        <article class="order-detail">
            <div class="order-detail__body">
                <section class="order-detail__section">
                    <div class="order-detail__section-head">
                        <h3>
                            <span class="order-detail__section-icon">
                                <x-icon name="patch-question-fill" />
                            </span>
                            {{ __('admin.customers.profile_questionnaire') }}
                        </h3>
                    </div>

                    <div class="customer-detail__question-list">
                        @foreach ($questionAnswers as $answer)
                            <div class="customer-detail__question-item">
                                <div class="customer-detail__answer">
                                    <span class="customer-detail__answer-label">{{ $answer['label'] }}</span>
                                    <strong class="customer-detail__answer-value">{{ is_array($answer['value']) ? implode(', ', $answer['value']) : $answer['value'] }}</strong>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            </div>
        </article>
    @endif

    <div class="customer-detail__grid" id="customer-history">
        <article class="order-detail">
            <div class="order-detail__body">
                <section class="order-detail__section">
                    <div class="order-detail__section-head">
                        <h3>
                            <span class="order-detail__section-icon">
                                <x-icon name="card-checklist" />
                            </span>
                            {{ __('admin.customers.order_history') }}
                        </h3>
                    </div>

                    @if ($orders->isNotEmpty())
                        <div class="customer-detail__activity-list">
                            @foreach ($orders as $order)
                                @php $status = $orderStatuses[$order->status] ?? ['label' => __('admin.customers.status_update'), 'tone' => 'warning']; @endphp
                                <article class="customer-detail__activity-item">
                                    <div class="customer-detail__activity-head">
                                        <div class="customer-detail__activity-copy">
                                            <span class="customer-detail__activity-label">{{ __('admin.customers.order_reference', ['id' => $order->id]) }}</span>
                                            <strong>{{ $formatHumanDateTime($order->activity_at, __('admin.customers.data_unavailable')) }}</strong>
                                        </div>
                                        <x-dashboard.state-pill :tone="$status['tone']">{{ $status['label'] }}</x-dashboard.state-pill>
                                    </div>

                                    <div class="order-detail__detail-values">
                                        <small>{{ !empty($order->comune) ? __('admin.common.delivery') : __('admin.common.takeaway') }}</small>
                                        <small>{{ \App\Support\Currency::formatCents($order->tot_price ?? 0) }}</small>
                                        <small>{{ __('admin.customers.historical_marketing', ['value' => $order->news_letter ? __('admin.common.yes') : __('admin.common.no')]) }}</small>
                                    </div>

                                    <div class="customer-detail__activity-footer">
                                        <a href="{{ route('admin.orders.show', $order->id) }}" class="order-detail__contact">
                                            <x-icon name="arrow-up-right-circle-fill" />
                                            <span>{{ __('admin.customers.open_order') }}</span>
                                        </a>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @else
                        <p class="customer-detail__empty">{{ __('admin.customers.no_orders') }}</p>
                    @endif
                </section>
            </div>
        </article>

        <article class="order-detail">
            <div class="order-detail__body">
                <section class="order-detail__section">
                    <div class="order-detail__section-head">
                        <h3>
                            <span class="order-detail__section-icon">
                                <x-icon name="people-fill" />
                            </span>
                            {{ __('admin.customers.reservation_history') }}
                        </h3>
                    </div>

                    @if ($reservations->isNotEmpty())
                        <div class="customer-detail__activity-list">
                            @foreach ($reservations as $reservation)
                                @php
                                    $status = $reservationStatuses[$reservation->status] ?? ['label' => __('admin.customers.status_update'), 'tone' => 'warning'];
                                    $guests = json_decode($reservation->n_person, true);
                                    $totalGuests = (int) ($guests['adult'] ?? 0) + (int) ($guests['child'] ?? 0);
                                @endphp
                                <article class="customer-detail__activity-item">
                                    <div class="customer-detail__activity-head">
                                        <div class="customer-detail__activity-copy">
                                            <span class="customer-detail__activity-label">{{ __('admin.customers.reservation_reference', ['id' => $reservation->id]) }}</span>
                                            <strong>{{ $formatHumanDateTime($reservation->activity_at, __('admin.customers.data_unavailable')) }}</strong>
                                        </div>
                                        <x-dashboard.state-pill :tone="$status['tone']">{{ $status['label'] }}</x-dashboard.state-pill>
                                    </div>

                                    <div class="order-detail__detail-values">
                                        <small>{{ __('admin.customers.guests_count', ['count' => $totalGuests]) }}</small>
                                        <small>{{ $reservation->sala ?: __('admin.customers.room_not_indicated') }}</small>
                                        <small>{{ __('admin.customers.historical_marketing', ['value' => $reservation->news_letter ? __('admin.common.yes') : __('admin.common.no')]) }}</small>
                                    </div>

                                    <div class="customer-detail__activity-footer">
                                        <a href="{{ route('admin.reservations.show', $reservation->id) }}" class="order-detail__contact">
                                            <x-icon name="arrow-up-right-circle-fill" />
                                            <span>{{ __('admin.customers.open_reservation') }}</span>
                                        </a>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @else
                        <p class="customer-detail__empty">{{ __('admin.customers.no_reservations') }}</p>
                    @endif
                </section>
            </div>
        </article>
    </div>
</div>
@endsection
