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
    $tone = match ($campaign->status) {
        'active', 'sent' => 'active',
        'archived' => 'off',
        default => 'warning',
    };

    $icon = match ($tone) {
        'active' => 'bi-check-circle-fill',
        'off' => 'bi-x-circle-fill',
        default => 'bi-exclamation-circle-fill',
    };

    $segmentLabel = $segments[$campaign->segment] ?? ($campaign->segment ?: '-');
    $modelName = $campaign->model?->name ?? '-';
    $modelObject = $campaign->model?->object ?? '-';
    $promotionsCount = $campaign->promotions->count();
    $scheduleState = 'Nessuna programmazione';

    if ($campaign->scheduled_at) {
        $scheduleState = $campaign->scheduled_at->isFuture()
            ? 'Invio programmato'
            : 'Pronta per il prossimo scheduler';
    }
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
                        'status' => $campaign->status,
                        'label' => $statuses[$campaign->status] ?? $campaign->status,
                    ])
                </div>

                <div class="order-detail__contacts">
                    <a class="order-detail__contact" href="{{ route('admin.campaigns.index') }}">
                        <i class="bi bi-arrow-left"></i>
                        <span>Lista</span>
                    </a>
                    @if ($campaign->status !== 'sent')
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
                                <i class="bi bi-info-circle-fill"></i>
                            </span>
                            Dettaglio campagna
                        </h3>
                    </div>

                    <div class="marketing-detail__grid">
                        <article class="marketing-detail__fact">
                            <span>Status</span>
                            <strong>{{ $statuses[$campaign->status] ?? $campaign->status }}</strong>
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
                        <div class="marketing-detail__linked-grid">
                            @foreach ($campaign->promotions as $promotion)
                                <article class="marketing-detail__linked-card">
                                    <span>Promozione</span>
                                    <strong>{{ $promotion->name }}</strong>
                                    <small>{{ $promotion->slug }}</small>
                                    @include('admin.Marketing.partials.status-pill', [
                                        'status' => $promotion->status,
                                        'label' => $promotion->status,
                                    ])
                                    <a href="{{ route('admin.promotions.show', $promotion) }}" class="order-detail__contact">
                                        <i class="bi bi-arrow-up-right-circle-fill"></i>
                                        <span>Apri</span>
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
                        @if (in_array($campaign->status, ['draft', 'paused'], true))
                            <form action="{{ route('admin.campaigns.activate', $campaign) }}" method="POST">
                                @csrf
                                <button class="order-detail__contact" type="submit">
                                    <i class="bi bi-check2-circle"></i>
                                    <span>Conferma/programma campagna</span>
                                </button>
                            </form>
                        @endif

                        @if ($campaign->status === 'active')
                            <form action="{{ route('admin.campaigns.pause', $campaign) }}" method="POST">
                                @csrf
                                <button class="order-detail__contact marketing-detail__contact--muted" type="submit">
                                    <i class="bi bi-pause-circle"></i>
                                    <span>Pausa</span>
                                </button>
                            </form>
                        @endif

                        @if (in_array($campaign->status, ['active', 'paused'], true))
                            <form action="{{ route('admin.campaigns.draft', $campaign) }}" method="POST">
                                @csrf
                                <button class="order-detail__contact marketing-detail__contact--muted" type="submit">
                                    <i class="bi bi-clock-history"></i>
                                    <span>Completa più tardi</span>
                                </button>
                            </form>
                        @endif

                        @if (! in_array($campaign->status, ['sent', 'archived'], true))
                            <form action="{{ route('admin.campaigns.preview-audience', $campaign) }}" method="POST">
                                @csrf
                                <button class="order-detail__contact" type="submit">
                                    <i class="bi bi-people-fill"></i>
                                    <span>Preview audience</span>
                                </button>
                            </form>

                            <form action="{{ route('admin.campaigns.prepare-assignments', $campaign) }}" method="POST">
                                @csrf
                                <button class="order-detail__contact marketing-detail__contact--danger" type="submit">
                                    <i class="bi bi-person-plus-fill"></i>
                                    <span>Prepara assegnazioni</span>
                                </button>
                            </form>
                        @endif

                        @if (in_array($campaign->status, ['active', 'paused', 'draft', 'sent'], true))
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
                            ['label' => 'Status campagna', 'value' => $statuses[$campaign->status] ?? $campaign->status],
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

                @include('admin.Marketing.partials.report-metrics', ['report' => $report])

                @include('admin.Marketing.partials.customer-promotions-table', [
                    'customerPromotions' => $customerPromotions,
                    'emptyText' => 'Nessuna assegnazione creata per questa campagna.',
                ])
            </div>
        </article>
    </div>
</div>

@endsection
