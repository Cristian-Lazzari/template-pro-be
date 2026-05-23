@extends('layouts.base')

@section('contents')

<div class="dash_page">
    @include('admin.Marketing.partials.breadcrumbs', [
        'items' => [
            ['label' => __('admin.nav.dashboard'), 'url' => route('admin.dashboard')],
            ['label' => __('admin.marketing.area_links.marketing'), 'url' => route('admin.marketing')],
            ['label' => __('admin.marketing.promotions.plural'), 'url' => route('admin.promotions.index')],
            ['label' => $promotion->name, 'url' => route('admin.promotions.show', $promotion)],
            ['label' => __('admin.common.edit')],
        ],
    ])

    <header class="menu-dashboard__hero order-detail__summary">
        <div class="order-detail__meta">
            <div class="order-detail__status">
                <span class="order-detail__status-icon order-detail__status-icon--warning">
                    <i class="bi bi-megaphone-fill"></i>
                </span>
                <strong>{{ __('admin.marketing.promotions.edit') }}</strong>
            </div>

            <h1 class="menu-dashboard__title">{{ __('admin.marketing.promotions.edit') }}</h1>
            <p>{{ __('admin.marketing.promotions.description') }}</p>
        </div>

        <div class="menu-dashboard__hero-actions dashboard-home__hero-actions">
            @include('admin.Marketing.partials.area-links', ['current' => 'promotions'])
        </div>
    </header>

    @include('admin.Promotions._form', [
        'action' => route('admin.promotions.update', $promotion),
        'method' => 'PUT',
    ])
</div>

@endsection
