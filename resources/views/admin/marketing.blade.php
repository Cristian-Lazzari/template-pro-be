@extends('layouts.base')

@section('contents')
@php
    $marketingCards = [
        [
            'label' => __('admin.marketing.promotions.plural'),
            'count' => (int) ($stat['promotions']['tot'] ?? 0),
            'description' => __('admin.marketing.overview.promotions_description'),
            'route' => route('admin.promotions.index'),
            'create' => route('admin.promotions.create'),
            'icon' => 'megaphone-fill',
        ],
        [
            'label' => __('admin.marketing.campaigns.plural'),
            'count' => (int) ($stat['campaigns']['tot'] ?? 0),
            'description' => __('admin.marketing.overview.campaigns_description'),
            'route' => route('admin.campaigns.index'),
            'create' => route('admin.campaigns.create'),
            'icon' => 'envelope-paper-fill',
        ],
        [
            'label' => __('admin.marketing.automations.plural'),
            'count' => (int) ($stat['automations']['tot'] ?? 0),
            'description' => __('admin.marketing.overview.automations_description'),
            'route' => route('admin.automations.index'),
            'create' => route('admin.automations.create'),
            'icon' => 'lightning-charge-fill',
        ],
        [
            'label' => __('admin.marketing.area_links.mail_models'),
            'count' => (int) ($stat['models']['tot'] ?? 0),
            'description' => __('admin.marketing.overview.mail_models_description'),
            'route' => route('admin.customers.mail_models.index'),
            'create' => route('admin.customers.mail_models.create'),
            'icon' => 'file-earmark-richtext-fill',
        ],
    ];

    $healthCards = [
        [
            'label' => __('admin.marketing.overview.active_promotions'),
            'value' => (int) ($stat['promotions']['active'] ?? 0),
            'total' => (int) ($stat['promotions']['tot'] ?? 0),
        ],
        [
            'label' => __('admin.marketing.overview.active_campaigns'),
            'value' => (int) ($stat['campaigns']['active'] ?? 0),
            'total' => (int) ($stat['campaigns']['tot'] ?? 0),
        ],
        [
            'label' => __('admin.marketing.overview.active_automations'),
            'value' => (int) ($stat['automations']['active'] ?? 0),
            'total' => (int) ($stat['automations']['tot'] ?? 0),
        ],
    ];

    $statPercent = static fn ($value, $total) => $total > 0 ? round((((int) $value) / ((int) $total)) * 100) : 0;
@endphp

<div class="dash_page menu-dashboard-page">
    @include('admin.Marketing.partials.breadcrumbs', [
        'items' => [
            ['label' => __('admin.nav.dashboard'), 'url' => route('admin.dashboard')],
            ['label' => __('admin.marketing.area_links.marketing')],
        ],
    ])

    <header class="menu-dashboard__hero order-detail__summary">
        <div class="order-detail__meta">
            <div class="order-detail__status">
                <span class="order-detail__status-icon order-detail__status-icon--active">
                    <x-icon name="megaphone-fill" />
                </span>
                <strong>{{ __('admin.marketing.overview.title') }}</strong>
            </div>

            <h1 class="menu-dashboard__title">{{ __('admin.marketing.area_links.marketing') }}</h1>
            <p>{{ __('admin.marketing.overview.subtitle') }}</p>
        </div>

        <div class="menu-dashboard__hero-actions dashboard-home__hero-actions">
            <a href="{{ route('admin.campaigns.create') }}" class="order-detail__contact">
                <x-icon name="envelope-plus-fill" />
                <span>{{ __('admin.marketing.campaigns.new') }}</span>
            </a>

            <a href="{{ route('admin.promotions.create') }}" class="order-detail__contact">
                <x-icon name="megaphone-fill" />
                <span>{{ __('admin.marketing.promotions.new') }}</span>
            </a>
        </div>
    </header>

    <section class="order-detail__section">
        <div class="order-detail__section-head">
            <h3>
                <span class="order-detail__section-icon">
                    <x-icon name="grid-1x2-fill" />
                </span>
                {{ __('admin.marketing.overview.section') }}
            </h3>
        </div>

        <div class="menu-dashboard__stat-grid">
            @foreach ($marketingCards as $card)
                <article class="menu-dashboard__stat-card">
                    <span class="menu-dashboard__stat-label">
                        <x-icon :name="$card['icon']" />
                        {{ $card['label'] }}
                    </span>
                    <strong>{{ $card['count'] }}</strong>
                    <small>{{ $card['description'] }}</small>
                    <div class="menu-dashboard__hero-actions dashboard-home__hero-actions mt-3">
                        <a class="order-detail__contact" href="{{ $card['route'] }}">
                            <x-icon name="list-check" />
                            <span>{{ __('admin.marketing.overview.list') }}</span>
                        </a>
                        <a class="order-detail__contact" href="{{ $card['create'] }}">
                            <x-icon name="plus-circle-fill" />
                            <span>{{ __('admin.marketing.overview.create') }}</span>
                        </a>
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    <section class="order-detail__section">
        <div class="order-detail__section-head">
            <h3>
                <span class="order-detail__section-icon">
                    <x-icon name="bar-chart-line-fill" />
                </span>
                {{ __('admin.marketing.overview.operational_status') }}
            </h3>
        </div>

        <div class="menu-dashboard__health-grid">
            @foreach ($healthCards as $card)
                @php
                    $percent = $statPercent($card['value'], $card['total']);
                @endphp

                <article class="menu-dashboard__health-card">
                    <div class="menu-dashboard__health-copy">
                        <span class="menu-dashboard__stat-label">{{ $card['label'] }}</span>
                        <strong>{{ $card['value'] }}</strong>
                        <small>{{ __('admin.marketing.overview.health_stat', ['value' => $card['value'], 'total' => $card['total'], 'percent' => $percent]) }}</small>
                    </div>

                    <div class="donut-wrapper" style="--percent: {{ $percent }}">
                        <p>{{ $card['value'] }}</p>
                    </div>
                </article>
            @endforeach
        </div>
    </section>

</div>
@endsection
