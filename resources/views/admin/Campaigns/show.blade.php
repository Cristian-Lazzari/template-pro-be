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
    $normalizedStatus = $sendProgress['status'] ?? match ($campaign->status) {
        'active' => 'scheduled',
        'sent' => 'completed',
        default => $campaign->status,
    };
    $statusLabel = $statuses[$normalizedStatus] ?? ($statuses[$campaign->status] ?? $campaign->status);
    $totalEmails = (int) ($sendProgress['involved_count'] ?? ($sendProgress['total'] ?? ($involvedCount ?? 0)));
    $sentEmails = (int) ($sendProgress['sent_count'] ?? ($sendProgress['sent'] ?? ($sentCount ?? 0)));
    $pendingEmails = (int) ($sendProgress['pending_count'] ?? ($sendProgress['pending'] ?? ($pendingCount ?? max(0, $totalEmails - $sentEmails))));
    $hasAssignments = $hasAssignments ?? $totalEmails > 0;
    $rawProgressPercentage = $sendProgress['progress_percentage'] ?? ($sendProgress['percentage'] ?? ($progressPercentage ?? 0));
    $progressPercentage = min(100, max(0, (float) $rawProgressPercentage));
    $tone = match ($normalizedStatus) {
        'completed' => 'active',
        'archived' => 'off',
        default => 'warning',
    };

    $icon = match ($normalizedStatus) {
        'completed' => 'bi-check-circle-fill',
        'scheduled' => 'bi-calendar-check-fill',
        'running' => 'bi-play-circle-fill',
        'paused' => 'bi-pause-circle-fill',
        'archived' => 'bi-x-circle-fill',
        default => 'bi-exclamation-circle-fill',
    };

    $legacySegmentMap = [
        'inactive_customers' => 'at_risk_customers',
        'high_spending_customers' => 'high_value_customers',
    ];
    $normalizedSegment = $legacySegmentMap[$campaign->segment] ?? ($campaign->segment ?: 'all');
    $segmentLabel = $segments[$normalizedSegment] ?? ($campaign->segment ?: '-');
    $consentBasis = $campaign->consentBasis();
    $consentBasisLabel = ($consentBasisOptions ?? \App\Models\Campaign::consentBasisOptions())[$consentBasis] ?? $campaign->consentBasisLabel();
    $channelLabel = $campaign->usesWhatsappMarketingConsent() ? 'WhatsApp' : 'Email';
    $isWhatsappMarketing = $campaign->usesWhatsappMarketingConsent();
    $modelName = $campaign->model?->name ?? '-';
    $modelObject = $campaign->model?->object ?? '-';
    $promotionsCount = $campaign->promotions->count();
    $scheduleState = $sendProgress['message'] ?? 'Nessuna programmazione';
    $nextBatchDueAt = $sendProgress['next_batch_due_at'] ?? ($nextBatchDueAt ?? null);
    $scheduleWindow = data_get($campaign->metadata, 'schedule_window');
    $scheduleWindowLabel = $scheduleWindows[$scheduleWindow] ?? ($scheduleWindow ?: '-');
    $requestedScheduledAt = data_get($campaign->metadata, 'requested_scheduled_at');
    $estimatedDuration = $sendProgress['estimated_duration_minutes'] ?? ($estimatedDurationMinutes ?? data_get($campaign->metadata, 'estimated_duration_minutes'));
    $batchIntervalMinutes = max(0, (int) data_get($campaign->metadata, 'batch_interval_minutes', 0));
    $completedAt = $sendProgress['completed_at'] ?? ($completedAt ?? $campaign->sent_at);
    $scheduledAtIso = $campaign->scheduled_at?->toIso8601String();
    $nextBatchDueAtIso = $nextBatchDueAt?->toIso8601String();
    $batchWaitStartedAt = ($nextBatchDueAt && $batchIntervalMinutes > 0)
        ? $nextBatchDueAt->copy()->subMinutes($batchIntervalMinutes)
        : null;
    $batchWaitStartedAtIso = $batchWaitStartedAt?->toIso8601String();
    $readyRunnerMessage = 'Pronta per il prossimo ciclo del runner marketing';
    $isScheduledFuture = $normalizedStatus === 'scheduled' && $campaign->scheduled_at?->isFuture();
    $isRunning = $normalizedStatus === 'running';
    $hasFutureNextBatch = $isRunning && $nextBatchDueAt?->isFuture();
    $progressPercentageLabel = number_format($progressPercentage, $progressPercentage === floor($progressPercentage) ? 0 : 2, ',', '.') . '%';
    $sendPanelTitle = match ($normalizedStatus) {
        'scheduled' => 'Invio programmato',
        'running' => 'Invio in corso',
        'completed' => 'Tutte le email sono state inviate',
        'paused' => 'Campagna in pausa',
        'archived' => 'Campagna archiviata',
        'draft' => 'Bozza',
        default => $statusLabel,
    };
    $sendPanelBadge = match ($normalizedStatus) {
        'scheduled' => 'Programmata',
        'running' => 'Live',
        'completed' => 'Completata',
        'paused' => 'In pausa',
        'archived' => 'Archiviata',
        'draft' => 'Bozza',
        default => $statusLabel,
    };
    $sendPanelMessage = match (true) {
        $normalizedStatus === 'scheduled' && $isScheduledFuture => 'Invio programmato per: ' . $campaign->scheduled_at->format('d/m/Y H:i'),
        $normalizedStatus === 'scheduled' => $readyRunnerMessage,
        $isRunning => 'Invio in corso tramite runner marketing.',
        $normalizedStatus === 'completed' => 'Tutte le email sono state inviate',
        $normalizedStatus === 'draft' => 'Bozza: programma la campagna per creare i destinatari.',
        $normalizedStatus === 'paused' => 'Campagna in pausa: non verranno inviati nuovi batch.',
        $normalizedStatus === 'archived' => 'Campagna archiviata.',
        default => $scheduleState,
    };
    $caseUseLabels = [
        'generic' => 'Generica',
        'take_away' => 'Asporto',
        'delivery' => 'Delivery',
        'table' => 'Tavolo',
        'gift' => 'Regalo',
    ];
    $discountTypeLabels = [
        'fixed' => 'Importo fisso',
        'percentage' => 'Percentuale',
        'gift' => 'Regalo',
    ];
    $formatDiscount = function ($promotion) {
        if (! $promotion) {
            return '-';
        }

        if ($promotion->type_discount === 'gift') {
            return 'Regalo';
        }

        if ($promotion->discount === null) {
            return '-';
        }

        $value = number_format((float) $promotion->discount, 2, ',', '.');
        $value = str_ends_with($value, ',00') ? substr($value, 0, -3) : $value;

        return match ($promotion->type_discount) {
            'fixed' => $value . '€',
            'percentage' => $value . '%',
            default => $value,
        };
    };
    $campaignPromotionCaseUses = collect($campaignPromotionCaseUses ?? $campaign->promotions->pluck('case_use')->filter()->unique()->values());
    $hasLinkedPromotions = $hasLinkedPromotions ?? $campaign->promotions->isNotEmpty();
    $showOrderMetrics = $showOrderMetrics
        ?? ($hasLinkedPromotions && $campaignPromotionCaseUses->intersect(['take_away', 'delivery', 'generic'])->isNotEmpty());
    $showReservationMetrics = $showReservationMetrics
        ?? ($hasLinkedPromotions && $campaignPromotionCaseUses->intersect(['table', 'generic'])->isNotEmpty());
    $canPreviewAudience = $canPreviewAudience ?? ($normalizedStatus === 'draft' && ! $hasAssignments && $hasLinkedPromotions);
    $canPrepareAssignments = $canPrepareAssignments ?? $canPreviewAudience;
    $canActivateCampaign = $canActivateCampaign ?? in_array($normalizedStatus, ['draft', 'paused'], true);
    $canPauseCampaign = $canPauseCampaign ?? in_array($normalizedStatus, ['scheduled', 'running'], true);
    $canDraftCampaign = $canDraftCampaign ?? in_array($normalizedStatus, ['scheduled', 'running', 'paused'], true);
    $canArchiveCampaign = $canArchiveCampaign ?? in_array($normalizedStatus, ['draft', 'scheduled', 'running', 'paused', 'completed'], true);
    $canRestoreCampaign = $canRestoreCampaign ?? $normalizedStatus === 'archived';
    $canDestroyCampaign = $canDestroyCampaign ?? ($normalizedStatus === 'archived' && ! $hasAssignments);
    $backToCampaignsRoute = $normalizedStatus === 'archived'
        ? route('admin.campaigns.archived')
        : route('admin.campaigns.index');
    $reportMetrics = [
        ['label' => 'Destinatari', 'value' => $totalEmails, 'tone' => 'neutral'],
        ['label' => 'Email inviate', 'value' => $sentEmails, 'tone' => 'neutral'],
        ['label' => 'Aperture', 'value' => $report['opened_count'] ?? 0, 'tone' => 'neutral'],
        ['label' => 'Click', 'value' => $report['clicked_count'] ?? 0, 'tone' => 'neutral'],
        ['label' => 'Promo usate', 'value' => $report['used_count'] ?? 0, 'tone' => 'active'],
    ];

    $reportMetrics[] = ['label' => 'Open rate', 'value' => number_format((float) ($report['open_rate'] ?? 0), 2, ',', '.') . '%', 'tone' => 'rate'];
    $reportMetrics[] = ['label' => 'Click rate', 'value' => number_format((float) ($report['click_rate'] ?? 0), 2, ',', '.') . '%', 'tone' => 'rate'];
    $reportMetrics[] = ['label' => 'Usage rate', 'value' => number_format((float) ($report['usage_rate'] ?? 0), 2, ',', '.') . '%', 'tone' => 'rate'];
@endphp

<div class="dash_page">
    @include('admin.Marketing.partials.breadcrumbs', [
        'items' => [
            ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['label' => 'Marketing', 'url' => route('admin.marketing')],
            ['label' => 'Campagne', 'url' => route('admin.campaigns.index')],
            ['label' => $campaign->name],
        ],
    ])

    @include('admin.Marketing.partials.show-style')

    <style>
        .campaign-send-panel {
            display: grid;
            gap: 16px;
        }

        .campaign-send-panel__head {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: flex-start;
            justify-content: space-between;
        }

        .campaign-send-panel__title {
            display: grid;
            gap: 5px;
            min-width: 0;
        }

        .campaign-send-panel__title strong {
            color: var(--c3);
            font-size: var(--fs-500);
            line-height: 1.15;
            overflow-wrap: anywhere;
        }

        .campaign-send-panel__title small {
            color: rgba(216, 221, 232, 0.74);
            line-height: 1.4;
            overflow-wrap: anywhere;
        }

        .campaign-send-panel__badge {
            display: inline-flex;
            width: fit-content;
            max-width: 100%;
            align-items: center;
            gap: 7px;
            border-radius: 999px;
            border: 1px solid rgba(14, 183, 146, 0.28);
            background: rgba(14, 183, 146, 0.12);
            color: rgba(184, 255, 236, 0.96);
            font-size: var(--fs-100);
            font-weight: 900;
            line-height: 1.2;
            padding: 8px 11px;
            overflow-wrap: anywhere;
        }

        .campaign-header-actions form {
            margin: 0;
            display: flex;
        }

        .campaign-send-panel .marketing-detail__progress-live {
            display: inline-flex;
            width: fit-content;
            max-width: 100%;
            align-items: center;
            gap: 8px;
            padding: 10px 12px;
            border-radius: 8px;
            border: 1px solid rgba(98, 166, 255, 0.2);
            background: rgba(98, 166, 255, 0.08);
            font-weight: 900;
            line-height: 1.35;
        }

        .campaign-send-panel .marketing-detail__progress-track {
            position: relative;
            min-height: 18px;
            overflow: hidden;
            box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.28);
        }

        .campaign-send-panel .marketing-detail__progress-bar {
            min-height: 18px;
            transition: width .8s cubic-bezier(.22, 1, .36, 1);
            transform-origin: left center;
            animation: campaignProgressLoad .7s ease-out both;
        }

        .campaign-send-panel .marketing-detail__progress-bar.is-running {
            background-image:
                repeating-linear-gradient(
                    45deg,
                    rgba(255, 255, 255, 0.18) 0,
                    rgba(255, 255, 255, 0.18) 8px,
                    transparent 8px,
                    transparent 16px
                ),
                linear-gradient(90deg, rgba(74, 222, 128, 0.96), rgba(45, 212, 191, 0.9));
            background-size: 24px 24px, auto;
            animation: campaignProgressLoad .7s ease-out both, campaignProgressStripes .95s linear infinite, campaignProgressPulse 1.8s ease-in-out infinite;
        }

        .campaign-send-panel .marketing-detail__progress-bar.is-scheduled {
            width: 100% !important;
            background-image:
                linear-gradient(90deg, rgba(98, 166, 255, 0.16), rgba(14, 183, 146, 0.28), rgba(98, 166, 255, 0.16));
            background-size: 220% 100%;
            animation: campaignProgressShimmer 1.7s ease-in-out infinite;
            opacity: .86;
        }

        .campaign-send-panel .marketing-detail__progress-bar.is-completed {
            background: linear-gradient(90deg, rgba(34, 197, 94, 0.96), rgba(14, 183, 146, 0.94));
        }

        .campaign-send-panel .marketing-detail__progress-percent {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 10px;
            color: var(--c3);
            font-size: var(--fs-100);
            font-weight: 900;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.55);
            pointer-events: none;
        }

        .campaign-send-panel .marketing-detail__progress-live {
            color: rgba(216, 221, 232, 0.9);
        }

        .campaign-send-panel .marketing-detail__progress-live.is-expired {
            border-color: rgba(14, 183, 146, 0.24);
            background: rgba(14, 183, 146, 0.09);
            color: rgba(184, 255, 236, 0.95);
        }

        .campaign-send-panel__wait {
            display: grid;
            gap: 8px;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid rgba(216, 221, 232, 0.12);
            background: rgba(216, 221, 232, 0.04);
        }

        .campaign-send-panel__wait-head,
        .campaign-send-panel__wait-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
            justify-content: space-between;
        }

        .campaign-send-panel__wait-head strong {
            color: var(--c3);
            line-height: 1.25;
        }

        .campaign-send-panel__wait-meta {
            color: rgba(216, 221, 232, 0.68);
            font-size: var(--fs-100);
            font-weight: 800;
            line-height: 1.35;
        }

        .campaign-send-panel__wait-track {
            position: relative;
            height: 8px;
            overflow: hidden;
            border-radius: 999px;
            background: rgba(9, 3, 51, 0.46);
        }

        .campaign-send-panel__wait-bar {
            width: 0;
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(90deg, rgba(98, 166, 255, 0.9), rgba(14, 183, 146, 0.9));
            transition: width .5s ease;
        }

        .campaign-config-list {
            display: grid;
            gap: 8px;
        }

        .campaign-config-row {
            display: grid;
            grid-template-columns: minmax(130px, .32fr) minmax(0, 1fr);
            gap: 12px;
            align-items: baseline;
            padding: 10px 0;
            border-bottom: 1px solid rgba(216, 221, 232, 0.08);
        }

        .campaign-config-row:last-child {
            border-bottom: 0;
        }

        .campaign-config-label {
            color: rgba(216, 221, 232, 0.58);
            font-size: var(--fs-100);
            font-weight: 900;
            text-transform: uppercase;
        }

        .campaign-config-value {
            display: grid;
            gap: 3px;
            color: var(--c3);
            line-height: 1.35;
            overflow-wrap: anywhere;
        }

        .campaign-config-value small {
            color: rgba(216, 221, 232, 0.66);
        }

        @media (max-width: 620px) {
            .campaign-config-row {
                grid-template-columns: 1fr;
                gap: 3px;
            }
        }

        @keyframes campaignProgressLoad {
            from {
                transform: scaleX(0);
            }

            to {
                transform: scaleX(1);
            }
        }

        @keyframes campaignProgressStripes {
            to {
                background-position: 24px 0, 0 0;
            }
        }

        @keyframes campaignProgressPulse {
            0%,
            100% {
                filter: saturate(1);
            }

            50% {
                filter: saturate(1.18) brightness(1.05);
            }
        }

        @keyframes campaignProgressShimmer {
            from {
                background-position: 160% 0;
            }

            to {
                background-position: -60% 0;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .campaign-send-panel .marketing-detail__progress-bar {
                transition: none;
            }

            .campaign-send-panel .marketing-detail__progress-bar.is-running,
            .campaign-send-panel .marketing-detail__progress-bar.is-scheduled {
                animation: none;
            }
        }

        @media (max-width: 720px) {
            .campaign-send-panel__head,
            .campaign-send-panel__wait-head,
            .campaign-send-panel__wait-meta {
                align-items: stretch;
                flex-direction: column;
            }
        }
    </style>

    <div class="marketing-detail-page">
        <article class="order-detail order-detail--{{ $tone }}">
            <header class="order-detail__header">
                <div class="order-detail__status">
                    <span class="order-detail__status-icon order-detail__status-icon--{{ $tone }}">
                        <i class="bi {{ $icon }}"></i>
                    </span>
                    @include('admin.Marketing.partials.status-pill', [
                        'status' => $normalizedStatus,
                        'label' => $statusLabel,
                    ])
                </div>

                <div class="order-detail__contacts campaign-header-actions">
                    <a class="order-detail__contact" href="{{ $backToCampaignsRoute }}">
                        <i class="bi bi-arrow-left"></i>
                        <span>{{ $normalizedStatus === 'archived' ? 'Archivio' : 'Lista' }}</span>
                    </a>

                    @if (! in_array($normalizedStatus, ['completed', 'archived'], true))
                        <a class="order-detail__contact" href="{{ route('admin.campaigns.edit', $campaign) }}">
                            <i class="bi bi-pencil-square"></i>
                            <span>Modifica</span>
                        </a>
                    @endif

                    @if ($canActivateCampaign)
                        <form action="{{ route('admin.campaigns.activate', $campaign) }}" method="POST">
                            @csrf
                            <button class="order-detail__contact" type="submit">
                                <i class="bi bi-check2-circle"></i>
                                <span>Conferma/programma</span>
                            </button>
                        </form>
                    @endif

                    @if ($canPauseCampaign)
                        <form action="{{ route('admin.campaigns.pause', $campaign) }}" method="POST">
                            @csrf
                            <button class="order-detail__contact marketing-detail__contact--muted" type="submit">
                                <i class="bi bi-pause-circle"></i>
                                <span>Pausa</span>
                            </button>
                        </form>
                    @endif

                    @if ($canDraftCampaign)
                        <form action="{{ route('admin.campaigns.draft', $campaign) }}" method="POST">
                            @csrf
                            <button class="order-detail__contact marketing-detail__contact--muted" type="submit">
                                <i class="bi bi-clock-history"></i>
                                <span>Completa più tardi</span>
                            </button>
                        </form>
                    @endif

                    @if ($canArchiveCampaign)
                        <form action="{{ route('admin.campaigns.archive', $campaign) }}" method="POST">
                            @csrf
                            <button class="order-detail__contact marketing-detail__contact--danger" type="submit">
                                <i class="bi bi-archive-fill"></i>
                                <span>Archivia</span>
                            </button>
                        </form>
                    @endif

                    @if ($canRestoreCampaign)
                        <form action="{{ route('admin.campaigns.restore', $campaign) }}" method="POST">
                            @csrf
                            <button class="order-detail__contact" type="submit">
                                <i class="bi bi-arrow-counterclockwise"></i>
                                <span>Ripristina come bozza</span>
                            </button>
                        </form>
                    @endif

                    @if ($canDestroyCampaign)
                        <form
                            action="{{ route('admin.campaigns.destroy', $campaign) }}"
                            method="POST"
                            onsubmit="return confirm('Eliminare definitivamente questa campagna? Questa azione non è reversibile.');"
                        >
                            @csrf
                            @method('DELETE')
                            <button class="order-detail__contact marketing-detail__contact--danger" type="submit">
                                <i class="bi bi-trash-fill"></i>
                                <span>Elimina definitivamente</span>
                            </button>
                        </form>
                    @endif
                </div>
            </header>

            <div class="order-detail__body">
                <section class="order-detail__summary">
                    <div class="order-detail__meta">
                        <p class="order-detail__code">#CAMP {{ $campaign->id }}</p>
                        <p class="order-detail__time">{{ $campaign->name }}</p>
                        <p class="order-detail__date">{{ $segmentLabel }}</p>
                    </div>

                    <div class="order-detail__customer">
                        <span>{{ $modelName }}</span>
                        <small>{{ $campaign->scheduled_at?->format('d/m/Y H:i') ?? 'Non programmata' }}</small>
                    </div>
                </section>

                <section class="order-detail__section">
                    <div class="order-detail__section-head">
                        <h3>
                            <span class="order-detail__section-icon">
                                <i class="bi bi-activity"></i>
                            </span>
                            Stato invio
                        </h3>
                    </div>

                    <div class="marketing-detail__empty marketing-detail__send-panel campaign-send-panel">
                        <div class="campaign-send-panel__head">
                            <div class="campaign-send-panel__title">
                                <strong>{{ $sendPanelTitle }}</strong>
                                <small id="campaign-send-message">{{ $sendPanelMessage }}</small>
                            </div>
                            <span class="campaign-send-panel__badge">
                                <i class="bi {{ $icon }}"></i>
                                {{ $sendPanelBadge }}
                            </span>
                        </div>

                        @if ($isScheduledFuture && $scheduledAtIso)
                            <span
                                class="marketing-detail__progress-live"
                                data-marketing-countdown
                                data-countdown-target="{{ $scheduledAtIso }}"
                                data-countdown-prefix="Parte tra"
                                data-countdown-expired="{{ $readyRunnerMessage }}"
                                data-countdown-sync="campaign-send-message"
                            >
                                <i class="bi bi-hourglass-split"></i>
                                <span data-countdown-text>Parte tra calcolo...</span>
                            </span>
                        @endif

                        @if ($isRunning && $nextBatchDueAt)
                            <span
                                class="marketing-detail__progress-live @if (! $hasFutureNextBatch) is-expired @endif"
                                @if ($hasFutureNextBatch && $nextBatchDueAtIso)
                                    data-marketing-countdown
                                    data-countdown-target="{{ $nextBatchDueAtIso }}"
                                    data-countdown-prefix="Prossimo batch tra"
                                    data-countdown-expired="In attesa del prossimo ciclo del runner"
                                @endif
                            >
                                <i class="bi bi-clock-history"></i>
                                <span data-countdown-text>
                                    {{ $hasFutureNextBatch ? 'Prossimo batch tra calcolo...' : 'In attesa del prossimo ciclo del runner' }}
                                </span>
                            </span>
                        @elseif ($isRunning)
                            <span class="marketing-detail__progress-live is-expired">
                                <i class="bi bi-clock-history"></i>
                                <span>In attesa del prossimo ciclo del runner</span>
                            </span>
                        @endif

                        @if ($normalizedStatus === 'completed')
                            <span class="marketing-detail__progress-live is-expired">
                                <i class="bi bi-check2-circle"></i>
                                <span>Tutte le email sono state inviate{{ $completedAt ? ' il ' . $completedAt->format('d/m/Y H:i') : '' }}</span>
                            </span>
                        @endif

                        @if ($totalEmails === 0)
                            <small>Nessun destinatario preparato</small>
                        @endif

                        <div class="marketing-detail__progress" aria-label="Avanzamento reale invio campagna">
                            <div class="marketing-detail__progress-track">
                                <div
                                    class="marketing-detail__progress-bar @if ($isScheduledFuture) is-scheduled @endif @if ($isRunning && $progressPercentage < 100) is-running @endif @if ($normalizedStatus === 'completed') is-completed @endif"
                                    style="width: {{ $progressPercentage }}%"
                                ></div>
                                <span class="marketing-detail__progress-percent">{{ $progressPercentageLabel }}</span>
                            </div>
                            <div class="marketing-detail__progress-meta">
                                <span>Avanzamento reale dai batch del runner</span>
                                <span>{{ $statusLabel }}</span>
                            </div>
                        </div>

                        @if ($isRunning && $nextBatchDueAt && $batchWaitStartedAtIso && $nextBatchDueAtIso)
                            <div
                                class="campaign-send-panel__wait"
                                data-batch-wait
                                data-batch-start="{{ $batchWaitStartedAtIso }}"
                                data-batch-due="{{ $nextBatchDueAtIso }}"
                            >
                                <div class="campaign-send-panel__wait-head">
                                    <strong>Attesa prossimo batch</strong>
                                    <span data-batch-wait-label>
                                        {{ $hasFutureNextBatch ? 'Calcolo attesa...' : 'In attesa del runner' }}
                                    </span>
                                </div>
                                <div class="campaign-send-panel__wait-track" aria-hidden="true">
                                    <div class="campaign-send-panel__wait-bar" data-batch-wait-bar></div>
                                </div>
                                <div class="campaign-send-panel__wait-meta">
                                    <span>Intervallo batch: {{ $batchIntervalMinutes }} min</span>
                                    <span>Prossimo: {{ $nextBatchDueAt->format('d/m/Y H:i') }}</span>
                                </div>
                            </div>
                        @endif
                    </div>
                </section>

                <section class="order-detail__section">
                    <div class="order-detail__section-head">
                        <h3>
                            <span class="order-detail__section-icon">
                                <i class="bi bi-bar-chart-fill"></i>
                            </span>
                            Metriche principali
                        </h3>
                    </div>

                    <div class="campaign-report-panel">
                        @unless ($hasLinkedPromotions)
                            <div class="marketing-detail__empty mt-3">
                                Collega almeno una promozione per ottenere metriche specifiche per ordini o prenotazioni.
                            </div>
                        @endunless

                        <div class="campaign-report-grid">
                            @foreach ($reportMetrics as $metric)
                                <article class="campaign-report-card campaign-report-card--{{ $metric['tone'] }}">
                                    <span>{{ $metric['label'] }}</span>
                                    <strong>{{ $metric['value'] }}</strong>
                                </article>
                            @endforeach
                        </div>
                    </div>
                </section>

                <section class="order-detail__section">
                    <div class="order-detail__section-head">
                        <h3>
                            <span class="order-detail__section-icon">
                                <i class="bi bi-info-circle-fill"></i>
                            </span>
                            Configurazione campagna
                        </h3>
                    </div>

                    <div class="campaign-config-list">
                        <div class="campaign-config-row">
                            <span class="campaign-config-label">Segmento</span>
                            <span class="campaign-config-value">{{ $segmentLabel }}</span>
                        </div>
                        <div class="campaign-config-row">
                            <span class="campaign-config-label">Tipo invio</span>
                            <span class="campaign-config-value">
                                {{ $channelLabel }}
                                <small>{{ $consentBasisLabel }}</small>
                            </span>
                        </div>
                        <div class="campaign-config-row">
                            <span class="campaign-config-label">Modello mail</span>
                            <span class="campaign-config-value">
                                {{ $modelName }}
                                <small>{{ $modelObject }}</small>
                            </span>
                        </div>
                        <div class="campaign-config-row">
                            <span class="campaign-config-label">Programmazione</span>
                            <span class="campaign-config-value">
                                {{ $campaign->scheduled_at?->format('d/m/Y H:i') ?? '-' }}
                                <small>{{ $requestedScheduledAt ? 'Richiesta: ' . $requestedScheduledAt : $scheduleWindowLabel }}</small>
                            </span>
                        </div>
                        <div class="campaign-config-row">
                            <span class="campaign-config-label">Promozioni</span>
                            <span class="campaign-config-value">{{ $promotionsCount }} collegate</span>
                        </div>
                        <div class="campaign-config-row">
                            <span class="campaign-config-label">Audience</span>
                            <span class="campaign-config-value">
                                {{ $totalEmails }} assegnati
                                <small>{{ $hasAssignments ? 'Assegnazioni create' : 'Nessuna assegnazione creata' }}</small>
                            </span>
                        </div>
                    </div>

                    @if ($isWhatsappMarketing)
                        <div class="marketing-detail__empty mt-3">
                            <strong>Invio WhatsApp non ancora implementato.</strong>
                            <small>La campagna puo preparare l'audience WhatsApp, ma il dispatch email la salta senza inviare messaggi.</small>
                        </div>
                    @endif

                </section>

                <section class="order-detail__section">
                    <div class="order-detail__section-head">
                        <h3>
                            <span class="order-detail__section-icon">
                                <i class="bi bi-megaphone-fill"></i>
                            </span>
                            Dettaglio promozioni
                        </h3>
                    </div>

                    @if ($promotionsCount > 0)
                        <div class="campaign-promotion-list">
                            @foreach ($campaign->promotions as $promotion)
                                @php
                                    $targetLabels = $promotion->targets->map(function ($target) {
                                        $resolvedTarget = null;

                                        try {
                                            $resolvedTarget = $target->target();
                                        } catch (\Throwable) {
                                            $resolvedTarget = null;
                                        }

                                        $name = $resolvedTarget?->name ?? $resolvedTarget?->title ?? null;

                                        return [
                                            'type' => $target->target_type,
                                            'name' => $name ?: ($target->target_type === 'generic' ? 'Generica' : '#' . $target->target_id),
                                        ];
                                    })->filter(fn ($target) => filled($target['name']))->values();
                                @endphp

                                <article class="campaign-promotion-card">
                                    <div class="campaign-promotion-card__main">
                                        <div class="campaign-promotion-card__heading">
                                            <div>
                                                <strong>{{ $promotion->name }}</strong>
                                                <small>
                                                    {{ $promotion->slug }}
                                                    · {{ $caseUseLabels[$promotion->case_use] ?? ($promotion->case_use ?: 'Nessun ambito') }}
                                                    · {{ $discountTypeLabels[$promotion->type_discount] ?? ($promotion->type_discount ?: 'Sconto non definito') }}
                                                </small>
                                            </div>
                                            @include('admin.Marketing.partials.status-pill', [
                                                'status' => $promotion->status,
                                                'label' => $promotion->status,
                                            ])
                                        </div>
                                    </div>

                                    <div class="campaign-promotion-card__stats">
                                        <span>
                                            <small>Sconto</small>
                                            <strong>{{ $formatDiscount($promotion) }}</strong>
                                        </span>
                                        <span>
                                            <small>Minimo</small>
                                            <strong>{{ $promotion->minimum_pretest !== null ? number_format((float) $promotion->minimum_pretest, 2, ',', '.') : '-' }}</strong>
                                        </span>
                                        <span>
                                            <small>Scadenza</small>
                                            <strong>{{ $promotion->expiring_at?->format('d/m/Y') ?? 'Senza scadenza' }}</strong>
                                        </span>
                                    </div>

                                    <div class="campaign-promotion-card__targets">
                                        <strong>Target</strong>
                                        @forelse ($targetLabels as $target)
                                            <span>{{ ucfirst($target['type']) }}: {{ $target['name'] }}</span>
                                        @empty
                                            <span>Target generico</span>
                                        @endforelse
                                    </div>

                                    <a href="{{ route('admin.promotions.show', $promotion) }}" class="order-detail__contact">
                                        <i class="bi bi-arrow-up-right-circle-fill"></i>
                                        <span>Apri promozione</span>
                                    </a>
                                </article>
                            @endforeach
                        </div>
                    @else
                        <div class="marketing-detail__empty">
                            <strong>Nessuna promozione collegata.</strong>
                        </div>
                    @endif
                </section>

                @if ($canPreviewAudience || $canPrepareAssignments)
                    <section class="order-detail__section">
                        <div class="order-detail__section-head">
                            <h3>
                                <span class="order-detail__section-icon">
                                    <i class="bi bi-people-fill"></i>
                                </span>
                                Preparazione audience
                            </h3>
                        </div>

                        <div class="marketing-detail__actions">
                            @if ($canPreviewAudience)
                                <form action="{{ route('admin.campaigns.preview-audience', $campaign) }}" method="POST">
                                    @csrf
                                    <button class="order-detail__contact" type="submit">
                                        <i class="bi bi-eye-fill"></i>
                                        <span>Preview audience</span>
                                    </button>
                                </form>
                            @endif

                            @if ($canPrepareAssignments)
                                <form action="{{ route('admin.campaigns.prepare-assignments', $campaign) }}" method="POST">
                                    @csrf
                                    <button class="order-detail__contact marketing-detail__contact--muted" type="submit">
                                        <i class="bi bi-person-plus-fill"></i>
                                        <span>Prepara assegnazioni</span>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </section>
                @endif

                @if (session('audience_preview'))
                    @php
                        $audiencePreview = session('audience_preview');
                        $assignableCount = $audiencePreview['assignable_count'] ?? $audiencePreview['assigned_count'] ?? 0;
                        $previewMetrics = [
                            ['label' => 'Assegnabile', 'value' => ($audiencePreview['can_assign'] ?? false) ? 'Si' : 'No'],
                            ['label' => 'Motivo', 'value' => $audiencePreview['failure_reason'] ?? '-'],
                            ['label' => 'Clienti', 'value' => $audiencePreview['customers_checked'] ?? 0],
                            ['label' => 'Promozioni', 'value' => $audiencePreview['promotions_count'] ?? 0],
                            ['label' => 'Simulate', 'value' => $assignableCount],
                            ['label' => 'Gia assegnate', 'value' => $audiencePreview['already_assigned_count'] ?? 0],
                            ['label' => 'Saltate', 'value' => $audiencePreview['skipped_count'] ?? 0],
                            ['label' => 'Errori', 'value' => $audiencePreview['errors_count'] ?? 0],
                        ];
                    @endphp

                    <section class="order-detail__section">
                        <div class="order-detail__section-head">
                            <h3>
                                <span class="order-detail__section-icon">
                                    <i class="bi bi-people-fill"></i>
                                </span>
                                Preview audience
                            </h3>
                        </div>

                        <div class="marketing-detail__empty">
                            <strong>Simulazione soltanto.</strong>
                            <small>La preview non crea assegnazioni e non invia email.</small>
                        </div>

                        <div class="marketing-detail__compact-grid">
                            @foreach ($previewMetrics as $metric)
                                <article class="marketing-detail__fact">
                                    <span>{{ $metric['label'] }}</span>
                                    <strong>{{ $metric['value'] }}</strong>
                                </article>
                            @endforeach
                        </div>

                        @include('admin.Marketing.partials.error-list', ['errors' => $audiencePreview['errors'] ?? []])
                    </section>
                @endif

                @if (session('campaign_assignment_result') && ((session('campaign_assignment_result')['failure_reason'] ?? null) || (int) (session('campaign_assignment_result')['errors_count'] ?? 0) > 0))
                    @php
                        $assignmentResult = session('campaign_assignment_result');
                        $assignmentMetrics = [
                            ['label' => 'Assegnabile', 'value' => ($assignmentResult['can_assign'] ?? false) ? 'Si' : 'No'],
                            ['label' => 'Motivo', 'value' => $assignmentResult['failure_reason'] ?? '-'],
                            ['label' => 'Errori', 'value' => $assignmentResult['errors_count'] ?? 0],
                        ];
                    @endphp

                    <section class="order-detail__section">
                        <div class="order-detail__section-head">
                            <h3>
                                <span class="order-detail__section-icon">
                                    <i class="bi bi-person-plus-fill"></i>
                                </span>
                                Risultato assegnazioni
                            </h3>
                        </div>

                        <div class="marketing-detail__empty">
                            <strong>Le assegnazioni richiedono attenzione.</strong>
                            <small>Le righe create restano visibili nella tabella read-only.</small>
                        </div>

                        <div class="marketing-detail__compact-grid">
                            @foreach ($assignmentMetrics as $metric)
                                <article class="marketing-detail__fact">
                                    <span>{{ $metric['label'] }}</span>
                                    <strong>{{ $metric['value'] }}</strong>
                                </article>
                            @endforeach
                        </div>

                        @include('admin.Marketing.partials.error-list', ['errors' => $assignmentResult['errors'] ?? []])
                    </section>
                @endif

                @include('admin.Marketing.partials.customer-promotions-table', [
                    'customerPromotions' => $customerPromotions,
                    'emptyText' => 'Nessuna assegnazione creata per questa campagna.',
                    'showSummary' => false,
                    'compact' => true,
                ])
            </div>
        </article>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const countdowns = document.querySelectorAll('[data-marketing-countdown]');
        const batchWaits = document.querySelectorAll('[data-batch-wait]');

        if (!countdowns.length && !batchWaits.length) {
            return;
        }

        const pad = function (value) {
            return String(value).padStart(2, '0');
        };

        const formatDuration = function (milliseconds) {
            const totalSeconds = Math.max(0, Math.floor(milliseconds / 1000));
            const hours = Math.floor(totalSeconds / 3600);
            const minutes = Math.floor((totalSeconds % 3600) / 60);
            const seconds = totalSeconds % 60;

            if (hours > 0) {
                return `${pad(hours)}h ${pad(minutes)}m ${pad(seconds)}s`;
            }

            return `${pad(minutes)}m ${pad(seconds)}s`;
        };

        const updateCountdown = function (element) {
            const targetValue = element.dataset.countdownTarget;
            const textElement = element.querySelector('[data-countdown-text]') || element;
            const targetDate = targetValue ? new Date(targetValue) : null;

            if (!targetDate || Number.isNaN(targetDate.getTime())) {
                element.hidden = true;
                return false;
            }

            const remaining = targetDate.getTime() - Date.now();

            if (remaining <= 0) {
                const expiredText = element.dataset.countdownExpired || 'Pronta per il prossimo ciclo del runner marketing';
                textElement.textContent = expiredText;
                element.classList.add('is-expired');

                if (element.dataset.countdownSync) {
                    const syncedElement = document.getElementById(element.dataset.countdownSync);

                    if (syncedElement) {
                        syncedElement.textContent = expiredText;
                    }
                }

                return false;
            }

            const prefix = element.dataset.countdownPrefix || 'Tempo restante:';
            textElement.textContent = `${prefix} ${formatDuration(remaining)}`;
            element.classList.remove('is-expired');

            return true;
        };

        const updateBatchWait = function (element) {
            const startDate = element.dataset.batchStart ? new Date(element.dataset.batchStart) : null;
            const dueDate = element.dataset.batchDue ? new Date(element.dataset.batchDue) : null;
            const bar = element.querySelector('[data-batch-wait-bar]');
            const label = element.querySelector('[data-batch-wait-label]');

            if (!startDate || !dueDate || Number.isNaN(startDate.getTime()) || Number.isNaN(dueDate.getTime())) {
                element.hidden = true;
                return false;
            }

            const now = Date.now();
            const total = Math.max(1, dueDate.getTime() - startDate.getTime());
            const elapsed = Math.min(total, Math.max(0, now - startDate.getTime()));
            const percentage = Math.round((elapsed / total) * 100);
            const remaining = dueDate.getTime() - now;

            if (bar) {
                bar.style.width = `${percentage}%`;
            }

            if (label) {
                label.textContent = remaining > 0
                    ? `Prossimo batch tra ${formatDuration(remaining)}`
                    : 'In attesa del prossimo ciclo del runner';
            }

            return remaining > 0;
        };

        let timerId = null;
        const tick = function () {
            let hasActiveWork = false;

            countdowns.forEach(function (element) {
                hasActiveWork = updateCountdown(element) || hasActiveWork;
            });

            batchWaits.forEach(function (element) {
                hasActiveWork = updateBatchWait(element) || hasActiveWork;
            });

            if (!hasActiveWork && timerId) {
                window.clearInterval(timerId);
                timerId = null;
            }
        };

        tick();
        timerId = window.setInterval(tick, 1000);
    });
</script>

@endsection
