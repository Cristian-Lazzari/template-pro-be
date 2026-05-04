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
            ['label' => 'Automazioni'],
        ],
    ])

    <header class="menu-dashboard__hero order-detail__summary">
        <div class="order-detail__meta">
            <div class="order-detail__status">
                <span class="order-detail__status-icon order-detail__status-icon--active">
                    <i class="bi bi-lightning-charge-fill"></i>
                </span>
                <strong>Marketing</strong>
            </div>

            <h1 class="menu-dashboard__title">Automazioni</h1>
            <p>Trigger marketing configurati, senza scheduler reale in questa fase.</p>
        </div>

        <div class="menu-dashboard__hero-actions dashboard-home__hero-actions">
            <a href="{{ route('admin.automations.create') }}" class="order-detail__contact">
                <i class="bi bi-cloud-plus-fill"></i>
                <span>Crea nuova</span>
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
                Elenco automazioni
            </h3>
        </div>

        @if ($automations->count() > 0)
            <div class="menu-dashboard__promo-grid">
                @foreach ($automations as $automation)
                    <article class="menu-dashboard__promo-card">
                        <div class="menu-dashboard__promo-banner">
                            <span class="order-detail__section-icon">
                                <i class="bi bi-lightning-charge-fill"></i>
                            </span>
                            <div>
                                <span class="menu-dashboard__banner-eyebrow">Automazione</span>
                                @include('admin.Marketing.partials.status-pill', [
                                    'status' => $automation->status,
                                    'label' => $statuses[$automation->status] ?? $automation->status,
                                ])
                            </div>
                        </div>

                        <div class="menu-dashboard__promo-body">
                            <div class="menu-dashboard__promo-top">
                                <div class="menu-dashboard__promo-intro">
                                    <div class="menu-dashboard__chip-row">
                                        <span class="menu-dashboard__chip">{{ $triggers[$automation->trigger] ?? ($automation->trigger ?: 'Trigger non definito') }}</span>
                                        <span class="menu-dashboard__chip menu-dashboard__chip--accent">{{ $automation->promotions->count() }} promo</span>
                                    </div>

                                    <h4>{{ $automation->name }}</h4>
                                    <p class="menu-dashboard__copy">Modello: {{ $automation->model?->name ?? '-' }}</p>
                                </div>

                                <div class="menu-dashboard__price-block">
                                    <span class="menu-dashboard__price">{{ $automation->total_activation }}</span>
                                    <small>coinvolti</small>
                                </div>
                            </div>

                            <div class="menu-dashboard__detail-list">
                                <span>Promozioni collegate</span>
                                <div class="menu-dashboard__pill-row">
                                    @forelse ($automation->promotions as $promotion)
                                        <small>{{ $promotion->name }}</small>
                                    @empty
                                        <small>Nessuna promozione</small>
                                    @endforelse
                                </div>
                            </div>

                            <div class="menu-dashboard__detail-list">
                                <span>Operativita</span>
                                <div class="menu-dashboard__pill-row">
                                    <small>Cooldown: {{ data_get($automation->metadata, 'cooldown_days') ?? '-' }}</small>
                                    <small>Ultimo run: {{ $automation->last_run_at?->format('d/m/Y H:i') ?? '-' }}</small>
                                    <small>{{ $automation->total_sent }} invii</small>
                                </div>
                            </div>

                            <div class="menu-dashboard__hero-actions dashboard-home__hero-actions mt-3">
                                <a class="order-detail__contact" href="{{ route('admin.automations.show', $automation) }}">
                                    <i class="bi bi-eye-fill"></i>
                                    <span>Apri</span>
                                </a>
                                <a class="order-detail__contact" href="{{ route('admin.automations.edit', $automation) }}">
                                    <i class="bi bi-pencil-square"></i>
                                    <span>Modifica</span>
                                </a>
                                @if (in_array($automation->status, ['draft', 'paused'], true))
                                    <form action="{{ route('admin.automations.activate', $automation) }}" method="POST" style="margin: 0;">
                                        @csrf
                                        <button class="order-detail__contact" type="submit">
                                            <i class="bi bi-check2-circle"></i>
                                            <span>Attiva</span>
                                        </button>
                                    </form>
                                @endif
                                @if ($automation->status === 'active')
                                    <form action="{{ route('admin.automations.pause', $automation) }}" method="POST" style="margin: 0;">
                                        @csrf
                                        <button class="order-detail__contact" type="submit">
                                            <i class="bi bi-pause-circle"></i>
                                            <span>Pausa</span>
                                        </button>
                                    </form>
                                @endif
                                @if ($automation->status !== 'archived')
                                    <form action="{{ route('admin.automations.archive', $automation) }}" method="POST" style="margin: 0;">
                                        @csrf
                                        <button class="order-detail__contact" type="submit">
                                            <i class="bi bi-archive-fill"></i>
                                            <span>Archivia</span>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="d-flex justify-content-center mt-3">
                {{ $automations->links() }}
            </div>
        @else
            <div class="dashboard-home__details-placeholder">
                <span class="dashboard-home__details-placeholder-icon">
                    <i class="bi bi-lightning-charge-fill"></i>
                </span>
                <div>
                    <strong>Nessuna automazione presente.</strong>
                    <p>Crea un trigger marketing per preparare audience in modo controllato.</p>
                </div>
            </div>
        @endif
    </section>
</div>

@endsection
