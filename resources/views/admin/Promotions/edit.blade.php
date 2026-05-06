@extends('layouts.base')

@section('contents')

<div class="dash_page">
    @include('admin.Marketing.partials.breadcrumbs', [
        'items' => [
            ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['label' => 'Marketing', 'url' => route('admin.marketing')],
            ['label' => 'Promozioni', 'url' => route('admin.promotions.index')],
            ['label' => $promotion->name, 'url' => route('admin.promotions.show', $promotion)],
            ['label' => 'Modifica'],
        ],
    ])

    <header class="menu-dashboard__hero order-detail__summary">
        <div class="order-detail__meta">
            <div class="order-detail__status">
                <span class="order-detail__status-icon order-detail__status-icon--warning">
                    <i class="bi bi-megaphone-fill"></i>
                </span>
                <strong>Modifica promozione</strong>
            </div>

            <h1 class="menu-dashboard__title">Modifica promozione</h1>
            <p>Definisci regola, validità e target della promozione.</p>
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
