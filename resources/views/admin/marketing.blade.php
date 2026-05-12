@extends('layouts.base')

@section('contents')
@php
    $marketingCards = [
        [
            'label' => 'Promozioni',
            'count' => (int) ($stat['promotions']['tot'] ?? 0),
            'description' => 'Regole sconto, CTA e validita.',
            'route' => route('admin.promotions.index'),
            'create' => route('admin.promotions.create'),
            'icon' => 'megaphone-fill',
        ],
        [
            'label' => 'Campagne',
            'count' => (int) ($stat['campaigns']['tot'] ?? 0),
            'description' => 'Invii manuali o programmati per segmento.',
            'route' => route('admin.campaigns.index'),
            'create' => route('admin.campaigns.create'),
            'icon' => 'envelope-paper-fill',
        ],
        [
            'label' => 'Automazioni',
            'count' => (int) ($stat['automations']['tot'] ?? 0),
            'description' => 'Trigger marketing preparati in sicurezza.',
            'route' => route('admin.automations.index'),
            'create' => route('admin.automations.create'),
            'icon' => 'lightning-charge-fill',
        ],
        [
            'label' => 'Modelli mail',
            'count' => (int) ($stat['models']['tot'] ?? 0),
            'description' => 'Template email usati da campagne e automazioni.',
            'route' => route('admin.customers.mail_models.index'),
            'create' => route('admin.customers.mail_models.create'),
            'icon' => 'file-earmark-richtext-fill',
        ],
    ];

    $healthCards = [
        [
            'label' => 'Promo attive',
            'value' => (int) ($stat['promotions']['active'] ?? 0),
            'total' => (int) ($stat['promotions']['tot'] ?? 0),
        ],
        [
            'label' => 'Campagne attive',
            'value' => (int) ($stat['campaigns']['active'] ?? 0),
            'total' => (int) ($stat['campaigns']['tot'] ?? 0),
        ],
        [
            'label' => 'Automazioni attive',
            'value' => (int) ($stat['automations']['active'] ?? 0),
            'total' => (int) ($stat['automations']['tot'] ?? 0),
        ],
    ];

    $statPercent = static fn ($value, $total) => $total > 0 ? round((((int) $value) / ((int) $total)) * 100) : 0;
@endphp

<div class="dash_page menu-dashboard-page">
    @include('admin.Marketing.partials.breadcrumbs', [
        'items' => [
            ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['label' => 'Marketing'],
        ],
    ])

    <header class="menu-dashboard__hero order-detail__summary">
        <div class="order-detail__meta">
            <div class="order-detail__status">
                <span class="order-detail__status-icon order-detail__status-icon--active">
                    <x-icon name="megaphone-fill" />
                </span>
                <strong>Gestisci il tuo marketing</strong>
            </div>

            <h1 class="menu-dashboard__title">Marketing</h1>
            <p>Promozioni, campagne, automazioni e modelli mail in un unico punto di ingresso.</p>
        </div>

        <div class="menu-dashboard__hero-actions dashboard-home__hero-actions">
            <a href="{{ route('admin.campaigns.create') }}" class="order-detail__contact">
                <x-icon name="envelope-plus-fill" />
                <span>Nuova campagna</span>
            </a>

            <a href="{{ route('admin.promotions.create') }}" class="order-detail__contact">
                <x-icon name="megaphone-fill" />
                <span>Nuova promozione</span>
            </a>
        </div>
    </header>

    <section class="order-detail__section">
        <div class="order-detail__section-head">
            <h3>
                <span class="order-detail__section-icon">
                    <x-icon name="grid-1x2-fill" />
                </span>
                Panoramica marketing
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
                            <span>Lista</span>
                        </a>
                        <a class="order-detail__contact" href="{{ $card['create'] }}">
                            <x-icon name="plus-circle-fill" />
                            <span>Crea</span>
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
                Stato operativo
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
                        <small>{{ $card['value'] }} su {{ $card['total'] }}, {{ $percent }}%</small>
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
