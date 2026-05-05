@php
    $items = method_exists($customerPromotions, 'getCollection')
        ? $customerPromotions->getCollection()
        : collect($customerPromotions);
    $pageCount = $items->count();
    $totalCount = method_exists($customerPromotions, 'total') ? $customerPromotions->total() : $pageCount;
    $pageSentCount = $items->whereNotNull('email_sent_at')->count();
    $pageUsedCount = $items->whereNotNull('promo_used')->count();
    $pageClickedCount = $items->whereNotNull('email_click_at')->count();
    $statusLabels = [
        'assigned' => 'Assegnata',
        'sent' => 'Inviata',
        'opened' => 'Aperta',
        'clicked' => 'Click',
        'used' => 'Usata',
    ];
    $formatDate = fn ($value) => $value ? $value->format('d/m/Y H:i') : '-';
@endphp

<section class="order-detail__section">
    <div class="order-detail__section-head">
        <h3>
            <span class="order-detail__section-icon">
                <i class="bi bi-person-lines-fill"></i>
            </span>
            Assegnazioni create
        </h3>
    </div>

    <div class="campaign-assignment-summary">
        <article class="marketing-detail__fact">
            <span>In questa pagina</span>
            <strong>{{ $pageCount }}</strong>
            <small>Totale: {{ $totalCount }}</small>
        </article>
        <article class="marketing-detail__fact">
            <span>Email inviate</span>
            <strong>{{ $pageSentCount }}</strong>
            <small>Conteggio sui record visibili</small>
        </article>
        <article class="marketing-detail__fact">
            <span>Click</span>
            <strong>{{ $pageClickedCount }}</strong>
            <small>Conteggio sui record visibili</small>
        </article>
        <article class="marketing-detail__fact">
            <span>Promo usate</span>
            <strong>{{ $pageUsedCount }}</strong>
            <small>Conteggio sui record visibili</small>
        </article>
    </div>

    @if ($pageCount > 0)
        <div class="campaign-assignment-list">
            @foreach ($items as $customerPromotion)
                @php
                    $customer = $customerPromotion->customer;
                    $promotion = $customerPromotion->promotion;
                    $token = $customerPromotion->tracking_token;
                    $customerEmail = $customer?->mail ?? $customer?->email ?? null;
                    $customerPhone = $customer?->phone ?? null;
                    $customerName = $customer
                        ? trim(($customer->name ?? '') . ' ' . ($customer->surname ?? ''))
                        : '';
                    $customerLabel = $customerName !== ''
                        ? $customerName
                        : ($customerEmail ?: ($customer ? 'Cliente #' . $customer->id : 'Cliente non disponibile'));
                    $initials = collect(explode(' ', $customerLabel))
                        ->filter()
                        ->map(fn ($part) => strtoupper(substr((string) $part, 0, 1)))
                        ->take(2)
                        ->implode('');
                    $initials = $initials !== '' ? $initials : '?';
                    $shortToken = $token ? substr($token, 0, 8) . '...' . substr($token, -6) : '-';
                    $steps = [
                        [
                            'label' => 'Assegnata',
                            'icon' => 'bi-person-check-fill',
                            'date' => $customerPromotion->created_at,
                            'done' => true,
                        ],
                        [
                            'label' => 'Inviata',
                            'icon' => 'bi-envelope-check-fill',
                            'date' => $customerPromotion->email_sent_at,
                            'done' => filled($customerPromotion->email_sent_at),
                        ],
                        [
                            'label' => 'Aperta',
                            'icon' => 'bi-eye-fill',
                            'date' => $customerPromotion->email_open_at,
                            'done' => filled($customerPromotion->email_open_at),
                        ],
                        [
                            'label' => 'Click',
                            'icon' => 'bi-cursor-fill',
                            'date' => $customerPromotion->email_click_at,
                            'done' => filled($customerPromotion->email_click_at),
                        ],
                        [
                            'label' => 'Usata',
                            'icon' => 'bi-check2-circle',
                            'date' => $customerPromotion->promo_used,
                            'done' => filled($customerPromotion->promo_used),
                        ],
                    ];
                @endphp

                <article class="campaign-assignment-card">
                    <div class="campaign-assignment-card__top">
                        <span class="campaign-assignment-avatar">{{ $initials }}</span>

                        <div class="campaign-assignment-person">
                            <strong>{{ $customerLabel }}</strong>
                            <div class="campaign-assignment-contact">
                                <span>{{ $customerEmail ?: 'Email non disponibile' }}</span>
                                <span>{{ $customerPhone ?: 'Telefono non disponibile' }}</span>
                            </div>
                        </div>

                        @include('admin.Marketing.partials.status-pill', [
                            'status' => $customerPromotion->status,
                            'label' => $statusLabels[$customerPromotion->status] ?? ($customerPromotion->status ?: '-'),
                        ])
                    </div>

                    <div class="campaign-assignment-promo">
                        <span class="campaign-assignment-promo__icon">
                            <i class="bi bi-megaphone-fill"></i>
                        </span>
                        <div>
                            <span>Promozione</span>
                            <strong>{{ $promotion?->name ?? 'Promozione non disponibile' }}</strong>
                        </div>
                    </div>

                    <div class="campaign-assignment-timeline" aria-label="Timeline assegnazione marketing">
                        @foreach ($steps as $step)
                            <div class="campaign-assignment-step @if ($step['done']) is-done @endif">
                                <i class="bi {{ $step['icon'] }}"></i>
                                <span>{{ $step['label'] }}</span>
                                <small>{{ $formatDate($step['date']) }}</small>
                            </div>
                        @endforeach
                    </div>

                    <div class="campaign-assignment-meta">
                        <span>
                            <small>Token</small>
                            <strong>{{ $shortToken }}</strong>
                        </span>
                        <span>
                            <small>Sconto</small>
                            <strong>{{ $customerPromotion->discount_amount !== null ? \App\Support\Currency::formatAmount($customerPromotion->discount_amount) : '-' }}</strong>
                        </span>
                        <span>
                            <small>Ordine</small>
                            <strong>{{ $customerPromotion->order_id ? '#' . $customerPromotion->order_id : '-' }}</strong>
                        </span>
                        <span>
                            <small>Prenotazione</small>
                            <strong>{{ $customerPromotion->reservation_id ? '#' . $customerPromotion->reservation_id : '-' }}</strong>
                        </span>
                        <span>
                            <small>Creata</small>
                            <strong>{{ $formatDate($customerPromotion->created_at) }}</strong>
                        </span>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="marketing-detail__pager">
            {{ $customerPromotions->links() }}
        </div>
    @else
        <div class="marketing-detail__empty">
            <strong>{{ $emptyText ?? 'Nessuna assegnazione creata.' }}</strong>
        </div>
    @endif
</section>
