@extends('layouts.base')

@section('contents')

<div class="dash_page">
    @include('admin.Marketing.partials.breadcrumbs', [
        'items' => [
            ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['label' => 'Marketing', 'url' => route('admin.marketing')],
            ['label' => 'Campagne', 'url' => route('admin.campaigns.index')],
            ['label' => 'Crea'],
        ],
    ])

    <header class="menu-dashboard__hero order-detail__summary">
        <div class="order-detail__meta">
            <div class="order-detail__status">
                <span class="order-detail__status-icon order-detail__status-icon--active">
                    <i class="bi bi-envelope-paper-fill"></i>
                </span>
                <strong>Nuova campagna</strong>
            </div>

            <h1 class="menu-dashboard__title">Crea campagna</h1>
            <p>Collega segmento, modello mail e promozioni da preparare.</p>
        </div>

        <div class="menu-dashboard__hero-actions dashboard-home__hero-actions">
            <a class="order-detail__contact" href="{{ route('admin.campaigns.index') }}">
                <i class="bi bi-arrow-left"></i>
                <span>Annulla</span>
            </a>
            @include('admin.Marketing.partials.area-links', ['current' => 'campaigns'])
        </div>
    </header>

    @include('admin.Campaigns._form', [
        'action' => route('admin.campaigns.store'),
        'method' => 'POST',
        'submitLabel' => 'Crea campagna',
    ])
</div>

@endsection
