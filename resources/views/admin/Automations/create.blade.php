@extends('layouts.base')

@section('contents')

<div class="dash_page">
    @include('admin.Marketing.partials.breadcrumbs', [
        'items' => [
            ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['label' => 'Marketing', 'url' => route('admin.marketing')],
            ['label' => 'Automazioni', 'url' => route('admin.automations.index')],
            ['label' => 'Crea'],
        ],
    ])

    <header class="menu-dashboard__hero order-detail__summary">
        <div class="order-detail__meta">
            <div class="order-detail__status">
                <span class="order-detail__status-icon order-detail__status-icon--active">
                    <i class="bi bi-lightning-charge-fill"></i>
                </span>
                <strong>Nuova automazione</strong>
            </div>

            <h1 class="menu-dashboard__title">Crea automazione</h1>
            <p>Configura trigger, modello mail e promozioni collegate.</p>
        </div>

        <div class="menu-dashboard__hero-actions dashboard-home__hero-actions">
            <a class="order-detail__contact" href="{{ route('admin.automations.index') }}">
                <i class="bi bi-arrow-left"></i>
                <span>Annulla</span>
            </a>
            @include('admin.Marketing.partials.area-links', ['current' => 'automations'])
        </div>
    </header>

    @include('admin.Automations._form', [
        'action' => route('admin.automations.store'),
        'method' => 'POST',
    ])
</div>

@endsection
