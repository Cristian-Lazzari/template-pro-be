@extends('layouts.base')

@section('contents')

@if (session('success'))
    <div class="dashboard-home__flash" role="alert">
        {{ session('success') }}
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger dashboard-home__flash" role="alert">
        {{ $errors->first() }}
    </div>
@endif

@php
    $isArchivedView = $isArchivedView ?? false;
@endphp

<div class="dash_page marketing-index-page">
    @include('admin.Marketing.partials.index-style')

    @include('admin.Marketing.partials.breadcrumbs', [
        'items' => [
            ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['label' => 'Marketing', 'url' => route('admin.marketing')],
            ['label' => $isArchivedView ? 'Promozioni archiviate' : 'Promozioni'],
        ],
    ])

    <header class="menu-dashboard__hero order-detail__summary">
        <div class="order-detail__meta">
            <div class="order-detail__status">
                <span class="order-detail__status-icon order-detail__status-icon--active">
                    <i class="bi {{ $isArchivedView ? 'bi-archive-fill' : 'bi-megaphone-fill' }}"></i>
                </span>
                <strong>Marketing</strong>
            </div>

            <h1 class="menu-dashboard__title">{{ $isArchivedView ? 'Promozioni archiviate' : 'Promozioni' }}</h1>
        </div>

        <div class="menu-dashboard__hero-actions dashboard-home__hero-actions">
            @if ($isArchivedView)
                <a href="{{ route('admin.promotions.index') }}" class="order-detail__contact">
                    <i class="bi bi-arrow-left"></i>
                    <span>Lista promozioni</span>
                </a>
            @else
                <a href="{{ route('admin.promotions.archived') }}" class="order-detail__contact">
                    <i class="bi bi-archive-fill"></i>
                    <span>Archiviate</span>
                </a>
                <a href="{{ route('admin.promotions.create') }}" class="order-detail__contact">
                    <i class="bi bi-cloud-plus-fill"></i>
                    <span>Crea nuova</span>
                </a>
            @endif
        </div>
    </header>

    <section class="promotion-index-board mt-4" aria-label="{{ $isArchivedView ? 'Promozioni archiviate' : 'Elenco promozioni' }}">
        @if ($promotions->count() > 0)
            <div class="promotion-list-render">
                @foreach ($promotions as $promotion)
                    @php
                        $currencySymbol = $appCurrency['symbol'] ?? '€';
                        $formatDecimal = static fn ($value) => number_format((float) $value, 2, ',', '.');
                        $isDraft = $promotion->status === 'draft';
                        $isReusable = data_get($promotion->metadata, 'reusable') === true;
                        $assignedCount = (int) ($promotion->assigned_customers_count ?? $promotion->total_activation ?? 0);
                        $usedCount = (int) ($promotion->used_customers_count ?? $promotion->total_used ?? 0);
                        $usageRate = $assignedCount > 0 ? min(100, round(($usedCount / $assignedCount) * 100)) : 0;
                        $usageLabel = $usageRate . '%';
                        $caseUseLabel = match ($promotion->case_use) {
                            'take_away' => 'asporto',
                            'delivery' => 'domicilio',
                            'generic' => 'asporto / domicilio',
                            'table' => 'TAVOLI',
                            default => $caseUses[$promotion->case_use] ?? ($promotion->case_use ?: 'generica'),
                        };
                        $discountLabel = match ($promotion->type_discount) {
                            'fixed' => $promotion->discount !== null ? $formatDecimal($promotion->discount) . ' ' . $currencySymbol : '-',
                            'percentage' => $promotion->discount !== null ? (int) $promotion->discount . ' %' : '-',
                            'gift' => 'Omaggio',
                            default => '-',
                        };
                        $validityLabel = match (true) {
                            (bool) $promotion->permanent => 'Permanente',
                            filled($promotion->schedule_at) && filled($promotion->expiring_at) => $promotion->schedule_at->format('d/m') . ' - ' . $promotion->expiring_at->format('d/m'),
                            filled($promotion->schedule_at) => 'Dal ' . $promotion->schedule_at->format('d/m'),
                            filled($promotion->expiring_at) => 'Fino al ' . $promotion->expiring_at->format('d/m'),
                            default => '-',
                        };
                        $specificTargets = $promotion->targets
                            ->filter(fn ($target) => $target->target_type !== \App\Models\PromotionTarget::TYPE_GENERIC)
                            ->values();
                        $firstTarget = $specificTargets->first();
                        $targetLabel = null;

                        if ($firstTarget) {
                            $targetKey = $firstTarget->target_type . ':' . ($firstTarget->target_id ?? '');
                            $targetLabel = $targetLabels[$targetKey]
                                ?? (($targetTypes[$firstTarget->target_type] ?? 'Target') . ($firstTarget->target_id ? ' #' . $firstTarget->target_id : ''));

                            if ($specificTargets->count() > 1) {
                                $targetLabel .= ' +' . ($specificTargets->count() - 1);
                            }
                        }

                        $minimumLabel = match (true) {
                            $promotion->minimum_pretest === null => 'Nessuno',
                            $promotion->case_use === 'table' => rtrim(rtrim(number_format((float) $promotion->minimum_pretest, 1, ',', '.'), '0'), ',') . ' ospiti',
                            default => $formatDecimal($promotion->minimum_pretest) . ' ' . $currencySymbol,
                        };
                        $secondaryLabel = $targetLabel ? 'Target' : ($promotion->case_use === 'table' ? 'Minimo' : 'Target');
                        $secondaryValue = $targetLabel ?: ($promotion->case_use === 'table' ? $minimumLabel : 'Carrello');
                    @endphp

                    <article class="promotion-list-row @if ($isDraft) promotion-list-row--draft @endif">
                        <div class="promotion-list-identity">
                            <div class="promotion-list-heading">
                                <h4 title="{{ $promotion->name }}">{{ $promotion->name }}</h4>
                                <span class="promotion-list-case" title="{{ $caseUseLabel }}">{{ $caseUseLabel }}</span>
                            </div>

                            <div class="promotion-list-code">
                                <span class="promotion-list-slug" title="{{ $promotion->slug }}">{{ $promotion->slug }}</span>
                                <span class="promotion-list-reuse @if (! $isReusable) promotion-list-reuse--off @endif">
                                    {{ $isReusable ? 'RIUTILIZZABILE' : 'NON RIUTILIZZABILE' }}
                                </span>
                            </div>
                        </div>

                        <div class="promotion-list-validity">
                            <span>Validità</span>
                            <strong title="{{ $validityLabel }}">{{ $validityLabel }}</strong>
                        </div>

                        <div class="promotion-list-rule">
                            <div>
                                <span>Sconto</span>
                                <strong title="{{ $discountLabel }}">{{ $discountLabel }}</strong>
                            </div>
                            <div>
                                <span>{{ $secondaryLabel }}</span>
                                <strong title="{{ $secondaryValue }}">{{ $secondaryValue }}</strong>
                            </div>
                        </div>

                        <div class="promotion-list-usage">
                            @if (! $isDraft)
                                <div
                                    class="promotion-list-donut"
                                    style="--promotion-usage: {{ $usageRate }}%;"
                                    role="img"
                                    aria-label="{{ $usedCount }} usi su {{ $assignedCount }} assegnazioni"
                                    title="{{ $usedCount }} usi su {{ $assignedCount }} assegnazioni"
                                >
                                    <strong>{{ $usageLabel }}</strong>
                                </div>
                            @endif
                        </div>

                        <div class="promotion-list-actions">
                            @if ($isDraft)
                                <a class="promotion-list-action promotion-list-action--primary" href="{{ route('admin.promotions.edit', $promotion) }}">
                                    Completa
                                </a>
                                <form action="{{ route('admin.promotions.destroy', $promotion) }}" method="POST" onsubmit="return confirm('Eliminare questa bozza e i collegamenti collegati?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="promotion-list-action promotion-list-action--danger" type="submit">
                                        Elimina
                                    </button>
                                </form>
                            @else
                                <a class="promotion-list-action promotion-list-action--primary" href="{{ route('admin.promotions.show', $promotion) }}">
                                    Apri
                                </a>
                                @if ($promotion->status !== 'archived')
                                    <form action="{{ route('admin.promotions.archive', $promotion) }}" method="POST">
                                        @csrf
                                        <button class="promotion-list-action promotion-list-action--danger" type="submit">
                                            Archivia
                                        </button>
                                    </form>
                                @endif
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="marketing-index-pager">
                {{ $promotions->links() }}
            </div>
        @else
            <div class="dashboard-home__details-placeholder">
                <span class="dashboard-home__details-placeholder-icon">
                    <i class="bi {{ $isArchivedView ? 'bi-archive-fill' : 'bi-megaphone-fill' }}"></i>
                </span>
                <div>
                    <strong>{{ $isArchivedView ? 'Nessuna promozione archiviata.' : 'Nessuna promozione presente.' }}</strong>
                    <p>
                        {{ $isArchivedView
                            ? 'Quando archivi una promozione, la ritrovi qui.'
                            : 'Crea la prima regola promozionale per collegarla a campagne o automazioni.' }}
                    </p>
                </div>
            </div>
        @endif
    </section>
</div>

@endsection
