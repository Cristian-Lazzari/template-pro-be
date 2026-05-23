@if (! empty($errors))
    <div class="order-detail__section-head">
        <h3>
            <span class="order-detail__section-icon">
                <i class="bi bi-exclamation-triangle-fill"></i>
            </span>
            {{ __('admin.marketing.campaigns.errors') }}
        </h3>
    </div>

    <div class="marketing-detail__linked-grid">
        @foreach ($errors as $error)
            <article class="marketing-detail__linked-card">
                <span>{{ __('admin.common.error') }}</span>
                <strong>{{ $error['message'] ?? '-' }}</strong>
                <small>{{ __('admin.common.customer') }}: {{ $error['customer_id'] ?? '-' }}</small>
                <small>{{ __('admin.common.promotion') }}: {{ $error['promotion_id'] ?? '-' }}</small>
            </article>
        @endforeach
    </div>
@endif
