@extends('layouts.base')

@section('contents')

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show dashboard-home__flash" role="alert">
        {{ session('success') }}
    </div>
@endif

<div class="dash_page">
    @include('admin.Marketing.partials.breadcrumbs', [
        'items' => [
            ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['label' => 'Marketing', 'url' => route('admin.marketing')],
            ['label' => 'Promozioni'],
        ],
    ])

    <header class="menu-dashboard__hero order-detail__summary">
        <div class="order-detail__meta">
            <div class="order-detail__status">
                <span class="order-detail__status-icon order-detail__status-icon--active">
                    <i class="bi bi-megaphone-fill"></i>
                </span>
                <strong>Marketing</strong>
            </div>

            <h1 class="menu-dashboard__title">Promozioni</h1>
            <p>Regole promozionali, validita e contatori operativi.</p>
        </div>

        <div class="menu-dashboard__hero-actions dashboard-home__hero-actions">
            <a href="{{ route('admin.promotions.create') }}" class="order-detail__contact">
                <i class="bi bi-cloud-plus-fill"></i>
                <span>Crea nuova</span>
            </a>
            @include('admin.Marketing.partials.area-links', ['current' => 'promotions'])
        </div>
    </header>

    <section class="order-detail__section mt-4">
        <div class="order-detail__section-head">
            <h3>
                <span class="order-detail__section-icon">
                    <i class="bi bi-list-check"></i>
                </span>
                Elenco promozioni
            </h3>
        </div>

        @if ($promotions->count() > 0)
            <div class="menu-dashboard__promo-grid">
                @foreach ($promotions as $promotion)
                    <article class="menu-dashboard__promo-card">
                        <div class="menu-dashboard__promo-banner">
                            <span class="order-detail__section-icon">
                                <i class="bi bi-megaphone-fill"></i>
                            </span>
                            <div>
                                <span class="menu-dashboard__banner-eyebrow">Promozione</span>
                                @include('admin.Marketing.partials.status-pill', [
                                    'status' => $promotion->status,
                                    'label' => $statuses[$promotion->status] ?? $promotion->status,
                                ])
                            </div>
                        </div>

                        <div class="menu-dashboard__promo-body">
                            <div class="menu-dashboard__promo-top">
                                <div class="menu-dashboard__promo-intro">
                                    <div class="menu-dashboard__chip-row">
                                        <span class="menu-dashboard__chip">{{ $caseUses[$promotion->case_use] ?? ($promotion->case_use ?: 'Uso generico') }}</span>
                                        <span class="menu-dashboard__chip menu-dashboard__chip--accent">{{ $promotion->permanent ? 'Permanente' : 'Programmabile' }}</span>
                                    </div>

                                    <h4>{{ $promotion->name }}</h4>
                                    <p class="menu-dashboard__copy"><code>{{ $promotion->slug }}</code></p>
                                </div>

                                <div class="menu-dashboard__price-block">
                                    <span class="menu-dashboard__price">
                                        {{ $promotion->discount !== null ? number_format((float) $promotion->discount, 2, ',', '.') : '-' }}
                                    </span>
                                    <small>{{ $discountTypes[$promotion->type_discount] ?? ($promotion->type_discount ?: 'Sconto non impostato') }}</small>
                                </div>
                            </div>

                            <div class="menu-dashboard__detail-list">
                                <span>Contatori</span>
                                <div class="menu-dashboard__pill-row">
                                    <small>{{ $promotion->total_activation }} coinvolti</small>
                                    <small>{{ $promotion->total_sent }} invii</small>
                                    <small>{{ $promotion->total_used }} usi</small>
                                    <small>{{ $promotion->targets_count }} target</small>
                                </div>
                            </div>

                            <div class="menu-dashboard__detail-list">
                                <span>Validita</span>
                                <div class="menu-dashboard__pill-row">
                                    <small>Dal {{ $promotion->schedule_at?->format('d/m/Y H:i') ?? '-' }}</small>
                                    <small>Al {{ $promotion->expiring_at?->format('d/m/Y H:i') ?? '-' }}</small>
                                </div>
                            </div>

                            <div class="menu-dashboard__hero-actions dashboard-home__hero-actions mt-3">
                                <a class="order-detail__contact" href="{{ route('admin.promotions.show', $promotion) }}">
                                    <i class="bi bi-eye-fill"></i>
                                    <span>Apri</span>
                                </a>
                                <a class="order-detail__contact" href="{{ route('admin.promotions.edit', $promotion) }}">
                                    <i class="bi bi-pencil-square"></i>
                                    <span>Modifica</span>
                                </a>
                                <form action="{{ route('admin.promotions.publish', $promotion) }}" method="POST" style="margin: 0;">
                                    @csrf
                                    <button class="order-detail__contact" type="submit">
                                        <i class="bi bi-check2-circle"></i>
                                        <span>Pubblica</span>
                                    </button>
                                </form>
                                <form action="{{ route('admin.promotions.pause', $promotion) }}" method="POST" style="margin: 0;">
                                    @csrf
                                    <button class="order-detail__contact" type="submit">
                                        <i class="bi bi-pause-circle"></i>
                                        <span>Pausa</span>
                                    </button>
                                </form>
                                <form action="{{ route('admin.promotions.archive', $promotion) }}" method="POST" style="margin: 0;">
                                    @csrf
                                    <button class="order-detail__contact" type="submit">
                                        <i class="bi bi-archive-fill"></i>
                                        <span>Archivia</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="d-flex justify-content-center mt-3">
                {{ $promotions->links() }}
            </div>
        @else
            <div class="dashboard-home__details-placeholder">
                <span class="dashboard-home__details-placeholder-icon">
                    <i class="bi bi-megaphone-fill"></i>
                </span>
                <div>
                    <strong>Nessuna promozione presente.</strong>
                    <p>Crea la prima regola promozionale per collegarla a campagne o automazioni.</p>
                </div>
            </div>
        @endif
    </section>
</div>

@endsection
