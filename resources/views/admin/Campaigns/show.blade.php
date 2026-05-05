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
    $progressPercentage = min(100, max(0, (float) ($sendProgress['percentage'] ?? 0)));
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
    $modelName = $campaign->model?->name ?? '-';
    $modelObject = $campaign->model?->object ?? '-';
    $promotionsCount = $campaign->promotions->count();
    $scheduleState = $sendProgress['message'] ?? 'Nessuna programmazione';
    $scheduleWindow = data_get($campaign->metadata, 'schedule_window');
    $scheduleWindowLabel = $scheduleWindows[$scheduleWindow] ?? ($scheduleWindow ?: '-');
    $requestedScheduledAt = data_get($campaign->metadata, 'requested_scheduled_at');
    $estimatedDuration = data_get($campaign->metadata, 'estimated_duration_minutes');
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
    $promotionCaseUses = $campaign->promotions->pluck('case_use')->filter()->unique()->values();
    $hasTablePromotion = $promotionCaseUses->contains('table');
    $hasOrderPromotion = $promotionCaseUses->intersect(['take_away', 'delivery', 'gift', 'generic'])->isNotEmpty();
    $conversionMode = $hasTablePromotion && ! $hasOrderPromotion
        ? 'reservation'
        : ($hasOrderPromotion && ! $hasTablePromotion ? 'order' : 'mixed');
    $reportMetrics = [
        ['label' => 'Coinvolti', 'value' => $report['involved_count'] ?? 0, 'tone' => 'neutral'],
        ['label' => 'Email inviate', 'value' => $report['sent_count'] ?? 0, 'tone' => 'neutral'],
        ['label' => 'Aperture', 'value' => $report['opened_count'] ?? 0, 'tone' => 'neutral'],
        ['label' => 'Click', 'value' => $report['clicked_count'] ?? 0, 'tone' => 'neutral'],
        ['label' => 'Promo usate', 'value' => $report['used_count'] ?? 0, 'tone' => 'active'],
        ['label' => 'Sconto totale', 'value' => \App\Support\Currency::formatAmount($report['discount_total'] ?? 0), 'tone' => 'active'],
    ];

    if ($conversionMode !== 'reservation') {
        $reportMetrics[] = ['label' => 'Ordini generati', 'value' => $report['order_conversion_count'] ?? 0, 'tone' => 'active'];
    }

    if ($conversionMode !== 'order') {
        $reportMetrics[] = ['label' => 'Prenotazioni generate', 'value' => $report['reservation_conversion_count'] ?? 0, 'tone' => 'active'];
    }

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

                <div class="order-detail__contacts">
                    <a class="order-detail__contact" href="{{ route('admin.campaigns.index') }}">
                        <i class="bi bi-arrow-left"></i>
                        <span>Lista</span>
                    </a>
                    @if (! in_array($normalizedStatus, ['completed', 'archived'], true))
                        <a class="order-detail__contact" href="{{ route('admin.campaigns.edit', $campaign) }}">
                            <i class="bi bi-pencil-square"></i>
                            <span>Modifica</span>
                        </a>
                    @endif
                    <a class="order-detail__contact" href="{{ route('admin.marketing') }}">
                        <i class="bi bi-grid-1x2-fill"></i>
                        <span>Marketing</span>
                    </a>
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

                    <div class="marketing-detail__grid">
                        <article class="marketing-detail__fact">
                            <span>Stato</span>
                            <strong>{{ $statusLabel }}</strong>
                        </article>
                        <article class="marketing-detail__fact">
                            <span>Programmata</span>
                            <strong>{{ $campaign->scheduled_at?->format('d/m/Y H:i') ?? '-' }}</strong>
                        </article>
                        <article class="marketing-detail__fact">
                            <span>Email totali</span>
                            <strong>{{ $sendProgress['total'] ?? 0 }}</strong>
                        </article>
                        <article class="marketing-detail__fact">
                            <span>Inviate</span>
                            <strong>{{ $sendProgress['sent'] ?? 0 }}</strong>
                        </article>
                        <article class="marketing-detail__fact">
                            <span>In attesa</span>
                            <strong>{{ $sendProgress['pending'] ?? 0 }}</strong>
                        </article>
                        <article class="marketing-detail__fact">
                            <span>Avanzamento</span>
                            <strong>{{ $progressPercentage }}%</strong>
                        </article>
                    </div>

                    <div
                        class="marketing-detail__empty marketing-detail__send-panel mt-3"
                        data-marketing-progress
                        data-target-percent="{{ $progressPercentage }}"
                        data-total="{{ $sendProgress['total'] ?? 0 }}"
                        data-sent="{{ $sendProgress['sent'] ?? 0 }}"
                        data-status="{{ $normalizedStatus }}"
                        data-started-at="{{ $campaign->scheduled_at?->toIso8601String() }}"
                        data-completed-at="{{ $campaign->sent_at?->toIso8601String() }}"
                    >
                        <strong>{{ $scheduleState }}</strong>
                        <div class="marketing-detail__progress" aria-label="Avanzamento invio campagna">
                            <div class="marketing-detail__progress-track">
                                <div
                                    class="marketing-detail__progress-bar @if ($normalizedStatus === 'running' && $progressPercentage < 100) is-running @endif"
                                    style="width: 0"
                                    data-progress-bar
                                ></div>
                            </div>
                            <div class="marketing-detail__progress-meta">
                                <span data-progress-count>{{ $sendProgress['sent'] ?? 0 }} di {{ $sendProgress['total'] ?? 0 }} email inviate</span>
                                <span data-progress-percent>{{ $progressPercentage }}%</span>
                            </div>
                            <div class="marketing-detail__progress-live">
                                <i class="bi bi-clock-history"></i>
                                <span data-progress-clock>{{ $campaign->scheduled_at?->format('d/m/Y H:i') ?? now()->format('H:i:s') }}</span>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="order-detail__section">
                    <div class="order-detail__section-head">
                        <h3>
                            <span class="order-detail__section-icon">
                                <i class="bi bi-info-circle-fill"></i>
                            </span>
                            Dettaglio campagna
                        </h3>
                    </div>

                    <div class="marketing-detail__grid">
                        <article class="marketing-detail__fact">
                            <span>Status</span>
                            <strong>{{ $statusLabel }}</strong>
                        </article>
                        <article class="marketing-detail__fact">
                            <span>Segmento</span>
                            <strong>{{ $segmentLabel }}</strong>
                        </article>
                        <article class="marketing-detail__fact">
                            <span>Modello mail</span>
                            <strong>{{ $modelName }}</strong>
                            <small>{{ $modelObject }}</small>
                        </article>
                        <article class="marketing-detail__fact">
                            <span>Programmata</span>
                            <strong>{{ $campaign->scheduled_at?->format('d/m/Y H:i') ?? '-' }}</strong>
                        </article>
                        <article class="marketing-detail__fact">
                            <span>Finestra</span>
                            <strong>{{ $scheduleWindowLabel }}</strong>
                        </article>
                        <article class="marketing-detail__fact">
                            <span>Orario richiesto</span>
                            <strong>{{ $requestedScheduledAt ?: '-' }}</strong>
                        </article>
                        <article class="marketing-detail__fact">
                            <span>Durata stimata</span>
                            <strong>{{ $estimatedDuration ? $estimatedDuration . ' min' : '-' }}</strong>
                        </article>
                        <article class="marketing-detail__fact">
                            <span>Invio email</span>
                            <strong>{{ $scheduleState }}</strong>
                        </article>
                        <article class="marketing-detail__fact">
                            <span>Inviata</span>
                            <strong>{{ $campaign->sent_at?->format('d/m/Y H:i') ?? '-' }}</strong>
                        </article>
                        <article class="marketing-detail__fact">
                            <span>Promozioni</span>
                            <strong>{{ $promotionsCount }}</strong>
                        </article>
                    </div>

                    <div class="marketing-detail__empty mt-3">
                        <strong>L’orario reale puo essere stato ottimizzato per evitare sovrapposizioni.</strong>
                    </div>
                </section>

                <section class="order-detail__section">
                    <div class="order-detail__section-head">
                        <h3>
                            <span class="order-detail__section-icon">
                                <i class="bi bi-speedometer2"></i>
                            </span>
                            Contatori
                        </h3>
                    </div>

                    <div class="marketing-detail__compact-grid">
                        <article class="marketing-detail__metric">
                            <span>Coinvolti</span>
                            <strong>{{ $campaign->total_activation }}</strong>
                        </article>
                        <article class="marketing-detail__metric">
                            <span>Inviate</span>
                            <strong>{{ $campaign->total_sent }}</strong>
                        </article>
                    </div>
                </section>

                <section class="order-detail__section">
                    <div class="order-detail__section-head">
                        <h3>
                            <span class="order-detail__section-icon">
                                <i class="bi bi-megaphone-fill"></i>
                            </span>
                            Promozioni collegate
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
                                            <span class="campaign-promotion-card__icon">
                                                <i class="bi bi-megaphone-fill"></i>
                                            </span>
                                            <div>
                                                <strong>{{ $promotion->name }}</strong>
                                                <small>{{ $promotion->slug }}</small>
                                            </div>
                                        </div>

                                        <div class="campaign-promotion-card__meta">
                                            @include('admin.Marketing.partials.status-pill', [
                                                'status' => $promotion->status,
                                                'label' => $promotion->status,
                                            ])
                                            <span>{{ $caseUseLabels[$promotion->case_use] ?? ($promotion->case_use ?: 'Nessun ambito') }}</span>
                                            <span>{{ $discountTypeLabels[$promotion->type_discount] ?? ($promotion->type_discount ?: 'Sconto non definito') }}</span>
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

                <section class="order-detail__section">
                    <div class="order-detail__section-head">
                        <h3>
                            <span class="order-detail__section-icon">
                                <i class="bi bi-sliders"></i>
                            </span>
                            Azioni
                        </h3>
                    </div>

                    <div class="marketing-detail__actions">
                        @if (in_array($normalizedStatus, ['draft', 'paused'], true))
                            <form action="{{ route('admin.campaigns.activate', $campaign) }}" method="POST">
                                @csrf
                                <button class="order-detail__contact" type="submit">
                                    <i class="bi bi-check2-circle"></i>
                                    <span>Programma campagna</span>
                                </button>
                            </form>
                        @endif

                        @if (in_array($normalizedStatus, ['scheduled', 'running'], true))
                            <form action="{{ route('admin.campaigns.pause', $campaign) }}" method="POST">
                                @csrf
                                <button class="order-detail__contact marketing-detail__contact--muted" type="submit">
                                    <i class="bi bi-pause-circle"></i>
                                    <span>Pausa</span>
                                </button>
                            </form>
                        @endif

                        @if (in_array($normalizedStatus, ['scheduled', 'running', 'paused'], true))
                            <form action="{{ route('admin.campaigns.draft', $campaign) }}" method="POST">
                                @csrf
                                <button class="order-detail__contact marketing-detail__contact--muted" type="submit">
                                    <i class="bi bi-clock-history"></i>
                                    <span>Completa più tardi</span>
                                </button>
                            </form>
                        @endif

                        @if (! in_array($normalizedStatus, ['completed', 'archived'], true))
                            <form action="{{ route('admin.campaigns.preview-audience', $campaign) }}" method="POST">
                                @csrf
                                <button class="order-detail__contact" type="submit">
                                    <i class="bi bi-people-fill"></i>
                                    <span>Preview audience</span>
                                </button>
                            </form>
                        @endif

                        @if (in_array($normalizedStatus, ['draft', 'scheduled', 'paused'], true))
                            <form action="{{ route('admin.campaigns.prepare-assignments', $campaign) }}" method="POST">
                                @csrf
                                <button class="order-detail__contact marketing-detail__contact--danger" type="submit">
                                    <i class="bi bi-person-plus-fill"></i>
                                    <span>Prepara assegnazioni</span>
                                </button>
                            </form>
                        @endif

                        @if (in_array($normalizedStatus, ['scheduled', 'running', 'paused', 'draft', 'completed'], true))
                            <form action="{{ route('admin.campaigns.archive', $campaign) }}" method="POST">
                                @csrf
                                <button class="order-detail__contact marketing-detail__contact--danger" type="submit">
                                    <i class="bi bi-archive-fill"></i>
                                    <span>Archivia</span>
                                </button>
                            </form>
                        @endif
                    </div>
                </section>

                @if (session('audience_preview'))
                    @php
                        $audiencePreview = session('audience_preview');
                        $assignableCount = $audiencePreview['assignable_count'] ?? $audiencePreview['assigned_count'] ?? 0;
                        $previewMetrics = [
                            ['label' => 'Status campagna', 'value' => $statusLabel],
                            ['label' => 'Programmazione', 'value' => $campaign->scheduled_at?->format('d/m/Y H:i') ?? 'Mancante'],
                            ['label' => 'Invio', 'value' => $scheduleState],
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

                @if (session('campaign_assignment_result'))
                    @php
                        $assignmentResult = session('campaign_assignment_result');
                        $assignmentMetrics = [
                            ['label' => 'Modalita', 'value' => $assignmentResult['mode'] ?? '-'],
                            ['label' => 'Assegnabile', 'value' => ($assignmentResult['can_assign'] ?? false) ? 'Si' : 'No'],
                            ['label' => 'Motivo', 'value' => $assignmentResult['failure_reason'] ?? '-'],
                            ['label' => 'Clienti', 'value' => $assignmentResult['customers_checked'] ?? 0],
                            ['label' => 'Promozioni', 'value' => $assignmentResult['promotions_count'] ?? 0],
                            ['label' => 'Nuove', 'value' => $assignmentResult['assigned_count'] ?? 0],
                            ['label' => 'Gia assegnate', 'value' => $assignmentResult['already_assigned_count'] ?? 0],
                            ['label' => 'Saltate', 'value' => $assignmentResult['skipped_count'] ?? 0],
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
                            <strong>Assegnazioni create, email non inviate.</strong>
                            <small>L’invio reale parte solo da scheduled_at tramite scheduler Laravel.</small>
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

                <section class="order-detail__section">
                    <div class="order-detail__section-head">
                        <h3>
                            <span class="order-detail__section-icon">
                                <i class="bi bi-bar-chart-fill"></i>
                            </span>
                            Report marketing
                        </h3>
                    </div>

                    <div class="campaign-report-panel">
                        <div class="campaign-report-panel__summary">
                            <strong>{{ $conversionMode === 'reservation' ? 'Promozione orientata alle prenotazioni' : ($conversionMode === 'order' ? 'Promozione orientata agli ordini' : 'Promozioni miste') }}</strong>
                            <span>Il report mostra sconto e conversioni coerenti con le promozioni collegate alla campagna.</span>
                        </div>

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

                @include('admin.Marketing.partials.customer-promotions-table', [
                    'customerPromotions' => $customerPromotions,
                    'emptyText' => 'Nessuna assegnazione creata per questa campagna.',
                ])
            </div>
        </article>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('[data-marketing-progress]').forEach((panel) => {
            const bar = panel.querySelector('[data-progress-bar]');
            const percentLabel = panel.querySelector('[data-progress-percent]');
            const clockLabel = panel.querySelector('[data-progress-clock]');
            const targetPercent = Math.max(0, Math.min(100, Number(panel.dataset.targetPercent || 0)));
            const status = panel.dataset.status || 'draft';
            const startedAt = panel.dataset.startedAt ? new Date(panel.dataset.startedAt) : null;
            const completedAt = panel.dataset.completedAt ? new Date(panel.dataset.completedAt) : null;
            const startedAtTime = startedAt instanceof Date && !Number.isNaN(startedAt.getTime()) ? startedAt.getTime() : null;
            const completedAtTime = completedAt instanceof Date && !Number.isNaN(completedAt.getTime()) ? completedAt.getTime() : null;
            const started = performance.now();
            const duration = 950;

            const formatDuration = (milliseconds) => {
                const totalSeconds = Math.max(0, Math.floor(milliseconds / 1000));
                const hours = Math.floor(totalSeconds / 3600);
                const minutes = Math.floor((totalSeconds % 3600) / 60);
                const seconds = totalSeconds % 60;

                if (hours > 0) {
                    return `${hours}h ${String(minutes).padStart(2, '0')}m ${String(seconds).padStart(2, '0')}s`;
                }

                return `${minutes}m ${String(seconds).padStart(2, '0')}s`;
            };

            const formatTime = (date) => date.toLocaleTimeString('it-IT', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
            });

            const animate = (now) => {
                const progress = Math.min(1, (now - started) / duration);
                const current = targetPercent * (1 - Math.pow(1 - progress, 3));

                if (bar) {
                    bar.style.width = `${current}%`;
                }

                if (percentLabel) {
                    percentLabel.textContent = `${current.toFixed(current >= 10 || current === 0 ? 0 : 1)}%`;
                }

                if (progress < 1) {
                    requestAnimationFrame(animate);
                    return;
                }

                if (bar) {
                    bar.style.width = `${targetPercent}%`;
                }

                if (percentLabel) {
                    percentLabel.textContent = `${targetPercent}%`;
                }
            };

            const tickClock = () => {
                if (!clockLabel) {
                    return;
                }

                const now = new Date();

                if (status === 'completed' && completedAtTime) {
                    clockLabel.textContent = `Completata alle ${formatTime(new Date(completedAtTime))}`;
                    return;
                }

                if (status === 'running' && startedAtTime) {
                    clockLabel.textContent = `In corso da ${formatDuration(now.getTime() - startedAtTime)} · ora ${formatTime(now)}`;
                    return;
                }

                if (status === 'scheduled' && startedAtTime) {
                    const diff = startedAtTime - now.getTime();
                    clockLabel.textContent = diff > 0
                        ? `Inizia tra ${formatDuration(diff)} · ora ${formatTime(now)}`
                        : `In attesa del prossimo batch · ora ${formatTime(now)}`;
                    return;
                }

                clockLabel.textContent = `Ora ${formatTime(now)}`;
            };

            requestAnimationFrame(animate);
            tickClock();
            window.setInterval(tickClock, 1000);
        });
    });
</script>

@endsection
