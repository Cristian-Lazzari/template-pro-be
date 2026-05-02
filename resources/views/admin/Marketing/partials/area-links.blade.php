@php
    $current = $current ?? null;
@endphp

@if ($current !== 'customers')
    <a class="order-detail__contact" href="{{ route('admin.customers.index') }}">
        <x-icon name="people-fill" />
        <span>Clienti</span>
    </a>
@endif

@if ($current !== 'promotions')
    <a class="order-detail__contact" href="{{ route('admin.promotions.index') }}">
        <x-icon name="megaphone-fill" />
        <span>Promozioni</span>
    </a>
@endif

@if ($current !== 'campaigns')
    <a class="order-detail__contact" href="{{ route('admin.campaigns.index') }}">
        <x-icon name="envelope-paper-fill" />
        <span>Campagne</span>
    </a>
@endif

@if ($current !== 'automations')
    <a class="order-detail__contact" href="{{ route('admin.automations.index') }}">
        <x-icon name="lightning-charge-fill" />
        <span>Automazioni</span>
    </a>
@endif
