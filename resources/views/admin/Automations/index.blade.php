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
            ['label' => __('admin.nav.dashboard'), 'url' => route('admin.dashboard')],
            ['label' => __('admin.marketing.area_links.marketing'), 'url' => route('admin.marketing')],
            ['label' => __('admin.marketing.automations.plural')],
        ],
    ])

    <header class="menu-dashboard__hero order-detail__summary">
        <div class="order-detail__meta">
            <div class="order-detail__status">
                <span class="order-detail__status-icon order-detail__status-icon--active">
                    <i class="bi bi-lightning-charge-fill"></i>
                </span>
                <strong>{{ __('admin.marketing.area_links.marketing') }}</strong>
            </div>

            <h1 class="menu-dashboard__title">{{ __('admin.marketing.automations.plural') }}</h1>
            <p>{{ __('admin.marketing.automations.index_description') }}</p>
        </div>

        <div class="menu-dashboard__hero-actions dashboard-home__hero-actions">
            <a href="{{ route('admin.automations.create') }}" class="order-detail__contact">
                <i class="bi bi-cloud-plus-fill"></i>
                <span>{{ __('admin.marketing.automations.create_new') }}</span>
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
                {{ __('admin.marketing.automations.list_title') }}
            </h3>
        </div>

        @if ($automations->count() > 0)
            <div class="marketing-index-list">
                @foreach ($automations as $automation)
                    <article class="marketing-index-row">
                        <div class="marketing-index-main">
                            <div class="marketing-index-kicker">
                                <i class="bi bi-lightning-charge-fill"></i>
                                <span>{{ __('admin.marketing.automations.singular') }}</span>
                                @include('admin.Marketing.partials.status-pill', [
                                    'status' => $automation->status,
                                    'label' => $statuses[$automation->status] ?? $automation->status,
                                ])
                            </div>

                            <h4 class="marketing-index-title">{{ $automation->name }}</h4>

                            <div class="marketing-index-meta">
                                <span class="marketing-index-chip">{{ $triggers[$automation->trigger] ?? ($automation->trigger ?: __('admin.marketing.automations.undefined_trigger')) }}</span>
                                <span class="marketing-index-chip marketing-index-chip--accent">{{ __('admin.marketing.automations.promo_count', ['count' => $automation->promotions->count()]) }}</span>
                            </div>
                        </div>

                        <div class="marketing-index-block">
                            <p class="marketing-index-copy">{{ __('admin.marketing.automations.model') }}: {{ $automation->model?->name ?? '-' }}</p>
                            <div class="marketing-index-meta marketing-index-extra">
                                @forelse ($automation->promotions as $promotion)
                                    <span>{{ $promotion->name }}</span>
                                @empty
                                    <span>{{ __('admin.marketing.automations.no_promotion') }}</span>
                                @endforelse
                            </div>
                        </div>

                        <div class="marketing-index-stats">
                            <div class="marketing-index-stat-row">
                                <span class="marketing-index-stat">
                                    <strong>{{ $automation->total_activation }}</strong>
                                    <span>{{ __('admin.marketing.automations.involved') }}</span>
                                </span>
                                <span class="marketing-index-stat">
                                    <strong>{{ $automation->total_sent }}</strong>
                                    <span>{{ __('admin.marketing.automations.sent') }}</span>
                                </span>
                            </div>
                            <div class="marketing-index-meta marketing-index-extra">
                                <span>{{ __('admin.marketing.automations.cooldown') }}: {{ data_get($automation->metadata, 'cooldown_days') ?? '-' }}</span>
                                <span>{{ __('admin.marketing.automations.last_run') }}: {{ $automation->last_run_at?->format('d/m/Y H:i') ?? '-' }}</span>
                            </div>
                        </div>

                        <div class="marketing-index-actions">
                            <a class="order-detail__contact" href="{{ route('admin.automations.show', $automation) }}">
                                <i class="bi bi-eye-fill"></i>
                                <span>{{ __('admin.marketing.automations.open') }}</span>
                            </a>
                            <a class="order-detail__contact" href="{{ route('admin.automations.edit', $automation) }}">
                                <i class="bi bi-pencil-square"></i>
                                <span>{{ __('admin.common.edit') }}</span>
                            </a>
                            @if (in_array($automation->status, ['draft', 'paused'], true))
                                <form class="marketing-index-secondary" action="{{ route('admin.automations.activate', $automation) }}" method="POST">
                                    @csrf
                                    <button class="order-detail__contact" type="submit">
                                        <i class="bi bi-check2-circle"></i>
                                        <span>{{ __('admin.marketing.automations.activate') }}</span>
                                    </button>
                                </form>
                            @endif
                            @if ($automation->status === 'active')
                                <form class="marketing-index-secondary" action="{{ route('admin.automations.pause', $automation) }}" method="POST">
                                    @csrf
                                    <button class="order-detail__contact marketing-index-muted" type="submit">
                                        <i class="bi bi-pause-circle"></i>
                                        <span>{{ __('admin.marketing.automations.pause') }}</span>
                                    </button>
                                </form>
                            @endif
                            @if ($automation->status !== 'archived')
                                <form class="marketing-index-secondary" action="{{ route('admin.automations.archive', $automation) }}" method="POST">
                                    @csrf
                                    <button class="order-detail__contact marketing-index-danger" type="submit">
                                        <i class="bi bi-archive-fill"></i>
                                        <span>{{ __('admin.marketing.automations.archive') }}</span>
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
                    <strong>{{ __('admin.marketing.automations.no_items') }}</strong>
                    <p>{{ __('admin.marketing.automations.empty_text_controlled') }}</p>
                </div>
            </div>
        @endif
    </section>
</div>

@endsection
