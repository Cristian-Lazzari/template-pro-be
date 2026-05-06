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
    $modelName = $campaign->model?->name ?? '-';
    $modelObject = $campaign->model?->object ?? '-';
    $promotionsCount = $campaign->promotions->count();
    $scheduleState = $sendProgress['message'] ?? 'Nessuna programmazione';
    $nextBatchDueAt = $sendProgress['next_batch_due_at'] ?? ($nextBatchDueAt ?? null);
    $scheduleWindow = data_get($campaign->metadata, 'schedule_window');
    $scheduleWindowLabel = $scheduleWindows[$scheduleWindow] ?? ($scheduleWindow ?: '-');
    $requestedScheduledAt = data_get($campaign->metadata, 'requested_scheduled_at');
    $estimatedDuration = $sendProgress['estimated_duration_minutes'] ?? ($estimatedDurationMinutes ?? data_get($campaign->metadata, 'estimated_duration_minutes'));
    $completedAt = $sendProgress['completed_at'] ?? ($completedAt ?? $campaign->sent_at);
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
    $reportMetrics = [
        ['label' => 'Coinvolti', 'value' => $totalEmails, 'tone' => 'neutral'],
        ['label' => 'Email inviate', 'value' => $sentEmails, 'tone' => 'neutral'],
        ['label' => 'Aperture', 'value' => $report['opened_count'] ?? 0, 'tone' => 'neutral'],
        ['label' => 'Click', 'value' => $report['clicked_count'] ?? 0, 'tone' => 'neutral'],
        ['label' => 'Promo usate', 'value' => $report['used_count'] ?? 0, 'tone' => 'active'],
    ];

    if ($showOrderMetrics) {
        $reportMetrics[] = ['label' => 'Conversioni ordini', 'value' => $report['order_conversion_count'] ?? 0, 'tone' => 'active'];
    }

    if ($showReservationMetrics) {
        $reportMetrics[] = ['label' => 'Conversioni prenotazioni', 'value' => $report['reservation_conversion_count'] ?? 0, 'tone' => 'active'];
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
	                            <strong>{{ $totalEmails }}</strong>
	                        </article>
	                        <article class="marketing-detail__fact">
	                            <span>Inviate</span>
	                            <strong>{{ $sentEmails }}</strong>
	                        </article>
	                        <article class="marketing-detail__fact">
	                            <span>In attesa</span>
	                            <strong>{{ $pendingEmails }}</strong>
	                        </article>
	                        <article class="marketing-detail__fact">
	                            <span>Avanzamento</span>
	                            <strong>{{ $progressPercentage }}%</strong>
	                        </article>
	                        @if ($nextBatchDueAt)
	                            <article class="marketing-detail__fact">
	                                <span>Prossimo batch previsto</span>
	                                <strong>{{ $nextBatchDueAt->format('d/m/Y H:i') }}</strong>
	                            </article>
	                        @endif
	                        @if ($estimatedDuration)
	                            <article class="marketing-detail__fact">
	                                <span>Durata stimata</span>
	                                <strong>{{ $estimatedDuration }} min</strong>
	                            </article>
	                        @endif
	                        @if ($completedAt && $normalizedStatus === 'completed')
	                            <article class="marketing-detail__fact">
	                                <span>Completata</span>
	                                <strong>{{ $completedAt->format('d/m/Y H:i') }}</strong>
	                            </article>
	                        @endif
	                    </div>
	
	                    <div
	                        class="marketing-detail__empty marketing-detail__send-panel mt-3"
	                    >
	                        <strong>{{ $scheduleState }}</strong>
	                        @if ($normalizedStatus === 'scheduled' && $campaign->scheduled_at?->isFuture())
	                            <small>Il prossimo batch partirà dopo questa finestra.</small>
	                        @endif
	                        <div class="marketing-detail__progress" aria-label="Avanzamento invio campagna">
	                            <div class="marketing-detail__progress-track">
	                                <div
	                                    class="marketing-detail__progress-bar @if ($normalizedStatus === 'running' && $progressPercentage < 100) is-running @endif"
	                                    style="width: {{ $progressPercentage }}%"
	                                ></div>
	                            </div>
	                            <div class="marketing-detail__progress-meta">
	                                <span>{{ $sentEmails }} di {{ $totalEmails }} email inviate</span>
	                                <span>{{ $progressPercentage }}%</span>
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
	                            <strong>{{ $completedAt?->format('d/m/Y H:i') ?? '-' }}</strong>
	                        </article>
                        <article class="marketing-detail__fact">
                            <span>Promozioni</span>
                            <strong>{{ $promotionsCount }}</strong>
                        </article>
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
	                            <strong>{{ $totalEmails }}</strong>
	                        </article>
	                        <article class="marketing-detail__metric">
	                            <span>Inviate</span>
	                            <strong>{{ $sentEmails }}</strong>
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
                            <strong>Report coerente con le promozioni collegate</strong>
                            <span>Metriche filtrate in base al tipo di promozione collegata.</span>
                        </div>

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

                @include('admin.Marketing.partials.customer-promotions-table', [
                    'customerPromotions' => $customerPromotions,
                    'emptyText' => 'Nessuna assegnazione creata per questa campagna.',
                ])
            </div>
        </article>
    </div>
</div>

@endsection
