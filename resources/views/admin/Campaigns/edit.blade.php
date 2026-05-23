@extends('layouts.base')

@section('contents')

<div class="dash_page">
    @include('admin.Marketing.partials.breadcrumbs', [
        'items' => [
            ['label' => __('admin.nav.dashboard'), 'url' => route('admin.dashboard')],
            ['label' => __('admin.marketing.area_links.marketing'), 'url' => route('admin.marketing')],
            ['label' => __('admin.marketing.campaigns.plural'), 'url' => route('admin.campaigns.index')],
            ['label' => $campaign->name, 'url' => route('admin.campaigns.show', $campaign)],
            ['label' => __('admin.marketing.campaigns.edit_breadcrumb')],
        ],
    ])

    <header class="menu-dashboard__hero order-detail__summary">
        <div class="order-detail__meta">
            <div class="order-detail__status">
                <span class="order-detail__status-icon order-detail__status-icon--warning">
                    <i class="bi bi-envelope-paper-fill"></i>
                </span>
                <strong>{{ __('admin.marketing.campaigns.edit') }}</strong>
            </div>

            <h1 class="menu-dashboard__title">{{ __('admin.marketing.campaigns.edit') }}</h1>
            <p>{{ __('admin.marketing.campaigns.description') }}</p>
        </div>

        <div class="menu-dashboard__hero-actions dashboard-home__hero-actions">
            @include('admin.Marketing.partials.area-links', ['current' => 'campaigns'])
        </div>
    </header>

    @include('admin.Campaigns._form', [
        'action' => route('admin.campaigns.update', $campaign),
        'method' => 'PUT',
    ])
</div>

@endsection
