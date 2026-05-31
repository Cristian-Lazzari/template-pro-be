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
    $channelLabel = $campaign->usesWhatsappMarketingConsent() ? __('admin.marketing.campaigns.consent_whatsapp_short') : __('admin.common.email');
    $isWhatsappMarketing = $campaign->usesWhatsappMarketingConsent();
    $modelName = $campaign->model?->name ?? '-';
    $modelObject = $campaign->model?->object ?? '-';
    $promotionsCount = $campaign->promotions->count();
    $scheduleState = $sendProgress['message'] ?? __('admin.marketing.campaigns.no_schedule');
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
    $readyRunnerMessage = __('admin.marketing.campaigns.ready_runner');
    $isScheduledFuture = $normalizedStatus === 'scheduled' && $campaign->scheduled_at?->isFuture();
    $isRunning = $normalizedStatus === 'running';
    $hasFutureNextBatch = $isRunning && $nextBatchDueAt?->isFuture();
    $progressPercentageLabel = number_format($progressPercentage, $progressPercentage === floor($progressPercentage) ? 0 : 2, ',', '.') . '%';
    $sendPanelTitle = match ($normalizedStatus) {
        'scheduled' => __('admin.marketing.campaigns.send_programmed'),
        'running' => __('admin.marketing.campaigns.send_running'),
        'completed' => __('admin.marketing.campaigns.all_emails_sent'),
        'paused' => __('admin.marketing.campaigns.paused_title'),
        'archived' => __('admin.marketing.campaigns.archived_singular_title'),
        'draft' => __('admin.marketing.campaigns.status_draft'),
        default => $statusLabel,
    };
    $sendPanelBadge = match ($normalizedStatus) {
        'scheduled' => __('admin.marketing.campaigns.status_scheduled'),
        'running' => __('admin.marketing.campaigns.live'),
        'completed' => __('admin.marketing.campaigns.status_completed'),
        'paused' => __('admin.marketing.campaigns.status_paused'),
        'archived' => __('admin.marketing.campaigns.status_archived'),
        'draft' => __('admin.marketing.campaigns.status_draft'),
        default => $statusLabel,
    };
    $sendPanelMessage = match (true) {
        $normalizedStatus === 'scheduled' && $isScheduledFuture => __('admin.marketing.campaigns.scheduled_for', ['date' => $campaign->scheduled_at->format('d/m/Y H:i')]),
        $normalizedStatus === 'scheduled' => $readyRunnerMessage,
        $isRunning => __('admin.marketing.campaigns.running_runner'),
        $normalizedStatus === 'completed' => __('admin.marketing.campaigns.all_emails_sent'),
        $normalizedStatus === 'draft' => __('admin.marketing.campaigns.send_draft_message'),
        $normalizedStatus === 'paused' => __('admin.marketing.campaigns.send_paused_message'),
        $normalizedStatus === 'archived' => __('admin.marketing.campaigns.send_archived_message'),
        default => $scheduleState,
    };
    $caseUseLabels = [
        'generic' => __('admin.marketing.campaigns.case_generic'),
        'take_away' => __('admin.marketing.campaigns.case_takeaway'),
        'delivery' => __('admin.marketing.campaigns.case_delivery'),
        'table' => __('admin.marketing.campaigns.case_table'),
        'gift' => __('admin.marketing.campaigns.case_gift'),
    ];
    $discountTypeLabels = [
        'fixed' => __('admin.marketing.campaigns.discount_fixed'),
        'percentage' => __('admin.marketing.campaigns.discount_percentage'),
        'gift' => __('admin.marketing.campaigns.discount_gift'),
    ];
    $formatDiscount = function ($promotion) {
        if (! $promotion) {
            return '-';
        }

        if ($promotion->type_discount === 'gift') {
            return __('admin.marketing.campaigns.discount_gift');
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
    $canEditCampaign = $canEditCampaign ?? in_array($normalizedStatus, ['draft', 'paused'], true);
    $canActivateCampaign = $canActivateCampaign ?? in_array($normalizedStatus, ['draft', 'paused'], true);
    $canPauseCampaign = $canPauseCampaign ?? in_array($normalizedStatus, ['scheduled', 'running'], true);
    $canDraftCampaign = $canDraftCampaign ?? $normalizedStatus === 'paused';
    $canArchiveCampaign = $canArchiveCampaign ?? in_array($normalizedStatus, ['draft', 'scheduled', 'running', 'paused', 'completed'], true);
    $canRestoreCampaign = $canRestoreCampaign ?? $normalizedStatus === 'archived';
    $canDestroyCampaign = $canDestroyCampaign ?? ($normalizedStatus === 'archived' && ! $hasAssignments);
    $backToCampaignsRoute = $normalizedStatus === 'archived'
        ? route('admin.campaigns.archived')
        : route('admin.campaigns.index');
    $reportMetrics = [
        ['label' => __('admin.marketing.campaigns.recipients'), 'value' => $totalEmails, 'tone' => 'neutral'],
        ['label' => __('admin.marketing.campaigns.sent_emails'), 'value' => $sentEmails, 'tone' => 'neutral'],
        ['label' => __('admin.marketing.campaigns.opens'), 'value' => $report['opened_count'] ?? 0, 'tone' => 'neutral'],
        ['label' => __('admin.marketing.campaigns.clicks'), 'value' => $report['clicked_count'] ?? 0, 'tone' => 'neutral'],
        ['label' => __('admin.marketing.campaigns.used_promos'), 'value' => $report['used_count'] ?? 0, 'tone' => 'active'],
    ];

    $reportMetrics[] = ['label' => __('admin.marketing.campaigns.open_rate'), 'value' => number_format((float) ($report['open_rate'] ?? 0), 2, ',', '.') . '%', 'tone' => 'rate'];
    $reportMetrics[] = ['label' => __('admin.marketing.campaigns.click_rate'), 'value' => number_format((float) ($report['click_rate'] ?? 0), 2, ',', '.') . '%', 'tone' => 'rate'];
    $reportMetrics[] = ['label' => __('admin.marketing.campaigns.usage_rate'), 'value' => number_format((float) ($report['usage_rate'] ?? 0), 2, ',', '.') . '%', 'tone' => 'rate'];
@endphp

<div class="dash_page">
    @include('admin.Marketing.partials.breadcrumbs', [
        'items' => [
            ['label' => __('admin.nav.dashboard'), 'url' => route('admin.dashboard')],
            ['label' => __('admin.marketing.area_links.marketing'), 'url' => route('admin.marketing')],
            ['label' => __('admin.marketing.campaigns.plural'), 'url' => route('admin.campaigns.index')],
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
                        <span>{{ $normalizedStatus === 'archived' ? __('admin.marketing.campaigns.archive_short') : __('admin.marketing.campaigns.list_short') }}</span>
                    </a>

                    @if ($canEditCampaign)
                        <a class="order-detail__contact" href="{{ route('admin.campaigns.edit', $campaign) }}">
                            <i class="bi bi-pencil-square"></i>
                            <span>{{ __('admin.common.edit') }}</span>
                        </a>
                    @endif

                    @if ($canActivateCampaign)
                        <form action="{{ route('admin.campaigns.activate', $campaign) }}" method="POST">
                            @csrf
                            <button class="order-detail__contact" type="submit">
                                <i class="bi bi-check2-circle"></i>
                                <span>{{ __('admin.marketing.campaigns.confirm_schedule') }}</span>
                            </button>
                        </form>
                    @endif

                    @if ($canPauseCampaign)
                        <form action="{{ route('admin.campaigns.pause', $campaign) }}" method="POST">
                            @csrf
                            <button class="order-detail__contact marketing-detail__contact--muted" type="submit">
                                <i class="bi bi-pause-circle"></i>
                                <span>{{ __('admin.marketing.promotions.pause') }}</span>
                            </button>
                        </form>
                    @endif

                    @if ($canDraftCampaign)
                        <form action="{{ route('admin.campaigns.draft', $campaign) }}" method="POST">
                            @csrf
                            <button class="order-detail__contact marketing-detail__contact--muted" type="submit">
                                <i class="bi bi-clock-history"></i>
                                <span>{{ __('admin.marketing.campaigns.complete_later') }}</span>
                            </button>
                        </form>
                    @endif

                    @if ($canArchiveCampaign)
                        <form action="{{ route('admin.campaigns.archive', $campaign) }}" method="POST">
                            @csrf
                            <button class="order-detail__contact marketing-detail__contact--danger" type="submit">
                                <i class="bi bi-archive-fill"></i>
                                <span>{{ __('admin.marketing.campaigns.archive') }}</span>
                            </button>
                        </form>
                    @endif

                    @if ($canRestoreCampaign)
                        <form action="{{ route('admin.campaigns.restore', $campaign) }}" method="POST">
                            @csrf
                            <button class="order-detail__contact" type="submit">
                                <i class="bi bi-arrow-counterclockwise"></i>
                                <span>{{ __('admin.marketing.campaigns.restore_as_draft') }}</span>
                            </button>
                        </form>
                    @endif

                    @if ($canDestroyCampaign)
                        <form
                            action="{{ route('admin.campaigns.destroy', $campaign) }}"
                            method="POST"
                            onsubmit="return confirm(@js(__('admin.marketing.campaigns.delete_forever_confirm')));"
                        >
                            @csrf
                            @method('DELETE')
                            <button class="order-detail__contact marketing-detail__contact--danger" type="submit">
                                <i class="bi bi-trash-fill"></i>
                                <span>{{ __('admin.marketing.campaigns.delete_forever') }}</span>
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
                        <small>{{ $campaign->scheduled_at?->format('d/m/Y H:i') ?? __('admin.marketing.campaigns.not_scheduled') }}</small>
                    </div>
                </section>

                <section class="order-detail__section">
                    <div class="order-detail__section-head">
                        <h3>
                            <span class="order-detail__section-icon">
                                <i class="bi bi-activity"></i>
                            </span>
                            {{ __('admin.marketing.campaigns.send_status') }}
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
                                data-countdown-prefix="{{ __('admin.marketing.campaigns.starts_in') }}"
                                data-countdown-expired="{{ $readyRunnerMessage }}"
                                data-countdown-sync="campaign-send-message"
                            >
                                <i class="bi bi-hourglass-split"></i>
                                <span data-countdown-text>{{ __('admin.marketing.campaigns.starts_in_calculation') }}</span>
                            </span>
                        @endif

                        @if ($isRunning && $nextBatchDueAt)
                            <span
                                class="marketing-detail__progress-live @if (! $hasFutureNextBatch) is-expired @endif"
                                @if ($hasFutureNextBatch && $nextBatchDueAtIso)
                                    data-marketing-countdown
                                    data-countdown-target="{{ $nextBatchDueAtIso }}"
                                    data-countdown-prefix="{{ __('admin.marketing.campaigns.next_batch_in') }}"
                                    data-countdown-expired="{{ __('admin.marketing.campaigns.waiting_runner') }}"
                                @endif
                            >
                                <i class="bi bi-clock-history"></i>
                                <span data-countdown-text>
                                    {{ $hasFutureNextBatch ? __('admin.marketing.campaigns.next_batch_calculation') : __('admin.marketing.campaigns.waiting_runner') }}
                                </span>
                            </span>
                        @elseif ($isRunning)
                            <span class="marketing-detail__progress-live is-expired">
                                <i class="bi bi-clock-history"></i>
                                <span>{{ __('admin.marketing.campaigns.waiting_runner') }}</span>
                            </span>
                        @endif

                        @if ($normalizedStatus === 'completed')
                            <span class="marketing-detail__progress-live is-expired">
                                <i class="bi bi-check2-circle"></i>
                                <span>{{ $completedAt ? __('admin.marketing.campaigns.completed_emails_sent_at', ['date' => $completedAt->format('d/m/Y H:i')]) : __('admin.marketing.campaigns.all_emails_sent') }}</span>
                            </span>
                        @endif

                        @if ($totalEmails === 0)
                            <small>{{ __('admin.marketing.campaigns.no_prepared_recipients') }}</small>
                        @endif

                        <div class="marketing-detail__progress" aria-label="{{ __('admin.marketing.campaigns.send_progress_aria') }}">
                            <div class="marketing-detail__progress-track">
                                <div
                                    class="marketing-detail__progress-bar @if ($isScheduledFuture) is-scheduled @endif @if ($isRunning && $progressPercentage < 100) is-running @endif @if ($normalizedStatus === 'completed') is-completed @endif"
                                    style="width: {{ $progressPercentage }}%"
                                ></div>
                                <span class="marketing-detail__progress-percent">{{ $progressPercentageLabel }}</span>
                            </div>
                            <div class="marketing-detail__progress-meta">
                                <span>{{ __('admin.marketing.campaigns.real_runner_progress') }}</span>
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
                                    <strong>{{ __('admin.marketing.campaigns.waiting_next_batch') }}</strong>
                                    <span data-batch-wait-label>
                                        {{ $hasFutureNextBatch ? __('admin.marketing.campaigns.wait_calculation') : __('admin.marketing.campaigns.runner_wait') }}
                                    </span>
                                </div>
                                <div class="campaign-send-panel__wait-track" aria-hidden="true">
                                    <div class="campaign-send-panel__wait-bar" data-batch-wait-bar></div>
                                </div>
                                <div class="campaign-send-panel__wait-meta">
                                    <span>{{ __('admin.marketing.campaigns.batch_interval', ['minutes' => $batchIntervalMinutes]) }}</span>
                                    <span>{{ __('admin.marketing.campaigns.next_at', ['date' => $nextBatchDueAt->format('d/m/Y H:i')]) }}</span>
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
                            {{ __('admin.marketing.campaigns.main_metrics') }}
                        </h3>
                    </div>

                    <div class="campaign-report-panel">
                        @unless ($hasLinkedPromotions)
                            <div class="marketing-detail__empty mt-3">
                                {{ __('admin.marketing.campaigns.link_promotion_warning') }}
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
                            {{ __('admin.marketing.campaigns.configuration') }}
                        </h3>
                    </div>

                    <div class="campaign-config-list">
                        <div class="campaign-config-row">
                            <span class="campaign-config-label">{{ __('admin.marketing.campaigns.segment') }}</span>
                            <span class="campaign-config-value">{{ $segmentLabel }}</span>
                        </div>
                        <div class="campaign-config-row">
                            <span class="campaign-config-label">{{ __('admin.marketing.campaigns.send_type') }}</span>
                            <span class="campaign-config-value">
                                {{ $channelLabel }}
                                <small>{{ $consentBasisLabel }}</small>
                            </span>
                        </div>
                        <div class="campaign-config-row">
                            <span class="campaign-config-label">{{ __('admin.marketing.campaigns.mail_model') }}</span>
                            <span class="campaign-config-value">
                                {{ $modelName }}
                                <small>{{ $modelObject }}</small>
                            </span>
                        </div>
                        <div class="campaign-config-row">
                            <span class="campaign-config-label">{{ __('admin.marketing.campaigns.schedule') }}</span>
                            <span class="campaign-config-value">
                                {{ $campaign->scheduled_at?->format('d/m/Y H:i') ?? '-' }}
                                <small>{{ $requestedScheduledAt ? __('admin.marketing.campaigns.requested_at', ['date' => $requestedScheduledAt]) : $scheduleWindowLabel }}</small>
                            </span>
                        </div>
                        <div class="campaign-config-row">
                            <span class="campaign-config-label">{{ __('admin.marketing.campaigns.promotions') }}</span>
                            <span class="campaign-config-value">{{ __('admin.marketing.campaigns.promotions_linked_count', ['count' => $promotionsCount]) }}</span>
                        </div>
                        <div class="campaign-config-row">
                            <span class="campaign-config-label">{{ __('admin.marketing.campaigns.estimated_audience') }}</span>
                            <span class="campaign-config-value">
                                {{ __('admin.marketing.campaigns.assigned_count', ['count' => $totalEmails]) }}
                                <small>{{ $hasAssignments ? __('admin.marketing.campaigns.assignments_created') : __('admin.marketing.campaigns.no_assignments_created') }}</small>
                            </span>
                        </div>
                    </div>

                    @if ($isWhatsappMarketing)
                        <div class="marketing-detail__empty mt-3">
                            <strong>{{ __('admin.marketing.campaigns.whatsapp_not_implemented') }}</strong>
                            <small>{{ __('admin.marketing.campaigns.whatsapp_not_implemented_note') }}</small>
                        </div>
                    @endif

                </section>

                <section class="order-detail__section">
                    <div class="order-detail__section-head">
                        <h3>
                            <span class="order-detail__section-icon">
                                <i class="bi bi-megaphone-fill"></i>
                            </span>
                            {{ __('admin.marketing.campaigns.promotion_detail') }}
                        </h3>
                    </div>

                    @if ($promotionsCount > 0)
                        <div class="order-detail__items">
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
                                            'name' => $name ?: ($target->target_type === 'generic' ? __('admin.marketing.campaigns.case_generic') : '#' . $target->target_id),
                                        ];
                                    })->filter(fn ($target) => filled($target['name']))->values();
                                @endphp

                                <div class="promo-card">
                                    <div class="promo-card__header">
                                        <div class="promo-card__title">
                                            <strong class="promo-card__name">{{ $promotion->name }}</strong>
                                            @if ($promotion->type_discount)
                                                <span class="promo-card__type-badge">{{ $discountTypeLabels[$promotion->type_discount] ?? $promotion->type_discount }}</span>
                                            @endif
                                        </div>
                                        @include('admin.Marketing.partials.status-pill', [
                                            'status' => $promotion->status,
                                            'label' => $promotion->status,
                                        ])
                                    </div>

                                    <div class="promo-card__breakdown">
                                        <div class="promo-card__row">
                                            <span>{{ __('admin.marketing.campaigns.discount') }}</span>
                                            <strong>{{ $formatDiscount($promotion) }}</strong>
                                        </div>
                                        <div class="promo-card__row">
                                            <span>{{ __('admin.marketing.campaigns.minimum') }}</span>
                                            <span>{{ $promotion->minimum_pretest !== null ? number_format((float) $promotion->minimum_pretest, 2, ',', '.') : '-' }}</span>
                                        </div>
                                        <div class="promo-card__row">
                                            <span>{{ __('admin.marketing.campaigns.expiration') }}</span>
                                            <span>{{ $promotion->expiring_at?->format('d/m/Y') ?? __('admin.marketing.campaigns.without_expiration') }}</span>
                                        </div>
                                        @if ($promotion->case_use)
                                            <div class="promo-card__row">
                                                <span>{{ $caseUseLabels[$promotion->case_use] ?? $promotion->case_use }}</span>
                                                <span>{{ $promotion->slug }}</span>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="promo-card__items">
                                        <p class="promo-card__items-title">{{ __('admin.marketing.campaigns.target') }}</p>
                                        <ul class="promo-card__items-list">
                                            @forelse ($targetLabels as $target)
                                                <li class="promo-card__items-entry">
                                                    <span>{{ ucfirst($target['type']) }}: {{ $target['name'] }}</span>
                                                </li>
                                            @empty
                                                <li class="promo-card__items-entry">
                                                    <span>{{ __('admin.marketing.campaigns.generic_target') }}</span>
                                                </li>
                                            @endforelse
                                        </ul>
                                    </div>

                                    <a href="{{ route('admin.promotions.show', $promotion) }}" class="order-detail__contact">
                                        <i class="bi bi-arrow-up-right-circle-fill"></i>
                                        <span>{{ __('admin.marketing.campaigns.open_promotion') }}</span>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="marketing-detail__empty">
                            <strong>{{ __('admin.marketing.campaigns.no_linked_promotions') }}</strong>
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
                                {{ __('admin.marketing.campaigns.audience_preparation') }}
                            </h3>
                        </div>

                        <div class="marketing-detail__actions">
                            @if ($canPreviewAudience)
                                <form action="{{ route('admin.campaigns.preview-audience', $campaign) }}" method="POST">
                                    @csrf
                                    <button class="order-detail__contact" type="submit">
                                        <i class="bi bi-eye-fill"></i>
                                        <span>{{ __('admin.marketing.campaigns.preview_audience') }}</span>
                                    </button>
                                </form>
                            @endif

                            @if ($canPrepareAssignments)
                                <form action="{{ route('admin.campaigns.prepare-assignments', $campaign) }}" method="POST">
                                    @csrf
                                    <button class="order-detail__contact marketing-detail__contact--muted" type="submit">
                                        <i class="bi bi-person-plus-fill"></i>
                                        <span>{{ __('admin.marketing.campaigns.prepare_assignments') }}</span>
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
                            ['label' => __('admin.marketing.campaigns.assignable'), 'value' => ($audiencePreview['can_assign'] ?? false) ? __('admin.common.yes') : __('admin.common.no')],
                            ['label' => __('admin.marketing.campaigns.reason'), 'value' => $audiencePreview['failure_reason'] ?? '-'],
                            ['label' => __('admin.marketing.campaigns.customers'), 'value' => $audiencePreview['customers_checked'] ?? 0],
                            ['label' => __('admin.marketing.campaigns.promotions'), 'value' => $audiencePreview['promotions_count'] ?? 0],
                            ['label' => __('admin.marketing.campaigns.simulated'), 'value' => $assignableCount],
                            ['label' => __('admin.marketing.campaigns.already_assigned'), 'value' => $audiencePreview['already_assigned_count'] ?? 0],
                            ['label' => __('admin.marketing.campaigns.skipped'), 'value' => $audiencePreview['skipped_count'] ?? 0],
                            ['label' => __('admin.marketing.campaigns.errors'), 'value' => $audiencePreview['errors_count'] ?? 0],
                        ];
                    @endphp

                    <section class="order-detail__section">
                        <div class="order-detail__section-head">
                            <h3>
                                <span class="order-detail__section-icon">
                                    <i class="bi bi-people-fill"></i>
                                </span>
                                {{ __('admin.marketing.campaigns.preview_audience') }}
                            </h3>
                        </div>

                        <div class="marketing-detail__empty">
                            <strong>{{ __('admin.marketing.campaigns.simulation_only') }}</strong>
                            <small>{{ __('admin.marketing.campaigns.preview_note') }}</small>
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
                            ['label' => __('admin.marketing.campaigns.assignable'), 'value' => ($assignmentResult['can_assign'] ?? false) ? __('admin.common.yes') : __('admin.common.no')],
                            ['label' => __('admin.marketing.campaigns.reason'), 'value' => $assignmentResult['failure_reason'] ?? '-'],
                            ['label' => __('admin.marketing.campaigns.errors'), 'value' => $assignmentResult['errors_count'] ?? 0],
                        ];
                    @endphp

                    <section class="order-detail__section">
                        <div class="order-detail__section-head">
                            <h3>
                                <span class="order-detail__section-icon">
                                    <i class="bi bi-person-plus-fill"></i>
                                </span>
                                {{ __('admin.marketing.campaigns.assignment_result') }}
                            </h3>
                        </div>

                        <div class="marketing-detail__empty">
                            <strong>{{ __('admin.marketing.campaigns.assignments_need_attention') }}</strong>
                            <small>{{ __('admin.marketing.campaigns.assignments_attention_note') }}</small>
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
                    'emptyText' => __('admin.marketing.customer_promotions_table.empty_assignments'),
                    'showSummary' => false,
                    'compact' => true,
                ])
            </div>
        </article>
    </div>
</div>

@php
    $showPageCopy = [
        'readyRunner'   => __('admin.marketing.campaigns.ready_runner'),
        'timeRemaining' => __('admin.marketing.campaigns.time_remaining'),
        'nextBatchIn'   => __('admin.marketing.campaigns.next_batch_in'),
        'waitingRunner' => __('admin.marketing.campaigns.waiting_runner'),
    ];
@endphp
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const countdowns = document.querySelectorAll('[data-marketing-countdown]');
        const batchWaits = document.querySelectorAll('[data-batch-wait]');
        const copy = @json($showPageCopy);

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
                const expiredText = element.dataset.countdownExpired || copy.readyRunner;
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

            const prefix = element.dataset.countdownPrefix || copy.timeRemaining;
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
                    ? `${copy.nextBatchIn} ${formatDuration(remaining)}`
                    : copy.waitingRunner;
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
