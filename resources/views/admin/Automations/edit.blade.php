@extends('layouts.base')

@section('contents')

<div class="dash_page">
    @include('admin.Marketing.partials.breadcrumbs', [
        'items' => [
            ['label' => __('admin.nav.dashboard'), 'url' => route('admin.dashboard')],
            ['label' => __('admin.marketing.area_links.marketing'), 'url' => route('admin.marketing')],
            ['label' => __('admin.marketing.automations.plural'), 'url' => route('admin.automations.index')],
            ['label' => $automation->name, 'url' => route('admin.automations.show', $automation)],
            ['label' => __('admin.marketing.automations.edit_breadcrumb')],
        ],
    ])

    <header class="menu-dashboard__hero order-detail__summary">
        <div class="order-detail__meta">
            <div class="order-detail__status">
                <span class="order-detail__status-icon order-detail__status-icon--warning">
                    <i class="bi bi-lightning-charge-fill"></i>
                </span>
                <strong>{{ __('admin.marketing.automations.edit') }}</strong>
            </div>

            <h1 class="menu-dashboard__title">{{ __('admin.marketing.automations.edit') }}</h1>
            <p>{{ __('admin.marketing.automations.description') }}</p>
        </div>

        <div class="menu-dashboard__hero-actions dashboard-home__hero-actions">
            @include('admin.Marketing.partials.area-links', ['current' => 'automations'])
        </div>
    </header>

    @include('admin.Automations._form', [
        'action' => route('admin.automations.update', $automation),
        'method' => 'PUT',
    ])
</div>

@endsection
