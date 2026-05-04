@extends('layouts.base')

@section('contents')

@if (session('success'))
    <div class="dashboard-home__flash" role="alert">
        {{ session('success') }}
    </div>
@endif

@php
    $tone = match ($automation->status) {
        'active' => 'active',
        'archived' => 'off',
        default => 'warning',
    };

    $icon = match ($tone) {
        'active' => 'bi-check-circle-fill',
        'off' => 'bi-x-circle-fill',
        default => 'bi-exclamation-circle-fill',
    };

    $triggerLabel = $triggers[$automation->trigger] ?? ($automation->trigger ?: '-');
    $modelName = $automation->model?->name ?? '-';
    $modelObject = $automation->model?->object ?? '-';
    $promotionsCount = $automation->promotions->count();
@endphp

<div class="dash_page">
    @include('admin.Marketing.partials.breadcrumbs', [
        'items' => [
            ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['label' => 'Marketing', 'url' => route('admin.marketing')],
            ['label' => 'Automazioni', 'url' => route('admin.automations.index')],
            ['label' => $automation->name],
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
                        'status' => $automation->status,
                        'label' => $statuses[$automation->status] ?? $automation->status,
                    ])
                </div>

                <div class="order-detail__contacts">
                    <a class="order-detail__contact" href="{{ route('admin.automations.index') }}">
                        <i class="bi bi-arrow-left"></i>
                        <span>Lista</span>
                    </a>
                    <a class="order-detail__contact" href="{{ route('admin.automations.edit', $automation) }}">
                        <i class="bi bi-pencil-square"></i>
                        <span>Modifica</span>
                    </a>
                    <a class="order-detail__contact" href="{{ route('admin.marketing') }}">
                        <i class="bi bi-grid-1x2-fill"></i>
                        <span>Marketing</span>
                    </a>
                </div>
            </header>

            <div class="order-detail__body">
                <section class="order-detail__summary">
                    <div class="order-detail__meta">
                        <p class="order-detail__code">#AUTO {{ $automation->id }}</p>
                        <p class="order-detail__time">{{ $automation->name }}</p>
                        <p class="order-detail__date">{{ $triggerLabel }}</p>
                    </div>

                    <div class="order-detail__customer">
                        <span>{{ $modelName }}</span>
                        <small>Run {{ $automation->last_run_at?->format('d/m/Y H:i') ?? '-' }}</small>
                    </div>
                </section>

                <section class="order-detail__section">
                    <div class="order-detail__section-head">
                        <h3>
                            <span class="order-detail__section-icon">
                                <i class="bi bi-info-circle-fill"></i>
                            </span>
                            Dettaglio automazione
                        </h3>
                    </div>

                    <div class="marketing-detail__grid">
                        <article class="marketing-detail__fact">
                            <span>Status</span>
                            <strong>{{ $statuses[$automation->status] ?? $automation->status }}</strong>
                        </article>
                        <article class="marketing-detail__fact">
                            <span>Trigger</span>
                            <strong>{{ $triggerLabel }}</strong>
                        </article>
                        <article class="marketing-detail__fact">
                            <span>Modello mail</span>
                            <strong>{{ $modelName }}</strong>
                            <small>{{ $modelObject }}</small>
                        </article>
                        <article class="marketing-detail__fact">
                            <span>Cooldown</span>
                            <strong>{{ data_get($automation->metadata, 'cooldown_days') ?? '-' }}</strong>
                        </article>
                        <article class="marketing-detail__fact">
                            <span>Da</span>
                            <strong>{{ data_get($automation->metadata, 'enabled_from') ?: '-' }}</strong>
                        </article>
                        <article class="marketing-detail__fact">
                            <span>Fino</span>
                            <strong>{{ data_get($automation->metadata, 'enabled_until') ?: '-' }}</strong>
                        </article>
                        <article class="marketing-detail__fact">
                            <span>Ultimo run</span>
                            <strong>{{ $automation->last_run_at?->format('d/m/Y H:i') ?? '-' }}</strong>
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
                            <strong>{{ $automation->total_activation }}</strong>
                        </article>
                        <article class="marketing-detail__metric">
                            <span>Inviate</span>
                            <strong>{{ $automation->total_sent }}</strong>
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
                            @foreach ($automation->promotions as $promotion)
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
                        @if (in_array($automation->status, ['draft', 'paused'], true))
                            <form action="{{ route('admin.automations.activate', $automation) }}" method="POST">
                                @csrf
                                <button class="order-detail__contact" type="submit">
                                    <i class="bi bi-check2-circle"></i>
                                    <span>Attiva</span>
                                </button>
                            </form>
                        @endif

                        @if ($automation->status === 'active')
                            <form action="{{ route('admin.automations.pause', $automation) }}" method="POST">
                                @csrf
                                <button class="order-detail__contact marketing-detail__contact--muted" type="submit">
                                    <i class="bi bi-pause-circle"></i>
                                    <span>Pausa</span>
                                </button>
                            </form>
                        @endif

                        @if (in_array($automation->status, ['active', 'paused'], true))
                            <form action="{{ route('admin.automations.draft', $automation) }}" method="POST">
                                @csrf
                                <button class="order-detail__contact marketing-detail__contact--muted" type="submit">
                                    <i class="bi bi-clock-history"></i>
                                    <span>Completa più tardi</span>
                                </button>
                            </form>
                        @endif

                        @if ($automation->status !== 'archived')
                            <form action="{{ route('admin.automations.preview-audience', $automation) }}" method="POST">
                                @csrf
                                <button class="order-detail__contact" type="submit">
                                    <i class="bi bi-people-fill"></i>
                                    <span>Preview audience</span>
                                </button>
                            </form>

                            <form action="{{ route('admin.automations.prepare-assignments', $automation) }}" method="POST">
                                @csrf
                                <button class="order-detail__contact marketing-detail__contact--danger" type="submit">
                                    <i class="bi bi-person-plus-fill"></i>
                                    <span>Prepara assegnazioni</span>
                                </button>
                            </form>

                            <form action="{{ route('admin.automations.archive', $automation) }}" method="POST">
                                @csrf
                                <button class="order-detail__contact marketing-detail__contact--danger" type="submit">
                                    <i class="bi bi-archive-fill"></i>
                                    <span>Archivia</span>
                                </button>
                            </form>
                        @endif
                    </div>
                </section>

                @if (session('automation_audience_preview'))
                    @php
                        $audiencePreview = session('automation_audience_preview');
                        $previewMetrics = [
                            ['label' => 'Disponibile', 'value' => ($audiencePreview['can_preview'] ?? false) ? 'Si' : 'No'],
                            ['label' => 'Motivo', 'value' => $audiencePreview['failure_reason'] ?? '-'],
                            ['label' => 'Trigger', 'value' => $triggers[$audiencePreview['trigger'] ?? null] ?? ($audiencePreview['trigger'] ?? '-')],
                            ['label' => 'Clienti', 'value' => $audiencePreview['customers_checked'] ?? 0],
                            ['label' => 'Promozioni', 'value' => $audiencePreview['promotions_count'] ?? 0],
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

                        <div class="marketing-detail__compact-grid">
                            @foreach ($previewMetrics as $metric)
                                <article class="marketing-detail__fact">
                                    <span>{{ $metric['label'] }}</span>
                                    <strong>{{ $metric['value'] }}</strong>
                                </article>
                            @endforeach
                        </div>
                    </section>
                @endif

                @if (session('automation_assignment_result'))
                    @php
                        $assignmentResult = session('automation_assignment_result');
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
                    'emptyText' => 'Nessuna assegnazione creata per questa automazione.',
                ])
            </div>
        </article>
    </div>
</div>

@endsection
