@extends('layouts.base')

@section('contents')

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show dashboard-home__flash" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger">
        @foreach ($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
    </div>
@endif

<div class="dash_page">
    @include('admin.Marketing.partials.breadcrumbs', [
        'items' => [
            ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['label' => 'Marketing', 'url' => route('admin.campaigns.index')],
            ['label' => 'Campagne', 'url' => route('admin.campaigns.index')],
            ['label' => $campaign->name],
        ],
    ])

    <header class="menu-dashboard__hero order-detail__summary">
        <div class="order-detail__meta">
            <div class="order-detail__status">
                <span class="order-detail__status-icon order-detail__status-icon--active">
                    <i class="bi bi-envelope-paper-fill"></i>
                </span>
                @include('admin.Marketing.partials.status-pill', [
                    'status' => $campaign->status,
                    'label' => $statuses[$campaign->status] ?? $campaign->status,
                ])
            </div>

            <h1 class="menu-dashboard__title">{{ $campaign->name }}</h1>
            <p>Campagna manuale per segmento {{ $segments[$campaign->segment] ?? ($campaign->segment ?: 'non definito') }}.</p>
        </div>

        <div class="menu-dashboard__hero-actions dashboard-home__hero-actions">
            <a class="order-detail__contact" href="{{ route('admin.campaigns.index') }}">
                <i class="bi bi-arrow-left"></i>
                <span>Lista</span>
            </a>
            <a class="order-detail__contact" href="{{ route('admin.campaigns.edit', $campaign) }}">
                <i class="bi bi-pencil-square"></i>
                <span>Modifica</span>
            </a>
            @include('admin.Marketing.partials.area-links', ['current' => 'campaigns'])
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
                <h4>Stato campagna</h4>
                <p>Cambia lo stato operativo senza creare assegnazioni o inviare email.</p>
                <div class="d-flex flex-wrap gap-2">
                    <form action="{{ route('admin.campaigns.activate', $campaign) }}" method="POST">
                        @csrf
                        <button class="my_btn_2 w-auto" type="submit">
                            <i class="bi bi-check2-circle"></i>
                            Attiva
                        </button>
                    </form>
                    <form action="{{ route('admin.campaigns.pause', $campaign) }}" method="POST">
                        @csrf
                        <button class="my_btn_5 w-auto" type="submit">
                            <i class="bi bi-pause-circle"></i>
                            Pausa
                        </button>
                    </form>
                    <form action="{{ route('admin.campaigns.archive', $campaign) }}" method="POST">
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
                <form action="{{ route('admin.campaigns.preview-audience', $campaign) }}" method="POST" class="mb-3">
                    @csrf
                    <button class="my_btn_5 w-auto" type="submit">
                        <i class="bi bi-people-fill"></i>
                        Preview audience
                    </button>
                </form>

                <p>Questa azione crea le assegnazioni cliente-promozione, ma NON invia email.</p>
                <form action="{{ route('admin.campaigns.prepare-assignments', $campaign) }}" method="POST">
                    @csrf
                    <button class="my_btn_2 w-auto" type="submit">
                        <i class="bi bi-person-plus-fill"></i>
                        Prepara assegnazioni
                    </button>
                </form>
            </div>
        </div>
    </section>

    @if (session('audience_preview'))
        @php
            $audiencePreview = session('audience_preview');
            $assignableCount = $audiencePreview['assignable_count'] ?? $audiencePreview['assigned_count'] ?? 0;
            $previewMetrics = [
                ['label' => 'Assegnabile', 'value' => ($audiencePreview['can_assign'] ?? false) ? 'Si' : 'No', 'meta' => $audiencePreview['failure_reason'] ?? 'Preview disponibile', 'icon' => 'bi-check-circle-fill'],
                ['label' => 'Clienti controllati', 'value' => $audiencePreview['customers_checked'] ?? 0, 'meta' => 'Audience letta', 'icon' => 'bi-people-fill'],
                ['label' => 'Promozioni', 'value' => $audiencePreview['promotions_count'] ?? 0, 'meta' => 'Collegate alla campagna', 'icon' => 'bi-megaphone-fill'],
                ['label' => 'Simulate', 'value' => $assignableCount, 'meta' => 'Assegnazioni possibili', 'icon' => 'bi-person-plus-fill'],
                ['label' => 'Gia assegnate', 'value' => $audiencePreview['already_assigned_count'] ?? 0, 'meta' => 'Idempotenza', 'icon' => 'bi-shield-check'],
                ['label' => 'Saltate', 'value' => $audiencePreview['skipped_count'] ?? 0, 'meta' => 'Non assegnabili', 'icon' => 'bi-skip-forward-fill'],
                ['label' => 'Errori', 'value' => $audiencePreview['errors_count'] ?? 0, 'meta' => 'Sintetici', 'icon' => 'bi-exclamation-triangle-fill'],
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

            @if (! empty($audiencePreview['errors']))
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
                            @foreach ($audiencePreview['errors'] as $error)
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

    @if (session('campaign_assignment_result'))
        @php
            $assignmentResult = session('campaign_assignment_result');
            $assignmentMetrics = [
                ['label' => 'Modalita', 'value' => $assignmentResult['mode'] ?? '-', 'meta' => 'Operazione controllata', 'icon' => 'bi-shield-lock-fill'],
                ['label' => 'Assegnabile', 'value' => ($assignmentResult['can_assign'] ?? false) ? 'Si' : 'No', 'meta' => $assignmentResult['failure_reason'] ?? 'Campagna processabile', 'icon' => 'bi-check-circle-fill'],
                ['label' => 'Clienti controllati', 'value' => $assignmentResult['customers_checked'] ?? 0, 'meta' => 'Audience letta', 'icon' => 'bi-people-fill'],
                ['label' => 'Promozioni', 'value' => $assignmentResult['promotions_count'] ?? 0, 'meta' => 'Collegate alla campagna', 'icon' => 'bi-megaphone-fill'],
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

            <p>Operazione completata senza invio email.</p>

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
                Dettaglio campagna
            </h3>
        </div>

        <div class="split">
            <div>
                <p>
                    <strong>Status:</strong>
                    @include('admin.Marketing.partials.status-pill', [
                        'status' => $campaign->status,
                        'label' => $statuses[$campaign->status] ?? $campaign->status,
                    ])
                </p>
                <p><strong>Segmento:</strong> {{ $segments[$campaign->segment] ?? ($campaign->segment ?: '-') }}</p>
                <p><strong>Modello mail:</strong> {{ $campaign->model?->name ?? '-' }}</p>
                <p><strong>Programmata:</strong> {{ $campaign->scheduled_at?->format('d/m/Y H:i') ?? '-' }}</p>
                <p><strong>Inviata:</strong> {{ $campaign->sent_at?->format('d/m/Y H:i') ?? '-' }}</p>
            </div>
            <div>
                <h4>Promozioni collegate</h4>
                @if ($campaign->promotions->count() > 0)
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
                                @foreach ($campaign->promotions as $promotion)
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
        'emptyText' => 'Nessuna assegnazione creata per questa campagna.',
    ])
</div>

@endsection
