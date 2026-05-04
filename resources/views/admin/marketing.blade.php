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

    <div class="order-detail__section-head">
        <h3 class="mt-5">
            <span class="order-detail__section-icon">
                <x-icon name="clock-history" />
            </span>
            Ultimi elementi aggiornati
        </h3>
    </div>

    <div class="menu-dashboard__promo-grid">
        <article class="menu-dashboard__promo-card">
            <div class="menu-dashboard__promo-banner">
                <span class="order-detail__section-icon">
                    <x-icon name="megaphone-fill" />
                </span>
                <div>
                    <span class="menu-dashboard__banner-eyebrow">Promozioni</span>
                    <strong>Ultime regole</strong>
                </div>
            </div>

            <div class="menu-dashboard__promo-body">
                @forelse ($latestPromotions as $promotion)
                    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap py-3 border-top border-secondary-subtle">
                        <div>
                            <strong>{{ $promotion->name }}</strong>
                            <p class="menu-dashboard__copy">{{ $promotion->slug }}</p>
                        </div>
                        <a href="{{ route('admin.promotions.show', $promotion) }}" class="order-detail__contact">
                            <x-icon name="arrow-up-right-circle-fill" />
                            <span>Apri</span>
                        </a>
                    </div>
                @empty
                    <div class="dashboard-home__details-placeholder">
                        <span class="dashboard-home__details-placeholder-icon">
                            <x-icon name="megaphone-fill" />
                        </span>
                        <div>
                            <strong>Nessuna promozione.</strong>
                            <p>Crea la prima regola promozionale.</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </article>

        <article class="menu-dashboard__promo-card">
            <div class="menu-dashboard__promo-banner">
                <span class="order-detail__section-icon">
                    <x-icon name="envelope-paper-fill" />
                </span>
                <div>
                    <span class="menu-dashboard__banner-eyebrow">Campagne</span>
                    <strong>Ultime campagne</strong>
                </div>
            </div>

            <div class="menu-dashboard__promo-body">
                @forelse ($latestCampaigns as $campaign)
                    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap py-3 border-top border-secondary-subtle">
                        <div>
                            <strong>{{ $campaign->name }}</strong>
                            <p class="menu-dashboard__copy">{{ $campaign->promotions->count() }} promozioni collegate</p>
                        </div>
                        <a href="{{ route('admin.campaigns.show', $campaign) }}" class="order-detail__contact">
                            <x-icon name="arrow-up-right-circle-fill" />
                            <span>Apri</span>
                        </a>
                    </div>
                @empty
                    <div class="dashboard-home__details-placeholder">
                        <span class="dashboard-home__details-placeholder-icon">
                            <x-icon name="envelope-paper-fill" />
                        </span>
                        <div>
                            <strong>Nessuna campagna.</strong>
                            <p>Crea una campagna per collegare modello e promozioni.</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </article>

        <article class="menu-dashboard__promo-card">
            <div class="menu-dashboard__promo-banner">
                <span class="order-detail__section-icon">
                    <x-icon name="lightning-charge-fill" />
                </span>
                <div>
                    <span class="menu-dashboard__banner-eyebrow">Automazioni</span>
                    <strong>Ultimi trigger</strong>
                </div>
            </div>

            <div class="menu-dashboard__promo-body">
                @forelse ($latestAutomations as $automation)
                    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap py-3 border-top border-secondary-subtle">
                        <div>
                            <strong>{{ $automation->name }}</strong>
                            <p class="menu-dashboard__copy">{{ $automation->trigger ?: 'Trigger non definito' }}</p>
                        </div>
                        <a href="{{ route('admin.automations.show', $automation) }}" class="order-detail__contact">
                            <x-icon name="arrow-up-right-circle-fill" />
                            <span>Apri</span>
                        </a>
                    </div>
                @empty
                    <div class="dashboard-home__details-placeholder">
                        <span class="dashboard-home__details-placeholder-icon">
                            <x-icon name="lightning-charge-fill" />
                        </span>
                        <div>
                            <strong>Nessuna automazione.</strong>
                            <p>Crea un trigger marketing da preparare in sicurezza.</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </article>

        <article class="menu-dashboard__promo-card">
            <div class="menu-dashboard__promo-banner">
                <span class="order-detail__section-icon">
                    <x-icon name="file-earmark-richtext-fill" />
                </span>
                <div>
                    <span class="menu-dashboard__banner-eyebrow">Modelli mail</span>
                    <strong>Ultimi template</strong>
                </div>
            </div>

            <div class="menu-dashboard__promo-body">
                @forelse ($latestMailModels as $mailModel)
                    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap py-3 border-top border-secondary-subtle">
                        <div>
                            <strong>{{ $mailModel->name }}</strong>
                            <p class="menu-dashboard__copy">{{ $mailModel->object ?: 'Oggetto non definito' }}</p>
                        </div>
                        <a href="{{ route('admin.customers.mail_models.edit', $mailModel->id) }}" class="order-detail__contact">
                            <x-icon name="arrow-up-right-circle-fill" />
                            <span>Apri</span>
                        </a>
                    </div>
                @empty
                    <div class="dashboard-home__details-placeholder">
                        <span class="dashboard-home__details-placeholder-icon">
                            <x-icon name="file-earmark-richtext-fill" />
                        </span>
                        <div>
                            <strong>Nessun modello mail.</strong>
                            <p>Crea un template da usare in campagne e automazioni.</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </article>
    </div>
</div>
@endsection
