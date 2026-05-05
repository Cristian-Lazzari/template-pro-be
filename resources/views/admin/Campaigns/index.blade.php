@extends('layouts.base')

@section('contents')

@if (session('success'))
    <div class="dashboard-home__flash" role="alert">
        {{ session('success') }}
    </div>
@endif

<div class="dash_page marketing-index-page">
    @include('admin.Marketing.partials.index-style')

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
            <div class="marketing-index-list">
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
                        $pendingAssignments = max(0, $totalAssignments - $sentAssignments);
                        $progressPercentage = $totalAssignments > 0 ? round(($sentAssignments / $totalAssignments) * 100, 2) : 0;
                    @endphp

                    <article class="marketing-index-row">
                        <div class="marketing-index-main">
                            <div class="marketing-index-kicker">
                                <i class="bi bi-envelope-paper-fill"></i>
                                <span>Campagna</span>
                                @include('admin.Marketing.partials.status-pill', [
                                    'status' => $normalizedStatus,
                                    'label' => $statusLabel,
                                ])
                            </div>

                            <h4 class="marketing-index-title">{{ $campaign->name }}</h4>

                            <div class="marketing-index-meta">
                                <span class="marketing-index-chip">{{ $segments[$campaign->segment] ?? ($campaign->segment ?: 'Segmento non definito') }}</span>
                                <span class="marketing-index-chip marketing-index-chip--accent">{{ $campaign->promotions->count() }} promo</span>
                            </div>
                        </div>

                        <div class="marketing-index-block">
                            <p class="marketing-index-copy">Modello: {{ $campaign->model?->name ?? '-' }}</p>
                            <div class="marketing-index-meta">
                                <span>Programmata: {{ $campaign->scheduled_at?->format('d/m/Y H:i') ?? '-' }}</span>
                            </div>
                            <div class="marketing-index-meta marketing-index-extra">
                                @forelse ($campaign->promotions as $promotion)
                                    <span>{{ $promotion->name }}</span>
                                @empty
                                    <span>Nessuna promozione</span>
                                @endforelse
                            </div>
                        </div>

                        <div class="marketing-index-stats">
                            <div class="marketing-index-progress" aria-label="Avanzamento invii campagna">
                                <div class="marketing-index-progress-track">
                                    <div class="marketing-index-progress-bar" style="width: {{ $progressPercentage }}%"></div>
                                </div>
                                <div class="marketing-index-meta">
                                    <span>{{ $sentAssignments }}/{{ $totalAssignments }} invii</span>
                                    <span class="marketing-index-extra">{{ $pendingAssignments }} in attesa</span>
                                </div>
                            </div>
                        </div>

                        <div class="marketing-index-actions">
                            <a class="order-detail__contact" href="{{ route('admin.campaigns.show', $campaign) }}">
                                <i class="bi bi-eye-fill"></i>
                                <span>Apri</span>
                            </a>
                            <a class="order-detail__contact" href="{{ route('admin.campaigns.edit', $campaign) }}">
                                <i class="bi bi-pencil-square"></i>
                                <span>Modifica</span>
                            </a>
                            @if (in_array($normalizedStatus, ['draft', 'paused'], true))
                                <form class="marketing-index-secondary" action="{{ route('admin.campaigns.activate', $campaign) }}" method="POST">
                                    @csrf
                                    <button class="order-detail__contact" type="submit">
                                        <i class="bi bi-check2-circle"></i>
                                        <span>Programma</span>
                                    </button>
                                </form>
                            @endif
                            @if (in_array($normalizedStatus, ['scheduled', 'running'], true))
                                <form class="marketing-index-secondary" action="{{ route('admin.campaigns.pause', $campaign) }}" method="POST">
                                    @csrf
                                    <button class="order-detail__contact marketing-index-muted" type="submit">
                                        <i class="bi bi-pause-circle"></i>
                                        <span>Pausa</span>
                                    </button>
                                </form>
                            @endif
                            @if (in_array($normalizedStatus, ['scheduled', 'running', 'paused', 'draft', 'completed'], true))
                                <form class="marketing-index-secondary" action="{{ route('admin.campaigns.archive', $campaign) }}" method="POST">
                                    @csrf
                                    <button class="order-detail__contact marketing-index-danger" type="submit">
                                        <i class="bi bi-archive-fill"></i>
                                        <span>Archivia</span>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="marketing-index-pager">
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
