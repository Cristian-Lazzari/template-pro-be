@php
    $current = $current ?? null;
@endphp

@if ($current !== 'marketing')
    <a class="order-detail__contact" href="{{ route('admin.marketing') }}">
        <x-icon name="grid-1x2-fill" />
        <span>{{ __('admin.marketing.area_links.marketing') }}</span>
    </a>
@endif

@if ($current !== 'customers')
    <a class="order-detail__contact" href="{{ route('admin.customers.index') }}">
        <x-icon name="people-fill" />
        <span>{{ __('admin.marketing.area_links.customers') }}</span>
    </a>
@endif

@if ($current !== 'promotions')
    <a class="order-detail__contact" href="{{ route('admin.promotions.index') }}">
        <x-icon name="megaphone-fill" />
        <span>{{ __('admin.marketing.area_links.promotions') }}</span>
    </a>
@endif

@if ($current !== 'campaigns')
    <a class="order-detail__contact" href="{{ route('admin.campaigns.index') }}">
        <x-icon name="envelope-paper-fill" />
        <span>{{ __('admin.marketing.area_links.campaigns') }}</span>
    </a>
@endif

@if ($current !== 'automations')
    <a class="order-detail__contact" href="{{ route('admin.automations.index') }}">
        <x-icon name="lightning-charge-fill" />
        <span>{{ __('admin.marketing.area_links.automations') }}</span>
    </a>
@endif

@if ($current !== 'models')
    <a class="order-detail__contact" href="{{ route('admin.customers.mail_models.index') }}">
        <x-icon name="file-earmark-richtext-fill" />
        <span>{{ __('admin.marketing.area_links.mail_models') }}</span>
    </a>
@endif
