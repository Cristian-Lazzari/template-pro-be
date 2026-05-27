@extends('layouts.base')

@section('contents')

@if (session('success'))
    <div class="dashboard-home__flash" role="alert">
        {{ session('success') }}
    </div>
@endif

@php
    $triggers = $triggers ?? [];
    $statuses = $statuses ?? [];
    $triggerDefinitions = $triggerDefinitions ?? [];
    $metadataOptionLabels = [
        'threshold_type' => [
            'total_spent' => __('admin.marketing.automations.option_total_spent'),
            'orders_count' => __('admin.marketing.automations.option_orders_count'),
            'bookings_count' => __('admin.marketing.automations.option_bookings_count'),
        ],
        'value_type' => [
            'total_spent' => __('admin.marketing.automations.option_total_spent'),
            'orders_count' => __('admin.marketing.automations.option_orders_count'),
            'bookings_count' => __('admin.marketing.automations.option_bookings_count'),
            'customer_score' => __('admin.marketing.automations.option_customer_score'),
        ],
        'anniversary_source' => [
            'first_order' => __('admin.marketing.automations.option_first_order'),
            'first_booking' => __('admin.marketing.automations.option_first_booking'),
        ],
    ];

    $metadataFor = function ($automation): array {
        $metadata = $automation->metadata ?? [];

        if ($metadata instanceof \Illuminate\Support\Collection) {
            return $metadata->all();
        }

        if (is_object($metadata)) {
            return (array) $metadata;
        }

        return is_array($metadata) ? $metadata : [];
    };

    $triggerLabelFor = function (?string $trigger) use ($triggers, $triggerDefinitions): string {
        if (! $trigger) {
            return __('admin.marketing.automations.undefined_trigger');
        }

        $translationKey = 'admin.marketing.automations.trigger_' . $trigger;
        $translatedLabel = __($translationKey);

        return $triggers[$trigger]
            ?? data_get($triggerDefinitions, $trigger . '.label')
            ?? ($translatedLabel !== $translationKey ? $translatedLabel : $trigger);
    };

    $optionLabel = function (string $group, $value) use ($metadataOptionLabels): string {
        return $metadataOptionLabels[$group][$value] ?? ($value ?: '-');
    };

    $valueOrDash = fn ($value) => $value !== null && $value !== '' ? $value : '-';

    $triggerSummaryFor = function (?string $trigger, array $metadata) use ($optionLabel, $valueOrDash): string {
        return match ($trigger) {
            'no_interaction_since' => __('admin.marketing.automations.summary_no_interaction_since', ['days' => $valueOrDash(data_get($metadata, 'days'))]),
            'no_order_since' => __('admin.marketing.automations.summary_no_order_since', ['days' => $valueOrDash(data_get($metadata, 'days'))]),
            'no_booking_since' => __('admin.marketing.automations.summary_no_booking_since', ['days' => $valueOrDash(data_get($metadata, 'days'))]),
            'birthday_before' => __('admin.marketing.automations.summary_birthday_before', ['days' => $valueOrDash(data_get($metadata, 'days_before'))]),
            'first_order_completed' => __('admin.marketing.automations.summary_first_order_completed', ['days' => $valueOrDash(data_get($metadata, 'delay_days'))]),
            'first_booking_completed' => __('admin.marketing.automations.summary_first_booking_completed', ['days' => $valueOrDash(data_get($metadata, 'delay_days'))]),
            'orders_without_bookings' => __('admin.marketing.automations.summary_orders_without_bookings', ['orders' => $valueOrDash(data_get($metadata, 'min_orders'))]),
            'bookings_without_orders' => __('admin.marketing.automations.summary_bookings_without_orders', ['bookings' => $valueOrDash(data_get($metadata, 'min_bookings'))]),
            'customer_reaches_value' => __('admin.marketing.automations.summary_customer_reaches_value', [
                'type' => $optionLabel('threshold_type', data_get($metadata, 'threshold_type')),
                'value' => $valueOrDash(data_get($metadata, 'threshold_value')),
            ]),
            'valuable_customer_at_risk' => __('admin.marketing.automations.summary_valuable_customer_at_risk', [
                'type' => $optionLabel('value_type', data_get($metadata, 'value_type')),
                'value' => $valueOrDash(data_get($metadata, 'value_threshold')),
                'days' => $valueOrDash(data_get($metadata, 'inactive_days')),
            ]),
            'customer_anniversary' => __('admin.marketing.automations.summary_customer_anniversary', [
                'source' => $optionLabel('anniversary_source', data_get($metadata, 'anniversary_source')),
                'days' => $valueOrDash(data_get($metadata, 'days_before')),
            ]),
            'high_average_order_value' => __('admin.marketing.automations.summary_high_average_order_value', [
                'value' => $valueOrDash(data_get($metadata, 'average_order_value')),
                'orders' => data_get($metadata, 'min_orders') ?: __('admin.marketing.automations.optional_orders'),
            ]),
            default => __('admin.marketing.automations.metadata_empty'),
        };
    };
@endphp

<div class="dash_page marketing-index-page">
    @include('admin.Marketing.partials.index-style')

    @include('admin.Marketing.partials.breadcrumbs', [
        'items' => [
            ['label' => __('admin.nav.dashboard'), 'url' => route('admin.dashboard')],
            ['label' => __('admin.marketing.area_links.marketing'), 'url' => route('admin.marketing')],
            ['label' => __('admin.marketing.automations.plural')],
        ],
    ])

    <header class="menu-dashboard__hero order-detail__summary">
        <div class="order-detail__meta">
            <div class="order-detail__status">
                <span class="order-detail__status-icon order-detail__status-icon--active">
                    <i class="bi bi-lightning-charge-fill"></i>
                </span>
                <strong>{{ __('admin.marketing.area_links.marketing') }}</strong>
            </div>

            <h1 class="menu-dashboard__title">{{ __('admin.marketing.automations.plural') }}</h1>
            <p>{{ __('admin.marketing.automations.index_description') }}</p>
        </div>

        <div class="menu-dashboard__hero-actions dashboard-home__hero-actions">
            <a href="{{ route('admin.automations.create') }}" class="order-detail__contact">
                <i class="bi bi-cloud-plus-fill"></i>
                <span>{{ __('admin.marketing.automations.create_new') }}</span>
            </a>
            @include('admin.Marketing.partials.area-links', ['current' => 'automations'])
        </div>
    </header>

    <section class="order-detail__section mt-4">
        <div class="order-detail__section-head">
            <h3>
                <span class="order-detail__section-icon">
                    <i class="bi bi-list-check"></i>
                </span>
                {{ __('admin.marketing.automations.list_title') }}
            </h3>
        </div>

        @if ($automations->count() > 0)
            <div class="campaign-list-render">
                @foreach ($automations as $automation)
                    @php
                        $metadata = $metadataFor($automation);
                        $normalizedStatus = $automation->status ?: 'draft';
                        $statusLabel = $statuses[$normalizedStatus] ?? $normalizedStatus;
                        $isDraft = $normalizedStatus === 'draft';
                        $triggerLabel = $triggerLabelFor($automation->trigger);
                        $triggerSummary = $triggerSummaryFor($automation->trigger, $metadata);
                        $primaryPromotion = $automation->promotions->first();
                        $promotionLabel = $primaryPromotion?->slug ?? $primaryPromotion?->name ?? __('admin.marketing.automations.no_promotion');
                        $modelName = $automation->model?->name ?? '-';
                        $activityLabel = $automation->last_run_at ? __('admin.marketing.automations.last_run') : __('admin.marketing.automations.updated_at');
                        $activityValue = $automation->last_run_at?->format('d/m/Y H:i') ?? $automation->updated_at?->format('d/m/Y H:i') ?? '-';
                        $cooldown = data_get($metadata, 'cooldown_days');
                        $cooldownLabel = $cooldown !== null && $cooldown !== '' ? __('admin.marketing.automations.days_count', ['count' => $cooldown]) : __('admin.marketing.automations.no_cooldown');
                        $totalActivation = (int) $automation->total_activation;
                        $totalSent = (int) $automation->total_sent;
                        $progressPercentage = $totalActivation > 0 ? min(100, (int) round(($totalSent / $totalActivation) * 100)) : 0;
                    @endphp

                    <article class="campaign-list-row @if ($isDraft) campaign-list-row--draft @endif">
                        <div class="campaign-list-identity">
                            <div class="campaign-list-heading">
                                <h4 title="{{ $automation->name }}">{{ $automation->name }}</h4>
                                <span class="campaign-list-consent campaign-list-consent--explicit" title="{{ __('admin.common.email') }}">
                                    {{ __('admin.marketing.automations.email_channel_short') }}
                                </span>
                            </div>

                            <p title="{{ $triggerLabel }}">{{ $triggerLabel }}</p>
                        </div>

                        <div class="campaign-list-state">
                            <strong class="campaign-list-status campaign-list-status--{{ $normalizedStatus }}" title="{{ $statusLabel }}">
                                {{ $statusLabel }}
                            </strong>
                            <span title="{{ $activityLabel }}: {{ $activityValue }}">{{ $activityLabel }}: {{ $activityValue }}</span>
                        </div>

                        <div class="campaign-list-rule">
                            <div>
                                <span>{{ __('admin.marketing.automations.parameters') }}</span>
                                <strong title="{{ $triggerSummary }}">{{ $triggerSummary }}</strong>
                            </div>
                            <div>
                                <span>{{ __('admin.common.promo') }}</span>
                                <strong class="campaign-list-promo" title="{{ $promotionLabel }}">{{ $promotionLabel }}</strong>
                            </div>
                            <div>
                                <span>{{ __('admin.marketing.automations.model') }}</span>
                                <strong title="{{ $modelName }}">{{ $modelName }}</strong>
                            </div>
                            <div>
                                <span>{{ __('admin.marketing.automations.cooldown') }}</span>
                                <strong title="{{ $cooldownLabel }}">{{ $cooldownLabel }}</strong>
                            </div>
                        </div>

                        <div class="campaign-list-usage">
                            @if (! $isDraft)
                                <div
                                    class="promotion-list-donut campaign-list-donut"
                                    style="--promotion-usage: {{ $progressPercentage }}%;"
                                    role="img"
                                    aria-label="{{ $totalSent }}/{{ $totalActivation }} {{ __('admin.marketing.automations.sent') }}"
                                    title="{{ $totalSent }}/{{ $totalActivation }} {{ __('admin.marketing.automations.sent') }}"
                                >
                                    <strong>{{ $progressPercentage }}%</strong>
                                </div>
                            @endif
                        </div>

                        <div class="promotion-list-actions campaign-list-actions">
                            <a class="promotion-list-action promotion-list-action--primary" href="{{ route('admin.automations.show', $automation) }}">
                                {{ __('admin.marketing.automations.open') }}
                            </a>

                            <a class="promotion-list-action promotion-list-action--primary" href="{{ route('admin.automations.edit', $automation) }}">
                                {{ __('admin.common.edit') }}
                            </a>

                            @if (in_array($automation->status, ['draft', 'paused'], true))
                                <form action="{{ route('admin.automations.activate', $automation) }}" method="POST">
                                    @csrf
                                    <button class="promotion-list-action promotion-list-action--primary" type="submit">
                                        {{ __('admin.marketing.automations.activate') }}
                                    </button>
                                </form>
                            @endif

                            @if ($automation->status === 'active')
                                <form action="{{ route('admin.automations.pause', $automation) }}" method="POST">
                                    @csrf
                                    <button class="promotion-list-action promotion-list-action--danger" type="submit">
                                        {{ __('admin.marketing.automations.pause') }}
                                    </button>
                                </form>
                            @endif

                            @if ($automation->status !== 'archived')
                                <form action="{{ route('admin.automations.archive', $automation) }}" method="POST">
                                    @csrf
                                    <button class="promotion-list-action promotion-list-action--danger" type="submit">
                                        {{ __('admin.marketing.automations.archive') }}
                                    </button>
                                </form>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="marketing-index-pager">
                {{ $automations->links() }}
            </div>
        @else
            <div class="dashboard-home__details-placeholder">
                <span class="dashboard-home__details-placeholder-icon">
                    <i class="bi bi-lightning-charge-fill"></i>
                </span>
                <div>
                    <strong>{{ __('admin.marketing.automations.no_items') }}</strong>
                    <p>{{ __('admin.marketing.automations.empty_text_controlled') }}</p>
                </div>
            </div>
        @endif
    </section>
</div>

@endsection
