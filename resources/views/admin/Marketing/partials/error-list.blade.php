@if (! empty($errors))
    <div class="order-detail__section-head">
        <h3>
            <span class="order-detail__section-icon">
                <i class="bi bi-exclamation-triangle-fill"></i>
            </span>
            Errori
        </h3>
    </div>

    <div class="marketing-detail__linked-grid">
        @foreach ($errors as $error)
            <article class="marketing-detail__linked-card">
                <span>Errore</span>
                <strong>{{ $error['message'] ?? '-' }}</strong>
                <small>Customer: {{ $error['customer_id'] ?? '-' }}</small>
                <small>Promotion: {{ $error['promotion_id'] ?? '-' }}</small>
            </article>
        @endforeach
    </div>
@endif
