@props([
    'status' => 2,
    'reservationCode' => null,
    'time' => null,
    'dateLabel' => null,
    'customer' => null,
    'email' => null,
    'phone' => null,
    'adults' => 0,
    'children' => 0,
    'roomLabel' => null,
    'note' => null,
    'sentAt' => null,
    'marketing' => null,
])

@php
    $status = (int) $status;
    $adults = (int) $adults;
    $children = (int) $children;

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
@endphp

<article {{ $attributes->class(['reservation-detail', 'order-detail', 'order-detail--' . $statusData['tone']]) }}>
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
                @if ($reservationCode)
                    <p class="order-detail__code">#{{ $reservationCode }}</p>
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

        <section class="order-detail__section reservation-detail__guests">
            <div class="order-detail__section-head">
                <h3>
                    <span class="order-detail__section-icon">
                        <x-icon name="people-fill" />
                    </span>
                    Ospiti
                </h3>
            </div>

            <div class="reservation-detail__guest-list">
                @if ($adults > 0)
                    <div class="reservation-detail__guest-chip">
                        <span class="reservation-detail__guest-icon">
                            <x-icon name="person-standing" />
                        </span>
                        <span class="reservation-detail__guest-copy">
                            <strong>{{ $adults }}</strong>
                            <small>Adulti</small>
                        </span>
                    </div>
                @endif

                @if ($children > 0)
                    <div class="reservation-detail__guest-chip">
                        <span class="reservation-detail__guest-icon">
                            <x-icon name="person-arms-up" />
                        </span>
                        <span class="reservation-detail__guest-copy">
                            <strong>{{ $children }}</strong>
                            <small>Bambini</small>
                        </span>
                    </div>
                @endif

                @if ($roomLabel)
                    <div class="reservation-detail__room">
                        <span class="reservation-detail__room-label">Sala prenotata</span>
                        <strong>{{ $roomLabel }}</strong>
                    </div>
                @endif
            </div>
        </section>

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
