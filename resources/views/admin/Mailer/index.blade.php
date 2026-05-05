@extends('layouts.base')

@section('contents')

@if (session('success'))
    <div class="dashboard-home__flash" role="alert">
        {{ session('success') }}
    </div>
@endif

<div class="dash_page marketing-index-page">
    @include('admin.Marketing.partials.index-style')

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
            <div class="marketing-index-list">
                @foreach ($models as $mailModel)
                    @php
                        $status = $mailModel->status ?: 'draft';
                        $bodyPreview = trim(strip_tags($mailModel->body_html ?: $mailModel->body ?: ''));
                        $usageCount = (int) ($mailModel->campaigns_count ?? 0) + (int) ($mailModel->automations_count ?? 0);
                    @endphp

                    <article class="marketing-index-row">
                        <div class="marketing-index-main">
                            <div class="marketing-index-kicker">
                                <x-icon name="file-earmark-richtext-fill" />
                                <span>Modello mail</span>
                                @include('admin.Marketing.partials.status-pill', [
                                    'status' => $status,
                                    'label' => ucfirst($status),
                                ])
                            </div>

                            <h4 class="marketing-index-title">{{ $mailModel->name }}</h4>

                            <div class="marketing-index-meta">
                                <span class="marketing-index-chip">{{ $mailModel->channel ?: 'email' }}</span>
                                <span class="marketing-index-chip marketing-index-chip--accent">{{ $mailModel->type ?: 'marketing' }}</span>
                            </div>
                        </div>

                        <div class="marketing-index-block">
                            <p class="marketing-index-copy">{{ $mailModel->object ?: 'Oggetto non definito' }}</p>
                            <div class="marketing-index-meta marketing-index-extra">
                                <span>Heading: {{ $mailModel->heading ?: '-' }}</span>
                                <span>Mittente: {{ $mailModel->sender ?: '-' }}</span>
                            </div>
                            @if ($bodyPreview !== '')
                                <p class="marketing-index-copy marketing-index-extra">{{ \Illuminate\Support\Str::limit($bodyPreview, 130) }}</p>
                            @endif
                        </div>

                        <div class="marketing-index-stats">
                            <div class="marketing-index-stat-row">
                                <span class="marketing-index-stat">
                                    <strong>{{ $usageCount }}</strong>
                                    <span>utilizzi</span>
                                </span>
                            </div>
                            <div class="marketing-index-meta marketing-index-extra">
                                <span>{{ $mailModel->campaigns_count ?? 0 }} campagne</span>
                                <span>{{ $mailModel->automations_count ?? 0 }} automazioni</span>
                                <span>Aggiornato: {{ $mailModel->updated_at?->format('d/m/Y H:i') ?? '-' }}</span>
                            </div>
                        </div>

                        <div class="marketing-index-actions">
                            <a class="order-detail__contact" href="{{ route('admin.customers.mail_models.edit', $mailModel->id) }}">
                                <x-icon name="pencil-square" />
                                <span>Modifica</span>
                            </a>
                            <form class="marketing-index-secondary" action="{{ route('admin.customers.mail_models.delete', $mailModel->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button class="order-detail__contact marketing-index-danger" type="submit">
                                    <x-icon name="trash-fill" />
                                    <span>Elimina</span>
                                </button>
                            </form>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="marketing-index-pager">
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
