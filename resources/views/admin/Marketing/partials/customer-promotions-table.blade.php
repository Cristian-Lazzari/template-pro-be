<section class="order-detail__section">
    <div class="order-detail__section-head">
        <h3>
            <span class="order-detail__section-icon">
                <i class="bi bi-person-lines-fill"></i>
            </span>
            Assegnazioni create
        </h3>
    </div>

    <div class="marketing-detail__compact-grid">
        <article class="marketing-detail__fact">
            <span>Pagina</span>
            <strong>{{ $customerPromotions->count() }}</strong>
            <small>Totale: {{ $customerPromotions->total() }}</small>
        </article>
    </div>

    @if ($customerPromotions->count() > 0)
        <div class="marketing-detail__assignment-list">
            @foreach ($customerPromotions as $customerPromotion)
                @php
                    $customer = $customerPromotion->customer;
                    $token = $customerPromotion->tracking_token;
                    $customerName = $customer
                        ? trim(($customer->name ?? '') . ' ' . ($customer->surname ?? ''))
                        : '';
                @endphp

                <article class="marketing-detail__assignment-card">
                    <div class="marketing-detail__assignment-head">
                        <div class="marketing-detail__assignment-person">
                            <span>Cliente</span>
                            <strong>{{ $customerName !== '' ? $customerName : '-' }}</strong>
                            <small>{{ $customer?->email ?? '-' }}</small>
                            <small>{{ $customer?->phone ?? '-' }}</small>
                        </div>

                        <div>
                            @include('admin.Marketing.partials.status-pill', [
                                'status' => $customerPromotion->status,
                                'label' => $customerPromotion->status,
                            ])
                        </div>
                    </div>

                    <div class="marketing-detail__assignment-grid">
                        <small>Promo: {{ $customerPromotion->promotion?->name ?? '-' }}</small>
                        <small>Token: {{ $token ? substr($token, 0, 8) . '...' . substr($token, -6) : '-' }}</small>
                        <small>Inviata: {{ $customerPromotion->email_sent_at?->format('d/m/Y H:i') ?? '-' }}</small>
                        <small>Aperta: {{ $customerPromotion->email_open_at?->format('d/m/Y H:i') ?? '-' }}</small>
                        <small>Click: {{ $customerPromotion->email_click_at?->format('d/m/Y H:i') ?? '-' }}</small>
                        <small>Usata: {{ $customerPromotion->promo_used?->format('d/m/Y H:i') ?? '-' }}</small>
                        <small>Ordine: {{ $customerPromotion->order_id ?? '-' }}</small>
                        <small>Prenotazione: {{ $customerPromotion->reservation_id ?? '-' }}</small>
                        <small>Creata: {{ $customerPromotion->created_at?->format('d/m/Y H:i') ?? '-' }}</small>
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
