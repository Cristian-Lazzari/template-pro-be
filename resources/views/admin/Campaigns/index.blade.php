@extends('layouts.base')

@section('contents')

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show dashboard-home__flash" role="alert">
        {{ session('success') }}
    </div>
@endif

<div class="dash_page">
    @include('admin.Marketing.partials.show-style')

    @include('admin.Marketing.partials.breadcrumbs', [
        'items' => [
            ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['label' => 'Marketing', 'url' => route('admin.marketing')],
            ['label' => 'Campagne'],
        ],
    ])

    <header class="menu-dashboard__hero order-detail__summary">
        <div class="order-detail__meta">
            <div class="order-detail__status">
                <span class="order-detail__status-icon order-detail__status-icon--active">
                    <i class="bi bi-envelope-paper-fill"></i>
                </span>
                <strong>Marketing</strong>
            </div>

            <h1 class="menu-dashboard__title">Campagne</h1>
            <p>Invii manuali o programmati verso segmenti clienti.</p>
        </div>

        <div class="menu-dashboard__hero-actions dashboard-home__hero-actions">
            <a href="{{ route('admin.campaigns.create') }}" class="order-detail__contact">
                <i class="bi bi-cloud-plus-fill"></i>
                <span>Crea nuova</span>
            </a>
            @include('admin.Marketing.partials.area-links', ['current' => 'campaigns'])
        </div>
    </header>

    <section class="order-detail__section mt-4">
        <div class="order-detail__section-head">
            <h3>
                <span class="order-detail__section-icon">
                    <i class="bi bi-list-check"></i>
                </span>
                Elenco campagne
            </h3>
        </div>

        @if ($campaigns->count() > 0)
            <div class="menu-dashboard__promo-grid">
                @foreach ($campaigns as $campaign)
                    @php
                        $normalizedStatus = match ($campaign->status) {
                            'active' => 'scheduled',
                            'sent' => 'completed',
                            default => $campaign->status,
                        };
                        $statusLabel = $statuses[$normalizedStatus] ?? ($statuses[$campaign->status] ?? $campaign->status);
                        $totalAssignments = (int) ($campaign->customer_promotions_count ?? 0);
                        $sentAssignments = (int) ($campaign->sent_customer_promotions_count ?? 0);
                        $progressPercentage = $totalAssignments > 0 ? round(($sentAssignments / $totalAssignments) * 100, 2) : 0;
                    @endphp
                    <article class="menu-dashboard__promo-card">
                        <div class="menu-dashboard__promo-banner">
                            <span class="order-detail__section-icon">
                                <i class="bi bi-envelope-paper-fill"></i>
                            </span>
                            <div>
                                <span class="menu-dashboard__banner-eyebrow">Campagna</span>
                                @include('admin.Marketing.partials.status-pill', [
                                    'status' => $normalizedStatus,
                                    'label' => $statusLabel,
                                ])
                            </div>
                        </div>

                        <div class="menu-dashboard__promo-body">
                            <div class="menu-dashboard__promo-top">
                                <div class="menu-dashboard__promo-intro">
                                    <div class="menu-dashboard__chip-row">
                                        <span class="menu-dashboard__chip">{{ $segments[$campaign->segment] ?? ($campaign->segment ?: 'Segmento non definito') }}</span>
                                        <span class="menu-dashboard__chip menu-dashboard__chip--accent">{{ $campaign->promotions->count() }} promo</span>
                                    </div>

                                    <h4>{{ $campaign->name }}</h4>
                                    <p class="menu-dashboard__copy">Modello: {{ $campaign->model?->name ?? '-' }}</p>
                                </div>

                                <div class="menu-dashboard__price-block">
                                    <span class="menu-dashboard__price">{{ $campaign->total_activation }}</span>
                                    <small>coinvolti</small>
                                </div>
                            </div>

                            <div class="menu-dashboard__detail-list">
                                <span>Promozioni collegate</span>
                                <div class="menu-dashboard__pill-row">
                                    @forelse ($campaign->promotions as $promotion)
                                        <small>{{ $promotion->name }}</small>
                                    @empty
                                        <small>Nessuna promozione</small>
                                    @endforelse
                                </div>
                            </div>

                            <div class="menu-dashboard__detail-list">
                                <span>Operativita</span>
                                <div class="menu-dashboard__pill-row">
                                    <small>Programmata: {{ $campaign->scheduled_at?->format('d/m/Y H:i') ?? '-' }}</small>
                                    <small>{{ $sentAssignments }}/{{ $totalAssignments }} invii</small>
                                </div>
                            </div>

                            <div class="marketing-detail__progress" aria-label="Avanzamento invii campagna">
                                <div class="marketing-detail__progress-track">
                                    <div class="marketing-detail__progress-bar" style="width: {{ $progressPercentage }}%"></div>
                                </div>
                                <div class="marketing-detail__progress-meta">
                                    <span>{{ $statusLabel }}</span>
                                    <span>{{ $progressPercentage }}%</span>
                                </div>
                            </div>

                            <div class="menu-dashboard__hero-actions dashboard-home__hero-actions mt-3">
                                <a class="order-detail__contact" href="{{ route('admin.campaigns.show', $campaign) }}">
                                    <i class="bi bi-eye-fill"></i>
                                    <span>Apri</span>
                                </a>
                                <a class="order-detail__contact" href="{{ route('admin.campaigns.edit', $campaign) }}">
                                    <i class="bi bi-pencil-square"></i>
                                    <span>Modifica</span>
                                </a>
                                @if (in_array($normalizedStatus, ['draft', 'paused'], true))
                                    <form action="{{ route('admin.campaigns.activate', $campaign) }}" method="POST" style="margin: 0;">
                                        @csrf
                                        <button class="order-detail__contact" type="submit">
                                            <i class="bi bi-check2-circle"></i>
                                            <span>Programma</span>
                                        </button>
                                    </form>
                                @endif
                                @if (in_array($normalizedStatus, ['scheduled', 'running'], true))
                                    <form action="{{ route('admin.campaigns.pause', $campaign) }}" method="POST" style="margin: 0;">
                                        @csrf
                                        <button class="order-detail__contact" type="submit">
                                            <i class="bi bi-pause-circle"></i>
                                            <span>Pausa</span>
                                        </button>
                                    </form>
                                @endif
                                @if (in_array($normalizedStatus, ['scheduled', 'running', 'paused', 'draft', 'completed'], true))
                                    <form action="{{ route('admin.campaigns.archive', $campaign) }}" method="POST" style="margin: 0;">
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
                {{ $campaigns->links() }}
            </div>
        @else
            <div class="dashboard-home__details-placeholder">
                <span class="dashboard-home__details-placeholder-icon">
                    <i class="bi bi-envelope-paper-fill"></i>
                </span>
                <div>
                    <strong>Nessuna campagna presente.</strong>
                    <p>Crea una campagna per preparare assegnazioni cliente-promozione.</p>
                </div>
            </div>
        @endif
    </section>
</div>

@endsection
