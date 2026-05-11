@extends('layouts.base')

@section('contents')

@if (session('success'))
    <div class="dashboard-home__flash" role="alert">
        {{ session('success') }}
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger dashboard-home__flash" role="alert">
        {{ $errors->first() }}
    </div>
@endif

@php
    $isArchivedView = $isArchivedView ?? false;
@endphp

<div class="dash_page marketing-index-page">
    @include('admin.Marketing.partials.index-style')

    @include('admin.Marketing.partials.breadcrumbs', [
        'items' => [
            ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['label' => 'Marketing', 'url' => route('admin.marketing')],
            ['label' => $isArchivedView ? 'Campagne archiviate' : 'Campagne'],
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

            <h1 class="menu-dashboard__title">{{ $isArchivedView ? 'Campagne archiviate' : 'Campagne' }}</h1>
            <p>
                {{ $isArchivedView
                    ? 'Campagne tolte dalla lista principale, disponibili per consultazione o ripristino.'
                    : 'Invii manuali o programmati verso segmenti clienti.' }}
            </p>
        </div>

        <div class="menu-dashboard__hero-actions dashboard-home__hero-actions">
            @if ($isArchivedView)
                <a href="{{ route('admin.campaigns.index') }}" class="order-detail__contact">
                    <i class="bi bi-arrow-left"></i>
                    <span>Torna alle campagne</span>
                </a>
            @else
                <a href="{{ route('admin.campaigns.create') }}" class="order-detail__contact">
                    <i class="bi bi-cloud-plus-fill"></i>
                    <span>Crea nuova</span>
                </a>
                <a href="{{ route('admin.campaigns.archived') }}" class="order-detail__contact marketing-index-muted">
                    <i class="bi bi-archive-fill"></i>
                    <span>Campagne archiviate</span>
                </a>
            @endif
            @include('admin.Marketing.partials.area-links', ['current' => 'campaigns'])
        </div>
    </header>

    <section class="order-detail__section mt-4">
        <div class="order-detail__section-head">
            <h3>
                <span class="order-detail__section-icon">
                    <i class="bi bi-list-check"></i>
                </span>
                {{ $isArchivedView ? 'Campagne archiviate' : 'Elenco campagne' }}
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
                        $progressPercentage = match (true) {
                            $totalAssignments === 0 => 0,
                            $normalizedStatus === 'completed' => 100,
                            default => round(($sentAssignments / $totalAssignments) * 100, 2),
                        };
                        $scheduleWindow = data_get($campaign->metadata, 'schedule_window');
                        $scheduleWindowLabel = $scheduleWindows[$scheduleWindow] ?? null;
                        $legacySegmentMap = [
                            'inactive_customers' => 'at_risk_customers',
                            'high_spending_customers' => 'high_value_customers',
                        ];
                        $normalizedSegment = $legacySegmentMap[$campaign->segment] ?? ($campaign->segment ?: 'all');
                        $segmentLabel = $segments[$normalizedSegment] ?? ($campaign->segment ?: 'Segmento non definito');
                        $consentBasisLabel = $campaign->consentBasisLabel();
                        $scheduledLabel = $campaign->scheduled_at?->format('d/m/Y H:i')
                            ?? ($scheduleWindowLabel ?: '-');
                        $canEdit = ! $isArchivedView && in_array($normalizedStatus, ['draft', 'paused'], true);
                        $canRestore = $isArchivedView && $normalizedStatus === 'archived';
                        $canDeletePermanently = $isArchivedView;
                        $hasSecondaryActions = $isArchivedView
                            ? ($canRestore || $canDeletePermanently)
                            : in_array($normalizedStatus, ['draft', 'paused', 'scheduled', 'running', 'completed'], true);
                    @endphp

                    <article class="marketing-index-row campaign-card--compact">
                        <div class="marketing-index-main">
                            <div class="campaign-card__chips">
                                @include('admin.Marketing.partials.status-pill', [
                                    'status' => $normalizedStatus,
                                    'label' => $statusLabel,
                                ])
                                <span class="marketing-index-chip marketing-index-chip--accent">{{ $consentBasisLabel }}</span>
                            </div>

                            <h4 class="marketing-index-title">{{ $campaign->name }}</h4>
                        </div>

                        <div class="marketing-index-block">
                            <div class="campaign-card__meta">
                                <div class="campaign-meta-row">
                                    <span class="campaign-meta-label">Segmento</span>
                                    <span class="campaign-meta-value">{{ $segmentLabel }}</span>
                                </div>
                                <div class="campaign-meta-row">
                                    <span class="campaign-meta-label">Programma</span>
                                    <span class="campaign-meta-value">{{ $scheduledLabel }}</span>
                                </div>
                                <div class="campaign-meta-row">
                                    <span class="campaign-meta-label">Modello</span>
                                    <span class="campaign-meta-value">{{ $campaign->model?->name ?? '-' }}</span>
                                </div>
                                <div class="campaign-meta-row">
                                    <span class="campaign-meta-label">Promo</span>
                                    <span class="campaign-meta-value">{{ $campaign->promotions->count() }} collegate</span>
                                </div>
                            </div>
                        </div>

                        <div class="marketing-index-stats">
                            <div class="marketing-index-progress" aria-label="Avanzamento invii campagna">
                                <div class="marketing-index-progress-track">
                                    <div class="marketing-index-progress-bar" style="width: {{ $progressPercentage }}%"></div>
                                </div>
                                <div class="marketing-index-meta">
                                    <span>{{ $sentAssignments }}/{{ $totalAssignments }} inviate</span>
                                </div>
                            </div>
                        </div>

                        <div class="marketing-index-actions campaign-actions-compact">
                            <div class="campaign-actions-compact__primary">
                                <a class="order-detail__contact" href="{{ route('admin.campaigns.show', $campaign) }}">
                                    <i class="bi bi-eye-fill"></i>
                                    <span>Apri</span>
                                </a>
                                @if ($canEdit)
                                    <a class="order-detail__contact marketing-index-muted" href="{{ route('admin.campaigns.edit', $campaign) }}">
                                        <i class="bi bi-pencil-square"></i>
                                        <span>Modifica</span>
                                    </a>
                                @endif
                            </div>

                            @if ($hasSecondaryActions)
                                <details class="campaign-actions-compact__secondary">
                                    <summary class="order-detail__contact marketing-index-muted">
                                        <i class="bi bi-three-dots"></i>
                                        <span>Azioni</span>
                                    </summary>
                                    <div class="campaign-actions-compact__panel">
                                        @if ($canRestore)
                                            <form class="marketing-index-secondary" action="{{ route('admin.campaigns.restore', $campaign) }}" method="POST">
                                                @csrf
                                                <button class="order-detail__contact" type="submit">
                                                    <i class="bi bi-arrow-counterclockwise"></i>
                                                    <span>Ripristina come bozza</span>
                                                </button>
                                            </form>
                                        @endif
                                        @if ($canDeletePermanently)
                                            <form
                                                class="marketing-index-secondary"
                                                action="{{ route('admin.campaigns.destroy', $campaign) }}"
                                                method="POST"
                                                onsubmit="return confirm('Eliminare definitivamente questa campagna? Questa azione non è reversibile.');"
                                            >
                                                @csrf
                                                @method('DELETE')
                                                <button class="order-detail__contact marketing-index-danger" type="submit">
                                                    <i class="bi bi-trash-fill"></i>
                                                    <span>Elimina definitivamente</span>
                                                </button>
                                            </form>
                                        @endif
                                        @if (! $isArchivedView && in_array($normalizedStatus, ['draft', 'paused'], true))
                                            <form class="marketing-index-secondary" action="{{ route('admin.campaigns.activate', $campaign) }}" method="POST">
                                                @csrf
                                                <button class="order-detail__contact" type="submit">
                                                    <i class="bi bi-check2-circle"></i>
                                                    <span>Programma</span>
                                                </button>
                                            </form>
                                        @endif
                                        @if (! $isArchivedView && in_array($normalizedStatus, ['scheduled', 'running'], true))
                                            <form class="marketing-index-secondary" action="{{ route('admin.campaigns.pause', $campaign) }}" method="POST">
                                                @csrf
                                                <button class="order-detail__contact marketing-index-muted" type="submit">
                                                    <i class="bi bi-pause-circle"></i>
                                                    <span>Pausa</span>
                                                </button>
                                            </form>
                                        @endif
                                        @if (! $isArchivedView && in_array($normalizedStatus, ['scheduled', 'running', 'paused', 'draft', 'completed'], true))
                                            <form class="marketing-index-secondary" action="{{ route('admin.campaigns.archive', $campaign) }}" method="POST">
                                                @csrf
                                                <button class="order-detail__contact marketing-index-danger" type="submit">
                                                    <i class="bi bi-archive-fill"></i>
                                                    <span>Archivia</span>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </details>
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
                    <strong>{{ $isArchivedView ? 'Nessuna campagna archiviata.' : 'Nessuna campagna presente.' }}</strong>
                    <p>
                        {{ $isArchivedView
                            ? 'Quando archivi una campagna, la ritrovi qui.'
                            : 'Crea una campagna per preparare assegnazioni cliente-promozione.' }}
                    </p>
                </div>
            </div>
        @endif
    </section>
</div>

@endsection
