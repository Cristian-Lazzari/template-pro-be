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
    $campaignIndexStatusLabels = [
        'draft' => 'Bozza',
        'scheduled' => 'Programmata',
        'running' => 'IN CORSO',
        'completed' => 'Completata',
        'paused' => 'In pausa',
        'archived' => 'Archiviata',
    ];
    $campaignConsentShortLabels = [
        \App\Models\Campaign::CONSENT_BASIS_EXPLICIT_EMAIL_MARKETING => 'Consenso esplicito',
        \App\Models\Campaign::CONSENT_BASIS_SOFT_EMAIL_MARKETING => 'Email soft-spam',
        \App\Models\Campaign::CONSENT_BASIS_WHATSAPP_MARKETING => 'WhatsApp',
    ];
    $campaignConsentTones = [
        \App\Models\Campaign::CONSENT_BASIS_EXPLICIT_EMAIL_MARKETING => 'explicit',
        \App\Models\Campaign::CONSENT_BASIS_SOFT_EMAIL_MARKETING => 'soft',
        \App\Models\Campaign::CONSENT_BASIS_WHATSAPP_MARKETING => 'whatsapp',
    ];
    $formatCampaignSchedule = static function ($date): ?string {
        if (! $date) {
            return null;
        }

        $date = $date instanceof \Illuminate\Support\Carbon
            ? $date->copy()
            : \Illuminate\Support\Carbon::parse($date);
        $time = $date->format('H:i');

        if ($date->isToday()) {
            return 'Oggi ore ' . $time;
        }

        if ($date->isYesterday()) {
            return 'Ieri ore ' . $time;
        }

        if ($date->isTomorrow()) {
            return 'Domani ore ' . $time;
        }

        return ucfirst($date->locale('it')->isoFormat('dddd')) . ' ' . $date->format('d/m') . ' ore ' . $time;
    };
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
                    <i class="bi {{ $isArchivedView ? 'bi-archive-fill' : 'bi-envelope-paper-fill' }}"></i>
                </span>
                <strong>Marketing</strong>
            </div>

            <h1 class="menu-dashboard__title">{{ $isArchivedView ? 'Campagne archiviate' : 'Campagne' }}</h1>
        </div>

        <div class="menu-dashboard__hero-actions dashboard-home__hero-actions">
            @if ($isArchivedView)
                <a href="{{ route('admin.campaigns.index') }}" class="order-detail__contact">
                    <i class="bi bi-arrow-left"></i>
                    <span>Lista campagne</span>
                </a>
            @else
                <a href="{{ route('admin.campaigns.archived') }}" class="order-detail__contact">
                    <i class="bi bi-archive-fill"></i>
                    <span>Archiviate</span>
                </a>
                <a href="{{ route('admin.campaigns.create') }}" class="order-detail__contact">
                    <i class="bi bi-cloud-plus-fill"></i>
                    <span>Crea nuova</span>
                </a>
            @endif
        </div>
    </header>

    <section class="campaign-index-board mt-4" aria-label="{{ $isArchivedView ? 'Campagne archiviate' : 'Elenco campagne' }}">
        @if ($campaigns->count() > 0)
            <div class="campaign-list-render">
                @foreach ($campaigns as $campaign)
                    @php
                        $normalizedStatus = match ($campaign->status) {
                            'active' => 'scheduled',
                            'sent' => 'completed',
                            default => $campaign->status ?: 'draft',
                        };
                        $isDraft = $normalizedStatus === 'draft';
                        $statusLabel = $isDraft
                            ? '-'
                            : ($campaignIndexStatusLabels[$normalizedStatus] ?? ($statuses[$normalizedStatus] ?? $normalizedStatus));
                        $totalAssignments = (int) ($campaign->customer_promotions_count ?? 0);
                        $sentAssignments = (int) ($campaign->sent_customer_promotions_count ?? 0);
                        $progressPercentage = match (true) {
                            $totalAssignments === 0 => 0,
                            $normalizedStatus === 'completed' => 100,
                            default => min(100, round(($sentAssignments / $totalAssignments) * 100)),
                        };
                        $scheduleWindow = data_get($campaign->metadata, 'schedule_window');
                        $scheduleWindowLabel = $scheduleWindows[$scheduleWindow] ?? null;
                        $legacySegmentMap = [
                            'inactive_customers' => 'at_risk_customers',
                            'high_spending_customers' => 'high_value_customers',
                        ];
                        $normalizedSegment = $legacySegmentMap[$campaign->segment] ?? ($campaign->segment ?: 'all');
                        $segmentLabel = $segments[$normalizedSegment] ?? ($campaign->segment ?: 'Segmento non definito');
                        $consentBasis = $campaign->consentBasis();
                        $consentBasisLabel = $campaignConsentShortLabels[$consentBasis] ?? $campaign->consentBasisLabel();
                        $consentBasisTone = $campaignConsentTones[$consentBasis] ?? 'explicit';
                        $promoSlug = $campaign->promotions->first()?->slug ?? '-';
                        $modelName = $campaign->model?->name ?? '-';
                        $scheduledLabel = $isDraft
                            ? '-'
                            : ($formatCampaignSchedule($campaign->scheduled_at) ?? ($scheduleWindowLabel ?: '-'));
                        $canRestore = $isArchivedView && $normalizedStatus === 'archived';
                        $canDeletePermanently = $isArchivedView && $totalAssignments === 0;
                        $canArchive = ! $isArchivedView && ! $isDraft && in_array($normalizedStatus, ['scheduled', 'running', 'paused', 'completed'], true);
                    @endphp

                    <article class="campaign-list-row @if ($isDraft) campaign-list-row--draft @endif">
                        <div class="campaign-list-identity">
                            <div class="campaign-list-heading">
                                <h4 title="{{ $campaign->name }}">{{ $campaign->name }}</h4>
                                <span class="campaign-list-consent campaign-list-consent--{{ $consentBasisTone }}" title="{{ $campaign->consentBasisLabel() }}">
                                    {{ $consentBasisLabel }}
                                </span>
                            </div>

                            <p title="{{ $segmentLabel }}">{{ $segmentLabel }}</p>
                        </div>

                        <div class="campaign-list-state">
                            <strong class="campaign-list-status campaign-list-status--{{ $normalizedStatus }}" title="{{ $statusLabel }}">
                                {{ $statusLabel }}
                            </strong>
                            <span title="{{ $scheduledLabel }}">{{ $scheduledLabel }}</span>
                        </div>

                        <div class="campaign-list-rule">
                            <div>
                                <span>Promo</span>
                                <strong class="campaign-list-promo" title="{{ $promoSlug }}">{{ $promoSlug }}</strong>
                            </div>
                            <div>
                                <span>Modello</span>
                                <strong title="{{ $modelName }}">{{ $modelName }}</strong>
                            </div>
                        </div>

                        <div class="campaign-list-usage">
                            @if (! $isDraft)
                                <div
                                    class="promotion-list-donut campaign-list-donut"
                                    style="--promotion-usage: {{ $progressPercentage }}%;"
                                    role="img"
                                    aria-label="{{ $sentAssignments }} email inviate su {{ $totalAssignments }}"
                                    title="{{ $sentAssignments }} email inviate su {{ $totalAssignments }}"
                                >
                                    <strong>{{ $progressPercentage }}%</strong>
                                </div>
                            @endif
                        </div>

                        <div class="promotion-list-actions campaign-list-actions">
                            @if ($isDraft && ! $isArchivedView)
                                <a class="promotion-list-action promotion-list-action--primary" href="{{ route('admin.campaigns.edit', $campaign) }}">
                                    Completa
                                </a>
                                <form action="{{ route('admin.campaigns.destroy', $campaign) }}" method="POST" onsubmit="return confirm('Eliminare questa bozza e i collegamenti collegati?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="promotion-list-action promotion-list-action--danger" type="submit">
                                        Elimina
                                    </button>
                                </form>
                            @else
                                <a class="promotion-list-action promotion-list-action--primary" href="{{ route('admin.campaigns.show', $campaign) }}">
                                    Apri
                                </a>

                                @if ($canRestore)
                                    <form action="{{ route('admin.campaigns.restore', $campaign) }}" method="POST">
                                        @csrf
                                        <button class="promotion-list-action promotion-list-action--primary" type="submit">
                                            Ripristina
                                        </button>
                                    </form>
                                @endif

                                @if ($canArchive)
                                    <form action="{{ route('admin.campaigns.archive', $campaign) }}" method="POST">
                                        @csrf
                                        <button class="promotion-list-action promotion-list-action--danger" type="submit">
                                            Archivia
                                        </button>
                                    </form>
                                @endif

                                @if ($canDeletePermanently)
                                    <form
                                        action="{{ route('admin.campaigns.destroy', $campaign) }}"
                                        method="POST"
                                        onsubmit="return confirm('Eliminare definitivamente questa campagna? Questa azione non è reversibile.');"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button class="promotion-list-action promotion-list-action--danger" type="submit">
                                            Elimina
                                        </button>
                                    </form>
                                @endif
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
