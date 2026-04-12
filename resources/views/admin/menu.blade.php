@extends('layouts.base')

@section('contents')
@php
    $fallbackImage = asset('public/favicon.png');
    $formatPrice = static fn ($value) => number_format(((int) $value) / 100, 2, ',', '.');
    $statPercent = static fn ($value, $total) => $total > 0 ? round((((int) $value) / ((int) $total)) * 100) : 0;

    $productTotal = (int) ($stat['products']['tot'] ?? 0);

    $catalogCards = [
        [
            'label' => __('admin.Prodotti'),
            'count' => (int) ($stat['products']['tot'] ?? 0),
            'description' => 'Catalogo prodotti e promozioni.',
            'route' => route('admin.products.index'),
        ],
        [
            'label' => __('admin.Menu'),
            'count' => (int) ($stat['menus']['tot'] ?? 0),
            'description' => 'Menu fissi, formule e bundle.',
            'route' => route('admin.menus.index'),
        ],
        [
            'label' => __('admin.Categorie'),
            'count' => (int) ($stat['categories']['tot'] ?? 0),
            'description' => 'Struttura e ordine del menu.',
            'route' => route('admin.categories.index'),
        ],
        [
            'label' => __('admin.Ingredienti'),
            'count' => (int) ($stat['ingredients']['tot'] ?? 0),
            'description' => 'Composizione e varianti dei piatti.',
            'route' => route('admin.ingredients.index'),
        ],
        [
            'label' => __('admin.Allergeni'),
            'count' => (int) ($stat['allergens']['tot'] ?? 0),
            'description' => 'Informazioni sensibili per il cliente.',
            'route' => route('admin.allergens.index'),
        ],
    ];

    $productHealthCards = [
        [
            'label' => __('admin.stat_1_menu'),
            'value' => (int) ($stat['products']['not_archived_visible'] ?? 0),
        ],
        [
            'label' => __('admin.stat_2_menu'),
            'value' => (int) ($stat['products']['not_archived'] ?? 0),
        ],
        [
            'label' => __('admin.stat_3_menu'),
            'value' => (int) ($stat['products']['archived'] ?? 0),
        ],
    ];
@endphp

<div class="dash_page menu-dashboard-page">
    <section class="menu-dashboard order-detail order-detail--active">
        <header class="menu-dashboard__hero order-detail__summary">
            <div class="order-detail__meta">
                <div class="order-detail__status">
                    <span class="order-detail__status-icon order-detail__status-icon--active">
                        <x-icon name="fork-knife" />
                    </span>
                    <strong>Dashboard catalogo</strong>
                </div>

                <h1 class="menu-dashboard__title">{{ __('admin.t_menu') }}</h1>
                <p class="menu-dashboard__lead">
                    Una vista unica per controllare prodotti, promozioni, menu e anagrafiche con lo stesso stile operativo delle pagine ordine e prenotazione.
                </p>
            </div>

            <div class="menu-dashboard__hero-actions">
                <a href="{{ route('admin.products.index') }}" class="order-detail__contact">
                    <x-icon name="box-seam" />
                    <span>{{ __('admin.Prodotti') }}</span>
                </a>

                <a href="{{ route('admin.menus.index') }}" class="order-detail__contact">
                    <x-icon name="card-checklist" />
                    <span>{{ __('admin.Menu') }}</span>
                </a>
            </div>
        </header>

        <section class="order-detail__section">
            <div class="order-detail__section-head">
                <h3>
                    <span class="order-detail__section-icon">
                        <x-icon name="grid-1x2-fill" />
                    </span>
                    Panoramica catalogo
                </h3>
            </div>

            <div class="menu-dashboard__stat-grid">
                @foreach ($catalogCards as $card)
                    <a href="{{ $card['route'] }}" class="menu-dashboard__stat-card">
                        <span class="menu-dashboard__stat-label">{{ $card['label'] }}</span>
                        <strong>{{ $card['count'] }}</strong>
                        <small>{{ $card['description'] }}</small>
                        <span class="menu-dashboard__stat-link">{{ __('admin.Vedi_tutti') }}</span>
                    </a>
                @endforeach
            </div>
        </section>

        <section class="order-detail__section">
            <div class="order-detail__section-head">
                <h3>
                    <span class="order-detail__section-icon">
                        <x-icon name="bar-chart-line-fill" />
                    </span>
                    Stato prodotti
                </h3>
            </div>

            <div class="menu-dashboard__health-grid">
                @foreach ($productHealthCards as $card)
                    @php
                        $percent = $statPercent($card['value'], $productTotal);
                    @endphp

                    <article class="menu-dashboard__health-card">
                        <div class="menu-dashboard__health-copy">
                            <span class="menu-dashboard__stat-label">{{ $card['label'] }}</span>
                            <strong>{{ $card['value'] }}</strong>
                            <small>{{ $card['value'] }} su {{ $productTotal }} prodotti, {{ $percent }}%</small>
                        </div>

                        <div class="donut-wrapper" style="--percent: {{ $percent }}">
                            <p>{{ $card['value'] }}</p>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>

        <section class="order-detail__section">
            <div class="order-detail__section-head">
                <h3>
                    <span class="order-detail__section-icon">
                        <x-icon name="bookmark-star-fill" />
                    </span>
                    {{ __('admin.promo_p') }}
                </h3>
            </div>

            @if (count($products))
                <div class="menu-dashboard__promo-grid">
                    @foreach ($products as $product)
                        <article class="menu-dashboard__promo-card">
                            <div class="menu-dashboard__promo-media">
                                <img
                                    src="{{ $product->image ? asset('public/storage/' . $product->image) : $fallbackImage }}"
                                    alt="{{ $product->name }}"
                                >
                            </div>

                            <div class="menu-dashboard__promo-body">
                                <div class="menu-dashboard__promo-top">
                                    <div class="menu-dashboard__promo-intro">
                                        <div class="menu-dashboard__chip-row">
                                            <span class="menu-dashboard__chip">{{ optional($product->category)->name ?? __('admin.Prodotti') }}</span>
                                            <span class="menu-dashboard__chip menu-dashboard__chip--accent">Promo</span>
                                        </div>

                                        <h4>{{ $product->name }}</h4>
                                    </div>

                                    <div class="menu-dashboard__price-block">
                                        <span class="menu-dashboard__price">€{{ $formatPrice($product->price) }}</span>
                                    </div>
                                </div>

                                @if ($product->description)
                                    <p class="menu-dashboard__copy">{{ $product->description }}</p>
                                @endif

                                @if ($product->ingredients->isNotEmpty())
                                    <div class="menu-dashboard__detail-list">
                                        <span>{{ __('admin.Ingredienti') }}</span>

                                        <div class="menu-dashboard__pill-row">
                                            @foreach ($product->ingredients as $ingredient)
                                                <small>{{ $ingredient->name }}</small>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="menu-dashboard__empty-state">
                    {{ __('admin.no_promo_p') }}
                </div>
            @endif
        </section>

        <section class="order-detail__section">
            <div class="order-detail__section-head">
                <h3>
                    <span class="order-detail__section-icon">
                        <x-icon name="stars" />
                    </span>
                    {{ __('admin.promo_m') }}
                </h3>
            </div>

            @if (count($menus))
                <div class="menu-dashboard__promo-grid">
                    @foreach ($menus as $menu)
                        @php
                            $hasChoices = is_array($menu->fixed_menu);
                        @endphp

                        <article class="menu-dashboard__promo-card menu-dashboard__promo-card--menu">
                            <div class="menu-dashboard__promo-banner">
                                <span class="order-detail__section-icon">
                                    <x-icon name="fork-knife" />
                                </span>

                                <div>
                                    <span class="menu-dashboard__banner-eyebrow">Formula in evidenza</span>
                                    <strong>{{ $hasChoices ? 'Menu a scelta' : 'Menu fisso' }}</strong>
                                </div>
                            </div>

                            <div class="menu-dashboard__promo-body">
                                <div class="menu-dashboard__promo-top">
                                    <div class="menu-dashboard__promo-intro">
                                        <div class="menu-dashboard__chip-row">
                                            <span class="menu-dashboard__chip">Promo</span>

                                            @if ($menu->category)
                                                <span class="menu-dashboard__chip">{{ $menu->category->name }}</span>
                                            @endif

                                            <span class="menu-dashboard__chip menu-dashboard__chip--accent">
                                                {{ $hasChoices ? 'Scelte multiple' : 'Percorso fisso' }}
                                            </span>
                                        </div>

                                        <h4>{{ $menu->name }}</h4>
                                    </div>

                                    <div class="menu-dashboard__price-block">
                                        @if ($menu->old_price)
                                            <span class="menu-dashboard__price-old">€{{ $formatPrice($menu->old_price) }}</span>
                                        @endif

                                        <span class="menu-dashboard__price">€{{ $formatPrice($menu->price) }}</span>
                                    </div>
                                </div>

                                @if ($menu->description)
                                    <p class="menu-dashboard__copy">{{ $menu->description }}</p>
                                @endif

                                @if ($hasChoices)
                                    <div class="menu-dashboard__choice-grid">
                                        @foreach ($menu->fixed_menu as $label => $choiceProducts)
                                            <div class="menu-dashboard__choice-group">
                                                <span class="menu-dashboard__choice-label">{{ $label }}</span>

                                                <div class="menu-dashboard__pill-row">
                                                    @foreach ($choiceProducts as $choiceProduct)
                                                        <small>
                                                            {{ $choiceProduct->name }}
                                                            @if ($choiceProduct->pivot->extra_price)
                                                                (+ €{{ $formatPrice($choiceProduct->pivot->extra_price) }})
                                                            @endif
                                                        </small>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @elseif ($menu->products->isNotEmpty())
                                    <div class="menu-dashboard__detail-list">
                                        <span>{{ __('admin.Prodotti') }}</span>

                                        <div class="menu-dashboard__pill-row">
                                            @foreach ($menu->products as $menuProduct)
                                                <small>
                                                    {{ $menuProduct->name }}
                                                    @if ($menuProduct->category)
                                                        ({{ $menuProduct->category->name }})
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
            @else
                <div class="menu-dashboard__empty-state">
                    {{ __('admin.no_promo_m') }}
                </div>
            @endif
        </section>
    </section>
</div>
@endsection
