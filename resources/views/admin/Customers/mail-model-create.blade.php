@extends('layouts.base')

@section('contents')

<div class="dash_page">
    @include('admin.Marketing.partials.breadcrumbs', [
        'items' => [
            ['label' => __('admin.nav.dashboard'), 'url' => route('admin.dashboard')],
            ['label' => __('admin.marketing.area_links.marketing'), 'url' => route('admin.marketing')],
            ['label' => __('admin.marketing.mailer.plural'), 'url' => route('admin.customers.mail_models.index')],
            ['label' => __('admin.marketing.mailer.create_breadcrumb')],
        ],
    ])

    <header class="menu-dashboard__hero order-detail__summary">
        <div class="order-detail__meta">
            <div class="order-detail__status">
                <span class="order-detail__status-icon order-detail__status-icon--active">
                    <x-icon name="file-earmark-richtext-fill" />
                </span>
                <strong>{{ __('admin.marketing.mailer.new_model') }}</strong>
            </div>

            <h1 class="menu-dashboard__title">{{ __('admin.marketing.mailer.create_model') }}</h1>
            <p>{{ __('admin.marketing.mailer.create_description') }}</p>
        </div>

        <div class="menu-dashboard__hero-actions dashboard-home__hero-actions">
            <a class="order-detail__contact" href="{{ route('admin.customers.mail_models.index') }}">
                <x-icon name="arrow-left-circle-fill" />
                <span>{{ __('admin.common.cancel') }}</span>
            </a>
            @include('admin.Marketing.partials.area-links', ['current' => 'models'])
        </div>
    </header>

    @include('admin.Mailer._form', [
        'action' => route('admin.customers.mail_models.store'),
        'method' => 'POST',
    ])
</div>

@endsection
