@extends('layouts.base')

@section('contents')

<div class="dash_page">
    @include('admin.Marketing.partials.breadcrumbs', [
        'items' => [
            ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['label' => 'Marketing', 'url' => route('admin.marketing')],
            ['label' => 'Automazioni', 'url' => route('admin.automations.index')],
            ['label' => $automation->name, 'url' => route('admin.automations.show', $automation)],
            ['label' => 'Modifica'],
        ],
    ])

    <header class="menu-dashboard__hero order-detail__summary">
        <div class="order-detail__meta">
            <div class="order-detail__status">
                <span class="order-detail__status-icon order-detail__status-icon--warning">
                    <i class="bi bi-lightning-charge-fill"></i>
                </span>
                <strong>Modifica automazione</strong>
            </div>

            <h1 class="menu-dashboard__title">Modifica automazione</h1>
            <p>Configura una regola automatica di marketing.</p>
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
