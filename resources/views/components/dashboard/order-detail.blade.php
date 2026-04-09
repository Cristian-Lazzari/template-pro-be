@props([
    'status' => 2,
    'orderCode' => null,
    'time' => null,
    'dateLabel' => null,
    'customer' => null,
    'email' => null,
    'phone' => null,
    'items' => [],
    'deliveryCost' => null,
    'total' => null,
    'fulfillmentTitle' => null,
    'fulfillmentValue' => null,
    'fulfillmentType' => 'delivery',
    'note' => null,
    'sentAt' => null,
    'marketing' => null,
])

@php
    $status = (int) $status;

    $statusMap = [
        0 => ['label' => 'Annullata', 'tone' => 'off'],
        1 => ['label' => 'Confermata', 'tone' => 'active'],
        2 => ['label' => 'In attesa', 'tone' => 'warning'],
        3 => ['label' => 'Gia pagata in attesa', 'tone' => 'warning'],
        5 => ['label' => 'Confermata e incassata', 'tone' => 'active'],
        6 => ['label' => 'Rimborsata', 'tone' => 'off'],
    ];

    $statusData = $statusMap[$status] ?? $statusMap[2];

    $statusIcon = match ($statusData['tone']) {
        'off' => 'x-circle',
        'active' => 'check-circle',
        default => 'exclamation-circle-fill',
    };

    $fulfillmentIcon = $fulfillmentType === 'takeaway' ? 'bag-fill' : 'truck';
@endphp

<article {{ $attributes->class(['order-detail', 'order-detail--' . $statusData['tone']]) }}>
    <header class="order-detail__header">
        <div class="order-detail__status">
            <span class="order-detail__status-icon order-detail__status-icon--{{ $statusData['tone'] }}">
                @if ($statusIcon === 'x-circle')
                    <x-icon name="x-circle" />
                @elseif ($statusIcon === 'check-circle')
                    <x-icon name="check-circle" />
                @else
                    <x-icon name="exclamation-circle-fill" />
                @endif
            </span>

            <strong>{{ $statusData['label'] }}</strong>
        </div>

        <div class="order-detail__contacts">
            @if ($email)
                <a href="{{ 'mailto:' . $email }}" class="order-detail__contact">
                    <x-icon name="envelope-arrow-up-fill" />
                    <span>{{ $email }}</span>
                </a>
            @endif

            @if ($phone)
                <a href="{{ 'tel:' . preg_replace('/\s+/', '', $phone) }}" class="order-detail__contact">
                    <x-icon name="telephone-outbound-fill" />
                    <span>{{ $phone }}</span>
                </a>
            @endif
        </div>
    </header>

    <div class="order-detail__body">
        <section class="order-detail__summary">
            <div class="order-detail__meta">
                @if ($orderCode)
                    <p class="order-detail__code">#{{ $orderCode }}</p>
                @endif

                @if ($time)
                    <p class="order-detail__time">{{ $time }}</p>
                @endif

                @if ($dateLabel)
                    <p class="order-detail__date">{{ $dateLabel }}</p>
                @endif
            </div>

            @if ($customer)
                <div class="order-detail__customer">{{ $customer }}</div>
            @endif
        </section>

        <section class="order-detail__section">
            <div class="order-detail__section-head">
                <h3>
                    <span class="order-detail__section-icon">
                        <x-icon name="card-checklist" />
                    </span>
                    Prodotti ordinati
                </h3>
            </div>

            <div class="order-detail__items">
                @foreach ($items as $item)
                    <article class="order-detail__item">
                        <div class="order-detail__item-top">
                            <span class="order-detail__qty">x{{ $item['quantity'] ?? 1 }}</span>
                            <strong class="order-detail__item-name">{{ $item['name'] ?? '' }}</strong>
                        </div>

                        @if (!empty($item['details']))
                            <div class="order-detail__detail-groups">
                                @foreach ($item['details'] as $detail)
                                    @if (!empty($detail['values']))
                                        <div class="order-detail__detail-group">
                                            <span>{{ $detail['label'] }}</span>
                                            <div class="order-detail__detail-values">
                                                @foreach ($detail['values'] as $value)
                                                    <small>{{ $value }}</small>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                    </article>
                @endforeach
            </div>

            @if ($deliveryCost || $total)
                <div class="order-detail__totals">
                    @if ($deliveryCost)
                        <div class="order-detail__total-row">
                            <span>Costo di consegna</span>
                            <strong>€{{ $deliveryCost }}</strong>
                        </div>
                    @endif

                    @if ($total)
                        <div class="order-detail__total-row order-detail__total-row--grand">
                            <span>Totale</span>
                            <strong>€{{ $total }}</strong>
                        </div>
                    @endif
                </div>
            @endif
        </section>

        @if ($fulfillmentTitle)
            <section class="order-detail__section order-detail__service">
                <div class="order-detail__section-head">
                    <h3>
                        <span class="order-detail__section-icon">
                        @if ($fulfillmentIcon === 'bag-fill')
                            <x-icon name="bag-fill" />
                        @else
                            <x-icon name="truck" />
                        @endif
                        </span>
                        {{ $fulfillmentTitle }}
                    </h3>
                </div>

                @if ($fulfillmentValue)
                    <p>{{ $fulfillmentValue }}</p>
                @endif
            </section>
        @endif

        @if ($note)
            <section class="order-detail__section order-detail__note">
                <div class="order-detail__section-head">
                    <h3>
                        <span class="order-detail__section-icon">
                            <x-icon name="chat-left-text-fill" />
                        </span>
                        Messaggio del cliente
                    </h3>
                </div>

                <p>{{ $note }}</p>
            </section>
        @endif

        @if ($sentAt || !is_null($marketing))
            <footer class="order-detail__footer">
                @if ($sentAt)
                    <div class="order-detail__footer-row">
                        <span>Inviato alle</span>
                        <strong>{{ $sentAt }}</strong>
                    </div>
                @endif

                @if (!is_null($marketing))
                    <div class="order-detail__footer-row">
                        <span>Marketing sul contatto</span>
                        <strong>{{ $marketing }}</strong>
                    </div>
                @endif
            </footer>
        @endif

        @if (trim((string) $slot) !== '')
            <div class="order-detail__actions">
                {{ $slot }}
            </div>
        @endif
    </div>
</article>
