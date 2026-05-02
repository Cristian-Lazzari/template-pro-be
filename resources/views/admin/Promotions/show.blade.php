@extends('layouts.base')

@section('contents')

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show dashboard-home__flash" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="dash_page">
    @include('admin.Marketing.partials.breadcrumbs', [
        'items' => [
            ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['label' => 'Marketing', 'url' => route('admin.campaigns.index')],
            ['label' => 'Promozioni', 'url' => route('admin.promotions.index')],
            ['label' => $promotion->name],
        ],
    ])

    <header class="menu-dashboard__hero order-detail__summary">
        <div class="order-detail__meta">
            <div class="order-detail__status">
                <span class="order-detail__status-icon order-detail__status-icon--active">
                    <i class="bi bi-megaphone-fill"></i>
                </span>
                @include('admin.Marketing.partials.status-pill', [
                    'status' => $promotion->status,
                    'label' => $statuses[$promotion->status] ?? $promotion->status,
                ])
            </div>

            <h1 class="menu-dashboard__title">{{ $promotion->name }}</h1>
            <p>Regola promozionale <code>{{ $promotion->slug }}</code> con report e contatori operativi.</p>
        </div>

        <div class="menu-dashboard__hero-actions dashboard-home__hero-actions">
            <a class="order-detail__contact" href="{{ route('admin.promotions.index') }}">
                <i class="bi bi-arrow-left"></i>
                <span>Lista</span>
            </a>
            <a class="order-detail__contact" href="{{ route('admin.promotions.edit', $promotion) }}">
                <i class="bi bi-pencil-square"></i>
                <span>Modifica</span>
            </a>
            @include('admin.Marketing.partials.area-links', ['current' => 'promotions'])
        </div>
    </header>

    <section class="order-detail__section mt-4">
        <div class="order-detail__section-head">
            <h3>
                <span class="order-detail__section-icon">
                    <i class="bi bi-toggles"></i>
                </span>
                Azioni stato
            </h3>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <form action="{{ route('admin.promotions.publish', $promotion) }}" method="POST">
                @csrf
                <button class="my_btn_2 w-auto" type="submit">
                    <i class="bi bi-check2-circle"></i>
                    Pubblica
                </button>
            </form>
            <form action="{{ route('admin.promotions.pause', $promotion) }}" method="POST">
                @csrf
                <button class="my_btn_5 w-auto" type="submit">
                    <i class="bi bi-pause-circle"></i>
                    Pausa
                </button>
            </form>
            <form action="{{ route('admin.promotions.archive', $promotion) }}" method="POST">
                @csrf
                <button class="my_btn_2 btn_delete w-auto" type="submit">
                    <i class="bi bi-archive-fill"></i>
                    Archivia
                </button>
            </form>
        </div>
    </section>

    <section class="order-detail__section mt-4">
        <div class="order-detail__section-head">
            <h3>
                <span class="order-detail__section-icon">
                    <i class="bi bi-info-circle-fill"></i>
                </span>
                Dettaglio promozione
            </h3>
        </div>

        <div class="split">
            <div>
                <p><strong>Slug:</strong> <code>{{ $promotion->slug }}</code></p>
                <p>
                    <strong>Status:</strong>
                    @include('admin.Marketing.partials.status-pill', [
                        'status' => $promotion->status,
                        'label' => $statuses[$promotion->status] ?? $promotion->status,
                    ])
                </p>
                <p><strong>Uso:</strong> {{ $caseUses[$promotion->case_use] ?? ($promotion->case_use ?: '-') }}</p>
                <p><strong>Tipo sconto:</strong> {{ $discountTypes[$promotion->type_discount] ?? ($promotion->type_discount ?: '-') }}</p>
                <p><strong>Sconto:</strong> {{ $promotion->discount !== null ? number_format((float) $promotion->discount, 2, ',', '.') : '-' }}</p>
                <p><strong>Minimo:</strong> {{ $promotion->minimum_pretest !== null ? number_format((float) $promotion->minimum_pretest, 2, ',', '.') : '-' }}</p>
                <p><strong>CTA:</strong> {{ $promotion->cta ?: '-' }}</p>
            </div>
            <div>
                <p>
                    <strong>Permanente:</strong>
                    <x-dashboard.state-pill :tone="$promotion->permanent ? 'active' : 'neutral'">
                        {{ $promotion->permanent ? 'Si' : 'No' }}
                    </x-dashboard.state-pill>
                </p>
                <p><strong>Programmata:</strong> {{ $promotion->schedule_at?->format('d/m/Y H:i') ?? '-' }}</p>
                <p><strong>Scadenza:</strong> {{ $promotion->expiring_at?->format('d/m/Y H:i') ?? '-' }}</p>
                <p>
                    <strong>Riusabile:</strong>
                    <x-dashboard.state-pill :tone="data_get($promotion->metadata, 'reusable') === true ? 'active' : 'neutral'">
                        {{ data_get($promotion->metadata, 'reusable') === true ? 'Si' : 'No' }}
                    </x-dashboard.state-pill>
                </p>
                <p><strong>Creata:</strong> {{ $promotion->created_at?->format('d/m/Y H:i') ?? '-' }}</p>
                <p><strong>Aggiornata:</strong> {{ $promotion->updated_at?->format('d/m/Y H:i') ?? '-' }}</p>
            </div>
        </div>
    </section>

    @include('admin.Marketing.partials.report-metrics', ['report' => $report])
</div>

@endsection
