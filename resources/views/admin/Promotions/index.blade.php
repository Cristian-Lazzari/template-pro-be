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
            ['label' => __('admin.nav.dashboard'), 'url' => route('admin.dashboard')],
            ['label' => __('admin.marketing.area_links.marketing'), 'url' => route('admin.marketing')],
            ['label' => $isArchivedView ? __('admin.marketing.promotions.archived_title') : __('admin.marketing.promotions.plural')],
        ],
    ])

    <header class="menu-dashboard__hero order-detail__summary">
        <div class="order-detail__meta">
            <div class="order-detail__status">
                <span class="order-detail__status-icon order-detail__status-icon--active">
                    <i class="bi {{ $isArchivedView ? 'bi-archive-fill' : 'bi-megaphone-fill' }}"></i>
                </span>
                <strong>{{ __('admin.marketing.area_links.marketing') }}</strong>
            </div>

            <h1 class="menu-dashboard__title">{{ $isArchivedView ? __('admin.marketing.promotions.archived_title') : __('admin.marketing.promotions.plural') }}</h1>
        </div>

        <div class="menu-dashboard__hero-actions dashboard-home__hero-actions">
            @if ($isArchivedView)
                <a href="{{ route('admin.promotions.index') }}" class="order-detail__contact">
                    <i class="bi bi-arrow-left"></i>
                    <span>{{ __('admin.marketing.promotions.list') }}</span>
                </a>
            @else
                <a href="{{ route('admin.promotions.archived') }}" class="order-detail__contact">
                    <i class="bi bi-archive-fill"></i>
                    <span>{{ __('admin.marketing.promotions.archived') }}</span>
                </a>
                <a href="{{ route('admin.promotions.create') }}" class="order-detail__contact">
                    <i class="bi bi-cloud-plus-fill"></i>
                    <span>{{ __('admin.common.create_new') }}</span>
                </a>
            @endif
        </div>
    </header>

    <section class="promotion-index-board mt-4" aria-label="{{ $isArchivedView ? __('admin.marketing.promotions.archived_aria') : __('admin.marketing.promotions.list_aria') }}">
        @if ($promotions->count() > 0)
            <div class="promotion-list-render">
                @foreach ($promotions as $promotion)
                    @php
                        $currencySymbol = $appCurrency['symbol'] ?? '€';
                        $formatDecimal = static fn ($value) => number_format((float) $value, 2, ',', '.');
                        $isDraft = $promotion->status === 'draft';
                        $isReusable = data_get($promotion->metadata, 'reusable') === true;
                        $isDefaultActive = (bool) $promotion->default_active;
                        $assignedCount = (int) ($promotion->assigned_customers_count ?? $promotion->total_activation ?? 0);
                        $usedCount = (int) ($promotion->used_customers_count ?? $promotion->total_used ?? 0);
                        $usageRate = $assignedCount > 0 ? min(100, round(($usedCount / $assignedCount) * 100)) : 0;
                        $usageLabel = $usageRate . '%';
                        $caseUseLabel = match ($promotion->case_use) {
                            'take_away' => __('admin.marketing.promotions.case_takeaway'),
                            'delivery' => __('admin.marketing.promotions.case_delivery'),
                            'generic' => __('admin.marketing.promotions.case_takeaway_delivery'),
                            'table' => __('admin.marketing.promotions.case_table'),
                            default => $caseUses[$promotion->case_use] ?? ($promotion->case_use ?: __('admin.marketing.promotions.case_generic')),
                        };
                        $discountLabel = match ($promotion->type_discount) {
                            'fixed' => $promotion->discount !== null ? $formatDecimal($promotion->discount) . ' ' . $currencySymbol : '-',
                            'percentage' => $promotion->discount !== null ? (int) $promotion->discount . ' %' : '-',
                            'gift' => __('admin.marketing.promotions.discount_gift'),
                            default => '-',
                        };
                        $validityLabel = match (true) {
                            (bool) $promotion->permanent => __('admin.marketing.promotions.permanent_validity'),
                            filled($promotion->schedule_at) && filled($promotion->expiring_at) => $promotion->schedule_at->format('d/m') . ' - ' . $promotion->expiring_at->format('d/m'),
                            filled($promotion->schedule_at) => __('admin.marketing.promotions.from_date', ['date' => $promotion->schedule_at->format('d/m')]),
                            filled($promotion->expiring_at) => __('admin.marketing.promotions.until_date', ['date' => $promotion->expiring_at->format('d/m')]),
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
                                ?? (($targetTypes[$firstTarget->target_type] ?? __('admin.marketing.promotions.target')) . ($firstTarget->target_id ? ' #' . $firstTarget->target_id : ''));

                            if ($specificTargets->count() > 1) {
                                $targetLabel .= ' +' . ($specificTargets->count() - 1);
                            }
                        }

                        $minimumLabel = match (true) {
                            $promotion->minimum_pretest === null => __('admin.marketing.promotions.none'),
                            $promotion->case_use === 'table' => rtrim(rtrim(number_format((float) $promotion->minimum_pretest, 1, ',', '.'), '0'), ',') . ' ' . __('admin.marketing.promotions.guests_suffix'),
                            default => $formatDecimal($promotion->minimum_pretest) . ' ' . $currencySymbol,
                        };
                        $secondaryLabel = $targetLabel ? __('admin.marketing.promotions.target') : ($promotion->case_use === 'table' ? __('admin.marketing.promotions.minimum') : __('admin.marketing.promotions.target'));
                        $secondaryValue = $targetLabel ?: ($promotion->case_use === 'table' ? $minimumLabel : __('admin.marketing.promotions.cart'));
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
                                    {{ $isReusable ? __('admin.marketing.promotions.reusable_short') : __('admin.marketing.promotions.not_reusable') }}
                                </span>
                                <span class="promotion-list-reuse @if (! $isDefaultActive) promotion-list-reuse--off @endif">
                                    {{ $isDefaultActive ? __('admin.marketing.promotions.default_active_short') : __('admin.marketing.promotions.private_short') }}
                                </span>
                            </div>
                        </div>

                        <div class="promotion-list-validity">
                                <span>{{ __('admin.marketing.promotions.validity') }}</span>
                            <strong title="{{ $validityLabel }}">{{ $validityLabel }}</strong>
                        </div>

                        <div class="promotion-list-rule">
                            <div>
                                <span>{{ __('admin.marketing.promotions.discount') }}</span>
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
                                    aria-label="{{ __('admin.marketing.promotions.usage') }} {{ $usedCount }} / {{ $assignedCount }}"
                                    title="{{ __('admin.marketing.promotions.usage') }} {{ $usedCount }} / {{ $assignedCount }}"
                                >
                                    <strong>{{ $usageLabel }}</strong>
                                </div>
                            @endif
                        </div>

                        <div class="promotion-list-actions">
                            @if ($isDraft)
                                <a class="promotion-list-action promotion-list-action--primary" href="{{ route('admin.promotions.edit', $promotion) }}">
                                    {{ __('admin.marketing.promotions.complete') }}
                                </a>
                                <form action="{{ route('admin.promotions.destroy', $promotion) }}" method="POST" onsubmit="return confirm(@js(__('admin.marketing.promotions.delete_draft_confirm')));">
                                    @csrf
                                    @method('DELETE')
                                    <button class="promotion-list-action promotion-list-action--danger" type="submit">
                                        {{ __('admin.common.delete') }}
                                    </button>
                                </form>
                            @else
                                <a class="promotion-list-action promotion-list-action--primary" href="{{ route('admin.promotions.show', $promotion) }}">
                                    {{ __('admin.Vedi') }}
                                </a>
                                @if ($promotion->status !== 'archived')
                                    <form action="{{ route('admin.promotions.archive', $promotion) }}" method="POST">
                                        @csrf
                                        <button class="promotion-list-action promotion-list-action--danger" type="submit">
                                            {{ __('admin.marketing.promotions.archive') }}
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
                    <strong>{{ $isArchivedView ? __('admin.marketing.promotions.no_archived_promotions') : __('admin.marketing.promotions.no_promotions') }}</strong>
                    <p>
                        {{ $isArchivedView
                            ? __('admin.marketing.promotions.archived_empty_text')
                            : __('admin.marketing.promotions.empty_text') }}
                    </p>
                </div>
            </div>
        @endif
    </section>
</div>

@endsection
