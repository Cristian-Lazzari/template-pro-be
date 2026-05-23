@php
    $accountLabels = [
        'guest' => __('admin.customers.guest'),
        'registered' => __('admin.customers.registered'),
    ];

    $marketingLabels = [
        'no_marketing' => __('admin.customers.no_marketing'),
        'soft_marketing' => __('admin.customers.soft_marketing'),
        'full' => __('admin.customers.full_marketing'),
    ];
@endphp

<div id="customerList" class="customer-page__list">
    @foreach ($customers as $customer)
        @php
            $displayName = trim(($customer->name ?? '') . ' ' . ($customer->surname ?? ''))
                ?: ($customer->email ?? $customer->phone ?? __('admin.customers.customer_without_contact'));
            $accountTone = $customer->account_state === 'registered' ? 'active' : 'warning';
            $marketingTone = match ($customer->marketing_state) {
                'full' => 'active',
                'soft_marketing' => 'warning',
                default => 'off',
            };
            $marketingLabel = $marketingLabels[$customer->marketing_state] ?? ucfirst((string) $customer->marketing_state);
            $lifecycleTone = match ($customer->lifecycle_segment) {
                'loyal_customers', 'active_customers' => 'active',
                'new_customers', 'at_risk_customers' => 'warning',
                'lost_customers' => 'off',
                default => 'neutral',
            };
            $highlightTone = match ($customer->highlight_segment) {
                'high_value_customers' => 'active',
                'habit_customers' => 'warning',
                'low_engagement' => 'off',
                default => 'neutral',
            };
            $scoreTone = match (true) {
                $customer->customer_score === null => 'neutral',
                $customer->customer_score >= 75 => 'active',
                $customer->customer_score >= 35 => 'warning',
                default => 'off',
            };
        @endphp

        <article class="order-detail order-detail--{{ $accountTone }} customer-card">
            <header class="order-detail__header">
                <div class="customer-card__identity">
                    <div class="order-detail__status">
                        <span class="order-detail__status-icon order-detail__status-icon--{{ $accountTone }}">
                            @if ($customer->account_state === 'registered')
                                <x-icon name="person-check-fill" />
                            @else
                                <x-icon name="person-fill" />
                            @endif
                        </span>
                        <strong>{{ $accountLabels[$customer->account_state] ?? ucfirst((string) $customer->account_state) }}</strong>
                    </div>

                    <div class="customer-card__title">
                        <h2>{{ $displayName }}</h2>
                        <p>
                            {{ __('admin.customers.last_movement') }}
                            {{ $customer->last_activity_at ? $customer->last_activity_at->format('d/m/Y H:i') : '-' }}
                        </p>
                    </div>
                </div>

                <div class="customer-card__pill-row">
                    <x-dashboard.state-pill :tone="$marketingTone">{{ $marketingLabel }}</x-dashboard.state-pill>
                    @if ($customer->lifecycle_label)
                        <x-dashboard.state-pill :tone="$lifecycleTone">{{ $customer->lifecycle_label }}</x-dashboard.state-pill>
                    @endif
                    @if ($customer->highlight_label)
                        <x-dashboard.state-pill :tone="$highlightTone">{{ $customer->highlight_label }}</x-dashboard.state-pill>
                    @endif
                </div>
            </header>

            <div class="customer-card__compact">
                <div class="customer-card__details">
                    <div class="customer-card__meta-row">
                        @if ($customer->email)
                            <a href="mailto:{{ $customer->email }}" class="order-detail__contact">
                                <x-icon name="envelope-arrow-up-fill" />
                                <span>{{ $customer->email }}</span>
                            </a>
                        @endif

                        @if ($customer->phone)
                            <a href="tel:{{ $customer->phone }}" class="order-detail__contact">
                                <x-icon name="telephone-outbound-fill" />
                                <span>{{ $customer->phone }}</span>
                            </a>
                        @endif
                    </div>

                    <p class="customer-card__summary">
                        <strong>{{ $customer->orders_count }}</strong> {{ __('admin.customers.orders_label') }}
                        <span aria-hidden="true">•</span>
                        <strong>{{ $customer->reservations_count }}</strong> {{ __('admin.customers.reservations_label') }}
                        <span aria-hidden="true">•</span>
                        <strong>{{ $customer->interactions_count }}</strong> {{ __('admin.customers.interactions_label') }}
                    </p>

                    <div class="customer-card__metrics">
                        @if ($customer->customer_score !== null)
                            <span class="customer-card__score">
                                <x-dashboard.state-pill :tone="$scoreTone">{{ __('admin.customers.score', ['score' => $customer->customer_score]) }}</x-dashboard.state-pill>
                            </span>
                        @endif

                        @if (($customer->total_spent ?? 0) > 0)
                            <span class="customer-card__score">
                                {{ __('admin.customers.spent', ['amount' => \App\Support\Currency::formatAmount($customer->total_spent)]) }}
                            </span>
                        @endif
                    </div>
                </div>

                @if ($customer->detail_url)
                    <div class="customer-card__action">
                        <a class="customer-page__button" href="{{ $customer->detail_url }}">
                            <x-icon name="arrow-up-right-circle-fill" />
                            <span>{{ __('admin.common.open') }}</span>
                        </a>
                    </div>
                @endif
            </div>
        </article>
    @endforeach
</div>

@if ($customers->count() === 0)
    <div id="customerEmpty" class="order-detail customer-page__empty">
        <div>
            <span class="customer-page__empty-icon" aria-hidden="true">
                <x-icon name="search" />
            </span>
            <strong>{{ __('admin.Nessun_cliente_trovato') }}</strong>
            <p>{{ __('admin.customers.no_results_hint_more') }}</p>
        </div>
    </div>
@endif

@if ($customers->count() > 0)
    <div class="customer-page__pagination">
        {{ $customers->fragment('customerResults')->links() }}
    </div>
@endif
