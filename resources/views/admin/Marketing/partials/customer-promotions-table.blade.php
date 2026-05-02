<section class="order-detail__section mt-4">
    <div class="order-detail__section-head">
        <h3>
            <span class="order-detail__section-icon">
                <i class="bi bi-person-lines-fill"></i>
            </span>
            Assegnazioni create
        </h3>
    </div>

    <p>Record in questa pagina: {{ $customerPromotions->count() }} di {{ $customerPromotions->total() }}</p>

    @if ($customerPromotions->count() > 0)
        <div class="table-responsive">
            <table class="table table-dark table-striped align-middle">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Promotion</th>
                        <th>Status</th>
                        <th>Tracking</th>
                        <th>Email sent</th>
                        <th>Open</th>
                        <th>Click</th>
                        <th>Usata</th>
                        <th>Order</th>
                        <th>Reservation</th>
                        <th>Creata</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($customerPromotions as $customerPromotion)
                        @php
                            $customer = $customerPromotion->customer;
                            $token = $customerPromotion->tracking_token;
                        @endphp
                        <tr>
                            <td>
                                @if ($customer)
                                    <strong>{{ trim(($customer->name ?? '') . ' ' . ($customer->surname ?? '')) ?: '-' }}</strong>
                                    <br>
                                    <small>{{ $customer->email ?? '-' }}</small>
                                    <br>
                                    <small>{{ $customer->phone ?? '-' }}</small>
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $customerPromotion->promotion?->name ?? '-' }}</td>
                            <td>
                                @include('admin.Marketing.partials.status-pill', [
                                    'status' => $customerPromotion->status,
                                    'label' => $customerPromotion->status,
                                ])
                            </td>
                            <td><code>{{ $token ? substr($token, 0, 8) . '...' . substr($token, -6) : '-' }}</code></td>
                            <td>{{ $customerPromotion->email_sent_at?->format('d/m/Y H:i') ?? '-' }}</td>
                            <td>{{ $customerPromotion->email_open_at?->format('d/m/Y H:i') ?? '-' }}</td>
                            <td>{{ $customerPromotion->email_click_at?->format('d/m/Y H:i') ?? '-' }}</td>
                            <td>{{ $customerPromotion->promo_used?->format('d/m/Y H:i') ?? '-' }}</td>
                            <td>{{ $customerPromotion->order_id ?? '-' }}</td>
                            <td>{{ $customerPromotion->reservation_id ?? '-' }}</td>
                            <td>{{ $customerPromotion->created_at?->format('d/m/Y H:i') ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-center mt-3">
            {{ $customerPromotions->links() }}
        </div>
    @else
        <div class="dashboard-home__details-placeholder">
            <span class="dashboard-home__details-placeholder-icon">
                <i class="bi bi-person-plus-fill"></i>
            </span>
            <div>
                <strong>{{ $emptyText ?? 'Nessuna assegnazione creata.' }}</strong>
                <p>Le assegnazioni appariranno qui dopo la preparazione controllata.</p>
            </div>
        </div>
    @endif
</section>
