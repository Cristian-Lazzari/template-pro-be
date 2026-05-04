@extends('layouts.base')

@section('contents')

<div class="dash_page">
    @include('admin.Marketing.partials.breadcrumbs', [
        'items' => [
            ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['label' => 'Marketing', 'url' => route('admin.marketing')],
            ['label' => 'Promozioni', 'url' => route('admin.promotions.index')],
            ['label' => 'Crea'],
        ],
    ])

    <header class="menu-dashboard__hero order-detail__summary">
        <div class="order-detail__meta">
            <div class="order-detail__status">
                <span class="order-detail__status-icon order-detail__status-icon--active">
                    <i class="bi bi-megaphone-fill"></i>
                </span>
                <strong>Nuova regola</strong>
            </div>

            <h1 class="menu-dashboard__title">Crea promozione</h1>
            <p>Definisci sconto, validita e CTA della promozione.</p>
        </div>

        <div class="menu-dashboard__hero-actions dashboard-home__hero-actions">
            <a class="order-detail__contact" href="{{ route('admin.promotions.index') }}">
                <i class="bi bi-arrow-left"></i>
                <span>Annulla</span>
            </a>
            @include('admin.Marketing.partials.area-links', ['current' => 'promotions'])
        </div>
    </header>

    @include('admin.Promotions._form', [
        'action' => route('admin.promotions.store'),
        'method' => 'POST',
        'submitLabel' => 'Crea promozione',
    ])
</div>

@endsection
