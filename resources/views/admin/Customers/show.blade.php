@extends('layouts.base')

@section('contents')

@php
    $customerDisplayName = trim(($customer->name ?? '') . ' ' . ($customer->surname ?? '')) ?: $customer->email;
    $customerReference = $customer->id ? '#C' . $customer->id : 'Profilo aggregato';
    $locale = app()->getLocale() ?: 'it';

    $accountLabels = [
        'guest' => 'Ospite',
        'registered' => 'Registrato',
    ];

    $orderStatuses = [
        0 => ['label' => 'Annullato', 'tone' => 'off'],
        1 => ['label' => 'Confermato', 'tone' => 'active'],
        2 => ['label' => 'In attesa', 'tone' => 'warning'],
        3 => ['label' => 'Pagato, in attesa', 'tone' => 'warning'],
        5 => ['label' => 'Confermato e pagato', 'tone' => 'active'],
        6 => ['label' => 'Rimborsato', 'tone' => 'off'],
    ];

    $reservationStatuses = [
        0 => ['label' => 'Annullata', 'tone' => 'off'],
        1 => ['label' => 'Confermata', 'tone' => 'active'],
        2 => ['label' => 'In attesa', 'tone' => 'warning'],
        3 => ['label' => 'Pagata, in attesa', 'tone' => 'warning'],
        5 => ['label' => 'Confermata e pagata', 'tone' => 'active'],
        6 => ['label' => 'Rimborsata', 'tone' => 'off'],
    ];

    $accountTone = match ($accountState) {
        'registered' => 'active',
        default => 'warning',
    };

    $pageTone = $hasCustomerRecord ? $accountTone : 'warning';

    $toCarbon = static function ($value): ?\Carbon\CarbonInterface {
        if (!$value) {
            return null;
        }

        return $value instanceof \Carbon\CarbonInterface
            ? $value
            : \Carbon\Carbon::parse($value);
    };

    $formatHumanDate = static function ($value, string $fallback = '-') use ($toCarbon, $locale): string {
        $carbon = $toCarbon($value);

        if (!$carbon) {
            return $fallback;
        }

        return mb_strtolower($carbon->locale($locale)->translatedFormat('l j F y'));
    };

    $formatHumanDateTime = static function ($value, string $fallback = '-') use ($toCarbon, $formatHumanDate): string {
        $carbon = $toCarbon($value);

        if (!$carbon) {
            return $fallback;
        }

        return $formatHumanDate($carbon) . ' alle ' . $carbon->format('H:i');
    };

    $latestActivity = $toCarbon($stats['latest_activity_at'] ?? null);
    $latestActivityTime = $latestActivity?->format('H:i') ?? '--:--';
    $latestActivityDate = $latestActivity
        ? $formatHumanDate($latestActivity)
        : 'nessuna attivita recente';

    $marketingEnabled = !empty($customer->marketing_consent_at);
    $profilingEnabled = !empty($customer->profiling_consent_at);

    $marketingTone = $marketingEnabled ? 'active' : 'off';
    $profilingTone = $profilingEnabled ? 'active' : 'off';

    $statItems = [
        [
            'label' => 'Ordini',
            'value' => $stats['orders_count'],
            'helper' => 'collegati a questo profilo',
        ],
        [
            'label' => 'Prenotazioni',
            'value' => $stats['reservations_count'],
            'helper' => 'tracciate nello storico cliente',
        ],
        [
            'label' => 'Interazioni',
            'value' => $stats['interactions_count'],
            'helper' => 'somma di ordini e prenotazioni',
        ],
        [
            'label' => 'Speso online',
            'value' => \App\Support\Currency::formatCents($stats['total_spent_cents'] ?? 0),
            'helper' => 'totale ordini non annullati',
        ],
    ];

    $profileItems = [
        ['label' => 'Nome', 'value' => $customer->name ?: '-'],
        ['label' => 'Cognome', 'value' => $customer->surname ?: '-'],
        ['label' => 'Email', 'value' => $customer->email ?: '-'],
        ['label' => 'Telefono', 'value' => $customer->phone ?: '-'],
        ['label' => 'Sesso', 'value' => $customer->gender ?: '-'],
        ['label' => 'Eta', 'value' => $customer->age ?: '-'],
    ];

    $consentItems = [
        [
            'label' => 'Marketing',
            'value' => $marketingEnabled ? 'Attivo' : 'Disattivo',
            'tone' => $marketingTone,
            'pill' => $marketingEnabled ? 'Presente' : 'Assente',
            'helper' => $marketingEnabled
                ? 'Attivato ' . $formatHumanDateTime($customer->marketing_consent_at)
                : 'Nessun consenso marketing registrato',
        ],
        [
            'label' => 'Profilazione',
            'value' => $profilingEnabled ? 'Attiva' : 'Disattiva',
            'tone' => $profilingTone,
            'pill' => $profilingEnabled ? 'Presente' : 'Assente',
            'helper' => $profilingEnabled
                ? 'Attivata ' . $formatHumanDateTime($customer->profiling_consent_at)
                : 'Nessun consenso profilazione registrato',
        ],
    ];

    $timelineItems = [
        [
            'label' => 'Profilo creato',
            'value' => $formatHumanDateTime($customer->created_at, 'data non disponibile'),
        ],
        [
            'label' => 'Account registrato',
            'value' => $formatHumanDateTime($customer->registered_at, 'non registrato'),
        ],
        [
            'label' => 'Email verificata',
            'value' => $formatHumanDateTime($customer->email_verified_at, 'non verificata'),
        ],
    ];

    $profileNotice = null;
    if (!$hasCustomerRecord) {
        $profileNotice = 'Profilo costruito da ordini e prenotazioni non ancora collegati a un account.';
    } elseif ($accountState === 'guest') {
        $profileNotice = 'Il cliente esiste gia, ma il profilo non e ancora stato completato dal cliente.';
    }
@endphp

<style>
    .customer-detail-page {
        width: 100%;
        display: grid;
        gap: 22px;
    }

    .customer-detail__grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 22px;
    }

    .customer-detail__hero-copy,
    .customer-detail__hero-title,
    .customer-detail__info-meta,
    .customer-detail__answer,
    .customer-detail__activity-copy,
    .customer-detail__activity-item {
        display: grid;
        gap: 10px;
    }

    .customer-detail__hero-title p,
    .customer-detail__notice p,
    .customer-detail__stat-card small,
    .customer-detail__info-help,
    .customer-detail__empty,
    .customer-detail__activity-footer p {
        margin: 0;
        color: rgba(216, 221, 232, 0.84);
        line-height: 1.7;
    }

    .customer-detail__notice {
        padding: 14px 16px;
        border-radius: 18px;
        border: 1px solid rgba(255, 211, 122, 0.24);
        background: rgba(255, 211, 122, 0.08);
    }

    .customer-detail__notice strong {
        color: #ffd37a;
    }

    .customer-detail__stat-grid,
    .customer-detail__info-grid,
    .customer-detail__question-list,
    .customer-detail__activity-list {
        display: grid;
        gap: 0;
    }

    .customer-detail__stat-grid {
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 18px;
    }

    .customer-detail__info-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        column-gap: 18px;
    }

    .customer-detail__stat-item {
        display: grid;
        gap: 8px;
        min-width: 0;
    }

    .customer-detail__info-item {
        display: grid;
        gap: 10px;
        min-width: 0;
    }

    .customer-detail__stat-item span,
    .customer-detail__info-label,
    .customer-detail__answer-label,
    .customer-detail__activity-label {
        color: rgba(216, 221, 232, 0.72);
        font-size: var(--fs-200);
        letter-spacing: .06em;
        text-transform: uppercase;
    }

    .customer-detail__stat-item strong,
    .customer-detail__info-value,
    .customer-detail__answer-value,
    .customer-detail__activity-copy strong {
        color: var(--c3);
    }

    .customer-detail__stat-item strong {
        font-size: var(--fs-500);
        line-height: 1.05;
    }

    .customer-detail__info-item,
    .customer-detail__question-item,
    .customer-detail__activity-item {
        padding: 14px 0;
        border-top: 1px solid rgba(216, 221, 232, 0.1);
    }

    .customer-detail__info-item:nth-child(-n + 2),
    .customer-detail__question-item:first-child,
    .customer-detail__activity-item:first-child {
        padding-top: 0;
        border-top: 0;
    }

    .customer-detail__info-value {
        font-size: var(--fs-200);
        line-height: 1.45;
        overflow-wrap: anywhere;
        word-break: break-word;
    }

    .customer-detail-page .order-detail__contact {
        align-items: flex-start;
    }

    .customer-detail-page .order-detail__contact span {
        white-space: normal;
        overflow: visible;
        text-overflow: initial;
        overflow-wrap: anywhere;
        word-break: break-word;
    }

    .customer-detail__activity-head,
    .customer-detail__activity-footer {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
    }

    .customer-detail__activity-footer .order-detail__contact {
        flex: 0 0 auto;
    }

    @media (max-width: 1040px) {
        .customer-detail__grid {
            grid-template-columns: 1fr;
        }

        .customer-detail__stat-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 720px) {
        .customer-detail__stat-grid,
        .customer-detail__info-grid {
            grid-template-columns: 1fr;
        }

        .customer-detail__info-item:nth-child(-n + 2) {
            padding-top: 14px;
            border-top: 1px solid rgba(216, 221, 232, 0.1);
        }

        .customer-detail__info-item:first-child {
            padding-top: 0;
            border-top: 0;
        }

        .customer-detail__activity-head,
        .customer-detail__activity-footer,
        .order-detail__contacts {
            flex-direction: column;
            align-items: stretch;
        }

        .customer-detail__activity-footer .order-detail__contact,
        .order-detail__contacts .order-detail__contact {
            width: 100%;
        }
    }
</style>

@include('admin.Marketing.partials.breadcrumbs', [
    'items' => [
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Clienti', 'url' => route('admin.customers.index')],
        ['label' => $customerDisplayName],
    ],
])

<a onclick="history.back()" class="btn btn-outline-light my-4">
    <x-icon name="arrow-90deg-left" />
</a>

<div class="customer-detail-page">
    <article class="order-detail order-detail--{{ $pageTone }}">
        <header class="order-detail__header">
            <div class="customer-detail__hero-copy">
                <div class="order-detail__status">
                    <span class="order-detail__status-icon order-detail__status-icon--{{ $accountTone }}">
                        @if ($accountState === 'registered')
                            <x-icon name="person-check-fill" />
                        @else
                            <x-icon name="person-fill" />
                        @endif
                    </span>
                    <strong>{{ $accountLabels[$accountState] ?? ucfirst($accountState) }}</strong>
                </div>

                @if ($profileNotice)
                    <div class="customer-detail__notice">
                        <strong>Attenzione profilo</strong>
                        <p>{{ $profileNotice }}</p>
                    </div>
                @endif
            </div>

            <div class="order-detail__contacts">
                @if (!empty($customer->email))
                    <a href="mailto:{{ $customer->email }}" class="order-detail__contact">
                        <x-icon name="envelope-arrow-up-fill" />
                        <span>{{ $customer->email }}</span>
                    </a>
                @endif

                @if (!empty($customer->phone))
                    <a href="tel:{{ preg_replace('/\s+/', '', $customer->phone) }}" class="order-detail__contact">
                        <x-icon name="telephone-outbound-fill" />
                        <span>{{ $customer->phone }}</span>
                    </a>
                @endif

                <a href="{{ route('admin.campaigns.index') }}" class="order-detail__contact">
                    <x-icon name="envelope-paper-fill" />
                    <span>Campagne marketing</span>
                </a>

                <a href="{{ route('admin.automations.index') }}" class="order-detail__contact">
                    <x-icon name="lightning-charge-fill" />
                    <span>Automazioni marketing</span>
                </a>

                <a href="{{ route('admin.promotions.index') }}" class="order-detail__contact">
                    <x-icon name="megaphone-fill" />
                    <span>Promozioni</span>
                </a>
            </div>
        </header>

        <div class="order-detail__body">
            <section class="order-detail__summary">
                <div class="order-detail__meta">
                    <p class="order-detail__code">{{ $customerReference }}</p>
                    <p class="order-detail__time">{{ $latestActivityTime }}</p>
                    <p class="order-detail__date">{{ $latestActivityDate }}</p>
                </div>

                <div class="order-detail__customer">{{ $customerDisplayName }}</div>
            </section>

            <section class="order-detail__section">
                <div class="order-detail__section-head">
                    <h3>
                        <span class="order-detail__section-icon">
                            <x-icon name="card-checklist" />
                        </span>
                        Riepilogo rapido
                    </h3>
                </div>

                <div class="customer-detail__stat-grid">
                    @foreach ($statItems as $item)
                        <div class="customer-detail__stat-item">
                            <span>{{ $item['label'] }}</span>
                            <strong>{{ $item['value'] }}</strong>
                            <small>{{ $item['helper'] }}</small>
                        </div>
                    @endforeach
                </div>
            </section>

            <footer class="order-detail__footer">
                @foreach ($timelineItems as $item)
                    <div class="order-detail__footer-row">
                        <span>{{ $item['label'] }}</span>
                        <strong>{{ $item['value'] }}</strong>
                    </div>
                @endforeach
            </footer>
        </div>
    </article>

    <div class="customer-detail__grid">
        <article class="order-detail">
            <div class="order-detail__body">
                <section class="order-detail__section">
                    <div class="order-detail__section-head">
                        <h3>
                            <span class="order-detail__section-icon">
                                <x-icon name="person-vcard-fill" />
                            </span>
                            Dati cliente
                        </h3>
                    </div>

                    <div class="customer-detail__info-grid">
                        @foreach ($profileItems as $item)
                            <div class="customer-detail__info-item">
                                <span class="customer-detail__info-label">{{ $item['label'] }}</span>
                                <strong class="customer-detail__info-value">{{ $item['value'] }}</strong>
                            </div>
                        @endforeach
                    </div>
                </section>
            </div>
        </article>


                <section class="order-detail__section">
                    <div class="order-detail__section-head">
                        <h3>
                            <span class="order-detail__section-icon">
                                <x-icon name="person-check-fill" />
                            </span>
                            Stati privacy
                        </h3>
                    </div>

                    <div class="customer-detail__info-grid">
                        @foreach ($consentItems as $item)
                            <div class="customer-detail__info-item">
                                <span class="customer-detail__info-label">{{ $item['label'] }}</span>
                                <strong class="customer-detail__info-value">{{ $item['value'] }}</strong>
                                <div class="customer-detail__info-meta">
                                    <x-dashboard.state-pill :tone="$item['tone']">{{ $item['pill'] }}</x-dashboard.state-pill>
                                    <small class="customer-detail__info-help">{{ $item['helper'] }}</small>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>

    </div>

    @if (!empty($questionAnswers))
        <article class="order-detail">
            <div class="order-detail__body">
                <section class="order-detail__section">
                    <div class="order-detail__section-head">
                        <h3>
                            <span class="order-detail__section-icon">
                                <x-icon name="patch-question-fill" />
                            </span>
                            Questionario profilo
                        </h3>
                    </div>

                    <div class="customer-detail__question-list">
                        @foreach ($questionAnswers as $answer)
                            <div class="customer-detail__question-item">
                                <div class="customer-detail__answer">
                                    <span class="customer-detail__answer-label">{{ $answer['label'] }}</span>
                                    <strong class="customer-detail__answer-value">{{ is_array($answer['value']) ? implode(', ', $answer['value']) : $answer['value'] }}</strong>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            </div>
        </article>
    @endif

    <div class="customer-detail__grid" id="customer-history">
        <article class="order-detail">
            <div class="order-detail__body">
                <section class="order-detail__section">
                    <div class="order-detail__section-head">
                        <h3>
                            <span class="order-detail__section-icon">
                                <x-icon name="card-checklist" />
                            </span>
                            Storico ordini
                        </h3>
                    </div>

                    @if ($orders->isNotEmpty())
                        <div class="customer-detail__activity-list">
                            @foreach ($orders as $order)
                                @php $status = $orderStatuses[$order->status] ?? ['label' => 'Aggiornamento', 'tone' => 'warning']; @endphp
                                <article class="customer-detail__activity-item">
                                    <div class="customer-detail__activity-head">
                                        <div class="customer-detail__activity-copy">
                                            <span class="customer-detail__activity-label">Ordine #O{{ $order->id }}</span>
                                            <strong>{{ $formatHumanDateTime($order->activity_at, 'data non disponibile') }}</strong>
                                        </div>
                                        <x-dashboard.state-pill :tone="$status['tone']">{{ $status['label'] }}</x-dashboard.state-pill>
                                    </div>

                                    <div class="order-detail__detail-values">
                                        <small>{{ !empty($order->comune) ? 'Domicilio' : 'Asporto' }}</small>
                                        <small>{{ \App\Support\Currency::formatCents($order->tot_price ?? 0) }}</small>
                                        <small>Marketing storico: {{ $order->news_letter ? 'si' : 'no' }}</small>
                                    </div>

                                    <div class="customer-detail__activity-footer">
                                        <a href="{{ route('admin.orders.show', $order->id) }}" class="order-detail__contact">
                                            <x-icon name="arrow-up-right-circle-fill" />
                                            <span>Apri ordine</span>
                                        </a>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @else
                        <p class="customer-detail__empty">Nessun ordine trovato.</p>
                    @endif
                </section>
            </div>
        </article>

        <article class="order-detail">
            <div class="order-detail__body">
                <section class="order-detail__section">
                    <div class="order-detail__section-head">
                        <h3>
                            <span class="order-detail__section-icon">
                                <x-icon name="people-fill" />
                            </span>
                            Storico prenotazioni
                        </h3>
                    </div>

                    @if ($reservations->isNotEmpty())
                        <div class="customer-detail__activity-list">
                            @foreach ($reservations as $reservation)
                                @php
                                    $status = $reservationStatuses[$reservation->status] ?? ['label' => 'Aggiornamento', 'tone' => 'warning'];
                                    $guests = json_decode($reservation->n_person, true);
                                    $totalGuests = (int) ($guests['adult'] ?? 0) + (int) ($guests['child'] ?? 0);
                                @endphp
                                <article class="customer-detail__activity-item">
                                    <div class="customer-detail__activity-head">
                                        <div class="customer-detail__activity-copy">
                                            <span class="customer-detail__activity-label">Prenotazione #R{{ $reservation->id }}</span>
                                            <strong>{{ $formatHumanDateTime($reservation->activity_at, 'data non disponibile') }}</strong>
                                        </div>
                                        <x-dashboard.state-pill :tone="$status['tone']">{{ $status['label'] }}</x-dashboard.state-pill>
                                    </div>

                                    <div class="order-detail__detail-values">
                                        <small>Ospiti: {{ $totalGuests }}</small>
                                        <small>{{ $reservation->sala ?: 'Sala non indicata' }}</small>
                                        <small>Marketing storico: {{ $reservation->news_letter ? 'si' : 'no' }}</small>
                                    </div>

                                    <div class="customer-detail__activity-footer">
                                        <a href="{{ route('admin.reservations.show', $reservation->id) }}" class="order-detail__contact">
                                            <x-icon name="arrow-up-right-circle-fill" />
                                            <span>Apri prenotazione</span>
                                        </a>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @else
                        <p class="customer-detail__empty">Nessuna prenotazione trovata.</p>
                    @endif
                </section>
            </div>
        </article>
    </div>
</div>
@endsection
