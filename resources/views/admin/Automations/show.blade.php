@extends('layouts.base')

@section('contents')

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show dashboard-home__flash" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="dash_page">
    @include('admin.Marketing.partials.breadcrumbs', [
        'items' => [
            ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['label' => 'Marketing', 'url' => route('admin.campaigns.index')],
            ['label' => 'Automazioni', 'url' => route('admin.automations.index')],
            ['label' => $automation->name],
        ],
    ])

    <header class="menu-dashboard__hero order-detail__summary">
        <div class="order-detail__meta">
            <div class="order-detail__status">
                <span class="order-detail__status-icon order-detail__status-icon--active">
                    <i class="bi bi-lightning-charge-fill"></i>
                </span>
                @include('admin.Marketing.partials.status-pill', [
                    'status' => $automation->status,
                    'label' => $statuses[$automation->status] ?? $automation->status,
                ])
            </div>

            <h1 class="menu-dashboard__title">{{ $automation->name }}</h1>
            <p>Automazione marketing su trigger {{ $triggers[$automation->trigger] ?? ($automation->trigger ?: 'non definito') }}.</p>
        </div>

        <div class="menu-dashboard__hero-actions dashboard-home__hero-actions">
            <a class="order-detail__contact" href="{{ route('admin.automations.index') }}">
                <i class="bi bi-arrow-left"></i>
                <span>Lista</span>
            </a>
            <a class="order-detail__contact" href="{{ route('admin.automations.edit', $automation) }}">
                <i class="bi bi-pencil-square"></i>
                <span>Modifica</span>
            </a>
            @include('admin.Marketing.partials.area-links', ['current' => 'automations'])
        </div>
    </header>

    <section class="order-detail__section mt-4">
        <div class="order-detail__section-head">
            <h3>
                <span class="order-detail__section-icon">
                    <i class="bi bi-sliders"></i>
                </span>
                Azioni operative
            </h3>
        </div>

        <div class="split">
            <div>
                <h4>Stato automazione</h4>
                <p>Cambia lo stato operativo senza eseguire trigger o inviare email.</p>
                <div class="d-flex flex-wrap gap-2">
                    <form action="{{ route('admin.automations.activate', $automation) }}" method="POST">
                        @csrf
                        <button class="my_btn_2 w-auto" type="submit">
                            <i class="bi bi-check2-circle"></i>
                            Attiva
                        </button>
                    </form>
                    <form action="{{ route('admin.automations.pause', $automation) }}" method="POST">
                        @csrf
                        <button class="my_btn_5 w-auto" type="submit">
                            <i class="bi bi-pause-circle"></i>
                            Pausa
                        </button>
                    </form>
                    <form action="{{ route('admin.automations.archive', $automation) }}" method="POST">
                        @csrf
                        <button class="my_btn_2 btn_delete w-auto" type="submit">
                            <i class="bi bi-archive-fill"></i>
                            Archivia
                        </button>
                    </form>
                </div>
            </div>
            <div>
                <h4>Audience e assegnazioni</h4>
                <p>Preview audience: questa azione non crea assegnazioni e non invia email.</p>
                <form action="{{ route('admin.automations.preview-audience', $automation) }}" method="POST" class="mb-3">
                    @csrf
                    <button class="my_btn_5 w-auto" type="submit">
                        <i class="bi bi-people-fill"></i>
                        Preview audience
                    </button>
                </form>

                <p>Questa azione crea le assegnazioni cliente-promozione, ma NON invia email.</p>
                <form action="{{ route('admin.automations.prepare-assignments', $automation) }}" method="POST">
                    @csrf
                    <button class="my_btn_2 w-auto" type="submit">
                        <i class="bi bi-person-plus-fill"></i>
                        Prepara assegnazioni
                    </button>
                </form>
            </div>
        </div>
    </section>

    @if (session('automation_audience_preview'))
        @php
            $audiencePreview = session('automation_audience_preview');
            $previewMetrics = [
                ['label' => 'Preview disponibile', 'value' => ($audiencePreview['can_preview'] ?? false) ? 'Si' : 'No', 'meta' => $audiencePreview['failure_reason'] ?? 'Trigger leggibile', 'icon' => 'bi-check-circle-fill'],
                ['label' => 'Trigger', 'value' => $triggers[$audiencePreview['trigger'] ?? null] ?? ($audiencePreview['trigger'] ?? '-'), 'meta' => 'Regola audience', 'icon' => 'bi-lightning-charge-fill'],
                ['label' => 'Clienti coinvolgibili', 'value' => $audiencePreview['customers_checked'] ?? 0, 'meta' => 'Audience letta', 'icon' => 'bi-people-fill'],
                ['label' => 'Promozioni', 'value' => $audiencePreview['promotions_count'] ?? 0, 'meta' => 'Collegate alla automazione', 'icon' => 'bi-megaphone-fill'],
            ];
        @endphp
        <section class="order-detail__section mt-4">
            <div class="order-detail__section-head">
                <h3>
                    <span class="order-detail__section-icon">
                        <i class="bi bi-people-fill"></i>
                    </span>
                    Preview audience
                </h3>
            </div>

            <p>Questa azione non crea assegnazioni e non invia email.</p>

            <div class="statistics-page__metric-grid">
                @foreach ($previewMetrics as $metric)
                    <article class="statistics-page__metric-card">
                        <span class="menu-dashboard__stat-label">
                            <i class="bi {{ $metric['icon'] }}"></i>
                            {{ $metric['label'] }}
                        </span>
                        <strong>{{ $metric['value'] }}</strong>
                        <p>{{ $metric['meta'] ?: '-' }}</p>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    @if (session('automation_assignment_result'))
        @php
            $assignmentResult = session('automation_assignment_result');
            $assignmentMetrics = [
                ['label' => 'Modalita', 'value' => $assignmentResult['mode'] ?? '-', 'meta' => 'Operazione controllata', 'icon' => 'bi-shield-lock-fill'],
                ['label' => 'Assegnabile', 'value' => ($assignmentResult['can_assign'] ?? false) ? 'Si' : 'No', 'meta' => $assignmentResult['failure_reason'] ?? 'Automazione processabile', 'icon' => 'bi-check-circle-fill'],
                ['label' => 'Clienti controllati', 'value' => $assignmentResult['customers_checked'] ?? 0, 'meta' => 'Audience letta', 'icon' => 'bi-people-fill'],
                ['label' => 'Promozioni', 'value' => $assignmentResult['promotions_count'] ?? 0, 'meta' => 'Collegate alla automazione', 'icon' => 'bi-megaphone-fill'],
                ['label' => 'Nuove assegnazioni', 'value' => $assignmentResult['assigned_count'] ?? 0, 'meta' => 'Customer promotion create', 'icon' => 'bi-person-plus-fill'],
                ['label' => 'Gia assegnate', 'value' => $assignmentResult['already_assigned_count'] ?? 0, 'meta' => 'Non duplicate', 'icon' => 'bi-shield-check'],
                ['label' => 'Saltate', 'value' => $assignmentResult['skipped_count'] ?? 0, 'meta' => 'Non assegnabili', 'icon' => 'bi-skip-forward-fill'],
                ['label' => 'Errori', 'value' => $assignmentResult['errors_count'] ?? 0, 'meta' => 'Sintetici', 'icon' => 'bi-exclamation-triangle-fill'],
            ];
        @endphp
        <section class="order-detail__section mt-4">
            <div class="order-detail__section-head">
                <h3>
                    <span class="order-detail__section-icon">
                        <i class="bi bi-person-plus-fill"></i>
                    </span>
                    Risultato assegnazioni
                </h3>
            </div>

            <p>Operazione completata senza invio email e senza aggiornare il last run.</p>

            <div class="statistics-page__metric-grid">
                @foreach ($assignmentMetrics as $metric)
                    <article class="statistics-page__metric-card">
                        <span class="menu-dashboard__stat-label">
                            <i class="bi {{ $metric['icon'] }}"></i>
                            {{ $metric['label'] }}
                        </span>
                        <strong>{{ $metric['value'] }}</strong>
                        <p>{{ $metric['meta'] ?: '-' }}</p>
                    </article>
                @endforeach
            </div>

            @if (! empty($assignmentResult['errors']))
                <h4 class="mt-4">Errori sintetici</h4>
                <div class="table-responsive">
                    <table class="table table-dark table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Promotion</th>
                                <th>Messaggio</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($assignmentResult['errors'] as $error)
                                <tr>
                                    <td>{{ $error['customer_id'] ?? '-' }}</td>
                                    <td>{{ $error['promotion_id'] ?? '-' }}</td>
                                    <td>{{ $error['message'] ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    @endif

    <section class="order-detail__section mt-4">
        <div class="order-detail__section-head">
            <h3>
                <span class="order-detail__section-icon">
                    <i class="bi bi-info-circle-fill"></i>
                </span>
                Dettaglio automazione
            </h3>
        </div>

        <div class="split">
            <div>
                <p>
                    <strong>Status:</strong>
                    @include('admin.Marketing.partials.status-pill', [
                        'status' => $automation->status,
                        'label' => $statuses[$automation->status] ?? $automation->status,
                    ])
                </p>
                <p><strong>Trigger:</strong> {{ $triggers[$automation->trigger] ?? ($automation->trigger ?: '-') }}</p>
                <p><strong>Modello mail:</strong> {{ $automation->model?->name ?? '-' }}</p>
                <p><strong>Ultimo run:</strong> {{ $automation->last_run_at?->format('d/m/Y H:i') ?? '-' }}</p>
                <p><strong>Cooldown:</strong> {{ data_get($automation->metadata, 'cooldown_days') ?? '-' }}</p>
                <p><strong>Abilitata da:</strong> {{ data_get($automation->metadata, 'enabled_from') ?: '-' }}</p>
                <p><strong>Abilitata fino a:</strong> {{ data_get($automation->metadata, 'enabled_until') ?: '-' }}</p>
            </div>
            <div>
                <h4>Promozioni collegate</h4>
                @if ($automation->promotions->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-dark table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>Slug</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($automation->promotions as $promotion)
                                    <tr>
                                        <td><a href="{{ route('admin.promotions.show', $promotion) }}">{{ $promotion->name }}</a></td>
                                        <td><code>{{ $promotion->slug }}</code></td>
                                        <td>
                                            @include('admin.Marketing.partials.status-pill', [
                                                'status' => $promotion->status,
                                                'label' => $promotion->status,
                                            ])
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="dashboard-home__details-placeholder">
                        <span class="dashboard-home__details-placeholder-icon">
                            <i class="bi bi-megaphone-fill"></i>
                        </span>
                        <div>
                            <strong>Nessuna promozione collegata.</strong>
                            <p>Collega almeno una promozione prima di preparare assegnazioni.</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </section>

    @include('admin.Marketing.partials.report-metrics', ['report' => $report])

    @include('admin.Marketing.partials.customer-promotions-table', [
        'customerPromotions' => $customerPromotions,
        'emptyText' => 'Nessuna assegnazione creata per questa automazione.',
    ])
</div>

@endsection
