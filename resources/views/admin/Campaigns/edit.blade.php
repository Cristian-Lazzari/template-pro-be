@extends('layouts.base')

@section('contents')

<div class="dash_page">
    @include('admin.Marketing.partials.breadcrumbs', [
        'items' => [
            ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['label' => 'Marketing', 'url' => route('admin.marketing')],
            ['label' => 'Campagne', 'url' => route('admin.campaigns.index')],
            ['label' => $campaign->name, 'url' => route('admin.campaigns.show', $campaign)],
            ['label' => 'Modifica'],
        ],
    ])

    <header class="menu-dashboard__hero order-detail__summary">
        <div class="order-detail__meta">
            <div class="order-detail__status">
                <span class="order-detail__status-icon order-detail__status-icon--warning">
                    <i class="bi bi-envelope-paper-fill"></i>
                </span>
                <strong>Modifica campagna</strong>
            </div>

            <h1 class="menu-dashboard__title">Modifica campagna</h1>
            <p>{{ $campaign->name }}</p>
        </div>

        <div class="menu-dashboard__hero-actions dashboard-home__hero-actions">
            <a class="order-detail__contact" href="{{ route('admin.campaigns.show', $campaign) }}">
                <i class="bi bi-arrow-left"></i>
                <span>Annulla</span>
            </a>
            @include('admin.Marketing.partials.area-links', ['current' => 'campaigns'])
        </div>
    </header>

    @include('admin.Campaigns._form', [
        'action' => route('admin.campaigns.update', $campaign),
        'method' => 'PUT',
        'submitLabel' => 'Salva modifiche',
    ])
</div>

@endsection
