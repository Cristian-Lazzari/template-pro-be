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
        'draft' => __('admin.marketing.campaigns.status_draft'),
        'scheduled' => __('admin.marketing.campaigns.status_scheduled'),
        'running' => __('admin.marketing.campaigns.status_running_upper'),
        'completed' => __('admin.marketing.campaigns.status_completed'),
        'paused' => __('admin.marketing.campaigns.status_paused'),
        'archived' => __('admin.marketing.campaigns.status_archived'),
    ];
    $campaignConsentShortLabels = [
        \App\Models\Campaign::CONSENT_BASIS_EXPLICIT_EMAIL_MARKETING => __('admin.marketing.campaigns.consent_explicit_short'),
        \App\Models\Campaign::CONSENT_BASIS_SOFT_EMAIL_MARKETING => __('admin.marketing.campaigns.consent_soft_short'),
        \App\Models\Campaign::CONSENT_BASIS_WHATSAPP_MARKETING => __('admin.marketing.campaigns.consent_whatsapp_short'),
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
            return __('admin.marketing.campaigns.today_at', ['time' => $time]);
        }

        if ($date->isYesterday()) {
            return __('admin.marketing.campaigns.yesterday_at', ['time' => $time]);
        }

        if ($date->isTomorrow()) {
            return __('admin.marketing.campaigns.tomorrow_at', ['time' => $time]);
        }

        return __('admin.marketing.campaigns.weekday_at', [
            'day' => ucfirst($date->locale(app()->getLocale())->isoFormat('dddd')),
            'date' => $date->format('d/m'),
            'time' => $time,
        ]);
    };
@endphp

<div class="dash_page marketing-index-page">
    @include('admin.Marketing.partials.index-style')

    @include('admin.Marketing.partials.breadcrumbs', [
        'items' => [
            ['label' => __('admin.nav.dashboard'), 'url' => route('admin.dashboard')],
            ['label' => __('admin.marketing.area_links.marketing'), 'url' => route('admin.marketing')],
            ['label' => $isArchivedView ? __('admin.marketing.campaigns.archived_title') : __('admin.marketing.campaigns.plural')],
        ],
    ])

    <header class="menu-dashboard__hero order-detail__summary">
        <div class="order-detail__meta">
            <div class="order-detail__status">
                <span class="order-detail__status-icon order-detail__status-icon--active">
                    <i class="bi {{ $isArchivedView ? 'bi-archive-fill' : 'bi-envelope-paper-fill' }}"></i>
                </span>
                <strong>{{ __('admin.marketing.area_links.marketing') }}</strong>
            </div>

            <h1 class="menu-dashboard__title">{{ $isArchivedView ? __('admin.marketing.campaigns.archived_title') : __('admin.marketing.campaigns.plural') }}</h1>
        </div>

        <div class="menu-dashboard__hero-actions dashboard-home__hero-actions">
            @if ($isArchivedView)
                <a href="{{ route('admin.campaigns.index') }}" class="order-detail__contact">
                    <i class="bi bi-arrow-left"></i>
                    <span>{{ __('admin.marketing.campaigns.list') }}</span>
                </a>
            @else
                <a href="{{ route('admin.campaigns.archived') }}" class="order-detail__contact">
                    <i class="bi bi-archive-fill"></i>
                    <span>{{ __('admin.marketing.campaigns.archived') }}</span>
                </a>
                <a href="{{ route('admin.campaigns.create') }}" class="order-detail__contact">
                    <i class="bi bi-cloud-plus-fill"></i>
                    <span>{{ __('admin.marketing.campaigns.create_new') }}</span>
                </a>
            @endif
        </div>
    </header>

    <section class="campaign-index-board mt-4" aria-label="{{ $isArchivedView ? __('admin.marketing.campaigns.archived_aria') : __('admin.marketing.campaigns.list_aria') }}">
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
                        $segmentLabel = $segments[$normalizedSegment] ?? ($campaign->segment ?: __('admin.marketing.campaigns.undefined_segment'));
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
                                <span>{{ __('admin.common.promo') }}</span>
                                <strong class="campaign-list-promo" title="{{ $promoSlug }}">{{ $promoSlug }}</strong>
                            </div>
                            <div>
                                <span>{{ __('admin.marketing.campaigns.model') }}</span>
                                <strong title="{{ $modelName }}">{{ $modelName }}</strong>
                            </div>
                        </div>

                        <div class="campaign-list-usage">
                            @if (! $isDraft)
                                <div
                                    class="promotion-list-donut campaign-list-donut"
                                    style="--promotion-usage: {{ $progressPercentage }}%;"
                                    role="img"
                                    aria-label="{{ __('admin.marketing.campaigns.sent_email_ratio', ['sent' => $sentAssignments, 'total' => $totalAssignments]) }}"
                                    title="{{ __('admin.marketing.campaigns.sent_email_ratio', ['sent' => $sentAssignments, 'total' => $totalAssignments]) }}"
                                >
                                    <strong>{{ $progressPercentage }}%</strong>
                                </div>
                            @endif
                        </div>

                        <div class="promotion-list-actions campaign-list-actions">
                            @if ($isDraft && ! $isArchivedView)
                                <a class="promotion-list-action promotion-list-action--primary" href="{{ route('admin.campaigns.edit', $campaign) }}">
                                    {{ __('admin.marketing.campaigns.complete') }}
                                </a>
                                <form action="{{ route('admin.campaigns.destroy', $campaign) }}" method="POST" onsubmit="return confirm(@js(__('admin.marketing.campaigns.delete_draft_confirm')));">
                                    @csrf
                                    @method('DELETE')
                                    <button class="promotion-list-action promotion-list-action--danger" type="submit">
                                        {{ __('admin.common.delete') }}
                                    </button>
                                </form>
                            @else
                                <a class="promotion-list-action promotion-list-action--primary" href="{{ route('admin.campaigns.show', $campaign) }}">
                                    {{ __('admin.marketing.campaigns.open') }}
                                </a>

                                @if ($canRestore)
                                    <form action="{{ route('admin.campaigns.restore', $campaign) }}" method="POST">
                                        @csrf
                                        <button class="promotion-list-action promotion-list-action--primary" type="submit">
                                            {{ __('admin.marketing.campaigns.restore') }}
                                        </button>
                                    </form>
                                @endif

                                @if ($canArchive)
                                    <form action="{{ route('admin.campaigns.archive', $campaign) }}" method="POST">
                                        @csrf
                                        <button class="promotion-list-action promotion-list-action--danger" type="submit">
                                            {{ __('admin.marketing.campaigns.archive') }}
                                        </button>
                                    </form>
                                @endif

                                @if ($canDeletePermanently)
                                    <form
                                        action="{{ route('admin.campaigns.destroy', $campaign) }}"
                                        method="POST"
                                        onsubmit="return confirm(@js(__('admin.marketing.campaigns.delete_forever_confirm')));"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button class="promotion-list-action promotion-list-action--danger" type="submit">
                                            {{ __('admin.common.delete') }}
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
                    <strong>{{ $isArchivedView ? __('admin.marketing.campaigns.no_archived_campaigns') : __('admin.marketing.campaigns.no_campaigns') }}</strong>
                    <p>
                        {{ $isArchivedView
                            ? __('admin.marketing.campaigns.archived_empty_text')
                            : __('admin.marketing.campaigns.empty_text') }}
                    </p>
                </div>
            </div>
        @endif
    </section>
</div>

@endsection
