@extends('layouts.base')

@section('contents')

@if (session('success'))
    <div class="dashboard-home__flash" role="alert">
        {{ session('success') }}
    </div>
@endif

<div class="dash_page marketing-index-page">
    @include('admin.Marketing.partials.index-style')

    @include('admin.Marketing.partials.breadcrumbs', [
        'items' => [
            ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['label' => 'Marketing', 'url' => route('admin.marketing')],
            ['label' => 'Automazioni'],
        ],
    ])

    <header class="menu-dashboard__hero order-detail__summary">
        <div class="order-detail__meta">
            <div class="order-detail__status">
                <span class="order-detail__status-icon order-detail__status-icon--active">
                    <i class="bi bi-lightning-charge-fill"></i>
                </span>
                <strong>Marketing</strong>
            </div>

            <h1 class="menu-dashboard__title">Automazioni</h1>
            <p>Trigger marketing configurati e pronti per il flusso operativo.</p>
        </div>

        <div class="menu-dashboard__hero-actions dashboard-home__hero-actions">
            <a href="{{ route('admin.automations.create') }}" class="order-detail__contact">
                <i class="bi bi-cloud-plus-fill"></i>
                <span>Crea nuova</span>
            </a>
            @include('admin.Marketing.partials.area-links', ['current' => 'automations'])
        </div>
    </header>

    <section class="order-detail__section mt-4">
        <div class="order-detail__section-head">
            <h3>
                <span class="order-detail__section-icon">
                    <i class="bi bi-list-check"></i>
                </span>
                Elenco automazioni
            </h3>
        </div>

        @if ($automations->count() > 0)
            <div class="marketing-index-list">
                @foreach ($automations as $automation)
                    <article class="marketing-index-row">
                        <div class="marketing-index-main">
                            <div class="marketing-index-kicker">
                                <i class="bi bi-lightning-charge-fill"></i>
                                <span>Automazione</span>
                                @include('admin.Marketing.partials.status-pill', [
                                    'status' => $automation->status,
                                    'label' => $statuses[$automation->status] ?? $automation->status,
                                ])
                            </div>

                            <h4 class="marketing-index-title">{{ $automation->name }}</h4>

                            <div class="marketing-index-meta">
                                <span class="marketing-index-chip">{{ $triggers[$automation->trigger] ?? ($automation->trigger ?: 'Trigger non definito') }}</span>
                                <span class="marketing-index-chip marketing-index-chip--accent">{{ $automation->promotions->count() }} promo</span>
                            </div>
                        </div>

                        <div class="marketing-index-block">
                            <p class="marketing-index-copy">Modello: {{ $automation->model?->name ?? '-' }}</p>
                            <div class="marketing-index-meta marketing-index-extra">
                                @forelse ($automation->promotions as $promotion)
                                    <span>{{ $promotion->name }}</span>
                                @empty
                                    <span>Nessuna promozione</span>
                                @endforelse
                            </div>
                        </div>

                        <div class="marketing-index-stats">
                            <div class="marketing-index-stat-row">
                                <span class="marketing-index-stat">
                                    <strong>{{ $automation->total_activation }}</strong>
                                    <span>coinvolti</span>
                                </span>
                                <span class="marketing-index-stat">
                                    <strong>{{ $automation->total_sent }}</strong>
                                    <span>invii</span>
                                </span>
                            </div>
                            <div class="marketing-index-meta marketing-index-extra">
                                <span>Cooldown: {{ data_get($automation->metadata, 'cooldown_days') ?? '-' }}</span>
                                <span>Ultimo run: {{ $automation->last_run_at?->format('d/m/Y H:i') ?? '-' }}</span>
                            </div>
                        </div>

                        <div class="marketing-index-actions">
                            <a class="order-detail__contact" href="{{ route('admin.automations.show', $automation) }}">
                                <i class="bi bi-eye-fill"></i>
                                <span>Apri</span>
                            </a>
                            <a class="order-detail__contact" href="{{ route('admin.automations.edit', $automation) }}">
                                <i class="bi bi-pencil-square"></i>
                                <span>Modifica</span>
                            </a>
                            @if (in_array($automation->status, ['draft', 'paused'], true))
                                <form class="marketing-index-secondary" action="{{ route('admin.automations.activate', $automation) }}" method="POST">
                                    @csrf
                                    <button class="order-detail__contact" type="submit">
                                        <i class="bi bi-check2-circle"></i>
                                        <span>Attiva</span>
                                    </button>
                                </form>
                            @endif
                            @if ($automation->status === 'active')
                                <form class="marketing-index-secondary" action="{{ route('admin.automations.pause', $automation) }}" method="POST">
                                    @csrf
                                    <button class="order-detail__contact marketing-index-muted" type="submit">
                                        <i class="bi bi-pause-circle"></i>
                                        <span>Pausa</span>
                                    </button>
                                </form>
                            @endif
                            @if ($automation->status !== 'archived')
                                <form class="marketing-index-secondary" action="{{ route('admin.automations.archive', $automation) }}" method="POST">
                                    @csrf
                                    <button class="order-detail__contact marketing-index-danger" type="submit">
                                        <i class="bi bi-archive-fill"></i>
                                        <span>Archivia</span>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="marketing-index-pager">
                {{ $automations->links() }}
            </div>
        @else
            <div class="dashboard-home__details-placeholder">
                <span class="dashboard-home__details-placeholder-icon">
                    <i class="bi bi-lightning-charge-fill"></i>
                </span>
                <div>
                    <strong>Nessuna automazione presente.</strong>
                    <p>Crea un trigger marketing per preparare audience in modo controllato.</p>
                </div>
            </div>
        @endif
    </section>
</div>

@endsection
