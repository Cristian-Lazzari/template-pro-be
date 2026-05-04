@extends('layouts.base')

@section('contents')

@if (session('success'))
    <div class="dashboard-home__flash" role="alert">
        {{ session('success') }}
    </div>
@endif

@php
    $tone = match ($promotion->status) {
        'active' => 'active',
        'archived' => 'off',
        default => 'warning',
    };

    $icon = match ($tone) {
        'active' => 'bi-check-circle-fill',
        'off' => 'bi-x-circle-fill',
        default => 'bi-exclamation-circle-fill',
    };

    $discountValue = $promotion->discount !== null
        ? number_format((float) $promotion->discount, 2, ',', '.')
        : '-';
    $minimumValue = $promotion->minimum_pretest !== null
        ? number_format((float) $promotion->minimum_pretest, 2, ',', '.')
        : '-';
    $isReusable = data_get($promotion->metadata, 'reusable') === true;
@endphp

<div class="dash_page">
    @include('admin.Marketing.partials.breadcrumbs', [
        'items' => [
            ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['label' => 'Marketing', 'url' => route('admin.marketing')],
            ['label' => 'Promozioni', 'url' => route('admin.promotions.index')],
            ['label' => $promotion->name],
        ],
    ])

    @include('admin.Marketing.partials.show-style')

    <div class="marketing-detail-page">
        <article class="order-detail order-detail--{{ $tone }}">
            <header class="order-detail__header">
                <div class="order-detail__status">
                    <span class="order-detail__status-icon order-detail__status-icon--{{ $tone }}">
                        <i class="bi {{ $icon }}"></i>
                    </span>
                    @include('admin.Marketing.partials.status-pill', [
                        'status' => $promotion->status,
                        'label' => $statuses[$promotion->status] ?? $promotion->status,
                    ])
                </div>

                <div class="order-detail__contacts">
                    <a class="order-detail__contact" href="{{ route('admin.promotions.index') }}">
                        <i class="bi bi-arrow-left"></i>
                        <span>Lista</span>
                    </a>
                    <a class="order-detail__contact" href="{{ route('admin.promotions.edit', $promotion) }}">
                        <i class="bi bi-pencil-square"></i>
                        <span>Modifica</span>
                    </a>
                    <a class="order-detail__contact" href="{{ route('admin.marketing') }}">
                        <i class="bi bi-grid-1x2-fill"></i>
                        <span>Marketing</span>
                    </a>
                </div>
            </header>

            <div class="order-detail__body">
                <section class="order-detail__summary">
                    <div class="order-detail__meta">
                        <p class="order-detail__code">#PROMO {{ $promotion->id }}</p>
                        <p class="order-detail__time">{{ $promotion->name }}</p>
                        <p class="order-detail__date">{{ $promotion->slug }}</p>
                    </div>

                    <div class="order-detail__customer">
                        <span>{{ $caseUses[$promotion->case_use] ?? ($promotion->case_use ?: 'Generica') }}</span>
                        <small>Aggiornata {{ $promotion->updated_at?->format('d/m/Y H:i') ?? '-' }}</small>
                    </div>
                </section>

                <section class="order-detail__section">
                    <div class="order-detail__section-head">
                        <h3>
                            <span class="order-detail__section-icon">
                                <i class="bi bi-info-circle-fill"></i>
                            </span>
                            Dettaglio promozione
                        </h3>
                    </div>

                    <div class="marketing-detail__grid">
                        <article class="marketing-detail__fact">
                            <span>Slug</span>
                            <strong>{{ $promotion->slug }}</strong>
                        </article>
                        <article class="marketing-detail__fact">
                            <span>Uso</span>
                            <strong>{{ $caseUses[$promotion->case_use] ?? ($promotion->case_use ?: '-') }}</strong>
                        </article>
                        <article class="marketing-detail__fact">
                            <span>Sconto</span>
                            <strong>{{ $discountValue }}</strong>
                            <small>{{ $discountTypes[$promotion->type_discount] ?? ($promotion->type_discount ?: '-') }}</small>
                        </article>
                        <article class="marketing-detail__fact">
                            <span>Minimo</span>
                            <strong>{{ $minimumValue }}</strong>
                        </article>
                        <article class="marketing-detail__fact">
                            <span>CTA</span>
                            <strong>{{ $promotion->cta ?: '-' }}</strong>
                        </article>
                        <article class="marketing-detail__fact">
                            <span>Periodo</span>
                            <strong>{{ $promotion->permanent ? 'Permanente' : 'Programmabile' }}</strong>
                            <small>{{ $promotion->schedule_at?->format('d/m/Y H:i') ?? '-' }} / {{ $promotion->expiring_at?->format('d/m/Y H:i') ?? '-' }}</small>
                        </article>
                        <article class="marketing-detail__fact">
                            <span>Riusabile</span>
                            <strong>{{ $isReusable ? 'Si' : 'No' }}</strong>
                        </article>
                        <article class="marketing-detail__fact">
                            <span>Creata</span>
                            <strong>{{ $promotion->created_at?->format('d/m/Y H:i') ?? '-' }}</strong>
                        </article>
                    </div>
                </section>

                <section class="order-detail__section">
                    <div class="order-detail__section-head">
                        <h3>
                            <span class="order-detail__section-icon">
                                <i class="bi bi-bullseye"></i>
                            </span>
                            Target promozione
                        </h3>
                    </div>

                    @if ($promotion->targets->count() > 0)
                        <div class="marketing-detail__linked-grid">
                            @foreach ($promotion->targets as $target)
                                @php
                                    $targetKey = $target->target_type . ':' . ($target->target_id ?? '');
                                    $targetLabel = $targetLabels[$targetKey] ?? (($targetTypes[$target->target_type] ?? 'Target') . ($target->target_id ? ' #' . $target->target_id : ''));
                                    $targetDiscount = $target->discount !== null
                                        ? number_format((float) $target->discount, 2, ',', '.')
                                        : 'Sconto principale';
                                    $targetDiscountType = $target->type_discount
                                        ? ($discountTypes[$target->type_discount] ?? $target->type_discount)
                                        : 'Tipo principale';
                                @endphp

                                <article class="marketing-detail__linked-card">
                                    <span>{{ $targetTypes[$target->target_type] ?? $target->target_type }}</span>
                                    <strong>{{ $targetLabel }}</strong>
                                    <small>Sconto: {{ $targetDiscount }}</small>
                                    <small>Tipo: {{ $targetDiscountType }}</small>
                                </article>
                            @endforeach
                        </div>
                    @else
                        <div class="marketing-detail__empty">
                            <strong>Nessun target collegato.</strong>
                        </div>
                    @endif
                </section>

                <section class="order-detail__section">
                    <div class="order-detail__section-head">
                        <h3>
                            <span class="order-detail__section-icon">
                                <i class="bi bi-speedometer2"></i>
                            </span>
                            Contatori
                        </h3>
                    </div>

                    <div class="marketing-detail__compact-grid">
                        <article class="marketing-detail__metric">
                            <span>Coinvolti</span>
                            <strong>{{ $promotion->total_activation }}</strong>
                        </article>
                        <article class="marketing-detail__metric">
                            <span>Inviate</span>
                            <strong>{{ $promotion->total_sent }}</strong>
                        </article>
                        <article class="marketing-detail__metric">
                            <span>Usate</span>
                            <strong>{{ $promotion->total_used }}</strong>
                        </article>
                    </div>
                </section>

                <section class="order-detail__section">
                    <div class="order-detail__section-head">
                        <h3>
                            <span class="order-detail__section-icon">
                                <i class="bi bi-toggles"></i>
                            </span>
                            Azioni stato
                        </h3>
                    </div>

                    <div class="marketing-detail__actions">
                        <form action="{{ route('admin.promotions.publish', $promotion) }}" method="POST">
                            @csrf
                            <button class="order-detail__contact" type="submit">
                                <i class="bi bi-check2-circle"></i>
                                <span>Pubblica</span>
                            </button>
                        </form>
                        <form action="{{ route('admin.promotions.pause', $promotion) }}" method="POST">
                            @csrf
                            <button class="order-detail__contact marketing-detail__contact--muted" type="submit">
                                <i class="bi bi-pause-circle"></i>
                                <span>Pausa</span>
                            </button>
                        </form>
                        <form action="{{ route('admin.promotions.archive', $promotion) }}" method="POST">
                            @csrf
                            <button class="order-detail__contact marketing-detail__contact--danger" type="submit">
                                <i class="bi bi-archive-fill"></i>
                                <span>Archivia</span>
                            </button>
                        </form>
                    </div>
                </section>

                @include('admin.Marketing.partials.report-metrics', ['report' => $report])
            </div>
        </article>
    </div>
</div>

@endsection
