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
            ['label' => 'Promozioni'],
        ],
    ])

    <header class="menu-dashboard__hero order-detail__summary">
        <div class="order-detail__meta">
            <div class="order-detail__status">
                <span class="order-detail__status-icon order-detail__status-icon--active">
                    <i class="bi bi-megaphone-fill"></i>
                </span>
                <strong>Marketing</strong>
            </div>

            <h1 class="menu-dashboard__title">Promozioni</h1>
            <p>Regole promozionali, validita e target collegati.</p>
        </div>

        <div class="menu-dashboard__hero-actions dashboard-home__hero-actions">
            <a href="{{ route('admin.promotions.create') }}" class="order-detail__contact">
                <i class="bi bi-cloud-plus-fill"></i>
                <span>Crea nuova</span>
            </a>
            @include('admin.Marketing.partials.area-links', ['current' => 'promotions'])
        </div>
    </header>

    <section class="order-detail__section mt-4">
        <div class="order-detail__section-head">
            <h3>
                <span class="order-detail__section-icon">
                    <i class="bi bi-list-check"></i>
                </span>
                Elenco promozioni
            </h3>
        </div>

        @if ($promotions->count() > 0)
            <div class="marketing-index-list">
                @foreach ($promotions as $promotion)
                    @php
                        $discountLabel = $promotion->discount !== null
                            ? number_format((float) $promotion->discount, 2, ',', '.')
                            : '-';
                        $discountType = $discountTypes[$promotion->type_discount] ?? ($promotion->type_discount ?: 'Sconto non impostato');
                    @endphp

                    <article class="marketing-index-row">
                        <div class="marketing-index-main">
                            <div class="marketing-index-kicker">
                                <i class="bi bi-megaphone-fill"></i>
                                <span>Promozione</span>
                                @include('admin.Marketing.partials.status-pill', [
                                    'status' => $promotion->status,
                                    'label' => $statuses[$promotion->status] ?? $promotion->status,
                                ])
                            </div>

                            <h4 class="marketing-index-title">{{ $promotion->name }}</h4>

                            <div class="marketing-index-meta">
                                <span class="marketing-index-chip">{{ $caseUses[$promotion->case_use] ?? ($promotion->case_use ?: 'Generica') }}</span>
                                <span class="marketing-index-chip marketing-index-chip--accent">{{ $promotion->permanent ? 'Permanente' : 'Programmabile' }}</span>
                                <span class="marketing-index-chip marketing-index-extra">{{ $promotion->targets_count }} target</span>
                            </div>
                        </div>

                        <div class="marketing-index-block">
                            <p class="marketing-index-copy">{{ $promotion->slug }}</p>
                            <div class="marketing-index-meta marketing-index-extra">
                                <span>Dal {{ $promotion->schedule_at?->format('d/m/Y H:i') ?? '-' }}</span>
                                <span>Al {{ $promotion->expiring_at?->format('d/m/Y H:i') ?? '-' }}</span>
                            </div>
                        </div>

                        <div class="marketing-index-stats">
                            <div class="marketing-index-stat-row">
                                <span class="marketing-index-stat">
                                    <strong>{{ $discountLabel }}</strong>
                                    <span>{{ $discountType }}</span>
                                </span>
                            </div>
                            <div class="marketing-index-meta marketing-index-extra">
                                <span>{{ $promotion->total_activation }} coinvolti</span>
                                <span>{{ $promotion->total_sent }} invii</span>
                                <span>{{ $promotion->total_used }} usi</span>
                            </div>
                        </div>

                        <div class="marketing-index-actions">
                            <a class="order-detail__contact" href="{{ route('admin.promotions.show', $promotion) }}">
                                <i class="bi bi-eye-fill"></i>
                                <span>Apri</span>
                            </a>
                            <a class="order-detail__contact" href="{{ route('admin.promotions.edit', $promotion) }}">
                                <i class="bi bi-pencil-square"></i>
                                <span>Modifica</span>
                            </a>
                            @if (in_array($promotion->status, ['draft', 'paused'], true))
                                <form class="marketing-index-secondary" action="{{ route('admin.promotions.publish', $promotion) }}" method="POST">
                                    @csrf
                                    <button class="order-detail__contact" type="submit">
                                        <i class="bi bi-check2-circle"></i>
                                        <span>Attiva</span>
                                    </button>
                                </form>
                            @endif
                            @if ($promotion->status === 'active')
                                <form class="marketing-index-secondary" action="{{ route('admin.promotions.pause', $promotion) }}" method="POST">
                                    @csrf
                                    <button class="order-detail__contact marketing-index-muted" type="submit">
                                        <i class="bi bi-pause-circle"></i>
                                        <span>Pausa</span>
                                    </button>
                                </form>
                            @endif
                            @if ($promotion->status !== 'archived')
                                <form class="marketing-index-secondary" action="{{ route('admin.promotions.archive', $promotion) }}" method="POST">
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
                {{ $promotions->links() }}
            </div>
        @else
            <div class="dashboard-home__details-placeholder">
                <span class="dashboard-home__details-placeholder-icon">
                    <i class="bi bi-megaphone-fill"></i>
                </span>
                <div>
                    <strong>Nessuna promozione presente.</strong>
                    <p>Crea la prima regola promozionale per collegarla a campagne o automazioni.</p>
                </div>
            </div>
        @endif
    </section>
</div>

@endsection
