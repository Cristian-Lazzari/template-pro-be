@extends('layouts.base')

@section('contents')

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show dashboard-home__flash" role="alert">
        {{ session('success') }}
    </div>
@endif

<div class="dash_page">
    @include('admin.Marketing.partials.breadcrumbs', [
        'items' => [
            ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['label' => 'Marketing', 'url' => route('admin.marketing')],
            ['label' => 'Modelli mail'],
        ],
    ])

    <header class="menu-dashboard__hero order-detail__summary">
        <div class="order-detail__meta">
            <div class="order-detail__status">
                <span class="order-detail__status-icon order-detail__status-icon--active">
                    <x-icon name="file-earmark-richtext-fill" />
                </span>
                <strong>Marketing</strong>
            </div>

            <h1 class="menu-dashboard__title">Modelli mail</h1>
            <p>Template email collegabili a campagne e automazioni.</p>
        </div>

        <div class="menu-dashboard__hero-actions dashboard-home__hero-actions">
            <a href="{{ route('admin.customers.mail_models.create') }}" class="order-detail__contact">
                <x-icon name="plus-circle-fill" />
                <span>Crea nuovo</span>
            </a>
            @include('admin.Marketing.partials.area-links', ['current' => 'models'])
        </div>
    </header>

    <section class="order-detail__section mt-4">
        <div class="order-detail__section-head">
            <h3>
                <span class="order-detail__section-icon">
                    <x-icon name="list-check" />
                </span>
                Elenco modelli
            </h3>
        </div>

        @if ($models->count() > 0)
            <div class="menu-dashboard__promo-grid">
                @foreach ($models as $mailModel)
                    @php
                        $status = $mailModel->status ?: 'draft';
                        $bodyPreview = trim(strip_tags($mailModel->body_html ?: $mailModel->body ?: ''));
                    @endphp

                    <article class="menu-dashboard__promo-card">
                        <div class="menu-dashboard__promo-banner">
                            <span class="order-detail__section-icon">
                                <x-icon name="file-earmark-richtext-fill" />
                            </span>
                            <div>
                                <span class="menu-dashboard__banner-eyebrow">Modello mail</span>
                                @include('admin.Marketing.partials.status-pill', [
                                    'status' => $status,
                                    'label' => ucfirst($status),
                                ])
                            </div>
                        </div>

                        <div class="menu-dashboard__promo-body">
                            <div class="menu-dashboard__promo-top">
                                <div class="menu-dashboard__promo-intro">
                                    <div class="menu-dashboard__chip-row">
                                        <span class="menu-dashboard__chip">{{ $mailModel->channel ?: 'email' }}</span>
                                        <span class="menu-dashboard__chip menu-dashboard__chip--accent">{{ $mailModel->type ?: 'marketing' }}</span>
                                    </div>

                                    <h4>{{ $mailModel->name }}</h4>
                                    <p class="menu-dashboard__copy">{{ $mailModel->object ?: 'Oggetto non definito' }}</p>
                                </div>

                                <div class="menu-dashboard__price-block">
                                    <span class="menu-dashboard__price">{{ ($mailModel->campaigns_count ?? 0) + ($mailModel->automations_count ?? 0) }}</span>
                                    <small>utilizzi</small>
                                </div>
                            </div>

                            <div class="menu-dashboard__detail-list">
                                <span>Contenuto</span>
                                <div class="menu-dashboard__pill-row">
                                    <small>Heading: {{ $mailModel->heading ?: '-' }}</small>
                                    <small>Mittente: {{ $mailModel->sender ?: '-' }}</small>
                                </div>
                            </div>

                            @if ($bodyPreview !== '')
                                <p class="menu-dashboard__copy">{{ \Illuminate\Support\Str::limit($bodyPreview, 180) }}</p>
                            @endif

                            <div class="menu-dashboard__detail-list">
                                <span>Collegamenti</span>
                                <div class="menu-dashboard__pill-row">
                                    <small>{{ $mailModel->campaigns_count ?? 0 }} campagne</small>
                                    <small>{{ $mailModel->automations_count ?? 0 }} automazioni</small>
                                    <small>Aggiornato: {{ $mailModel->updated_at?->format('d/m/Y H:i') ?? '-' }}</small>
                                </div>
                            </div>

                            <div class="menu-dashboard__hero-actions dashboard-home__hero-actions mt-3">
                                <a class="order-detail__contact" href="{{ route('admin.customers.mail_models.edit', $mailModel->id) }}">
                                    <x-icon name="pencil-square" />
                                    <span>Modifica</span>
                                </a>
                                <form action="{{ route('admin.customers.mail_models.delete', $mailModel->id) }}" method="POST" style="margin: 0;">
                                    @csrf
                                    @method('DELETE')
                                    <button class="order-detail__contact" type="submit">
                                        <x-icon name="trash-fill" />
                                        <span>Elimina</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="d-flex justify-content-center mt-3">
                {{ $models->links() }}
            </div>
        @else
            <div class="dashboard-home__details-placeholder">
                <span class="dashboard-home__details-placeholder-icon">
                    <x-icon name="file-earmark-richtext-fill" />
                </span>
                <div>
                    <strong>Nessun modello mail presente.</strong>
                    <p>Crea un template da collegare a campagne o automazioni.</p>
                </div>
            </div>
        @endif
    </section>
</div>

@endsection
