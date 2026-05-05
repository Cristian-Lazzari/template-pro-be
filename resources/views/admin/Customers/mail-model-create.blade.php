@extends('layouts.base')

@section('contents')

<div class="dash_page">
    @include('admin.Marketing.partials.breadcrumbs', [
        'items' => [
            ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['label' => 'Marketing', 'url' => route('admin.marketing')],
            ['label' => 'Modelli mail', 'url' => route('admin.customers.mail_models.index')],
            ['label' => 'Crea'],
        ],
    ])

    <header class="menu-dashboard__hero order-detail__summary">
        <div class="order-detail__meta">
            <div class="order-detail__status">
                <span class="order-detail__status-icon order-detail__status-icon--active">
                    <x-icon name="file-earmark-richtext-fill" />
                </span>
                <strong>Nuovo modello mail</strong>
            </div>

            <h1 class="menu-dashboard__title">Crea modello mail</h1>
            <p>Prepara il contenuto che campagne e automazioni useranno per inviare email marketing.</p>
        </div>

        <div class="menu-dashboard__hero-actions dashboard-home__hero-actions">
            <a class="order-detail__contact" href="{{ route('admin.customers.mail_models.index') }}">
                <x-icon name="arrow-left-circle-fill" />
                <span>Annulla</span>
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
