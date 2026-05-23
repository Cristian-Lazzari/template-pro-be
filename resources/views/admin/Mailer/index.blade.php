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
            ['label' => __('admin.marketing.mailer.plural')],
        ],
    ])

    <header class="menu-dashboard__hero order-detail__summary">
        <div class="order-detail__meta">
            <div class="order-detail__status">
                <span class="order-detail__status-icon order-detail__status-icon--active">
                    <x-icon name="file-earmark-richtext-fill" />
                </span>
                <strong>{{ __('admin.marketing.area_links.marketing') }}</strong>
            </div>

            <h1 class="menu-dashboard__title">{{ __('admin.marketing.mailer.plural') }}</h1>
            <p>{{ __('admin.marketing.mailer.description') }}</p>
        </div>

        <div class="menu-dashboard__hero-actions dashboard-home__hero-actions">
            <a href="{{ route('admin.customers.mail_models.create') }}" class="order-detail__contact">
                <x-icon name="plus-circle-fill" />
                <span>{{ __('admin.marketing.mailer.create_new') }}</span>
            </a>
            @include('admin.Marketing.partials.area-links', ['current' => 'models'])
        </div>
    </header>

    <section class="order-detail__section mt-4">
        <div class="order-detail__section-head">
            <h3>
                <span class="order-detail__section-icon">
                    <x-icon name="list-check" />
                </span>
                {{ __('admin.marketing.mailer.list_title') }}
            </h3>
        </div>

        @if ($models->count() > 0)
            <div class="marketing-index-list">
                @foreach ($models as $mailModel)
                    @php
                        $status = $mailModel->status ?: 'draft';
                        $statusLabels = [
                            'draft' => __('admin.marketing.mailer.draft'),
                            'active' => __('admin.marketing.mailer.active'),
                            'archived' => __('admin.marketing.mailer.archived'),
                        ];
                        $bodyPreview = trim(strip_tags($mailModel->body_html ?: $mailModel->body ?: ''));
                        $usageCount = (int) ($mailModel->campaigns_count ?? 0) + (int) ($mailModel->automations_count ?? 0);
                    @endphp

                    <article class="marketing-index-row">
                        <div class="marketing-index-main">
                            <div class="marketing-index-kicker">
                                <x-icon name="file-earmark-richtext-fill" />
                                <span>{{ __('admin.marketing.mailer.mail_model') }}</span>
                                @include('admin.Marketing.partials.status-pill', [
                                    'status' => $status,
                                    'label' => $statusLabels[$status] ?? ucfirst($status),
                                ])
                            </div>

                            <h4 class="marketing-index-title">{{ $mailModel->name }}</h4>

                            <div class="marketing-index-meta">
                                <span class="marketing-index-chip">{{ $mailModel->channel ?: 'email' }}</span>
                                <span class="marketing-index-chip marketing-index-chip--accent">{{ $mailModel->type ?: 'marketing' }}</span>
                            </div>
                        </div>

                        <div class="marketing-index-block">
                            <p class="marketing-index-copy">{{ $mailModel->object ?: __('admin.marketing.mailer.undefined_object') }}</p>
                            <div class="marketing-index-meta marketing-index-extra">
                                <span>{{ __('admin.marketing.mailer.heading') }}: {{ $mailModel->heading ?: '-' }}</span>
                                <span>{{ __('admin.marketing.mailer.sender') }}: {{ $mailModel->sender ?: '-' }}</span>
                            </div>
                            @if ($bodyPreview !== '')
                                <p class="marketing-index-copy marketing-index-extra">{{ \Illuminate\Support\Str::limit($bodyPreview, 130) }}</p>
                            @endif
                        </div>

                        <div class="marketing-index-stats">
                            <div class="marketing-index-stat-row">
                                <span class="marketing-index-stat">
                                    <strong>{{ $usageCount }}</strong>
                                    <span>{{ __('admin.marketing.mailer.usage_count') }}</span>
                                </span>
                            </div>
                            <div class="marketing-index-meta marketing-index-extra">
                                <span>{{ __('admin.marketing.mailer.campaigns_count', ['count' => $mailModel->campaigns_count ?? 0]) }}</span>
                                <span>{{ __('admin.marketing.mailer.automations_count', ['count' => $mailModel->automations_count ?? 0]) }}</span>
                                <span>{{ __('admin.marketing.mailer.updated_at', ['date' => $mailModel->updated_at?->format('d/m/Y H:i') ?? '-']) }}</span>
                            </div>
                        </div>

                        <div class="marketing-index-actions">
                            <a class="order-detail__contact" href="{{ route('admin.customers.mail_models.edit', $mailModel->id) }}">
                                <x-icon name="pencil-square" />
                                <span>{{ __('admin.common.edit') }}</span>
                            </a>
                            <form class="marketing-index-secondary" action="{{ route('admin.customers.mail_models.delete', $mailModel->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button class="order-detail__contact marketing-index-danger" type="submit">
                                    <x-icon name="trash-fill" />
                                    <span>{{ __('admin.common.delete') }}</span>
                                </button>
                            </form>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="marketing-index-pager">
                {{ $models->links() }}
            </div>
        @else
            <div class="dashboard-home__details-placeholder">
                <span class="dashboard-home__details-placeholder-icon">
                    <x-icon name="file-earmark-richtext-fill" />
                </span>
                <div>
                    <strong>{{ __('admin.marketing.mailer.no_models') }}</strong>
                    <p>{{ __('admin.marketing.mailer.empty_text') }}</p>
                </div>
            </div>
        @endif
    </section>
</div>

@endsection
