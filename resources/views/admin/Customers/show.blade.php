@extends('layouts.base')

@section('contents')
@php
    $customerDisplayName = trim(($customer->name ?? '') . ' ' . ($customer->surname ?? '')) ?: $customer->email;

    $accountLabels = [
        'guest' => 'Ospite',
        'registered' => 'Registrato',
    ];

    $marketingLabels = [
        'no_marketing' => 'No marketing',
        'soft_marketing' => 'Soft marketing',
        'full' => 'Full',
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

    $marketingTone = match ($marketingState) {
        'full' => 'active',
        'soft_marketing' => 'warning',
        default => 'off',
    };

    $marketingConsentTone = $customer->marketing_consent_at ? 'active' : 'off';
    $profilingConsentTone = $customer->profiling_consent_at ? 'active' : 'off';

    $profileNotice = null;
    if (!$hasCustomerRecord) {
        $profileNotice = 'Profilo costruito da ordini e prenotazioni legacy non ancora collegati a un record customers.';
    } elseif ($accountState === 'guest') {
        $profileNotice = 'Il record cliente esiste gia, ma il profilo non e ancora stato completato dal cliente.';
    }
@endphp

<style>
    .customer-doc-page {
        max-width: 1240px;
        margin: 0 auto;
        display: grid;
        gap: 22px;
        color: var(--c3);
    }

    .customer-doc-page .public-panel,
    .customer-doc-page .public-card {
        color: inherit;
    }

    .customer-doc-hero {
        display: grid;
        grid-template-columns: minmax(0, 1.7fr) minmax(280px, .9fr);
        gap: 20px;
        align-items: start;
    }

    .customer-doc-hero__copy h1 {
        margin: 0;
        font-size: clamp(30px, 4vw, 48px);
        line-height: 1.02;
    }

    .customer-doc-hero__lead,
    .customer-doc-side-card p:not(.public-card__eyebrow),
    .customer-doc-detail-card small,
    .customer-doc-activity-card p,
    .customer-doc-empty {
        margin: 0;
        line-height: 1.7;
        color: rgba(216, 221, 232, 0.86);
    }

    .customer-doc-hero__lead {
        max-width: 62ch;
    }

    .customer-doc-chip-row,
    .customer-doc-actions,
    .customer-doc-contact-list,
    .customer-doc-activity-meta,
    .customer-doc-activity-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .customer-doc-chip-row {
        margin-top: 16px;
    }

    .customer-doc-actions {
        margin-top: 18px;
    }

    .customer-doc-side {
        display: grid;
        gap: 14px;
    }

    .customer-doc-side-card,
    .customer-doc-kpi-card,
    .customer-doc-detail-card,
    .customer-doc-answer,
    .customer-doc-activity-card {
        border: 1px solid rgba(216, 221, 232, 0.12);
        background: rgba(9, 3, 51, 0.44);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.04);
    }

    .customer-doc-side-card,
    .customer-doc-kpi-card,
    .customer-doc-detail-card,
    .customer-doc-answer,
    .customer-doc-activity-card {
        border-radius: 22px;
    }

    .customer-doc-side-card,
    .customer-doc-kpi-card,
    .customer-doc-detail-card,
    .customer-doc-activity-card {
        padding: 18px;
    }

    .customer-doc-side-card strong,
    .customer-doc-detail-card strong,
    .customer-doc-kpi-card strong {
        display: block;
        color: var(--c3);
    }

    .customer-doc-side-card strong {
        font-size: 1.15rem;
        margin-top: 4px;
    }

    .customer-doc-kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
        margin-top: 22px;
    }

    .customer-doc-kpi-card > span,
    .customer-doc-detail-card > span,
    .customer-doc-activity-label {
        display: block;
        margin-bottom: 8px;
        font-size: .8rem;
        letter-spacing: .12em;
        text-transform: uppercase;
        color: rgba(216, 221, 232, 0.68);
    }

    .customer-doc-kpi-card strong {
        font-size: clamp(1.15rem, 2vw, 1.6rem);
    }

    .customer-doc-sections,
    .customer-doc-activity-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 22px;
    }

    .customer-doc-detail-grid,
    .customer-doc-answer-list,
    .customer-doc-activity-list {
        display: grid;
        gap: 14px;
    }

    .customer-doc-detail-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .customer-doc-detail-card small {
        display: block;
        margin-top: 8px;
    }

    .customer-doc-answer {
        display: grid;
        grid-template-columns: minmax(180px, .7fr) minmax(0, 1.3fr);
        gap: 14px;
        padding: 16px 18px;
        align-items: start;
    }

    .customer-doc-answer strong,
    .customer-doc-answer span {
        display: block;
    }

    .customer-doc-answer span {
        line-height: 1.7;
        color: rgba(216, 221, 232, 0.92);
    }

    .customer-doc-note {
        margin-top: 18px;
        border-radius: 18px;
        border: 1px solid rgba(255, 211, 122, 0.2);
        background: rgba(255, 211, 122, 0.08);
        color: rgba(255, 239, 202, 0.96);
    }

    .customer-doc-note strong {
        display: block;
        margin-bottom: 6px;
        color: #ffd37a;
    }

    .customer-doc-activity-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 14px;
    }

    .customer-doc-activity-head strong {
        display: block;
        margin-bottom: 4px;
        font-size: 1.05rem;
    }

    .customer-doc-activity-actions {
        margin-top: 14px;
        justify-content: space-between;
        align-items: center;
    }

    .customer-doc-pill {
        display: inline-flex;
        align-items: center;
        width: fit-content;
        padding: 8px 12px;
        border-radius: 999px;
        border: 1px solid rgba(216, 221, 232, 0.12);
        background: rgba(216, 221, 232, 0.06);
        color: rgba(216, 221, 232, 0.92);
        font-size: .86rem;
        font-weight: 700;
    }

    .customer-doc-empty {
        padding: 8px 0 2px;
    }

    @media (max-width: 1040px) {
        .customer-doc-hero,
        .customer-doc-sections,
        .customer-doc-activity-grid,
        .customer-doc-detail-grid {
            grid-template-columns: 1fr;
        }

        .customer-doc-kpi-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 720px) {
        .customer-doc-page {
            gap: 18px;
        }

        .customer-doc-kpi-grid,
        .customer-doc-answer {
            grid-template-columns: 1fr;
        }

        .customer-doc-actions,
        .customer-doc-contact-list,
        .customer-doc-activity-actions {
            flex-direction: column;
            align-items: stretch;
        }

        .customer-doc-page .public-button {
            width: 100%;
        }

        .customer-doc-activity-head {
            flex-direction: column;
        }
    }
</style>

<div class="dash_page customer-doc-page">
    <section class="public-panel public-panel--soft">
        <div class="public-breadcrumbs">
            <a href="{{ route('admin.customers.index') }}">Clienti</a>
            <i class="bi bi-chevron-right" aria-hidden="true"></i>
            <span>{{ $customerDisplayName }}</span>
        </div>

        <div class="customer-doc-hero">
            <div class="customer-doc-hero__copy">
                <div class="public-title-row">
                    <span class="public-icon-badge" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6"/>
                            <path d="M14 14s-1-4-6-4-6 4-6 4 1 1 6 1 6-1 6-1"/>
                        </svg>
                    </span>
                    <div>
                        <p class="public-card__eyebrow">Scheda cliente</p>
                        <h1>{{ $customerDisplayName }}</h1>
                    </div>
                </div>

                <p class="customer-doc-hero__lead">
                    Vista unica del cliente con profilo, consensi e storico di ordini e prenotazioni. Il layout riprende la documentazione interna per favorire lettura rapida e priorita chiare sia su desktop sia su mobile.
                </p>

                <div class="customer-doc-chip-row">
                    <x-dashboard.state-pill :tone="$accountTone">{{ $accountLabels[$accountState] ?? ucfirst($accountState) }}</x-dashboard.state-pill>
                    <x-dashboard.state-pill :tone="$marketingTone">{{ $marketingLabels[$marketingState] ?? $marketingState }}</x-dashboard.state-pill>
                </div>

                <div class="customer-doc-actions">
                    <a class="public-button public-button--ghost" href="{{ route('admin.customers.index') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M1.146 4.854a.5.5 0 0 1 0-.708l4-4a.5.5 0 1 1 .708.708L2.707 4H12.5A2.5 2.5 0 0 1 15 6.5v8a.5.5 0 0 1-1 0v-8A1.5 1.5 0 0 0 12.5 5H2.707l3.147 3.146a.5.5 0 1 1-.708.708l-4-4z"/>
                        </svg>
                        Torna ai clienti
                    </a>

                    <a class="public-button public-button--solid" href="#storico-attivita">Vai allo storico</a>
                </div>

                @if ($profileNotice)
                    <div class="public-note customer-doc-note">
                        <strong>Attenzione profilo</strong>
                        {{ $profileNotice }}
                    </div>
                @endif
            </div>

            <div class="customer-doc-side">
                <article class="customer-doc-side-card">
                    <p class="public-card__eyebrow">Ultima attivita</p>
                    <strong>{{ $stats['latest_activity_at'] ? $stats['latest_activity_at']->format('d/m/Y H:i') : '-' }}</strong>
                    <p>Ultimo evento rilevato tra ordini, prenotazioni e dati cliente disponibili.</p>
                </article>

                <article class="customer-doc-side-card">
                    <p class="public-card__eyebrow">Contatti rapidi</p>
                    <div class="customer-doc-contact-list">
                        <a class="public-button public-button--ghost" href="mailto:{{ $customer->email }}">
                            {{ $customer->email }}
                        </a>
                        @if (!empty($customer->phone))
                            <a class="public-button public-button--ghost" href="tel:{{ $customer->phone }}">
                                {{ $customer->phone }}
                            </a>
                        @endif
                    </div>
                </article>
            </div>
        </div>

        <div class="customer-doc-kpi-grid">
            <article class="customer-doc-kpi-card">
                <span>Ordini</span>
                <strong>{{ $stats['orders_count'] }}</strong>
            </article>
            <article class="customer-doc-kpi-card">
                <span>Prenotazioni</span>
                <strong>{{ $stats['reservations_count'] }}</strong>
            </article>
            <article class="customer-doc-kpi-card">
                <span>Interazioni</span>
                <strong>{{ $stats['interactions_count'] }}</strong>
            </article>
            <article class="customer-doc-kpi-card">
                <span>Speso sul sito</span>
                <strong>EUR {{ number_format(($stats['total_spent_cents'] ?? 0) / 100, 2, ',', '.') }}</strong>
            </article>
        </div>
    </section>

    <div class="customer-doc-sections">
        <section class="public-panel">
            <div class="public-panel__header">
                <p class="public-panel__eyebrow">Anagrafica</p>
                <h2>Dati profilo</h2>
            </div>

            <div class="customer-doc-detail-grid">
                <article class="customer-doc-detail-card">
                    <span>Nome</span>
                    <strong>{{ $customer->name ?: '-' }}</strong>
                </article>
                <article class="customer-doc-detail-card">
                    <span>Cognome</span>
                    <strong>{{ $customer->surname ?: '-' }}</strong>
                </article>
                <article class="customer-doc-detail-card">
                    <span>Email</span>
                    <strong>{{ $customer->email ?: '-' }}</strong>
                </article>
                <article class="customer-doc-detail-card">
                    <span>Telefono</span>
                    <strong>{{ $customer->phone ?: '-' }}</strong>
                </article>
                <article class="customer-doc-detail-card">
                    <span>Sesso</span>
                    <strong>{{ $customer->gender ?: '-' }}</strong>
                </article>
                <article class="customer-doc-detail-card">
                    <span>Eta</span>
                    <strong>{{ $customer->age ?: '-' }}</strong>
                </article>
                <article class="customer-doc-detail-card">
                    <span>Stato account</span>
                    <strong>{{ $accountLabels[$accountState] ?? ucfirst($accountState) }}</strong>
                </article>
                <article class="customer-doc-detail-card">
                    <span>Stato marketing</span>
                    <strong>{{ $marketingLabels[$marketingState] ?? $marketingState }}</strong>
                </article>
                <article class="customer-doc-detail-card">
                    <span>Registrato il</span>
                    <strong>{{ $customer->registered_at ? \Carbon\Carbon::parse($customer->registered_at)->format('d/m/Y H:i') : '-' }}</strong>
                </article>
                <article class="customer-doc-detail-card">
                    <span>Email verificata il</span>
                    <strong>{{ $customer->email_verified_at ? \Carbon\Carbon::parse($customer->email_verified_at)->format('d/m/Y H:i') : '-' }}</strong>
                </article>
                <article class="customer-doc-detail-card">
                    <span>Creato il</span>
                    <strong>{{ $customer->created_at ? \Carbon\Carbon::parse($customer->created_at)->format('d/m/Y H:i') : '-' }}</strong>
                </article>
            </div>
        </section>

        <section class="public-panel">
            <div class="public-panel__header">
                <p class="public-panel__eyebrow">Privacy</p>
                <h2>Consensi e testi</h2>
            </div>

            <div class="customer-doc-detail-grid">
                <article class="customer-doc-detail-card">
                    <span>Marketing</span>
                    <strong>{{ $customer->marketing_consent_at ? 'Attivo' : 'Disattivo' }}</strong>
                    <small>
                        <x-dashboard.state-pill :tone="$marketingConsentTone">{{ $customer->marketing_consent_at ? 'Registrato' : 'Assente' }}</x-dashboard.state-pill>
                    </small>
                    <small>{{ $customer->marketing_consent_at ? \Carbon\Carbon::parse($customer->marketing_consent_at)->format('d/m/Y H:i') : 'Mai registrato' }}</small>
                </article>
                <article class="customer-doc-detail-card">
                    <span>Profilazione</span>
                    <strong>{{ $customer->profiling_consent_at ? 'Attiva' : 'Disattiva' }}</strong>
                    <small>
                        <x-dashboard.state-pill :tone="$profilingConsentTone">{{ $customer->profiling_consent_at ? 'Registrata' : 'Assente' }}</x-dashboard.state-pill>
                    </small>
                    <small>{{ $customer->profiling_consent_at ? \Carbon\Carbon::parse($customer->profiling_consent_at)->format('d/m/Y H:i') : 'Mai registrata' }}</small>
                </article>
                <article class="customer-doc-detail-card">
                    <span>Testo marketing</span>
                    <strong>{{ $profileSettings['marketing_consent_text'] ?? '-' }}</strong>
                </article>
                <article class="customer-doc-detail-card">
                    <span>Testo profilazione</span>
                    <strong>{{ $profileSettings['profiling_consent_text'] ?? '-' }}</strong>
                </article>
            </div>
        </section>
    </div>

    <section class="public-panel">
        <div class="public-panel__header">
            <p class="public-panel__eyebrow">Profilazione</p>
            <h2>Questionario profilo</h2>
        </div>

        @if (!empty($questionAnswers))
            <div class="customer-doc-answer-list">
                @foreach ($questionAnswers as $answer)
                    <article class="customer-doc-answer">
                        <strong>{{ $answer['label'] }}</strong>
                        <span>{{ is_array($answer['value']) ? implode(', ', $answer['value']) : $answer['value'] }}</span>
                    </article>
                @endforeach
            </div>
        @else
            <p class="customer-doc-empty">Nessuna risposta salvata nel questionario profilo.</p>
        @endif
    </section>

    <div class="customer-doc-activity-grid" id="storico-attivita">
        <section class="public-panel">
            <div class="public-panel__header">
                <p class="public-panel__eyebrow">Storico ordini</p>
                <h2>Ordini collegati al cliente</h2>
            </div>

            @if ($orders->isNotEmpty())
                <div class="customer-doc-activity-list">
                    @foreach ($orders as $order)
                        @php $status = $orderStatuses[$order->status] ?? ['label' => 'Aggiornamento', 'tone' => 'warning']; @endphp
                        <article class="customer-doc-activity-card">
                            <div class="customer-doc-activity-head">
                                <div>
                                    <span class="customer-doc-activity-label">Ordine</span>
                                    <strong>#O{{ $order->id }}</strong>
                                    <p>{{ $order->activity_at ? $order->activity_at->format('d/m/Y H:i') : '-' }}</p>
                                </div>
                                <x-dashboard.state-pill :tone="$status['tone']">{{ $status['label'] }}</x-dashboard.state-pill>
                            </div>

                            <div class="customer-doc-activity-meta">
                                <span class="customer-doc-pill">{{ $order->comune ? 'Domicilio' : 'Asporto' }}</span>
                                <span class="customer-doc-pill">EUR {{ number_format(($order->tot_price ?? 0) / 100, 2, ',', '.') }}</span>
                                <span class="customer-doc-pill">Marketing storico: {{ $order->news_letter ? 'si' : 'no' }}</span>
                            </div>

                            <div class="customer-doc-activity-actions">
                                <p>Ordine legato al profilo per email o record cliente.</p>
                                <a class="public-button public-button--ghost" href="{{ route('admin.orders.show', $order->id) }}">Apri ordine</a>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <p class="customer-doc-empty">Nessun ordine trovato.</p>
            @endif
        </section>

        <section class="public-panel">
            <div class="public-panel__header">
                <p class="public-panel__eyebrow">Storico prenotazioni</p>
                <h2>Prenotazioni collegate al cliente</h2>
            </div>

            @if ($reservations->isNotEmpty())
                <div class="customer-doc-activity-list">
                    @foreach ($reservations as $reservation)
                        @php
                            $status = $reservationStatuses[$reservation->status] ?? ['label' => 'Aggiornamento', 'tone' => 'warning'];
                            $guests = json_decode($reservation->n_person, true);
                            $totalGuests = (int) ($guests['adult'] ?? 0) + (int) ($guests['child'] ?? 0);
                        @endphp
                        <article class="customer-doc-activity-card">
                            <div class="customer-doc-activity-head">
                                <div>
                                    <span class="customer-doc-activity-label">Prenotazione</span>
                                    <strong>#R{{ $reservation->id }}</strong>
                                    <p>{{ $reservation->activity_at ? $reservation->activity_at->format('d/m/Y H:i') : '-' }}</p>
                                </div>
                                <x-dashboard.state-pill :tone="$status['tone']">{{ $status['label'] }}</x-dashboard.state-pill>
                            </div>

                            <div class="customer-doc-activity-meta">
                                <span class="customer-doc-pill">Ospiti: {{ $totalGuests }}</span>
                                <span class="customer-doc-pill">{{ $reservation->sala ?: 'Sala non indicata' }}</span>
                                <span class="customer-doc-pill">Marketing storico: {{ $reservation->news_letter ? 'si' : 'no' }}</span>
                            </div>

                            <div class="customer-doc-activity-actions">
                                <p>Prenotazione rintracciata dal profilo cliente o dalla mail storica.</p>
                                <a class="public-button public-button--ghost" href="{{ route('admin.reservations.show', $reservation->id) }}">Apri prenotazione</a>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <p class="customer-doc-empty">Nessuna prenotazione trovata.</p>
            @endif
        </section>
    </div>
</div>
@endsection
