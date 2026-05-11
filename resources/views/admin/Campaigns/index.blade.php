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

    <header class="campaign-index-hero">
        <div class="campaign-index-hero__top">
            <div class="campaign-index-hero__title">
                <span>Marketing CRM</span>
                <h1>{{ $isArchivedView ? 'Campagne archiviate' : 'Campagne' }}</h1>
                <p>
                    {{ $isArchivedView
                        ? 'Campagne tolte dalla lista principale, consultabili o ripristinabili.'
                        : 'Lista operativa degli invii verso segmenti clienti.' }}
                </p>
            </div>

            @if ($isArchivedView)
                <a href="{{ route('admin.campaigns.index') }}" class="campaign-index-hero__primary">
                    <i class="bi bi-arrow-left"></i>
                    <span>Torna alle campagne</span>
                </a>
            @else
                <a href="{{ route('admin.campaigns.create') }}" class="campaign-index-hero__primary">
                    <i class="bi bi-plus-lg"></i>
                    <span>Crea nuova</span>
                </a>
            @endif
        </div>

        <nav class="campaign-index-quicklinks" aria-label="Collegamenti marketing">
            @if (! $isArchivedView)
                <a href="{{ route('admin.campaigns.archived') }}">
                    <i class="bi bi-archive-fill"></i>
                    <span>Campagne archiviate</span>
                </a>
            @endif
            @include('admin.Marketing.partials.area-links', ['current' => 'campaigns'])
        </nav>
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
                        $canArchive = ! $isArchivedView && in_array($normalizedStatus, ['scheduled', 'running', 'paused', 'draft', 'completed'], true);
                    @endphp

                    <article class="campaign-row-card">
                        <div class="campaign-row-card__identity">
                            <h4 class="campaign-row-card__title" title="{{ $campaign->name }}">{{ $campaign->name }}</h4>
                            <span class="campaign-row-card__basis" title="{{ $consentBasisLabel }}">{{ $consentBasisLabel }}</span>
                            <div class="campaign-row-card__meta-line">
                                <span class="w-100" title="{{ $segmentLabel }}">{{ $segmentLabel }}</span>
                            </div>
                        </div>

                        <div class="campaign-row-card__status">
                            @include('admin.Marketing.partials.status-pill', [
                                'status' => $normalizedStatus,
                                'label' => $statusLabel,
                            ])
                            <span title="{{ $scheduledLabel === '-' ? 'Non programmata' : $scheduledLabel }}">{{ $scheduledLabel === '-' ? 'Non programmata' : $scheduledLabel }}</span>
                        </div>

                        <div class="campaign-row-card__details">
                            <div>
                                <span>Modello</span>
                                <strong title="{{ $campaign->model?->name ?? '-' }}">{{ $campaign->model?->name ?? '-' }}</strong>
                            </div>
                            <div>
                                <span>Promo</span>
                                <strong class="promo">{{ $campaign->promotions[0]->slug }} </strong>
                            </div>
                        </div>

                        <div class="campaign-row-card__progress">
                            <div class="campaign-progress-compact" aria-label="Avanzamento invii campagna">
                                <div class="campaign-progress-compact__head">
                                    <span>{{ $totalAssignments > 0 ? $sentAssignments . '/' . $totalAssignments . ' inviate' : 'Nessun invio' }}</span>
                                    @if ($totalAssignments > 0 && $pendingAssignments > 0)
                                        <small>{{ $pendingAssignments }} in attesa</small>
                                    @endif
                                </div>
                                <div class="campaign-progress-compact__track">
                                    <div class="campaign-progress-compact__bar" style="width: {{ $progressPercentage }}%"></div>
                                </div>
                            </div>
                        </div>

                        <div class="campaign-row-card__actions campaign-actions-compact">
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

                            @if ($canArchive)
                                <form class="marketing-index-secondary" action="{{ route('admin.campaigns.archive', $campaign) }}" method="POST">
                                    @csrf
                                    <button
                                        class="campaign-row-card__icon-action campaign-row-card__icon-action--danger"
                                        type="submit"
                                        aria-label="Archivia campagna"
                                        title="Archivia campagna"
                                    >
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                </form>
                            @endif

                            @if ($canRestore)
                                <form class="marketing-index-secondary" action="{{ route('admin.campaigns.restore', $campaign) }}" method="POST">
                                    @csrf
                                    <button
                                        class="campaign-row-card__icon-action"
                                        type="submit"
                                        aria-label="Ripristina come bozza"
                                        title="Ripristina come bozza"
                                    >
                                        <i class="bi bi-arrow-counterclockwise"></i>
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
                                    <button
                                        class="campaign-row-card__icon-action campaign-row-card__icon-action--danger"
                                        type="submit"
                                        aria-label="Elimina definitivamente"
                                        title="Elimina definitivamente"
                                    >
                                        <i class="bi bi-trash-fill"></i>
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
